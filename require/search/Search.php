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
     * Final query and args used for multicrits
     */
    private $finalQuery;
    private $finalArgs;

    /**
     * Construct
     */
    function __construct($translationSearch, $databaseSearch, $accountinfoSearch) 
    {
        $this->translationSearch = $translationSearch;
        $this->databaseSearch = $databaseSearch;
        $this->accountinfoSearch = $accountinfoSearch;
    }

    public function addSessionsInfos($postData)
    {
        $_SESSION['OCS']['multi_search'][$postData['table_select']][uniqid()] = [
            self::SESS_FIELDS => $postData['columns_select'],
            self::SESS_VALUES => null,
            self::SESS_OPERATOR => null,
        ];
    }

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

    public function getSearchedFieldType($tablename, $fieldsname)
    {
        $tableFields = $this->databaseSearch->getColumnsList($tablename);
        return $tableFields[$fieldsname][DatabaseSearch::TYPE];
    }

    public function getTranslationFor($name)
    {
        //TODO: Translation
        return $name;
    }

    public function getOperatorUniqId($uniqid, $tableName)
    {
        return $uniqid."_".$tableName."_".self::SESS_OPERATOR;
    }

    public function getFieldUniqId($uniqid, $tableName)
    {
        return $uniqid."_".$tableName."_".self::SESS_FIELDS;
    }

    public function generateSearchQuery($sessData){
        var_dump($sessData);
    }

    /**
     * Below all show method
     * Bad method but no choice since there isn't any templating render system
     */

    public function getSelectOptionForOperators($defaultValue)
    {

        $html = "";

        // TODO: Add translation
        foreach ($this->operatorList as $value) {
            if ($defaultValue == $value) {
                $html .= "<option selected value=".$value." >".$value."</option>";
            } else {
                $html .= "<option value=".$value." >".$value."</option>";
            }
        }

        return $html;
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
            if(!in_array($fieldsIndefaultTablefos[DatabaseSearch::FIELD], $this->excludedVisuColumns)){
                // TODO: Add translation
                $html .= "<option value=".$fieldsInfos[DatabaseSearch::FIELD]." >".
                $fieldsInfos[DatabaseSearch::FIELD].
                "</option>";
            }
        }
        return $html;
    }

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

 }
 