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
    const SESS_COMPARATOR = "comparator";

    const DB_TEXT = "text";
    const DB_INT = "int";
    const DB_VARCHAR = "varchar";
    const DB_DATETIME = "datetime";

    const MULTIPLE_DONE = "DONE";

    private $type;

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
        "ISNULL",
    ];

    /**
     * Comparator list
     */
    private $comparatorList = [
        "AND",
        "OR"
    ];

    /**
     * Multiples fields search
     */
    private $multipleFieldsSearch = [];
    private $multipleFieldsSearchCache = [];

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
            self::SESS_COMPARATOR => null,
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
                } elseif($keyExploded[2] == self::SESS_FIELDS && $_SESSION['OCS']['multi_search'][$keyExploded[1]][$keyExploded[0]][self::SESS_OPERATOR] != 'ISNULL') {
                    $_SESSION['OCS']['multi_search'][$keyExploded[1]][$keyExploded[0]][self::SESS_VALUES] = $value;
                }elseif($keyExploded[2] == self::SESS_COMPARATOR){
                  $_SESSION['OCS']['multi_search'][$keyExploded[1]][$keyExploded[0]][self::SESS_COMPARATOR] = $value;
                }
            }elseif(count($keyExploded) == 4){
                $keyExplodedBis = $keyExploded[1]."_".$keyExploded[2];
                if(!is_null($_SESSION['OCS']['multi_search'][$keyExplodedBis])){
                  if ($keyExploded[3] == self::SESS_OPERATOR) {
                      $_SESSION['OCS']['multi_search'][$keyExplodedBis][$keyExploded[0]][self::SESS_OPERATOR] = $value;
                  } elseif($keyExploded[3] == self::SESS_COMPARATOR){
                    $_SESSION['OCS']['multi_search'][$keyExploded[1]][$keyExploded[0]][self::SESS_COMPARATOR] = $value;
                  } else {
                      $_SESSION['OCS']['multi_search'][$keyExplodedBis][$keyExploded[0]][self::SESS_VALUES] = $value;
                  }
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
        if(empty($explodedRef[2])){
            unset($_SESSION['OCS']['multi_search'][$explodedRef[1]][$explodedRef[0]]);
        }else{
            $exploded = $explodedRef[1]."_".$explodedRef[2];
            unset($_SESSION['OCS']['multi_search'][$exploded][$explodedRef[0]]);
            if(empty($_SESSION['OCS']['multi_search'][$exploded])){
                unset($_SESSION['OCS']['multi_search'][$exploded]);
            }
        }
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
     * Generate comparator uniq id for displaying
     *
     * @param String $uniqid
     * @param String $tableName
     * @return void
     */
    public function getComparatorUniqId($uniqid, $tableName)
    {
        return $uniqid."_".$tableName."_".self::SESS_COMPARATOR;
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
        if(!isset($sessData['accountinfo'])) $sessData['accountinfo'] = array();
        foreach ($sessData as $tableName => $searchInfos) {
            if($tableName != "hardware"){
                $this->pushBaseQueryForTable($tableName, $sessData);
            }
        }
        $i = 0;
        $p = 0;
        foreach ($sessData as $tableName => $searchInfos) {

            if($tableName != "hardware"){
                // Generate union
                $this->searchQuery .= "INNER JOIN $tableName on hardware.id = $tableName.hardware_id ";
            }

            foreach ($searchInfos as $index => $value) {

                if($value['comparator'] != null){
                    $operator[] = $value['comparator'];
                }elseif($i != 0 && $value['comparator'] == null){
                    $operator[] = "AND";
                }else{
                    $operator[] = "";
                }
                $i++;
            }

            foreach ($searchInfos as $index => $value) {
                  $values[] = $value;
            }

            foreach ($searchInfos as $index => $value) {
                $open="";
                $close="";
                // Generate condition
                $this->getOperatorSign($value);

                if($p == 0 && $operator[$p+1] == 'OR'){
                    $open = "(";
                }if($operator[$p] =='OR' && $operator[$p+1] !='OR'){
                    $close=")";
                }if($p != 0 && $operator[$p] !='OR' && $operator[$p+1] =='OR'){
                    $open = "(";
                }

                if($value[self::SESS_OPERATOR] == 'IS NULL'){
                  $this->columnsQueryConditions .= "$operator[$p] $open%s.%s %s$close ";
                  $this->queryArgs[] = $tableName;
                  $this->queryArgs[] = $value[self::SESS_FIELDS];
                  $this->queryArgs[] = $value[self::SESS_OPERATOR];
                }else if($value[self::SESS_FIELDS] == 'LASTCOME' || $value[self::SESS_FIELDS] == 'LASTDATE'){
                  $this->columnsQueryConditions .= "$operator[$p] $open%s.%s %s str_to_date('%s', '%s')$close ";
                  $this->queryArgs[] = $tableName;
                  $this->queryArgs[] = $value[self::SESS_FIELDS];
                  $this->queryArgs[] = $value[self::SESS_OPERATOR];
                  $this->queryArgs[] = $value[self::SESS_VALUES];
                  global $l;
                  $this->queryArgs[] = $l->g(269);
                }else{
                  $this->columnsQueryConditions .= "$operator[$p] $open%s.%s %s '%s'$close ";
                  $this->queryArgs[] = $tableName;
                  $this->queryArgs[] = $value[self::SESS_FIELDS];
                  $this->queryArgs[] = $value[self::SESS_OPERATOR];
                  $this->queryArgs[] = $value[self::SESS_VALUES];
                }
                $p++;
            }
        }
        $this->columnsQueryConditions = "WHERE".$this->columnsQueryConditions;

        // has tag restrictions?
        if(!empty($_SESSION['OCS']['TAGS']))
        {
            $tags = $_SESSION['OCS']['TAGS'];
            foreach($tags as $k => $v)
                $tags[$k] = "'".mysqli_real_escape_string($_SESSION['OCS']["readServer"], $v)."'";
            $tags = implode(', ', $tags);
            $this->columnsQueryConditions .= " AND accountinfo.TAG IN ($tags)";
        }

        $this->columnsQueryConditions .= " GROUP BY hardware.id";
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

            /*if($sessData != null){
                if($sessData[$tableName][key($sessData[$tableName])][self::SESS_FIELDS] == $fieldsInfos['Field']){
                    $this->defaultFields[$this->translationSearch->getTranslationForListField($generatedId)] = $generatedId;
                }
            }*/

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
            case 'ISNULL':
                $valueArray[self::SESS_OPERATOR] = "IS NULL";
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
        $this->type = $this->getSearchedFieldType($tableName, $fieldsInfos[self::SESS_FIELDS]);
        $html = "";
        if($fieldsInfos[self::SESS_OPERATOR]== 'ISNULL'){
          $attr = 'disabled';
        }else{
          $attr = '';
        }

        switch ($this->type) {
            case self::DB_VARCHAR:
                $html = '<input class="form-control" type="text" name="'.$fieldId.'" id="'.$fieldId.'" value="'.$fieldsInfos[self::SESS_VALUES].'" '.$attr.'>';
                break;

            case self::DB_TEXT:
                $html = '<input class="form-control" type="text" name="'.$fieldId.'" id="'.$fieldId.'" value="'.$fieldsInfos[self::SESS_VALUES].'" '.$attr.'>';
                break;

            case self::DB_INT:
                $html = '<input class="form-control" type="number" name="'.$fieldId.'" id="'.$fieldId.'" value="'.$fieldsInfos[self::SESS_VALUES].'" '.$attr.'>';
                break;

            case self::DB_DATETIME:
                $html = '<input class="form-control" class="form-control" type="text" name="'.$fieldId.'" id="'.$fieldId.'" value="'.$fieldsInfos[self::SESS_VALUES].'" '.$attr.'>';
                $html = '
                <div class="input-group date form_datetime">
                    <input type="text" class="form-control" name="'.$fieldId.'" id="'.$fieldId.'" value="'.$fieldsInfos[self::SESS_VALUES].'" '.$attr.'/>
                    <span class="input-group-addon">
                        '.calendars($fieldId, $l->g(1270)).'
                    </span>
                </div>';
                break;

            default:
                $html = '<input class="form-control" type="text" name="'.$fieldId.'" id="'.$fieldId.'" value="'.$fieldsInfos[self::SESS_VALUES].'" '.$attr.'>';
                break;
        }

        return $html;
    }

    public function returnFieldHtmlAndOr($uniqid, $fieldsInfos, $infos, $tableName, $defaultValue = null)
    {
        global $l;
        $i = 0;

        foreach ($infos as $id => $value){
            if($value['fields'] == $fieldsInfos['fields']){
              $i++;
            }
        }

        $fieldId = $this->getFieldUniqId($uniqid, $tableName);
        $html = "";

        if($i > 1){
          $html = "<select class='form-control' name='".$this->getComparatorUniqId($uniqid, $tableName)."' id='".$this->getComparatorUniqId($uniqid, $tableName)."'>";
          foreach ($this->comparatorList as $value) {
              $trValue = $this->translationSearch->getTranslationForComparator($value);
              if ($defaultValue == $value) {
                  $html .= "<option selected value=".$value." >".$trValue."</option>";
              } else {
                  $html .= "<option value=".$value." >".$trValue."</option>";
              }
          }
          $html .= "</select>";
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
               $i++;
               $this->values_cache_sql[$key][$table] = $field;
               if($key != 'hardware'){
                 if(!array_key_exists($key, $this->multipleFieldsSearchCache)){
                     $this->multipleFieldsSearchCache[$key] = 1;
                 }else{
                     $this->multipleFieldsSearchCache[$key] += 1;
                 }
                 if( $this->multipleFieldsSearchCache[$key] == 1 ){
                     $cache_sql .= "INNER JOIN ".$key." on hardware.id = ".$key.".hardware_id ";
                 }
               }
           }
        }

        $cache_sql .= "WHERE ";

        $ind=0;
        foreach ($values as $index => $value) {
          foreach($value as $key => $compar){
            if($compar['comparator'] != null){
                $operator[] = $compar['comparator'];
            }elseif($ind != 0 && $compar['comparator'] == null){
                $operator[] = "AND";
            }else{
                $operator[] = "";
            }
            $ind++;
          }
        }

        $p=0;
        $in=0;
        foreach ($this->values_cache_sql as $table=>$value){
           foreach ($value as $key => $values) {

             $open="";
             $close="";

             if($in == 0 && $operator[$in+1] == 'OR'){
                 $open = "(";
             }if($operator[$in] =='OR' && $operator[$in+1] !='OR'){
                 $close=")";
             }if($p != 0 && $operator[$in] !='OR' && $operator[$in+1] =='OR'){
                 $open = "(";
             }
             $p++;
             $cache_sql .= $operator[$in]." ".$open.$table.".".$values['fields']." ";
             if($values['operator'] == 'LIKE'){
                $cache_sql .= $values['operator']." '%".$values['value']."%'".$close;
             }elseif($values['operator'] == 'DIFFERENT'){
                $cache_sql .= "NOT LIKE '%".$values['value']."%'".$close;
             }elseif($values['operator'] == 'EQUAL'){
               $cache_sql .= "= '".$values['value']."'".$close;
             }elseif($values['operator'] == 'LESS'){
               $cache_sql .= "< ".$values['value'].$close;
             }elseif($values['operator'] == 'MORE'){
               $cache_sql .= "> ".$values['value'].$close;
             }
             $in++;
           }
        }
        return $cache_sql;
    }

    /**
     * [link_multi description]
     * @param  string $fields [description]
     * @param  string $value  [description]
     * @param  string $option [description]
     * @return [type]         [description]
     */
    public function link_multi($fields, $value, $option = ""){
        switch($fields){
          case 'allsoft':
            if(!array_key_exists('allsoft',$_SESSION['OCS']['multi_search']['softwares'])){
                $_SESSION['OCS']['multi_search'] = array();
                $_SESSION['OCS']['multi_search']['softwares']['allsoft'] = [
                    'fields' => 'NAME',
                    'value' => $value,
                    'operator' => 'EQUAL',
                ];
            }
            break;

          case 'ipdiscover1':
            if(!array_key_exists('ipdiscover1',$_SESSION['OCS']['multi_search']['networks'])){
                $_SESSION['OCS']['multi_search'] = array();
                $_SESSION['OCS']['multi_search']['networks']['ipdiscover1'] = [
                    'fields' => 'IPSUBNET',
                    'value' => $value,
                    'operator' => 'EQUAL',
                ];
                $_SESSION['OCS']['multi_search']['devices']['ipdiscover1'] = [
                    'fields' => 'NAME',
                    'value' => 'IPDISCOVER',
                    'operator' => 'EQUAL',
                ];
                $_SESSION['OCS']['multi_search']['devices']['ipdiscover2'] = [
                    'fields' => 'IVALUE',
                    'value' => '1',
                    'operator' => 'EQUAL',
                ];
                $_SESSION['OCS']['multi_search']['devices']['ipdiscover3'] = [
                    'fields' => 'IVALUE',
                    'value' => '2',
                    'operator' => 'EQUAL',
                    'comparator' => 'OR',
                ];

                $_SESSION['OCS']['multi_search']['devices']['ipdiscover4'] = [
                    'fields' => 'TVALUE',
                    'value' => $value,
                    'operator' => 'EQUAL',
                ];
            }
            break;

          case 'stat':
            {
              $_SESSION['OCS']['multi_search'] = array();
              $_SESSION['OCS']['multi_search']['devices']['stat'] = [
                  'fields' => 'NAME',
                  'value' => 'DOWNLOAD',
                  'operator' => 'EQUAL',
              ];

              $i = 0;
              foreach($option['idPackage'] as $key =>$value){
                if($i == 0){
                  $comparator = 'AND';
                }else{
                  $comparator = 'OR';
                }
                $_SESSION['OCS']['multi_search']['devices']['stat'.$key] = [
                    'fields' => 'IVALUE',
                    'value' => $value,
                    'operator' => 'EQUAL',
                    'comparator' => $comparator
                ];
                $i++;
              }

              $comparator = 'AND';

              if($option['stat'] == 'WAITING')
              {
                $value_stat = '';
                $operator_stat = 'ISNULL';
              }
              else if($option['stat'] == 'ERRORS')
              {
                $value_stat = 'EXIT_CODE';
                $operator_stat = 'LIKE';

                $_SESSION['OCS']['multi_search']['devices']['stattvalue2'] = [
                    'fields' => 'TVALUE',
                    'value' => $value_stat,
                    'operator' => $operator_stat,
                    'comparator' => $comparator
                ];

                $value_stat = 'ERR';
                $comparator = 'OR';
              }
              else
              {
                $value_stat = $option['stat'];
                $operator_stat = 'EQUAL';
              }

              $_SESSION['OCS']['multi_search']['devices']['stattvalue'] = [
                  'fields' => 'TVALUE',
                  'value' => $value_stat,
                  'operator' => $operator_stat,
                  'comparator' => $comparator
              ];
            }
            break;

          default :
            break;
        }
    }

    /**
     * [link_index description]
     * @param  string $fields [description]
     * @param  string $comp   [description]
     * @param  string $value  [description]
     * @return [type]         [description]
     */
    public function link_index($fields, $comp = "", $value){
      $field = explode("-", $fields) ;

      if($comp== 'small') { $operator = 'LESS'; }
      elseif($comp == 'tall') { $operator = 'MORE'; }
      elseif($comp == 'exact') { $operator = 'EQUAL'; }

      if(empty($field[2])){
        if(strpos($field[0], 'HARDWARE') !== false){
          if(!array_key_exists('HARDWARE-'.$field[1].$comp.$value,$_SESSION['OCS']['multi_search']['hardware'])){
              $_SESSION['OCS']['multi_search'] = array();
              $_SESSION['OCS']['multi_search']['hardware']['HARDWARE-'.$field[1].$comp.preg_replace("/_/","",$value)] = [
                  'fields' => $field[1],
                  'value' => $value,
                  'operator' => $operator,
              ];
          }
        }elseif(strpos($field[0], 'ACCOUNTINFO') !== false){
          if(!array_key_exists('ACCOUNTINFO-'.$field[1].$comp.$value,$_SESSION['OCS']['multi_search']['accountinfo'])){
              $_SESSION['OCS']['multi_search'] = array();
              $_SESSION['OCS']['multi_search']['accountinfo']['ACCOUNTINFO-'.$field[1].$comp.preg_replace("/_/","",$value)] = [
                  'fields' => $field[1],
                  'value' => $value,
                  'operator' => $operator,
              ];
          }
        }elseif(strpos($field[0], 'NETWORKS') !== false){
          if(!array_key_exists('NETWORKS-'.$field[1].$comp.$value,$_SESSION['OCS']['multi_search']['networks'])){
              $_SESSION['OCS']['multi_search'] = array();
              $_SESSION['OCS']['multi_search']['networks']['NETWORKS-'.$field[1].$comp.preg_replace("/_/","",$value)] = [
                  'fields' => $field[1],
                  'value' => preg_replace("/[^A-Za-z0-9\.]/", "", $value),
                  'operator' => $operator,
              ];
          }
        }elseif(strpos($field[0], 'VIDEOS') !== false){
          if(!array_key_exists('VIDEOS-'.$field[1].$comp.$value,$_SESSION['OCS']['multi_search']['videos'])){
              $_SESSION['OCS']['multi_search'] = array();
              $_SESSION['OCS']['multi_search']['videos']['VIDEOS-'.$field[1].$comp.preg_replace("/_/","",$value)] = [
                  'fields' => $field[1],
                  'value' => $value,
                  'operator' => $operator,
              ];
          }
        }
      }else{
        $field_bis = explode(",", $field[1]) ;
        $comps = explode(",", $comp) ;
        $values = explode(",", $value) ;

        if($comps[1] == 'small') { $operator1 = 'LESS'; }
        if($comps[0] == 'tall') { $operator2 = 'MORE'; }

        if(!array_key_exists('HARDWARE-'.$field_bis[0].$comps[0].$values[0],$_SESSION['OCS']['multi_search']['hardware'])){
            $_SESSION['OCS']['multi_search'] = array();
            $_SESSION['OCS']['multi_search']['hardware']['HARDWARE-'.$field_bis[0].$comps[0].preg_replace("/_/","",$values[0])] = [
                'fields' => $field_bis[0],
                'value' => $values[0],
                'operator' => $operator2,
            ];

            $_SESSION['OCS']['multi_search']['hardware']['HARDWARE-'.$field_bis[0].$comp[1].preg_replace("/_/","",$values[1])] = [
                'fields' => $field_bis[0],
                'value' => $values[1],
                'operator' => $operator1,
            ];
        }
      }
    }
 }
