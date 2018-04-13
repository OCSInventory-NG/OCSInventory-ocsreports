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
     * Construct
     */
    function __construct($translationSearch, $databaseSearch, $accountinfoSearch) 
    {
        $this->translationSearch = $translationSearch;
        $this->databaseSearch = $databaseSearch;
        $this->accountinfoSearch = $accountinfoSearch;
    }

    public function getSelectOptionForTables($defautValue = null)
    {
        $html = "<option>----------</option>";
        foreach ($this->databaseSearch->getTablesList() as $tableName) {
            
            // TODO: Add translation
            if ($defautValue == $tableName) {
                $html .= "<option selected value=".$tableName." >".$tableName."</option>";
            } else {
                $html .= "<option value=".$tableName." >".$tableName."</option>";
            }
            
        }
        return $html;
    }

    public function getSelectOptionForColumns($tableName = null)
    {
        $html = "";
        foreach ($this->databaseSearch->getColumnsList($tableName) as $index => $fieldsInfos) {
            if(!in_array($fieldsInfos[DatabaseSearch::FIELD], $this->excludedVisuColumns)){
                // TODO: Add translation
                $html .= "<option value=".$fieldsInfos[DatabaseSearch::FIELD]." >".
                $fieldsInfos[DatabaseSearch::FIELD].
                "</option>";
            }
        }
        return $html;
    }

    public function addSessionsInfos($postData)
    {
        $_SESSION['OCS']['multi_search'][$postData['table_select']][uniqid()] = [
            self::SESS_FIELDS => $postData['columns_select'],
            self::SESS_VALUES => null,
        ];
    }

    public function updateSessionsInfos($postData)
    {
        
    }

    public function processSearchFields($tablename, $searchinfos)
    {
        foreach ($searchinfos as $uniqid => $fieldsInfos) {
            $fieldType = $this->getSearchedFieldType(
                $tablename, 
                $fieldsInfos[self::SESS_FIELDS]
            );
            //$this->generateHtmlFieldsFor
        }
    }

    private function getSearchedFieldType($tablename, $fieldsname)
    {
        $tableFields = $this->databaseSearch->getColumnsList($tablename);
        return $tableFields[$fieldsname][DatabaseSearch::TYPE];
    }

 }
 