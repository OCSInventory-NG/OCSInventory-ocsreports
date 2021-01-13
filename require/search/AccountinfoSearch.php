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

require_once 'require/function_admininfo.php';

 /**
  * This class implement basic behavior for accountinfo search management
  */
 class AccountinfoSearch
 {

    /**
     * Constants
     */
    const ACC_TYPE_COMPUTER = 'COMPUTERS';
    const ACC_TYPE_SNMP = 'SNMP';

    /**
     * Account infos data property
     */
    private $accountInfosList = [];

    /**
     * Specific account infos data property
     */
    private $specificAccountInfos = [
        "TAG",
    ];

    /**
     * Query
     */
    private $accountInfoConfigQuery= "SELECT * FROM `accountinfo_config`";

    /**
     * Objects
     */
    private $dbObject = null;
    private $dbName = null;
    private $accountInfosStruct = null;


    /**
     * Construct
     */
    function __construct() {
        $this->dbObject = $_SESSION['OCS']["readServer"];
        $this->dbName = DB_NAME;
        $this->createAccountInfoStruct();
        $this->retrieveAccountInfosConfig();
    }

    /**
     * Create account info struct
     */
    private function createAccountInfoStruct(){
        $this->accountInfosStruct = new StdClass();
        $this->accountInfosStruct->id = "ID";
        $this->accountInfosStruct->comment = "COMMENT";
        // Only for TAG
        $this->accountInfosStruct->nameacc = "NAME_ACCOUNTINFO";
    }

    /**
     * Get accountinfos datamap for multi criteria
     */
    private function retrieveAccountInfosConfig(){
        $accountInfosConfig = mysql2_query_secure($this->accountInfoConfigQuery, $this->dbObject);

        while ($accountInfos = mysqli_fetch_array($accountInfosConfig)) {

            // Management for specific acc infos (TAG)
            if(in_array($accountInfos[$this->accountInfosStruct->nameacc], $this->specificAccountInfos)){
                $accountInfos[$this->accountInfosStruct->id] = $accountInfos[$this->accountInfosStruct->nameacc];
            }else{
                $accountInfos[$this->accountInfosStruct->id] = "fields_".$accountInfos[$this->accountInfosStruct->id];
            }

            switch ($accountInfos['ACCOUNT_TYPE']) {
                case self::ACC_TYPE_SNMP:
                    $accType = self::ACC_TYPE_SNMP;
                    break;
                case self::ACC_TYPE_COMPUTER:
                    $accType = self::ACC_TYPE_COMPUTER;
                    break;
                default:
                    break;
            }

            $this->accountInfosList[$accType][$accountInfos[$this->accountInfosStruct->id]] = $accountInfos[$this->accountInfosStruct->comment];
        }
    }

    /**
     * Get accountinfos list
     */
    public function getAccountInfosList(){
        return $this->accountInfosList;
    }

    /**
     * Get accountinfos type
     * @param  string $field_account
     * @return string $info
     */
    public function getSearchAccountInfo($field_account){
        $id = explode("_", $field_account);
        $sql = "SELECT TYPE FROM accountinfo_config WHERE ID = %s";
        $arg = array($id[1]);
        $result = mysql2_query_secure($sql, $this->dbObject, $arg);

        while ($type = mysqli_fetch_array($result)){
          $info = $type['TYPE'];
        }

        return $info;
    }

    /**
     * Get accountinfos_config name
     * @param  string $field
     * @return array $values
     */
    public function find_accountinfo_values($field, $typeInfo = null){
        $id = explode("_", $field);
        $sql = "SELECT `NAME` FROM accountinfo_config WHERE ID = %s";
        $arg = array($id[1]);
        $result = mysql2_query_secure($sql, $this->dbObject, $arg);

        while ($type = mysqli_fetch_array($result)){
          $info = 'ACCOUNT_VALUE_'.$type['NAME'];
        }

        $values = find_value_field($info, $typeInfo);

        return $values;
    }

 }
