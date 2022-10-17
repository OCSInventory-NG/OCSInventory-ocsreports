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

    const HTML_SELECT = "SELECT";

    const MULTIPLE_DONE = "DONE";

    const GROUP_TABLE = "groups_cache";

    public $correspondance = [
      "NAME_ID" => self::DB_VARCHAR,
      "PUBLISHER_ID" => self::DB_VARCHAR,
      "VERSION_ID" => self::DB_VARCHAR,
      "CATEGORY_ID" => self::HTML_SELECT,
      "CATEGORY" => self::HTML_SELECT,
    ];

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
    public $groupSearch;
    private $softwareSearch;

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
    public $operatorList = [
        "EQUAL",
        "MORE",
        "LESS",
        "LIKE",
        "DIFFERENT",
        "ISNULL",
        "DOESNTCONTAIN",
        "ISNOTEMPTY",
    ];

    /**
     * Delay operator list
     */
    public $operatorDelay = [
      "MORETHANXDAY",
      "LESSTHANXDAY",
    ];

    /**
     * Operator list
     */
    private $operatorGroup = [
        "BELONG",
        "DONTBELONG",
    ];

    /**
     * Operator list
     */
    public $operatorAccount = [
        "HAVING",
        "NOTHAVING",
    ];

    /**
     * Operator list
     */
    public $operatorAccountCheckbox = [
        "HAVINGCHECK",
        "NOTHAVINGCHECK",
    ];

    /**
     * Comparator list
     */
    private $comparatorList = [
        "AND",
        "OR"
    ];


    /**
     * Constructor
     *
     * @param TranslationSearch $translationSearch
     * @param DatabaseSearch $databaseSearch
     * @param AccountinfoSearch $accountinfoSearch
     * @param GroupSearch $groupSearch
     * @param SQLCache $sqlCache
     */
    function __construct($translationSearch, $databaseSearch, $accountinfoSearch, $groupSearch, $softwareSearch)
    {

        $this->translationSearch = $translationSearch;
        $this->databaseSearch = $databaseSearch;
        $this->accountinfoSearch = $accountinfoSearch;
        $this->groupSearch = $groupSearch;
        $this->softwareSearch = $softwareSearch;

        if ($_SESSION['OCS']['profile']->getConfigValue('DELETE_COMPUTERS') == "YES" || $_SESSION['OCS']['profile']->getConfigValue('ARCHIVE_COMPUTERS') == "YES"
            || $_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES"
        ) {
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
            if(count($keyExploded) > 1 && isset($_SESSION['OCS']['multi_search'][$keyExploded[1]]) && !is_null($_SESSION['OCS']['multi_search'][$keyExploded[1]])){
                if ($keyExploded[2] == self::SESS_OPERATOR) {
                    $_SESSION['OCS']['multi_search'][$keyExploded[1]][$keyExploded[0]][self::SESS_OPERATOR] = $value;
                } elseif($keyExploded[2] == self::SESS_FIELDS && $_SESSION['OCS']['multi_search'][$keyExploded[1]][$keyExploded[0]][self::SESS_OPERATOR] != 'ISNULL') {
                    $_SESSION['OCS']['multi_search'][$keyExploded[1]][$keyExploded[0]][self::SESS_VALUES] = $value;
                }elseif($keyExploded[2] == self::SESS_COMPARATOR){
                  $_SESSION['OCS']['multi_search'][$keyExploded[1]][$keyExploded[0]][self::SESS_COMPARATOR] = $value;
                }
            }elseif(count($keyExploded) == 4){
                $keyExplodedBis = $keyExploded[1]."_".$keyExploded[2];
                if(isset ($_SESSION['OCS']['multi_search'][$keyExplodedBis]) && !is_null($_SESSION['OCS']['multi_search'][$keyExplodedBis])){
                  if ($keyExploded[3] == self::SESS_OPERATOR) {
                      $_SESSION['OCS']['multi_search'][$keyExplodedBis][$keyExploded[0]][self::SESS_OPERATOR] = $value;
                  } elseif($keyExploded[3] == self::SESS_COMPARATOR){
                    $_SESSION['OCS']['multi_search'][$keyExplodedBis][$keyExploded[0]][self::SESS_COMPARATOR] = $value;
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
        return $tableFields[$fieldsname][DatabaseSearch::TYPE] ?? '';
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

        new AccountinfoSearch();
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
                if ($tableName == "groups_cache") {
                  $this->searchQuery .= "LEFT JOIN $tableName on hardware.id = $tableName.hardware_id ";
                } else {
                  $this->searchQuery .= "INNER JOIN $tableName on hardware.id = $tableName.hardware_id ";
                }
			      } 
			
			if($tableName == SoftwareSearch::SOFTWARE_TABLE) {
				$this->searchQuery .= "LEFT JOIN software_name on software_name.id = $tableName.name_id ";
				$this->searchQuery .= "LEFT JOIN software_publisher on software_publisher.id = $tableName.publisher_id ";
				$this->searchQuery .= "LEFT JOIN software_version on software_version.id = $tableName.version_id ";
            }

            foreach ($searchInfos as $value) {
				if($tableName == "download_history" && $value['fields'] == "PKG_NAME") {
					// Generate union
					$this->searchQuery .= "INNER JOIN download_available on download_available.FILEID = $tableName.PKG_ID ";
				}

                if(isset($value['comparator'])){
                    $operator[] = $value['comparator'];
                }elseif($i != 0 && !isset($value['comparator'])){
                    $operator[] = "AND";
                }else{
                    $operator[] = "";
                }
                $i++;
            }

            $isSameColumn = [];
            $columnName = [];
            $doesntcontainmulti = [];
            

            foreach ($searchInfos as $index => $value) {
                  $values[] = $value;
                  $columnName[$index] = $value['fields'];
                  $containvalue[$index] = $value['operator'];
            }

            foreach ($searchInfos as $value) {
              $nameTable = $tableName;
              $open="";
              $close="";
              // Generate condition
              $this->getOperatorSign($value);
              if($nameTable == SoftwareSearch::SOFTWARE_TABLE) {
                $nameTable = $this->softwareSearch->getTableName($value['fields']);
                $value[self::SESS_FIELDS] = $this->softwareSearch->getColumnName($value['fields']);
              }

              foreach(array_count_values($columnName) as $name => $nb){
                if($nb > 1){
                $isSameColumn[$nameTable] = $name;
                }
              }

              foreach(array_count_values($containvalue) as $name => $nb){
                if($nb > 1){
                $doesntcontainmulti[$nameTable] = $name;
                }
              }

              if($p == 0 && isset($operator[$p+1]) && $operator[$p+1] == 'OR'){
                $open = "(";
              }if($operator[$p] =='OR' && (!isset($operator[$p+1]) || $operator[$p+1] !='OR')){
                $close=")";
              }if($p != 0 && $operator[$p] !='OR' && isset($operator[$p+1]) && $operator[$p+1] =='OR'){
                $open = "(";
              }

              unset($value['ignore']);

              if(!empty($isSameColumn) && $isSameColumn[$nameTable] == $value[self::SESS_FIELDS] && !array_key_exists("ignore", $value) && !array_key_exists('devices', $isSameColumn) && $value[self::SESS_OPERATOR] != "DOESNTCONTAIN"){
                if($value[self::SESS_OPERATOR] != "IS NULL"){
                  if ($nameTable != DatabaseSearch::COMPUTER_DEF_TABLE && $nameTable != self::GROUP_TABLE && $value[self::SESS_FIELDS] != 'CATEGORY_ID' && $value[self::SESS_FIELDS] != 'CATEGORY' 
                  && $value[self::SESS_OPERATOR] != "NOT IN" && $value[self::SESS_OPERATOR] != "ISNOTEMPTY") {
                    $this->columnsQueryConditions .= "$operator[$p] $open EXISTS (SELECT 1 FROM %s WHERE hardware.ID = %s.HARDWARE_ID AND %s.%s %s '%s')$close ";
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = $value[self::SESS_FIELDS];
                    $this->queryArgs[] = $value[self::SESS_OPERATOR];
                    $this->queryArgs[] = $value[self::SESS_VALUES];
                  }elseif($nameTable == self::GROUP_TABLE || $value[self::SESS_FIELDS] == 'CATEGORY_ID' || $value[self::SESS_FIELDS] == 'CATEGORY' 
                  || $value[self::SESS_OPERATOR] == "NOT IN"){
                    $this->columnsQueryConditions .= "$operator[$p] $open EXISTS (SELECT 1 FROM %s WHERE hardware.ID = %s.HARDWARE_ID AND %s.%s %s (%s))$close ";
                    if($nameTable == self::GROUP_TABLE){
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = 'hardware';
                    $this->queryArgs[] = 'ID';
                    $this->queryArgs[] = $value[self::SESS_OPERATOR];
                    $this->queryArgs[] = $this->groupSearch->get_all_id($value[self::SESS_VALUES]);
                    }else{
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = $value[self::SESS_FIELDS];
                    $this->queryArgs[] = $value[self::SESS_OPERATOR];
                    $this->queryArgs[] = $value[self::SESS_VALUES];
                    }
                  } elseif($value[self::SESS_OPERATOR] == "ISNOTEMPTY") {
                    $this->columnsQueryConditions .= "$operator[$p] $open EXISTS (SELECT 1 FROM %s WHERE hardware.ID = %s.HARDWARE_ID AND %s.%s IS NOT NULL AND TRIM(%s.%s) != '')$close ";
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = $value[self::SESS_FIELDS];
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = $value[self::SESS_FIELDS];
                  }else{
                    if($value[self::SESS_OPERATOR] == "MORETHANXDAY" || $value[self::SESS_OPERATOR] == "LESSTHANXDAY") {
                      $this->columnsQueryConditions .= "$operator[$p] $open EXISTS (SELECT 1 FROM %s WHERE %s.%s %s NOW() - INTERVAL %s DAY)$close ";
                      $this->queryArgs[] = $nameTable;
                      $this->queryArgs[] = $nameTable;
                      $this->queryArgs[] = $value[self::SESS_FIELDS];
                      if($value[self::SESS_OPERATOR] == "MORETHANXDAY") { $op = "<"; } else { $op = ">"; }
                      $this->queryArgs[] = $op;
                      $this->queryArgs[] = $value[self::SESS_VALUES];
                    // text fix datetime in multisearch
                    } else if($this->getSearchedFieldType($nameTable, $value[self::SESS_FIELDS]) == 'datetime' && $value[self::SESS_OPERATOR] != "MORETHANXDAY" && $value[self::SESS_OPERATOR] != "LESSTHANXDAY" ) {
                      $this->columnsQueryConditions .= "$operator[$p] $open%s.%s %s str_to_date('%s', '%s')$close ";
                      $this->queryArgs[] = $nameTable;
                      $this->queryArgs[] = $value[self::SESS_FIELDS];
                      $this->queryArgs[] = $value[self::SESS_OPERATOR];
                      $this->queryArgs[] = $value[self::SESS_VALUES];
                      global $l;
                      $this->queryArgs[] = $l->g(269);
                    } else {
                      $this->columnsQueryConditions .= "$operator[$p] $open(%s.%s %s '%s')$close ";
                      $this->queryArgs[] = $nameTable;
                      $this->queryArgs[] = $value[self::SESS_FIELDS];
                      $this->queryArgs[] = $value[self::SESS_OPERATOR];
                      $this->queryArgs[] = $value[self::SESS_VALUES];
                    }
                  }
                }else{
                  if ($nameTable != DatabaseSearch::COMPUTER_DEF_TABLE) {
                    $this->columnsQueryConditions .= "$operator[$p] $open EXISTS (SELECT 1 FROM %s WHERE hardware.ID = %s.HARDWARE_ID AND %s.%s %s)$close ";
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = $value[self::SESS_FIELDS];
                    $this->queryArgs[] = $value[self::SESS_OPERATOR];
                  }else{
                    $this->columnsQueryConditions .= "$operator[$p] $open EXISTS (SELECT 1 FROM %s WHERE %s.%s %s)$close ";
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = $nameTable;
                    $this->queryArgs[] = $value[self::SESS_FIELDS];
                    $this->queryArgs[] = $value[self::SESS_OPERATOR];
                  }
                }
              }elseif($value[self::SESS_OPERATOR] == 'IS NULL' && (empty($isSameColumn))){
                $this->columnsQueryConditions .= "$operator[$p] $open(%s.%s IS NULL OR TRIM(%s.%s) = '')$close ";
                $this->queryArgs[] = $nameTable;
                $this->queryArgs[] = $value[self::SESS_FIELDS];
                $this->queryArgs[] = $nameTable;
                $this->queryArgs[] = $value[self::SESS_FIELDS];
              } elseif($value[self::SESS_OPERATOR] == "ISNOTEMPTY") {
                $this->columnsQueryConditions .= "$operator[$p] $open%s.%s IS NOT NULL AND TRIM(%s.%s) != ''$close ";
                $this->queryArgs[] = $nameTable;
                $this->queryArgs[] = $value[self::SESS_FIELDS];
                $this->queryArgs[] = $nameTable;
                $this->queryArgs[] = $value[self::SESS_FIELDS];
              } elseif($nameTable == self::GROUP_TABLE || $value[self::SESS_FIELDS] == 'CATEGORY_ID' || $value[self::SESS_FIELDS] == 'CATEGORY' 
                    || $value[self::SESS_OPERATOR] == "NOT IN"){
                $this->columnsQueryConditions .= "$operator[$p] $open%s.%s %s (%s)$close ";
                if($nameTable == self::GROUP_TABLE){
                  $this->queryArgs[] = 'hardware';
                  $this->queryArgs[] = 'ID';
                  $this->queryArgs[] = $value[self::SESS_OPERATOR];
                  $this->queryArgs[] = $this->groupSearch->get_all_id($value[self::SESS_VALUES]);
                }else{
                  $this->queryArgs[] = $nameTable;
                  $this->queryArgs[] = $value[self::SESS_FIELDS];
                  $this->queryArgs[] = $value[self::SESS_OPERATOR];
                  $this->queryArgs[] = $value[self::SESS_VALUES];
                }
              } elseif ($value[self::SESS_OPERATOR] == "DOESNTCONTAIN"){
                $result = $this->contain_subquery($nameTable, $value);

                if($tableName == DatabaseSearch::COMPUTER_DEF_TABLE || substr($nameTable, 0, 9) == 'software_'){
                  $value[self::SESS_FIELDS] = "ID";
                  }else{
                  $value[self::SESS_FIELDS] = "HARDWARE_ID";
                  }

                $this->columnsQueryConditions .= " $operator[$p] $open hardware.ID NOT IN ($result) $close ";
                
              // test fix datetime in multisearch
              }else if($this->getSearchedFieldType($nameTable, $value[self::SESS_FIELDS]) == 'datetime' && $value[self::SESS_OPERATOR] != "MORETHANXDAY" && $value[self::SESS_OPERATOR] != "LESSTHANXDAY" ){
                $this->columnsQueryConditions .= "$operator[$p] $open%s.%s %s str_to_date('%s', '%s')$close ";
                $this->queryArgs[] = $nameTable;
                $this->queryArgs[] = $value[self::SESS_FIELDS];
                $this->queryArgs[] = $value[self::SESS_OPERATOR];
                $this->queryArgs[] = $value[self::SESS_VALUES];
                global $l;
                $this->queryArgs[] = $l->g(269);
              } elseif($value[self::SESS_OPERATOR] == "MORETHANXDAY" || $value[self::SESS_OPERATOR] == "LESSTHANXDAY") {
                $this->columnsQueryConditions .= "$operator[$p] $open%s.%s %s NOW() - INTERVAL %s DAY$close ";
                $this->queryArgs[] = $nameTable;
                $this->queryArgs[] = $value[self::SESS_FIELDS];
                if($value[self::SESS_OPERATOR] == "MORETHANXDAY") { $op = "<"; } else { $op = ">"; }
                $this->queryArgs[] = $op;
                $this->queryArgs[] = $value[self::SESS_VALUES];
              } else {
                $this->columnsQueryConditions .= "$operator[$p] $open%s.%s %s '%s'$close ";
                if($nameTable == "download_history" && $value[self::SESS_FIELDS] == "PKG_NAME"){
                $this->queryArgs[] = 'download_available';
                $this->queryArgs[] = 'NAME';
                }else{
                $this->queryArgs[] = $nameTable;
                $this->queryArgs[] = $value[self::SESS_FIELDS];
                }
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

        // has lock machine ?
        if (isset($_SESSION['OCS']["mesmachines"]) && strpos($_SESSION['OCS']["mesmachines"], 'a.TAG') === false) {
            $lockResult = str_replace('a.hardware_id', 'accountinfo.hardware_id', $_SESSION['OCS']["mesmachines"]);
            $this->columnsQueryConditions .=  " AND " . $lockResult;
        }

        $this->columnsQueryConditions .= " GROUP BY hardware.id";
        $this->baseQuery = substr($this->baseQuery, 0, -1);
    }

    /**
     * Generate select query for table using session variables generated from the search
     *
     * @param String $tableName
     * @return void
     */
				private function pushBaseQueryForTable($tableName){
        foreach($this->databaseSearch->getColumnsList($tableName) as $fieldsInfos){
			  $name = "";
            if($tableName == "download_history" && $fieldsInfos['Field'] == "PKG_NAME"){
				$tableName = "download_available";
				$fieldsInfos['Field'] = "NAME";
			}
			if($tableName == "software" && array_key_exists($fieldsInfos['Field'], $this->correspondance)) {
				if($fieldsInfos['Field'] == "CATEGORY") {
					$table = $tableName."_name";
					$field = "CATEGORY";
				} else {
					$name = explode("_", $fieldsInfos['Field']);
					$table = $tableName."_".strtolower($name[0]);
					$field = $name[0];
				}
				$generatedId = $table.".".$field;
				$selectAs = $table.$field;
				$this->baseQuery .= " %s.%s AS ".$selectAs." ,";
				$this->queryArgs[] = $table;
				$this->queryArgs[] = $field;
			} else {
				$generatedId = $tableName.".".$fieldsInfos['Field'];
				$selectAs = $tableName.$fieldsInfos['Field'];
				$this->baseQuery .= " %s.%s AS ".$selectAs." ,";
				$this->queryArgs[] = $tableName;
				$this->queryArgs[] = $fieldsInfos['Field'];
			}
            

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
            case 'ISNOTEMPTY':
                $valueArray[self::SESS_OPERATOR] = "ISNOTEMPTY";
                break;
            case 'DOESNTCONTAIN':
                $valueArray[self::SESS_OPERATOR] = "DOESNTCONTAIN";
                break;
            case 'BELONG' :
                $valueArray[self::SESS_OPERATOR] = "IN";
                break;
            case 'DONTBELONG' :
                $valueArray[self::SESS_OPERATOR] = "NOT IN";
                break;
            case 'NOTHAVING' :
                $valueArray[self::SESS_OPERATOR] = "!=";
                break;
            case 'NOTHAVINGCHECK' :
                $valueArray[self::SESS_OPERATOR] = "NOT LIKE";
                $valueArray[self::SESS_VALUES] = "%".$valueArray[self::SESS_VALUES]."%";
                break;
            case 'HAVINGCHECK' :
                $valueArray[self::SESS_OPERATOR] = "LIKE";
                $valueArray[self::SESS_VALUES] = "%".$valueArray[self::SESS_VALUES]."%";
                break;
            case 'MORETHANXDAY' :
                $valueArray[self::SESS_OPERATOR] = "MORETHANXDAY";
                break;
            case 'LESSTHANXDAY' :
                $valueArray[self::SESS_OPERATOR] = "LESSTHANXDAY";
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
    public function getSelectOptionForOperators($defaultValue, $table = null, $field = null)
    {
        $account = new AccountinfoSearch();
        $accounttype = null;
        if($field != null && $field != "LASTCOME" && $field != "LASTDATE"){
          $accounttype = $account->getSearchAccountInfo($field);
        }

        $html = "";
        if($table == self::GROUP_TABLE || $field == "CATEGORY_ID" || $field == "CATEGORY") {
            $operatorList = $this->operatorGroup;
        } elseif($accounttype == '2' || $accounttype == '11') {
            $operatorList = $this->operatorAccount;
        } elseif($accounttype == '5') {
            $operatorList = $this->operatorAccountCheckbox;
        } elseif(isset($field) && $this->getSearchedFieldType($table, $field) == 'datetime') {
            $operatorList = array_merge($this->operatorList, $this->operatorDelay);
        } else {
            $operatorList = $this->operatorList;
        }

        foreach ($operatorList as $value) {
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
            $sortTable[$tableName] = $translation;
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
        $sortColumn = array();
        if($tableName == "accountinfo"){
          $accountinfoList = new AccountinfoSearch();
          $accountFields = $accountinfoList->getAccountInfosList();
          if(isset($accountFields['COMPUTERS']) && is_array($accountFields['COMPUTERS']))
          foreach ($accountFields['COMPUTERS'] as $index => $fieldsInfos) {
              // if(!in_array($fieldsIndefaultTablefos[DatabaseSearch::FIELD], $this->excludedVisuColumns)){
                  $trField = $fieldsInfos;
              $sortColumn[$index] = $trField;
              // }
          }
        }elseif($tableName == self::GROUP_TABLE){
          $trField = $this->translationSearch->getTranslationFor('NAME');
          $sortColumn['name'] = $trField;
        }else{
          $fields = $this->databaseSearch->getColumnsList($tableName);
          if(is_array($fields)) foreach ($fields as $fieldsInfos) {
              //if(!in_array($fieldsIndefaultTablefos[DatabaseSearch::FIELD], $this->excludedVisuColumns)){
                  $trField = $this->translationSearch->getTranslationFor($fieldsInfos[DatabaseSearch::FIELD]);
                  $sortColumn[$fieldsInfos[DatabaseSearch::FIELD]] = $trField;
              //}
          }
        }
        asort($sortColumn);
        foreach ($sortColumn as $key => $value){
            //if(!in_array($fieldsIndefaultTablefos[DatabaseSearch::FIELD], $this->excludedVisuColumns)){
                $html .= "<option value=".$key." >".$value."</option>";
            //}
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
    public function returnFieldHtml($uniqid, $fieldsInfos, $tableName, $field = null, $operator = null)
    {
        global $l;

        $fieldId = $this->getFieldUniqId($uniqid, $tableName);
        $fieldGroup = array();

        $accountInfos = new AccountinfoSearch();

        if($field != null && $field != "CATEGORY_ID" && $field != "CATEGORY" && $operator == null){
          $accounttype = $accountInfos->getSearchAccountInfo($field);
        }

        if($tableName == self::GROUP_TABLE) {
          $this->type = self::HTML_SELECT;
        } elseif(array_key_exists($field, $this->correspondance)) {
          $this->type = $this->correspondance[$field];
        }elseif(isset($accounttype) && ($accounttype == '2' || $accounttype == '11' || $accounttype =='5')) {
          $this->type = self::HTML_SELECT;
        } else {
          $this->type = $this->getSearchedFieldType($tableName, $fieldsInfos[self::SESS_FIELDS]);
        }

        if($operator == "MORETHANXDAY" || $operator == "LESSTHANXDAY") {
          $this->type = self::DB_INT;
        }

        $html = "";
        if($fieldsInfos[self::SESS_OPERATOR]== 'ISNULL' || $fieldsInfos[self::SESS_OPERATOR]== 'ISNOTEMPTY'){
          $attr = 'disabled';
        }else{
          $attr = '';
        }

        switch ($this->type) {
            case self::DB_VARCHAR:

            case self::DB_TEXT:
                $html = '<input class="form-control" type="text" name="'.$fieldId.'" id="'.$fieldId.'" value="'.$fieldsInfos[self::SESS_VALUES].'" '.$attr.'>';
                break;

            case self::DB_INT:
                $html = '<input class="form-control" type="number" name="'.$fieldId.'" id="'.$fieldId.'" value="'.($fieldsInfos[self::SESS_VALUES] ?? '') .'" '.$attr.'>';
                break;

            case self::DB_DATETIME:
                $html = '<div class="input-group date form_datetime">
                            <input type="text" class="form-control" name="'.$fieldId.'" id="'.$fieldId.'" value="'.$fieldsInfos[self::SESS_VALUES].'" '.$attr.'/>
                            <span class="input-group-addon">
                                '.calendars($fieldId, $l->g(1270)).'
                            </span>
                        </div>';
                break;
            case self::HTML_SELECT:

                $html = '<select class="form-control" name="'.$fieldId.'" id="'.$fieldId.'">';
                if(isset($accounttype) && $accounttype != null) {
                  $fieldSelect = $accountInfos->find_accountinfo_values($field, $accounttype);
                } elseif($field == 'CATEGORY_ID') {
                   $fieldSelect = $this->asset_categories();
                } elseif($field == 'CATEGORY'){
                  require_once('require/softwares/SoftwareCategory.php');
                  $soft = new SoftwareCategory();
                  $fieldSelect = $soft->search_all_cat();
                  unset($fieldSelect[0]);
                } else {
                  $fieldSelect = $this->groupSearch->get_group_name();
                }

                foreach ($fieldSelect as $key => $value){
                    if ($fieldsInfos[self::SESS_VALUES] == $key) {
                        $html .= "<option value=".$key." selected >".$value."</option>";
                    } else {
                        $html .= "<option value=".$key." >".$value."</option>";
                    }
                }
                $html .= '</select>';
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

        foreach ($infos as $value){
            if($value['fields'] == $fieldsInfos['fields']){
              $i++;
            }
        }

        $this->getFieldUniqId($uniqid, $tableName);
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
     * [link_multi description]
     * @param  string $fields [description]
     * @param  string $value  [description]
     * @param  string $option [description]
     * @return [type]         [description]
     */
    public function link_multi($fields, $value, $option = ""){
        switch($fields){
          case 'cveNamePublisherVersion':
            $value = explode(";", $value);
            $_SESSION['OCS']['multi_search'] = array();
            $_SESSION['OCS']['multi_search']['software']['allsoft'] = [
                'fields' => 'NAME_ID',
                'value' => $value[0],
                'operator' => 'EQUAL',
            ];
            $_SESSION['OCS']['multi_search']['software']['allsoftpublisher'] = [
              'fields' => 'PUBLISHER_ID',
              'value' => $value[1],
              'operator' => 'EQUAL',
            ];
            $_SESSION['OCS']['multi_search']['software']['allsoftversion'] = [
              'fields' => 'VERSION_ID',
              'value' => $value[2],
              'operator' => 'EQUAL',
            ];
            break;
            case 'cveNameVersion':
              $value = explode(";", $value);
              $_SESSION['OCS']['multi_search'] = array();
              $_SESSION['OCS']['multi_search']['software']['allsoft'] = [
                  'fields' => 'NAME_ID',
                  'value' => $value[0],
                  'operator' => 'EQUAL',
              ];
              $_SESSION['OCS']['multi_search']['software']['allsoftversion'] = [
                'fields' => 'VERSION_ID',
                'value' => $value[1],
                'operator' => 'EQUAL',
              ];
              break;
          case 'cveName':
            $_SESSION['OCS']['multi_search'] = array();
            $_SESSION['OCS']['multi_search']['software']['allsoft'] = [
                'fields' => 'NAME_ID',
                'value' => $value,
                'operator' => 'EQUAL',
            ];
            break;

          case 'ipdiscover1':
            $_SESSION['OCS']['multi_search'] = array();
            if(!isset($_SESSION['OCS']['multi_search']['networks']['ipdiscover1'])){
                if(strpos($value, ";") !== false) {
                  $explode = explode(";", $value);
                  $value = $explode[0];
                  if(count($explode) == 2) {
                    $tag = $explode[1];
                  } else {
                    $tag = null;
                  }
                }
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
                if(isset($tag) && !empty($tag)) {
                  $_SESSION['OCS']['multi_search']['accountinfo']['ipdiscover5'] = [
                    'fields' => 'TAG',
                    'value' => $tag,
                    'operator' => 'EQUAL',
                ];
                }
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
              if(isset($option['idPackage'])) foreach($option['idPackage'] as $key =>$value){
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

          case 'saas':
            if(!isset($_SESSION['OCS']['multi_search']['saas']) || !array_key_exists('saas',$_SESSION['OCS']['multi_search']['saas'])){
                $_SESSION['OCS']['multi_search'] = array();
                $_SESSION['OCS']['multi_search']['saas']['saas'] = [
                    'fields' => 'ENTRY',
                    'value' => $value,
                    'operator' => 'EQUAL',
                ];
            }
            break;

          case 'querysave':
            $parameters = $this->query_save($value);

            $_SESSION['OCS']['multi_search'] = array();
            $_SESSION['OCS']['multi_search'] = $parameters;

            break;

          default :
            break;
        }
    }

    public function query_save($id) {
      $sql = "SELECT PARAMETERS FROM save_query WHERE ID = %s";
      $sql_arg = array($id);

      $result = mysql2_query_secure($sql, $_SESSION['OCS']['readServer'], $sql_arg);

      while($item = mysqli_fetch_array($result)){
        $parameters = $item['PARAMETERS'];
      }

      return unserialize($parameters);
    }

    /**
     * [link_index description]
     * @param  string $fields [description]
     * @param  string $comp   [description]
     * @param  string $value  [description]
     * @return [type]         [description]
     */
    public function link_index($fields, $value, $comp = "", $value2 = null){
      $field = explode("-", $fields) ;

      if($comp == 'small') { $operator = 'LESS'; }
      elseif($comp == 'tall') { $operator = 'MORE'; }
      elseif($comp == 'exact') { $operator = 'EQUAL'; }

      if($fields == 'HARDWARE-LASTDATE' || $fields == 'HARDWARE-LASTCOME'){
          $value = str_replace(substr($value, -5), '00:00', $value);
      }

      $configToLookOut = [
        'EXCLUDE_ARCHIVE_COMPUTER' => 'EXCLUDE_ARCHIVE_COMPUTER'
      ];

      $configValues = look_config_default_values($configToLookOut)['ivalue']['EXCLUDE_ARCHIVE_COMPUTER'] ?? '';

      if(empty($field[2])){
        if(strpos($field[0], 'HARDWARE') !== false){
          if(!isset($_SESSION['OCS']['multi_search']['hardware']) || !array_key_exists('HARDWARE-'.$field[1].$comp.preg_replace("/\s+/","", preg_replace("/_/","",$value)).preg_replace("/_/","",$value2),$_SESSION['OCS']['multi_search']['hardware'])){
              $_SESSION['OCS']['multi_search'] = array();
              $_SESSION['OCS']['multi_search']['hardware']['HARDWARE-'.$field[1].$comp.preg_replace("/\s+/","", preg_replace("/_/","",$value)).preg_replace("/_/","",$value2)] = [
                  'fields' => $field[1],
                  'value' => $value,
                  'operator' => $operator,
              ];
              if($value2 != null && $value2 != 'all')
              $_SESSION['OCS']['multi_search']['hardware']['HARDWARE-'.$field[1].$comp.preg_replace("/_/","",$value2)] = [
                  'fields' => 'USERAGENT',
                  'value' => $value2,
                  'operator' => 'LIKE',
              ];
          }
          if($configValues == 1) {
            $_SESSION['OCS']['multi_search']['hardware']['HARDWARE-ARCHIVE'] = [
              'fields' => 'ARCHIVE',
              'operator' => 'ISNULL',
            ];
          }
        }elseif(strpos($field[0], 'ACCOUNTINFO') !== false){
          if(!isset($_SESSION['OCS']['multi_search']['accountinfo']) || !array_key_exists('ACCOUNTINFO-'.$field[1].$comp.$value,$_SESSION['OCS']['multi_search']['accountinfo'])){
              $_SESSION['OCS']['multi_search'] = array();
              $_SESSION['OCS']['multi_search']['accountinfo']['ACCOUNTINFO-'.$field[1].$comp.preg_replace("/_/","",$value)] = [
                  'fields' => $field[1],
                  'value' => $value,
                  'operator' => $operator,
              ];
          }
        }elseif(strpos($field[0], 'NETWORKS') !== false){
          if(!isset($_SESSION['OCS']['multi_search']['networks']) || !array_key_exists('NETWORKS-'.$field[1].$comp.$value,$_SESSION['OCS']['multi_search']['networks'])){
              $_SESSION['OCS']['multi_search'] = array();
              $_SESSION['OCS']['multi_search']['networks']['NETWORKS-'.$field[1].$comp.preg_replace("/_/","",$value)] = [
                  'fields' => $field[1],
                  'value' => preg_replace("/[^A-Za-z0-9\.]/", "", $value),
                  'operator' => $operator,
              ];
          }
        }elseif(strpos($field[0], 'VIDEOS') !== false){
          if(!isset($_SESSION['OCS']['multi_search']['videos']) || !array_key_exists('VIDEOS-'.$field[1].$comp.$value,$_SESSION['OCS']['multi_search']['videos'])){
              $_SESSION['OCS']['multi_search'] = array();
              $_SESSION['OCS']['multi_search']['videos']['VIDEOS-'.$field[1].$comp.preg_replace("/_/","",$value)] = [
                  'fields' => $field[1],
                  'value' => $value,
                  'operator' => $operator,
              ];
          }
        }elseif(strpos($field[0], 'ASSETS') !== false){
          if(!isset($_SESSION['OCS']['multi_search']['hardware']) || !array_key_exists('ASSETS'.$value,$_SESSION['OCS']['multi_search']['hardware'])){
              $_SESSION['OCS']['multi_search'] = array();
              $_SESSION['OCS']['multi_search']['hardware']['ASSETS'.preg_replace("/_/","",$value)] = [
                  'fields' => 'CATEGORY_ID',
                  'value' => $value,
                  'operator' => "BELONG",
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

    /**
     * Get all assets category
     */
    public function asset_categories(){
      $sqlAsset = "SELECT CATEGORY_NAME, ID FROM assets_categories";
      $result = mysqli_query($_SESSION['OCS']["readServer"], $sqlAsset);

      while($asset_row = mysqli_fetch_array($result)){
        $asset[$asset_row['ID']] = $asset_row['CATEGORY_NAME'];
      }
      return $asset;
    }

    /**
     * Doesn't contain traitment
     */
    public function contain($value, $tableName){
       // if table is hardware or table name begins with 'software_' : field name is ID
      if($tableName == DatabaseSearch::COMPUTER_DEF_TABLE || substr($tableName, 0, 9) == 'software_') {
        $field = "ID";
      } elseif ($tableName != DatabaseSearch::COMPUTER_DEF_TABLE) {
        $field = "HARDWARE_ID";
      }

      // if search is software related, process is slighty different
      if (substr($tableName, 0, 9) == 'software_') {
        $crit = $value['value'];
        $result = $this->contain_subquery($tableName, $crit);
      } else {
        // classic way for other tables
        $sql_search = "SELECT DISTINCT %s FROM %s WHERE %s LIKE '%s'";
        $sql_search_arg = array($field, $tableName, $value['fields'], "%".$value['value']."%");
        $result = mysql2_query_secure($sql_search, $_SESSION['OCS']["readServer"], $sql_search_arg);
      }
      
      if (is_object($result)) {
        while($notcontain = mysqli_fetch_array($result)) {
          $excluID[] = $notcontain[$field];
        }
  
        if(!isset($excluID) || $excluID[0] == null){
          $excluID[0] = 0;
        }
      } else {
        $excluID = $result;
      }


      return $excluID;
    }

    /**
     * Doesn't contain traitment if multi search
     */
    public function containmulti($name, $value){
      $excluID = null;
      $allID = null;
      foreach ($name as $table => $field){
        $tablename = $table;
        $column = $field;
      }

      // if table is hardware or table name begins with 'software_' : field name is ID
      if($tablename == DatabaseSearch::COMPUTER_DEF_TABLE || substr($tablename, 0, 9) == 'software_') {
        $fieldname = "ID";
      } elseif ($tablename != DatabaseSearch::COMPUTER_DEF_TABLE) {
        $fieldname = "HARDWARE_ID";
      }

      foreach ($value as $values){
        if ($values['fields'] == $field && $values['operator'] == "DOESNTCONTAIN"){
          $search[] = $values['value'];
          if($values['comparator'] != null){
            $comparator[] = $values['comparator'];
          }
        }
      }

      for($i = 0; $i != count($comparator)+1; $i++){
        // if search is software related, process is slighty different
        if (substr($tablename, 0, 9) == 'software_') {
          $crit = $search[$i];
          $result = $this->contain_subquery($tablename, $crit);
        } else {
          // classic way for other tables
          $sql = "SELECT DISTINCT %s FROM %s WHERE %s LIKE '%s'";
          $sql_arg = array($fieldname, $tablename, $column, "%".$search[$i]."%");
          $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $sql_arg);
        }

        while($notcontain = mysqli_fetch_array($result)){
          $excluID[$notcontain[$fieldname]] = $notcontain[$fieldname];
          $allID[$search[$i]][$notcontain[$fieldname]] = $notcontain[$fieldname];
        }
      }

      for($i = 0; $i != count($comparator)+1; $i++){
        foreach($excluID as $key => $values){
          foreach($allID as $compare){
            if(!array_key_exists($values, $compare) && $comparator[$i] == "AND"){
              unset($excluID[$key]);
            }
          }
        }
      }

      if(!isset($excluID) || array_key_first($excluID) == null){
        $excluID[0] = 0;
      }

      return $excluID;
    }

    /**
     * Handling of doesntcontain operator 
     * Returns : a sql query to use as subquery
     */
    public function contain_subquery($tableName, $value) {
      // software tables
      if (substr($tableName, 0, 9) == 'software_') {
        $main_col = substr($tableName, strpos($tableName, "_") + 1);
        $id_col = $main_col.'_id';
        $sql_search = "SELECT DISTINCT hardware.ID FROM hardware INNER JOIN software on hardware.id = software.hardware_id LEFT JOIN $tableName on $tableName.id = software.$id_col WHERE $tableName.$main_col LIKE '%s' GROUP BY hardware.id";  
        $arg = array("%%".$value['value']."%%");
      // any other table
      } else {
        if($tableName == DatabaseSearch::COMPUTER_DEF_TABLE){
          $field = "ID";
          }else{
          $field = "HARDWARE_ID";
          }
        $sql_search = "SELECT DISTINCT %s FROM %s WHERE %s LIKE '%s'";
        $arg = array($field, $tableName, $value['fields'], "%%".$value[self::SESS_VALUES]."%%");
      }

      $result = generate_secure_sql($sql_search, $arg);

      return $result; 
    }
  }