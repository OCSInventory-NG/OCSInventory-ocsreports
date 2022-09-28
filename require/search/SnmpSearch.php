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
  * This class implement the base behavior for snmp search :
  * - Query generation
  * - Data management
  * - Return structure
  * Used in new snmp search
  */

class SnmpSearch
{
	const SESS_TABLE 		= "table";
	const SESS_FIELDS 		= "fields";
    const SESS_VALUES 		= "value";
    const SESS_OPERATOR 	= "operator";
    const SESS_COMPARATOR 	= "comparator";

	const DB_TEXT 		= "text";
    const DB_INT 		= "int";
    const DB_VARCHAR 	= "varchar";
    const DB_DATETIME 	= "datetime";

	private $databaseSearch;
    private $accountinfoSearch;
	private $search;
	private $translationSearch;

	private $type;

	public $baseQuery 				= "SELECT";
	public $searchQuery 			= "FROM `%s` ";
	public $queryArgs 				= [];
	public $columnsQueryConditions 	= "";

	function __construct($search, $accountinfoSearch, $databaseSearch, $translationSearch) {
		$this->search = $search;
		$this->accountinfoSearch = $accountinfoSearch;
		$this->databaseSearch = $databaseSearch;
		$this->translationSearch = $translationSearch;
	}

	/**
     * Generate HTML select options for tables select
     *
     * @param String $defautValue
     * @return void
     */
    public function getSelectOptionForTables($defautValue = null) {
        $html = "<option>----------</option>";
        foreach ($this->databaseSearch->getTablesSnmpList() as $tableName => $labelTable) {
            $sortTable[$tableName] = $labelTable;
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
    public function getSelectOptionForColumns($tableName = null) {
        $html = "";
        $sortColumn = array();

		$fields = $this->databaseSearch->getColumnsSnmpList($tableName);

		if(is_array($fields)) foreach ($fields as $fieldsInfos) {
			if($fieldsInfos[DatabaseSearch::FIELD] == "LASTDATE" || $fieldsInfos[DatabaseSearch::FIELD] == "ID") {
				$trField = $this->translationSearch->getTranslationFor($fieldsInfos[DatabaseSearch::FIELD]);
			} else {
				$trField = $fieldsInfos[DatabaseSearch::FIELD];
			}
			$sortColumn[$fieldsInfos[DatabaseSearch::FIELD]] = $trField;
		}

        asort($sortColumn);
        foreach ($sortColumn as $key => $value){
            $html .= "<option value=".$key." >".$value."</option>";
        }
        return $html;
    }

	/**
     * Add sessions infos when snmp search criteria is added
     *
     * @param Array $postData
     * @return void
     */
    public function addSessionsInfos($postData) {
        $_SESSION['OCS']['SNMP']['multi_search'][$postData['old_table']][uniqid()] = [
            self::SESS_FIELDS => $postData['columns_select'],
            self::SESS_VALUES => null,
            self::SESS_OPERATOR => null,
            self::SESS_COMPARATOR => null,
			self::SESS_TABLE => $postData['old_table_name'],
        ];
    }

	/**
     * Update sessions infos when changing search criteria
     *
     * @param Array $postData
     * @return void
     */
    public function updateSessionsInfos($postData) {
        foreach ($postData as $key => $value) {
            $keyExploded = explode("_", $key);
            if(count($keyExploded) > 1 && isset($_SESSION['OCS']['SNMP']['multi_search'][$keyExploded[1]]) && !is_null($_SESSION['OCS']['SNMP']['multi_search'][$keyExploded[1]])) {
                if ($keyExploded[2] == self::SESS_OPERATOR) {
                    $_SESSION['OCS']['SNMP']['multi_search'][$keyExploded[1]][$keyExploded[0]][self::SESS_OPERATOR] = $value;
                } elseif($keyExploded[2] == self::SESS_FIELDS && $_SESSION['OCS']['SNMP']['multi_search'][$keyExploded[1]][$keyExploded[0]][self::SESS_OPERATOR] != 'ISNULL') {
                    $_SESSION['OCS']['SNMP']['multi_search'][$keyExploded[1]][$keyExploded[0]][self::SESS_VALUES] = $value;
                } elseif($keyExploded[2] == self::SESS_COMPARATOR) {
                  $_SESSION['OCS']['SNMP']['multi_search'][$keyExploded[1]][$keyExploded[0]][self::SESS_COMPARATOR] = $value;
                }
            } elseif(count($keyExploded) == 4) {
                $keyExplodedBis = $keyExploded[1]."_".$keyExploded[2];
                if(isset($_SESSION['OCS']['SNMP']['multi_search'][$keyExplodedBis]) && !is_null($_SESSION['OCS']['SNMP']['multi_search'][$keyExplodedBis])) {
					if ($keyExploded[3] == self::SESS_OPERATOR) {
						$_SESSION['OCS']['SNMP']['multi_search'][$keyExplodedBis][$keyExploded[0]][self::SESS_OPERATOR] = $value;
					} elseif($keyExploded[3] == self::SESS_COMPARATOR) {
						$_SESSION['OCS']['SNMP']['multi_search'][$keyExplodedBis][$keyExploded[0]][self::SESS_COMPARATOR] = $value;
					} else {
						$_SESSION['OCS']['SNMP']['multi_search'][$keyExplodedBis][$keyExploded[0]][self::SESS_VALUES] = $value;
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
    public function removeSessionsInfos($rowReference) {
        $explodedRef = explode("_", $rowReference);
        if(empty($explodedRef[2])){
            unset($_SESSION['OCS']['SNMP']['multi_search'][$explodedRef[1]][$explodedRef[0]]);
        }else{
            $exploded = $explodedRef[1]."_".$explodedRef[2];
            unset($_SESSION['OCS']['SNMP']['multi_search'][$exploded][$explodedRef[0]]);
            
			if(empty($_SESSION['OCS']['SNMP']['multi_search'][$exploded])){
                unset($_SESSION['OCS']['SNMP']['multi_search'][$exploded]);
            }
        }
        if(empty($_SESSION['OCS']['SNMP']['multi_search'][$explodedRef[1]])){
            unset($_SESSION['OCS']['SNMP']['multi_search'][$explodedRef[1]]);
        }
    }

	 /**
     * Get the type of the searched field
     *
     * @param String $tablename
     * @param String $fieldsname
     * @return void
     */
    public function getSearchedFieldType($tablename, $fieldsname) {
        $tableFields = $this->databaseSearch->getColumnsSnmpList($tablename);
        return $tableFields[$fieldsname][DatabaseSearch::TYPE] ?? '';
    }

	/**
     * Generate HTML select options for operators
     *
     * @param String $defaultValue
     * @return void
     */
    public function getSelectOptionForOperators($defaultValue, $table = null, $field = null) {
        $html = "";
        if(isset($field) && $this->getSearchedFieldType($table, $field) == 'datetime') {
            $operatorList = array_merge($this->search->operatorList, $this->search->operatorDelay);
        } else {
            $operatorList = $this->search->operatorList;
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

        $fieldId = $this->search->getFieldUniqId($uniqid, $tableName);
        $this->type = $this->getSearchedFieldType($tableName, $fieldsInfos[self::SESS_FIELDS]);

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

            default:
                $html = '<input class="form-control" type="text" name="'.$fieldId.'" id="'.$fieldId.'" value="'.$fieldsInfos[self::SESS_VALUES].'" '.$attr.'>';
                break;
        }

        return $html;
    }

	/**
     * Generate select query for table using session variables generated from the search
     *
     * @param String $tableName
     * @return void
     */
	private function pushBaseQueryForTable($tableName) {
        foreach($this->databaseSearch->getColumnsSnmpList($tableName) as $fieldsInfos) {
			$selectAs = $tableName.$fieldsInfos['Field'];
			$this->baseQuery .= " %s.%s AS ".$selectAs." ,";
			$this->queryArgs[] = $tableName;
			$this->queryArgs[] = $fieldsInfos['Field'];

			if($fieldsInfos['Field'] == 'LASTDATE' || $fieldsInfos['Field'] == 'ID') {
				$this->fieldsList[$this->translationSearch->getTranslationFor($fieldsInfos['Field'])] = $selectAs;
			} else {
				$this->fieldsList[$fieldsInfos['Field']] = $selectAs;
			}
        }
    }

	/**
     * Generate search query (operator and values)
     *
     * @param Array $sessData
     * @return void
     */
	public function generateSearchQuery($sessData, $tablename) {
		$index = 0;
        $pIndex = 0;

		$this->pushBaseQueryForTable($tablename, null);

		$this->queryArgs[] = $tablename;

		foreach ($sessData as $tableName => $searchInfos) {
			foreach ($searchInfos as $value){
                if(isset($value['comparator'])) {
                    $operator[] = $value['comparator'];
                } elseif ($index != 0 && !isset($value['comparator'])) {
                    $operator[] = "AND";
                } else {
                    $operator[] = "";
                }

                $index++;
            }

			$isSameColumn = [];
			$columnName = [];
			$doesntcontainmulti = [];

			foreach ($searchInfos as $index => $value) {
				$values[] = $value;
				$columnName[$index] = $value['fields'];
				$containvalue[$index] = $value['operator'];
		  	}

			foreach($searchInfos as $value) {
				$nameTable = $tableName;
				$open = "";
				$close = "";

				// Generate conditions
				$this->search->getOperatorSign($value);

				foreach(array_count_values($columnName) as $name => $nb) {
					if($nb > 1) $isSameColumn[$nameTable] = $name;
				}

				foreach(array_count_values($containvalue) as $name => $nb) {
					if($nb > 1) $doesntcontainmulti[$nameTable] = $name;
				}

				if($pIndex == 0 && isset($operator[$pIndex+1]) && $operator[$pIndex+1] == 'OR') $open = "(";
				if($operator[$pIndex] =='OR' && (!isset($operator[$pIndex+1]) || $operator[$pIndex+1] !='OR')) $close = ")";
				if($pIndex != 0 && $operator[$pIndex] !='OR' && isset($operator[$pIndex+1]) && $operator[$pIndex+1] =='OR') $open = "(";

				unset($value['ignore']);

				// If isSameColumn not empty
				if(!empty($isSameColumn) && $isSameColumn[$nameTable] == $value[self::SESS_FIELDS] && $value[self::SESS_OPERATOR] != "DOESNTCONTAIN") {
					if($value[self::SESS_OPERATOR] != "IS NULL"){
						if($value[self::SESS_OPERATOR] != "NOT IN" && $value[self::SESS_OPERATOR] != "ISNOTEMPTY") {
							$this->columnsQueryConditions .= "$operator[$pIndex] $open EXISTS (SELECT 1 FROM `%s` WHERE %s.%s %s '%s') $close ";
							$this->queryArgs[] = $nameTable;
							$this->queryArgs[] = $nameTable;
							$this->queryArgs[] = $value[self::SESS_FIELDS];
							$this->queryArgs[] = $value[self::SESS_OPERATOR];
							$this->queryArgs[] = $value[self::SESS_VALUES];
						} elseif($value[self::SESS_OPERATOR] == "NOT IN") {
							$this->columnsQueryConditions .= "$operator[$pIndex] $open EXISTS (SELECT 1 FROM `%s` WHERE %s.%s %s (%s)) $close ";
							$this->queryArgs[] = $nameTable;
							$this->queryArgs[] = $nameTable;
							$this->queryArgs[] = $value[self::SESS_FIELDS];
							$this->queryArgs[] = $value[self::SESS_OPERATOR];
							$this->queryArgs[] = $value[self::SESS_VALUES];
						} elseif($value[self::SESS_OPERATOR] == "ISNOTEMPTY") {
							$this->columnsQueryConditions .= "$operator[$pIndex] $open EXISTS (SELECT 1 FROM `%s` WHERE %s.%s IS NOT NULL AND TRIM(%s.%s) != '') $close ";
							$this->queryArgs[] = $nameTable;
							$this->queryArgs[] = $nameTable;
							$this->queryArgs[] = $value[self::SESS_FIELDS];
							$this->queryArgs[] = $nameTable;
							$this->queryArgs[] = $value[self::SESS_FIELDS];
						} elseif(in_array($value[self::SESS_OPERATOR], $this->search->operatorDelay)) {
							$this->columnsQueryConditions .= "$operator[$pIndex] $open EXISTS (SELECT 1 FROM `%s` WHERE %s.%s %s NOW() - INTERVAL %s DAY) $close ";
							$this->queryArgs[] = $nameTable;
							$this->queryArgs[] = $nameTable;
							$this->queryArgs[] = $value[self::SESS_FIELDS];
							if($value[self::SESS_OPERATOR] == "MORETHANXDAY") { $op = "<"; } else { $op = ">"; }
							$this->queryArgs[] = $op;
							$this->queryArgs[] = $value[self::SESS_VALUES];
						} elseif($this->getSearchedFieldType($nameTable, $value[self::SESS_FIELDS]) == 'datetime' && !in_array($value[self::SESS_OPERATOR], $this->search->operatorDelay)) {
							$this->columnsQueryConditions .= "$operator[$pIndex] $open %s.%s %s str_to_date('%s', '%s') $close ";
							$this->queryArgs[] = $nameTable;
							$this->queryArgs[] = $value[self::SESS_FIELDS];
							$this->queryArgs[] = $value[self::SESS_OPERATOR];
							$this->queryArgs[] = $value[self::SESS_VALUES];
							global $l;
							$this->queryArgs[] = $l->g(269);
						} else {
							$this->columnsQueryConditions .= "$operator[$pIndex] $open (%s.%s %s '%s') $close ";
							$this->queryArgs[] = $nameTable;
							$this->queryArgs[] = $value[self::SESS_FIELDS];
							$this->queryArgs[] = $value[self::SESS_OPERATOR];
							$this->queryArgs[] = $value[self::SESS_VALUES];
						}
					} else {
						$this->columnsQueryConditions .= "$operator[$pIndex] $open EXISTS (SELECT 1 FROM `%s` WHERE %s.%s %s) $close ";
						$this->queryArgs[] = $nameTable;
						$this->queryArgs[] = $nameTable;
						$this->queryArgs[] = $value[self::SESS_FIELDS];
						$this->queryArgs[] = $value[self::SESS_OPERATOR];
					}
				} elseif($value[self::SESS_OPERATOR] == 'IS NULL' && (empty($isSameColumn))) {
					$this->columnsQueryConditions .= "$operator[$pIndex] $open (%s.%s IS NULL OR TRIM(%s.%s) = '') $close ";
					$this->queryArgs[] = $nameTable;
					$this->queryArgs[] = $value[self::SESS_FIELDS];
					$this->queryArgs[] = $nameTable;
					$this->queryArgs[] = $value[self::SESS_FIELDS];
				} elseif($value[self::SESS_OPERATOR] == "ISNOTEMPTY") {
					$this->columnsQueryConditions .= "$operator[$pIndex] $open %s.%s IS NOT NULL AND TRIM(%s.%s) != '' $close ";
					$this->queryArgs[] = $nameTable;
					$this->queryArgs[] = $value[self::SESS_FIELDS];
					$this->queryArgs[] = $nameTable;
					$this->queryArgs[] = $value[self::SESS_FIELDS];
				} elseif($value[self::SESS_OPERATOR] == "NOT IN" && $value[self::SESS_OPERATOR] == "DOESNTCONTAIN") {
					$this->columnsQueryConditions .= "$operator[$pIndex] $open %s.%s %s (%s) $close ";
					$this->queryArgs[] = $nameTable;
					$this->queryArgs[] = $value[self::SESS_FIELDS];
					$this->queryArgs[] = 'NOT IN';
					$this->queryArgs[] = $value[self::SESS_VALUES];
				} elseif($this->getSearchedFieldType($nameTable, $value[self::SESS_FIELDS]) == 'datetime' && !in_array($value[self::SESS_OPERATOR], $this->search->operatorDelay)) {
					$this->columnsQueryConditions .= "$operator[$pIndex] $open %s.%s %s str_to_date('%s', '%s') $close ";
					$this->queryArgs[] = $nameTable;
					$this->queryArgs[] = $value[self::SESS_FIELDS];
					$this->queryArgs[] = $value[self::SESS_OPERATOR];
					$this->queryArgs[] = $value[self::SESS_VALUES];
					global $l;
					$this->queryArgs[] = $l->g(269);
				} elseif(in_array($value[self::SESS_OPERATOR], $this->search->operatorDelay)) {
					$this->columnsQueryConditions .= "$operator[$pIndex] $open %s.%s %s NOW() - INTERVAL %s DAY $close ";
					$this->queryArgs[] = $nameTable;
					$this->queryArgs[] = $value[self::SESS_FIELDS];
					if($value[self::SESS_OPERATOR] == "MORETHANXDAY") { $op = "<"; } else { $op = ">"; }
					$this->queryArgs[] = $op;
					$this->queryArgs[] = $value[self::SESS_VALUES];
				} else {
					$this->columnsQueryConditions .= "$operator[$pIndex] $open %s.%s %s '%s' $close ";
					$this->queryArgs[] = $nameTable;
					$this->queryArgs[] = $value[self::SESS_FIELDS];
					$this->queryArgs[] = $value[self::SESS_OPERATOR];
					$this->queryArgs[] = $value[self::SESS_VALUES];
				}
				$pIndex++;
			}
		}

		$this->columnsQueryConditions = "WHERE".$this->columnsQueryConditions;
		$this->columnsQueryConditions .= " GROUP BY ID";
        $this->baseQuery = substr($this->baseQuery, 0, -1);
	}
}