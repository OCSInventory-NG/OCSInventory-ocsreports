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
        /*"accountinfo",*/
        "download_servers",
        "itmgmt_comments",
        "javainfo",
        "journallog",
        "groups",
        "accesslog"
    ];

    /**
     * Data storage property
     */
    private $tableList = [];
    private $columnsList = [];

    /**
     * Default query
     */
    private $tableQuery = "SHOW TABLES FROM `%s`";
    private $columnsQuery = "SHOW COLUMNS FROM `%s`";

    /**
     * Objects
     */
    private $dbObject = null;
    private $dbName = null;
    private $softwareSearch = null;

    /**
     * Constructor
     */
    function __construct($softwareSearch)
    {
        $this->dbObject = $_SESSION['OCS']["readServer"];
        $this->dbName = DB_NAME;
        $this->softwareSearch = $softwareSearch;
        $this->retrieveTablesList();
    }

    /**
     * Get the database columns of $tableName
     *
     * @param String $tableName
     * @return Array columnsList
     */
    public function getColumnsList($tableName)
    {
        return $this->columnsList[$tableName];
    }

    /**
    * Get tables list of the current database
    *
    * @return Array tablesList
    */
    public function getTablesList()
    {
        return $this->tableList;
    }

    /**
     * Retrieve tables list from the current database
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
     * Retrieve columns list from the current database
     *
     * Will use excludedTales property and COMPUTER_COL_RED const
     * to see if columns need to be retrieved
     *
     * @param String $tableName
     * @return void
     */
    private function retireveColumnsList($tableName)
    {
        if (!in_array($tableName, $this->excludedTables)) {
            if($tableName != SoftwareSearch::SOFTWARE_TABLE) {
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
                    && $tableName !== self::COMPUTER_DEF_TABLE) {
                    unset($this->columnsList[$tableName]);
                    $this->removeValueFromTableList($tableName);
                }
            } else {
                $this->columnsList = array_merge($this->columnsList, $this->softwareSearch->retrieveSoftwareColumns());
            }
        } else {
            $this->removeValueFromTableList($tableName);
        }
    }

    /**
     * Get an list of id of the current multi search (needed for buttons at the bottom of the page)
     *
     * @param Search $searchObj
     * @return Array list of computers ID
     */
    public function getIdList(Search $searchObj){
        $query = $searchObj->baseQuery.$searchObj->searchQuery.$searchObj->columnsQueryConditions;
        $idList = mysql2_query_secure($query, $this->dbObject, $searchObj->queryArgs);
        $idArray = [];

        if($idList) foreach ($idList as $index => $fields) {
            $idArray[] = $fields['hardwareID'];
        }
        return $idArray;
    }

    /**
     * Normalize the field type
     * Exemple : from varchar(255) to varchar
     * This will be used to determine generated html
     *
     * @param String $type
     * @return String Normalized field type
     */
    private function normalizeFieldType($type)
    {
        $splittedType = preg_replace('/\(.*?\)|\s*/', '', $type);
        return $splittedType;
    }

    /**
     * Remove value from table list property
     *
     * @param String $tableName
     * @return void
     */
    private function removeValueFromTableList($tableName)
    {
        if (($key = array_search($tableName, $this->tableList)) !== false) {
            unset($this->tableList[$key]);
        }
    }

    public function get_package_id($fileid){
        $sql= "SELECT id FROM download_enable d_e LEFT JOIN download_available d_a ON d_a.fileid=d_e.fileid
                WHERE 1=1 AND d_a.comment NOT LIKE '%[VISIBLE=0]%' AND d_e.fileid='".$fileid."'";
        $idPackage = mysql2_query_secure($sql, $this->dbObject);
        foreach ($idPackage as $index => $fields) {
            $idArray[] = $fields['id'];
        }
        return $idArray;
    }
}
