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
     * Excluded tables
     */
    private $excludedTables = [
        "accountinfo",
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
     * Construct
     */
    function __construct()
    {
        $this->dbObject = $_SESSION['OCS']["readServer"];
        $this->dbName = DB_NAME;
        $this->retrieveTablesList();
    }

    /**
     * Get columnsList property
     */
    public function getColumnsList($tableName)
    {
        return $this->columnsList[$tableName];
    }

    /**
     * Get tableList property
     */
    public function getTablesList()
    {
        return $this->tableList;
    }

    /**
     * Retrieve tables list from database
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
     * Retrieve columns list from table $tableName
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
                && !array_key_exists(self::SNMP_COL_REF, $this->columnsList[$tableName]) 
            ) {
                unset($this->columnsList[$tableName]);
                $this->removeValueFromTableList($tableName);
            }
        } else {
            $this->removeValueFromTableList($tableName);
        }

    }

    /**
     * Get field type simplified without the length 
     * ( ie : varchar(255) => varchar )
     */
    private function normalizeFieldType($type)
    {
        $splittedType = preg_replace('/\(.*?\)|\s*/', '', $type);
        return $splittedType;
    }

    /**
     * 
     */
    private function removeValueFromTableList($tableName)
    {
        if (($key = array_search($tableName, $this->tableList)) !== false) {
            unset($this->tableList[$key]);
        }
    }
     
}
 