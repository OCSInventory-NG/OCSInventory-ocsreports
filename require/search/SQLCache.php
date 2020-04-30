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
  * This class create SQL Query cache for dynamic group
  */
 class SQLCache
 {

    const GROUP_TABLE = "groups_cache";

    private $search;
    private $software;
    private $searchQuery;
    private $columnsQueryConditions;

    /**
     * @param Search $search
     */
    function __construct($search, $software) {
        $this->search = $search;
        $this->software = $software;
        $this->searchQuery = "SELECT DISTINCT hardware.ID FROM hardware ";
    }

    /**
     * Generate SQL query for dynamic Group
     *
     * @param array $sessData
     * @return string
     */
    public function generateCacheSql($sessData){
        global $l;
        
        $i = 0;
        $p = 0;

        foreach ($sessData as $tableName => $searchInfos) {

            if($tableName != "hardware"){
                // Generate union
                $this->searchQuery .= "INNER JOIN $tableName on hardware.id = $tableName.hardware_id ";
            }

            if($tableName == SoftwareSearch::SOFTWARE_TABLE) {
				$this->searchQuery .= "LEFT JOIN software_name on software_name.id = $tableName.name_id ";
				$this->searchQuery .= "LEFT JOIN software_publisher on software_publisher.id = $tableName.publisher_id ";
				$this->searchQuery .= "LEFT JOIN software_version on software_version.id = $tableName.version_id ";
            }

            foreach ($searchInfos as $index => $value) {
                $nameTable = $tableName;
                if($nameTable == "download_history" && $value['fields'] == "PKG_NAME") {
                    // Generate union
                    $this->searchQuery .= "INNER JOIN download_available on download_available.FILEID = $tableName.PKG_ID ";
                }
                if($value['comparator'] != null){
                    $operator[] = $value['comparator'];
                }elseif($i != 0 && $value['comparator'] == null){
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

            foreach ($searchInfos as $index => $value) {
                $open="";
                $close="";
                // Generate condition
                $this->search->getOperatorSign($value);
                if($nameTable == SoftwareSearch::SOFTWARE_TABLE) {
					$nameTable = $this->software->getTableName($value['fields']);
					$value[Search::SESS_FIELDS] = $this->software->getColumnName($value['fields']);
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

                if($p == 0 && $operator[$p+1] == 'OR'){
                    $open = "(";
                }if($operator[$p] =='OR' && $operator[$p+1] !='OR'){
                    $close=")";
                }if($p != 0 && $operator[$p] !='OR' && $operator[$p+1] =='OR'){
                    $open = "(";
                }

                unset($value['ignore']);

                if($value[Search::SESS_OPERATOR] == "DOESNTCONTAIN" && empty($doesntcontainmulti)){
                    $excluID = $this->search->contain($value, $nameTable);
                    if($nameTable != DatabaseSearch::COMPUTER_DEF_TABLE){
                        $value[Search::SESS_FIELDS] = "HARDWARE_ID";
                    }else{
                        $value[Search::SESS_FIELDS] = "ID";
                    }
                    
                    $value[Search::SESS_VALUES] = implode(',', $excluID);
                    $value[Search::SESS_OPERATOR] = "NOT IN";

                }elseif($value[Search::SESS_OPERATOR] == "DOESNTCONTAIN" && !empty($isSameColumn) && !empty($doesntcontainmulti)){
                    $excluID = $this->search->containmulti($isSameColumn, $searchInfos);
                    if($nameTable != DatabaseSearch::COMPUTER_DEF_TABLE){
                        $value[Search::SESS_FIELDS] = "HARDWARE_ID";
                    }else{
                        $value[Search::SESS_FIELDS] = "ID";
                    }
                    
                    $value[Search::SESS_VALUES] = implode(',', $excluID);
                    $value[Search::SESS_OPERATOR] = "NOT IN";
                    $value['ignore'] = "";
                }

                $argFields = $value[Search::SESS_FIELDS];
                $argOperators = $value[Search::SESS_OPERATOR];
                $argValues = $value[Search::SESS_VALUES];

                if(!empty($isSameColumn) && $isSameColumn[$nameTable] == $value[Search::SESS_FIELDS] 
                            && !array_key_exists("ignore", $value) && !array_key_exists('devices', $isSameColumn)){
                    if($value[Search::SESS_OPERATOR] != "IS NULL"){
                        if ($nameTable != DatabaseSearch::COMPUTER_DEF_TABLE&& $nameTable != self::GROUP_TABLE && $value[Search::SESS_FIELDS] != 'CATEGORY_ID' && $value[Search::SESS_FIELDS] != 'CATEGORY' 
                        && $value[Search::SESS_OPERATOR] != "NOT IN") {
                            $this->columnsQueryConditions .= "$operator[$p] $open EXISTS (
                                    SELECT 1 FROM $nameTable 
                                    WHERE hardware.ID = $nameTable.HARDWARE_ID 
                                    AND $nameTable.$argFields $argOperators '$argValues')$close ";
                        }elseif($nameTable == self::GROUP_TABLE || $value[Search::SESS_FIELDS] == 'CATEGORY_ID' || $value[Search::SESS_FIELDS] == 'CATEGORY' 
                        || $value[Search::SESS_OPERATOR] == "NOT IN"){
                            if($nameTable == self::GROUP_TABLE){
                                $argValues = $this->search->groupSearch->get_all_id($value[Search::SESS_VALUES]);
                                $this->columnsQueryConditions .= "$operator[$p] $open EXISTS (
                                        SELECT 1 FROM $nameTable 
                                        WHERE hardware.ID = $nameTable.HARDWARE_ID 
                                        AND hardware.ID $argOperators ($argValues))$close ";
                            }else{
                                $this->columnsQueryConditions .= "$operator[$p] $open EXISTS (
                                    SELECT 1 FROM $nameTable 
                                    WHERE hardware.ID = $nameTable.HARDWARE_ID 
                                    AND $nameTable.$argFields $argOperators ($argValues))$close ";
                            }    
                        }else{
                            if($value[Search::SESS_OPERATOR] == "MORETHANXDAY" || $value[Search::SESS_OPERATOR] == "LESSTHANXDAY") {
                                if($value[Search::SESS_OPERATOR] == "MORETHANXDAY") { $op = "<"; } else { $op = ">"; }
                                $this->columnsQueryConditions .= "$operator[$p] $open EXISTS (
                                        SELECT 1 FROM $nameTable 
                                        WHERE $nameTable.$argFields $op NOW() - INTERVAL $argValues DAY)$close ";
                            } else {
                                $this->columnsQueryConditions .= "$operator[$p] $open EXISTS (
                                        SELECT 1 FROM $nameTable 
                                        WHERE $nameTable.$argFields $argOperators '$argValues')$close ";
                            }
                        }
                    }else{
                        if ($nameTable != DatabaseSearch::COMPUTER_DEF_TABLE) {
                            $this->columnsQueryConditions .= "$operator[$p] $open EXISTS (
                                    SELECT 1 FROM $nameTable 
                                    WHERE hardware.ID = $nameTable.HARDWARE_ID 
                                    AND $nameTable.$argFields $argOperators)$close ";
                        }else{
                            $this->columnsQueryConditions .= "$operator[$p] $open EXISTS (
                                    SELECT 1 FROM $nameTable 
                                    WHERE $nameTable.$argFields $argOperators)$close ";
                        }
                    }
                }elseif($value[Search::SESS_OPERATOR] == 'IS NULL' && (empty($isSameColumn))){
                    $this->columnsQueryConditions .= "$operator[$p] $open $nameTable.$argFields $argOperators $close ";
                }elseif($nameTable == Search::GROUP_TABLE || $value[Search::SESS_FIELDS] == 'CATEGORY_ID' || $value[Search::SESS_FIELDS] == 'CATEGORY' 
                            || $value[Search::SESS_OPERATOR] == "NOT IN"){
                    if($nameTable == Search::GROUP_TABLE){
                        $groupid = $this->search->groupSearch->get_all_id($value[Search::SESS_VALUES]);
                        $this->columnsQueryConditions .= "$operator[$p] $open hardware.ID $argOperators ($groupid)$close ";
                    }else{
                        $this->columnsQueryConditions .= "$operator[$p] $open $nameTable.$argFields $argOperators ($argValues)$close ";
                    }
                }else if(($value[Search::SESS_FIELDS] == 'LASTCOME' || $value[Search::SESS_FIELDS] == 'LASTDATE') && $value[Search::SESS_OPERATOR] != "MORETHANXDAY" && $value[Search::SESS_OPERATOR] != "LESSTHANXDAY" ){
                    $trad = $l->g(269);
                    $this->columnsQueryConditions .= "$operator[$p] $open $nameTable.$argFields $argOperators str_to_date('$argValues', '$trad')$close ";
                }elseif($value[Search::SESS_OPERATOR] == "MORETHANXDAY" || $value[Search::SESS_OPERATOR] == "LESSTHANXDAY") {
                    if($value[Search::SESS_OPERATOR] == "MORETHANXDAY") { $op = "<"; } else { $op = ">"; }
                    $this->columnsQueryConditions .= "$operator[$p] $open $nameTable.$argFields $op NOW() - INTERVAL $argValues DAY $close ";
                }else{
                    if($nameTable == "download_history" && $value[Search::SESS_FIELDS] == "PKG_NAME"){
                        $this->columnsQueryConditions .= "$operator[$p] $open download_available.NAME $argOperators '$argValues' $close ";
                    }else{
                        $this->columnsQueryConditions .= "$operator[$p] $open $nameTable.$argFields $argOperators '$argValues' $close ";
                    }
                }
                $p++;
            }
        }
        $this->columnsQueryConditions = "WHERE".$this->columnsQueryConditions;

        $this->columnsQueryConditions .= " GROUP BY hardware.id";

        $this->searchQuery .= $this->columnsQueryConditions;

        return $this->searchQuery;
    }

 }
