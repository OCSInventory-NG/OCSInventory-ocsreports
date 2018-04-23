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
  * This class implement method needed to get database tables
  * It also permit de get columns with their type
  * Used in new search
  */
class DatabaseSearch
{

    /**
     * Constants
     */
    const FIELD = 'Field';
    const TYPE = 'Type';
    const NULLABLE = 'Nullable';
    const KEY = 'Key';
    const DEFAULT_VAL = 'Default';
    const EXTRA = 'Extra';

    /**
     * SNMP / COMPUTER ref columns constant
     */
    const COMPUTER_COL_RED = 'HARDWARE_ID';
    const SNMP_COL_REF = 'SNMP_ID';

    /**
     * Default table
     */
    const COMPUTER_DEF_TABLE = "hardware";
    const SNMP_DEF_TABLE = "snmp";

    /**
     * Excluded tables
     */
    private $excludedTables = [
        "accountinfo",
        "download_servers",
        "groups_cache",
        "itmgmt_comments",
        "javainfo",
        "journallog"
    ];

    /**
     * Data storage property
     */
    private $tableList = [];
    private $columnsList = [];

    /**
     * Default query
     */
    private $tableQuery = "SHOW TABLES FROM %s";
    private $columnsQuery = "SHOW COLUMNS FROM %s";

    /**
     * Objects
     */
    private $dbObject = null;
    private $dbName = null;

    /**
     * Undocumented function
     */
    function __construct()
    {
        $this->dbObject = $_SESSION['OCS']["readServer"];
        $this->dbName = DB_NAME;
        $this->retrieveTablesList();
    }

    /**
     * Undocumented function
     *
     * @param [type] $tableName
     * @return void
     */
    public function getColumnsList($tableName)
    {
        return $this->columnsList[$tableName];
    }

    /**
    * Undocumented function
    *
    * @return void
    */
    public function getTablesList()
    {
        return $this->tableList;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    private function retrieveTablesList()
    {

        if (empty($this->dbObject) || empty($this->dbName)) {
            return;
        }

        $tableList = mysql2_query_secure($this->tableQuery, $this->dbObject, $this->dbName);

        while ($tableInfos = mysqli_fetch_array($tableList)) {
            $this->tableList[] = $tableInfos[0];
            $this->retireveColumnsList($tableInfos[0]);
        }

    }

    /**
     * Undocumented function
     *
     * @param [type] $tableName
     * @return void
     */
    private function retireveColumnsList($tableName)
    {

        if (!in_array($tableName, $this->excludedTables)) {
            $columnsList = mysql2_query_secure($this->columnsQuery, $this->dbObject, $tableName);

            while ($columnsInfos = mysqli_fetch_array($columnsList)) {
                $columnsInfos[self::TYPE] = $this->normalizeFieldType($columnsInfos['Type']);
                $this->columnsList[$tableName][$columnsInfos['Field']] = [
                    self::FIELD => $columnsInfos[self::FIELD],
                    self::TYPE => $columnsInfos[self::TYPE],
                    self::NULLABLE => $columnsInfos[self::NULLABLE],
                    self::KEY => $columnsInfos[self::KEY],
                    self::DEFAULT_VAL => $columnsInfos[self::DEFAULT_VAL],
                    self::EXTRA => $columnsInfos[self::EXTRA],
                ];
            }
            // Remove tables that doesn't reference a computer or an snmp device
            if (!array_key_exists(self::COMPUTER_COL_RED, $this->columnsList[$tableName]) 
                && $tableName !== self::COMPUTER_DEF_TABLE
            ) {
                unset($this->columnsList[$tableName]);
                $this->removeValueFromTableList($tableName);
            }
        } else {
            $this->removeValueFromTableList($tableName);
        }

    }

    /**
     * Undocumented function
     *
     * @param Search $searchObj
     * @return void
     */
    public function getIdList(Search $searchObj){
        $query = $searchObj->baseQuery.$searchObj->searchQuery.$searchObj->columnsQueryConditions;
        $idList = mysql2_query_secure($query, $this->dbObject, $searchObj->queryArgs);
        $idArray = [];
        foreach ($idList as $index => $fields) {
            $idArray[] = $fields['hardwareID'];
        }
        return $idArray;
    }

    /**
     * Undocumented function
     *
     * @param [type] $type
     * @return void
     */
    private function normalizeFieldType($type)
    {
        $splittedType = preg_replace('/\(.*?\)|\s*/', '', $type);
        return $splittedType;
    }

    /**
     * Undocumented function
     *
     * @param [type] $tableName
     * @return void
     */
    private function removeValueFromTableList($tableName)
    {
        if (($key = array_search($tableName, $this->tableList)) !== false) {
            unset($this->tableList[$key]);
        }
    }
     
}
 