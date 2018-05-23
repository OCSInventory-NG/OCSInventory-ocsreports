<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

 /**
  * This class implement the base behavior for search :
  * - Query generation
  * - Data management
  * - Return structure
  * Used in new search
  */
 class Search
 {

    const SESS_FIELDS = "fields";
    const SESS_VALUES = "value";
    const SESS_OPERATOR = "operator";

    const DB_TEXT = "text";
    const DB_INT = "int";
    const DB_VARCHAR = "varchar";
    const DB_DATETIME = "datetime";

    const MULTIPLE_DONE = "DONE";

    public $fieldsList = [];
    public $defaultFields = [
        "hardware.ID",
        "hardware.DEVICEID",
        "hardware.NAME",
        "hardware.WORKGROUP",
        "hardware.OSNAME",
    ];

    public $baseQuery = "SELECT";
    public $baseLsitIdQuery = "SELECT hardware.ID";
    public $searchQuery = "FROM hardware ";
    public $queryArgs = [];
    public $columnsQueryConditions = "";
    public $values_cache_sql = [];

    private $translationSearch;
    private $databaseSearch;
    private $accountinfoSearch;

    /**
     * Excluded columns that won't be visible
     */
    private $excludedVisuColumns = [
        "ID",
        "HARDWARE_ID"
    ];

    /**
     * Operator list
     */
    private $operatorList = [
        "EQUAL",
        "MORE",
        "LESS",
        "LIKE",
        "DIFFERENT",
    ];

    /**
     * Multiples fields search
     */
    private $multipleFieldsSearch = [];

    /**
     * Final query and args used for multicrits
     */
    private $finalQuery;
    private $finalArgs;

    /**
     * Constructor
     *
     * @param TranslationSearch $translationSearch
     * @param DatabaseSearch $databaseSearch
     * @param AccountinfoSearch $accountinfoSearch
     */
    function __construct($translationSearch, $databaseSearch, $accountinfoSearch)
    {

        $this->translationSearch = $translationSearch;
        $this->databaseSearch = $databaseSearch;
        $this->accountinfoSearch = $accountinfoSearch;

        if ($_SESSION['OCS']['profile']->getConfigValue('DELETE_COMPUTERS') == "YES") {
            $this->fieldsList['CHECK'] = 'hardwareID';
        }

        // Translation for default fields
        $defaultFieldsArray = ['CHECK' => 'CHECK'];
        foreach ($this->defaultFields as $value) {
            $translation = $this->translationSearch->getTranslationForListField($value);
            if($value = "hardware.NAME"){
                $defaultFieldsArray["NAME"] = $value;
            }
            $defaultFieldsArray[$translation] = $value;
        }
        $this->defaultFields = $defaultFieldsArray;
    }

    /**
     * Add sessions infos when search criteria is added
     *
     * @param Array $postData
     * @return void
     */
    public function addSessionsInfos($postData)
    {
        $_SESSION['OCS']['multi_search'][$postData['table_select']][uniqid()] = [
            self::SESS_FIELDS => $postData['columns_select'],
            self::SESS_VALUES => null,
            self::SESS_OPERATOR => null,
        ];
    }

    /**
     * Update sessions infos when changing search criteria
     *
     * @param Array $postData
     * @return void
     */
    public function updateSessionsInfos($postData)
    {
        foreach ($postData as $key => $value) {
            $keyExploded = explode("_", $key);
            if(count($keyExploded) > 1 && !is_null($_SESSION['OCS']['multi_search'][$keyExploded[1]])){
                if ($keyExploded[2] == self::SESS_OPERATOR) {
                    $_SESSION['OCS']['multi_search'][$keyExploded[1]][$keyExploded[0]][self::SESS_OPERATOR] = $value;
                } else {
                    $_SESSION['OCS']['multi_search'][$keyExploded[1]][$keyExploded[0]][self::SESS_VALUES] = $value;
                }
            }
        }
    }

    /**
     * Remove sessions data when removing search field
     *
     * @param String $rowReference
     * @return void
     */
    public function removeSessionsInfos($rowReference){
        $explodedRef = explode("_", $rowReference);
        unset($_SESSION['OCS']['multi_search'][$explodedRef[1]][$explodedRef[0]]);
        if(empty($_SESSION['OCS']['multi_search'][$explodedRef[1]])){
            unset($_SESSION['OCS']['multi_search'][$explodedRef[1]]);
        }

    }

    /**
     * Get the type of the searched field
     *
     * @param String $tablename
     * @param String $fieldsname
     * @return void
     */
    public function getSearchedFieldType($tablename, $fieldsname)
    {
        $tableFields = $this->databaseSearch->getColumnsList($tablename);
        return $tableFields[$fieldsname][DatabaseSearch::TYPE];
    }

    /**
     * Generate operator uniq id for displaying
     *
     * @param String $uniqid
     * @param String $tableName
     * @return void
     */
    public function getOperatorUniqId($uniqid, $tableName)
    {
        return $uniqid."_".$tableName."_".self::SESS_OPERATOR;
    }

    /**
     * Generate feilds uniq id for displaying
     *
     * @param String $uniqid
     * @param String $tableName
     * @return void
     */
    public function getFieldUniqId($uniqid, $tableName)
    {
        return $uniqid."_".$tableName."_".self::SESS_FIELDS;
    }

    /**
     * Generate search query (operator and values)
     *
     * @param Array $sessData
     * @return void
     */
    public function generateSearchQuery($sessData){
        $this->pushBaseQueryForTable("hardware", null);
        foreach ($sessData as $tableName => $searchInfos) {
            if($tableName != "hardware"){
                $this->pushBaseQueryForTable($tableName, $sessData);
            }
        }
        foreach ($sessData as $tableName => $searchInfos) {

            if($tableName != "hardware"){
                // Generate union
                $this->searchQuery .= "INNER JOIN $tableName on hardware.id = $tableName.hardware_id ";
            }

            foreach ($searchInfos as $index => $value) {
                if(!array_key_exists($tableName.$value[self::SESS_FIELDS], $this->multipleFieldsSearch)){
                    $this->multipleFieldsSearch[$tableName.$value[self::SESS_FIELDS]] = 1;
                }else{
                    $this->multipleFieldsSearch[$tableName.$value[self::SESS_FIELDS]] += 1;
                }
            }

            foreach ($searchInfos as $index => $value) {
                if( $this->multipleFieldsSearch[$tableName.$value[self::SESS_FIELDS]] > 1 ){
                    $operator = "OR";
                }else{
                    $operator = "AND";
                }
                // Generate condition
                $this->getOperatorSign($value);
                $this->columnsQueryConditions .= " %s.%s %s '%s' $operator";
                $this->queryArgs[] = $tableName;
                $this->queryArgs[] = $value[self::SESS_FIELDS];
                $this->queryArgs[] = $value[self::SESS_OPERATOR];
                $this->queryArgs[] = $value[self::SESS_VALUES];
            }

        }
        $this->columnsQueryConditions = "WHERE".$this->columnsQueryConditions;
        $this->columnsQueryConditions = substr($this->columnsQueryConditions, 0, -3);
        $this->columnsQueryConditions .= "GROUP BY hardware.id";
        $this->baseQuery = substr($this->baseQuery, 0, -1);
    }

    /**
     * Generate select query for table using session variables generated from the search
     *
     * @param String $tableName
     * @param Array $sessData
     * @return void
     */
    private function pushBaseQueryForTable($tableName, $sessData = null){
        foreach($this->databaseSearch->getColumnsList($tableName) as $index => $fieldsInfos){
            $generatedId = $tableName.".".$fieldsInfos['Field'];
            $selectAs = $tableName.$fieldsInfos['Field'];
            $this->baseQuery .= " %s.%s AS ".$selectAs." ,";
            $this->queryArgs[] = $tableName;
            $this->queryArgs[] = $fieldsInfos['Field'];

            if($generatedId == 'hardware.NAME'){
                $this->fieldsList["NAME"] = $selectAs;
            } elseif($generatedId != 'hardware.ID') {
                $this->fieldsList[$this->translationSearch->getTranslationForListField($generatedId)] = $selectAs;
            }

            if($sessData != null){
                if($sessData[$tableName][key($sessData[$tableName])][self::SESS_FIELDS] == $fieldsInfos['Field']){
                    $this->defaultFields[$this->translationSearch->getTranslationForListField($generatedId)] = $generatedId;
                }
            }

        }
    }

    /**
     * Depending on selected operator
     * Return compliant operator for database query
     *
     * @param Array $valueArray
     * @return void
     */
    public function getOperatorSign(&$valueArray){
        switch ($valueArray[self::SESS_OPERATOR]) {
            case 'EQUAL':
                $valueArray[self::SESS_OPERATOR] = "=";
                break;
            case 'MORE':
                $valueArray[self::SESS_OPERATOR] = ">";
                break;
            case 'LESS':
                $valueArray[self::SESS_OPERATOR] = "<";
                break;
            case 'LIKE':
                $valueArray[self::SESS_OPERATOR] = "LIKE";
                $valueArray[self::SESS_VALUES] = "%".$valueArray[self::SESS_VALUES]."%";
                break;
            case 'DIFFERENT':
                $valueArray[self::SESS_OPERATOR] = "NOT LIKE";
                $valueArray[self::SESS_VALUES] = "%".$valueArray[self::SESS_VALUES]."%";
                break;
            default:
                $valueArray[self::SESS_OPERATOR] = "=";
                break;
        }
    }

    /**
     * Generate HTML select options for operators
     *
     * @param String $defaultValue
     * @return void
     */
    public function getSelectOptionForOperators($defaultValue)
    {

        $html = "";

        foreach ($this->operatorList as $value) {
            $trValue = $this->translationSearch->getTranslationForOperator($value);
            if ($defaultValue == $value) {
                $html .= "<option selected value=".$value." >".$trValue."</option>";
            } else {
                $html .= "<option value=".$value." >".$trValue."</option>";
            }
        }

        return $html;
    }

    /**
     * Generate HTML select options for tables select
     *
     * @param String $defautValue
     * @return void
     */
    public function getSelectOptionForTables($defautValue = null)
    {
        $html = "<option>----------</option>";
        foreach ($this->databaseSearch->getTablesList() as $tableName) {
            $translation = $this->translationSearch->getTranslationFor($tableName);
            $sortTable[$tableName] .= $translation;
        }
        asort($sortTable);
        foreach ($sortTable as $key => $value){
            if ($defautValue == $key) {
                $html .= "<option selected value=".$key." >".$value."</option>";
            } else {
                $html .= "<option value=".$key." >".$value."</option>";
            }

        }
        return $html;
    }

    /**
     * Generate HTML select options for columns select
     *
     * @param String $tableName
     * @return void
     */
    public function getSelectOptionForColumns($tableName = null)
    {
        $html = "";
        if($tableName == "accountinfo"){
          $accountinfoList = new AccountinfoSearch();
          $accountFields = $accountinfoList->getAccountInfosList();
          foreach ($accountFields['COMPUTERS'] as $index => $fieldsInfos) {
              if(!in_array($fieldsIndefaultTablefos[DatabaseSearch::FIELD], $this->excludedVisuColumns)){
                  $trField = $fieldsInfos;
                  $sortColumn[$index] .= $trField;
              }
          }
        }else{
          foreach ($this->databaseSearch->getColumnsList($tableName) as $index => $fieldsInfos) {
              if(!in_array($fieldsIndefaultTablefos[DatabaseSearch::FIELD], $this->excludedVisuColumns)){
                  $trField = $this->translationSearch->getTranslationFor($fieldsInfos[DatabaseSearch::FIELD]);
                  $sortColumn[$fieldsInfos[DatabaseSearch::FIELD]] .= $trField;
              }
          }
        }
        asort($sortColumn);
        foreach ($sortColumn as $key => $value){
            if(!in_array($fieldsIndefaultTablefos[DatabaseSearch::FIELD], $this->excludedVisuColumns)){
                $html .= "<option value=".$key." >".$value."</option>";
            }
        }
        return $html;
    }

    /**
     * Generate HTML fields depending on database field type
     *
     * @param String $uniqid
     * @param Array $fieldsInfos
     * @param String $tableName
     * @return String HTML
     */
    public function returnFieldHtml($uniqid, $fieldsInfos, $tableName)
    {

        global $l;

        $fieldId = $this->getFieldUniqId($uniqid, $tableName);
        $type = $this->getSearchedFieldType($tableName, $fieldsInfos[self::SESS_FIELDS]);
        $html = "";

        switch ($type) {
            case self::DB_VARCHAR:
                $html = '<input class="form-control" type="text" name="'.$fieldId.'" value="'.$fieldsInfos[self::SESS_VALUES].'">';
                break;

            case self::DB_TEXT:
                $html = '<input class="form-control" type="text" name="'.$fieldId.'" value="'.$fieldsInfos[self::SESS_VALUES].'">';
                break;

            case self::DB_INT:
                $html = '<input class="form-control" type="number" name="'.$fieldId.'" value="'.$fieldsInfos[self::SESS_VALUES].'">';
                break;

            case self::DB_DATETIME:
                $html = '<input class="form-control" class="form-control" type="text" name="'.$fieldId.'" value="'.$fieldsInfos[self::SESS_VALUES].'">';
                $html = '
                <div class="input-group date form_datetime">
                    <input type="text" class="form-control" name="'.$fieldId.'" value="'.$fieldsInfos[self::SESS_VALUES].'" />
                    <span class="input-group-addon">
                        '.calendars($fieldId, $l->g(1270)).'
                    </span>
                </div>';
                break;

            default:
                $html = '<input class="form-control" type="text" name="'.$fieldId.'" value="'.$fieldsInfos[self::SESS_VALUES].'">';
                break;
        }

        return $html;
    }

    /**
     * Create sql for dynamic group
     * @param  Array $values
     * @return String
     */
    public function create_sql_cache($values){
        $cache_sql = "SELECT DISTINCT hardware.ID FROM hardware ";
        $i =0;
        foreach ($values as $key=>$value){
           foreach ($value as $table => $field) {
               $this->values_cache_sql[$key] = $field;
               if($key != 'hardware'){
                 $cache_sql .= "INNER JOIN ".$key." on hardware.id = ".$key.".hardware_id ";
               }
           }
           $i++;
        }
        $cache_sql .= "WHERE ";
        $p=1;
        foreach ($this->values_cache_sql as $table=>$values){
           $cache_sql .= $table.".".$values['fields']." ";
           if($values['operator'] == 'LIKE'){
              $cache_sql .= $values['operator']." '%".$values['value']."%' ";
           }elseif($values['operator'] == 'DIFFERENT'){
              $cache_sql .= "NOT LIKE '%".$values['value']."%' ";
           }elseif($values['operator'] == 'EQUAL'){
             $cache_sql .= "= '".$values['value']."' ";
           }elseif($values['operator'] == 'LESS'){
             $cache_sql .= "< ".$values['value']." ";
           }elseif($values['operator'] == 'MORE'){
             $cache_sql .= "> ".$values['value']." ";
           }
           if($p != $i){
             $cache_sql .= "AND ";
           }
           $p++;
        }
        return $cache_sql;
    }
 }
