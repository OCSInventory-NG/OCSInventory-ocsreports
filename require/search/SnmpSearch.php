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
	private $databaseSearch;
    private $accountinfoSearch;
	private $search;
	private $translationSearch;

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
}