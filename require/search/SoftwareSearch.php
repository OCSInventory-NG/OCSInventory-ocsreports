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
  * This class implement basic behavior for software search management
  */
 class SoftwareSearch
 {
    const NAME_TABLE = "software_name";
    const PUBLISHER_TABLE = "software_publisher";
    const VERSION_TABLE = "software_version";
    const SOFTWARE_TABLE = "software";

    public $tables = [
        self::SOFTWARE_TABLE,
        self::NAME_TABLE,
    ];

    private $dbObject;
    private $columnsList = [];

    /**
     * Constructor
     */
    function __construct()
    {
        $this->dbObject = $_SESSION['OCS']["readServer"];
    }

    /**
     * Retrieve software columns
     */
    public function retrieveSoftwareColumns() {
        foreach ($this->tables as $table) {
            $columnsList = mysql2_query_secure("SHOW COLUMNS FROM %s", $this->dbObject, $table);
            while ($columnsInfos = mysqli_fetch_array($columnsList)) {
                if($table == self::SOFTWARE_TABLE || $columnsInfos['Field'] == "CATEGORY") {
                    $columnsInfos[DatabaseSearch::TYPE] = $this->normalizeFieldType($columnsInfos['Type']);
                    $this->columnsList[self::SOFTWARE_TABLE][$columnsInfos['Field']] = [
                        DatabaseSearch::FIELD => $columnsInfos[DatabaseSearch::FIELD],
                        DatabaseSearch::TYPE => $columnsInfos[DatabaseSearch::TYPE],
                        DatabaseSearch::NULLABLE => $columnsInfos[DatabaseSearch::NULLABLE],
                        DatabaseSearch::KEY => $columnsInfos[DatabaseSearch::KEY],
                        DatabaseSearch::DEFAULT_VAL => $columnsInfos[DatabaseSearch::DEFAULT_VAL],
                        DatabaseSearch::EXTRA => $columnsInfos[DatabaseSearch::EXTRA],
                    ];
                } 
            }
        }
        return $this->columnsList;
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
        return preg_replace('/\(.*?\)|\s*/', '', $type);
    }

    /**
     *  Get Software table name
     */
    public function getTableName($field) {
        return match ($field) {
            "NAME_ID" => self::NAME_TABLE,
            "PUBLISHER_ID" => self::PUBLISHER_TABLE,
            "VERSION_ID" => self::VERSION_TABLE,
            "CATEGORY" => self::NAME_TABLE,
            default => self::SOFTWARE_TABLE,
        };
    }

    /**
     * Get software column name
     */
    public function getColumnName($field) {
        return match ($field) {
            "NAME_ID" => "NAME",
            "PUBLISHER_ID" => "PUBLISHER",
            "VERSION_ID" => "VERSION",
            default => $field,
        };
    }

 }