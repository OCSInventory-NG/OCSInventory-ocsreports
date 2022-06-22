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

class ExtensionManager{

    /**
     * Attributes
     */
    public $installedExtensionsList;
    public $installableExtensionsList;
    public $installableExtensions_errors = array();
    public $errorMessage;

    /**
     * Constants
     */

    // Extensions directories
    const MAIN_SEC_EXT = 'main_sections';
    const COMP_DETAIL_EXT = 'computer_detail';
    const LANGUAGE_EXT = 'language';
    const APACHE_EXT = 'apache';
    const AGENT_EXT = 'agent';

    // Json infos of the extension
    const INFOS_EXT = 'infos.json';

    // Extensions fobiden name
    private $FORBIDEN_EXT_NAME = [
        ".",
        "..",
        "readme.md"
    ];

    // Extensions install required method
    const EXTENSION_INSTALL_METHD = 'extension_install_';
    const EXTENSION_DELETE_METHD = 'extension_delete_';
    const EXTENSION_UPGRADE_METHD = 'extension_upgrade_';
    const EXTENSION_HOOK_METHD = 'extension_hook_';

    /**
     * List query
     */
    private $selectQuery = "SELECT * FROM `extensions`";

    /**
     * Insert query
     */
    private $insertQuery = "INSERT INTO `extensions`(`id`, `name`, `description`, `version`, `licence`, `author`, `contributor`) VALUES ('%s','%s','%s',%s,'%s','%s','%s')";

    /**
     * Delete query
     */
    private $deleteQuery = "DELETE FROM `extensions` WHERE id = '%s'";

    /**
     * Objects
     */
    private $dbObject;

    /**
     * Write server.
     */
    private $dbWrite;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->dbObject = $_SESSION['OCS']["readServer"];
        $this->dbWrite = $_SESSION['OCS']["writeServer"];
        $this->getInstalledExtensionsList();
    }

    /**
     * Will set an array of valid extensions
     */
    public function checkInstallableExtensions(){
		// reset error list
		$this->installableExtensions_errors = array();
		// check if directory exists
		if (!is_dir(EXT_DL_DIR)) {
			$this->installableExtensions_errors[] = 'Extension directory ('.EXT_DL_DIR.') does not exist!';
		}		
        // Scan dir and get all sub directory in extensions directory
        $items = scandir(EXT_DL_DIR);
        $installableExtList = [];
        foreach ($items as $name) {
            if(in_array($name, $this->FORBIDEN_EXT_NAME) || !is_dir(EXT_DL_DIR.$name)){
                continue;
            }

            if($this->isExtensionCompliant($name)){
                $installableExtList[] = $name;
            }
        }

        $this->installableExtensionsList = $installableExtList;
    }

    /**
     * Check if extensions is compliant to OCS Inventory Model
     */
    private function isExtensionCompliant($name){
		global $l;
        try{
            require EXT_DL_DIR.$name."/install.php";

			if (!function_exists(self::EXTENSION_INSTALL_METHD.$name)) {
				$this->installableExtensions_errors[] = sprintf($l->g(7021), $name).': '.sprintf($l->g(7022), self::EXTENSION_INSTALL_METHD.$name, EXT_DL_DIR.$name.'/install.php');
				return false;
			}
			
			if (!function_exists(self::EXTENSION_DELETE_METHD.$name)) {
				$this->installableExtensions_errors[] = sprintf($l->g(7021), $name).': '.sprintf($l->g(7022), self::EXTENSION_DELETE_METHD.$name, EXT_DL_DIR.$name.'/install.php');
				return false;
			}
			
			if (!function_exists(self::EXTENSION_UPGRADE_METHD.$name)) {
				$this->installableExtensions_errors[] = sprintf($l->g(7021), $name).': '.sprintf($l->g(7022), self::EXTENSION_UPGRADE_METHD.$name, EXT_DL_DIR.$name.'/install.php');
				return false;
			}

            return true;
        } catch (Exception $ex) {
			$this->installableExtensions_errors[] = sprintf($l->g(7021), $name).': '.$l->g(7023);
            return false;
        }
    }

    /**
     * Will check pre-requisites for extensions installation
     */
    public function checkPrerequisites(){
        return true;
    }

    /**
     * Extension installation
     */
    public function installExtension($name){

        if($this->isInstalled($name)){
            // TODO : error already installed
            return 'isInstalled';
        }

        // Add plugin record in database
        $jsonStr = file_get_contents(EXT_DL_DIR.$name."/".self::INFOS_EXT);
        $jsonInfos = json_decode($jsonStr, true);

        $queryArrayArgs = [];
        $queryArrayArgs[] = $name;
        $queryArrayArgs[] = $jsonInfos['displayName'];
        $queryArrayArgs[] = $jsonInfos['description']['en'];
        $queryArrayArgs[] = $jsonInfos['version'];
        $queryArrayArgs[] = $jsonInfos['licence'];
        $queryArrayArgs[] = $jsonInfos['author'][0];
        $queryArrayArgs[] = $jsonInfos['author'][0];

        mysql2_query_secure($this->insertQuery, $this->dbWrite, $queryArrayArgs);

        try{
            $installMethod = self::EXTENSION_INSTALL_METHD.$name;
            $installMethod();
            // TODO : Successfuly instllaed
            return true;
        } catch (Exception) {
            // TODO : PHP Error occured
            return false;
        }

    }

    /**
     * Extension removal
     *
     * @param $name : Name of the plugin
     * @return bool : true is ok else false
     */
    public function deleteExtension($name){

        if(!$this->isInstalled($name)){
            return false;
        }

        try{
            mysql2_query_secure($this->deleteQuery, $this->dbWrite, $name);
            $deleteMethod = self::EXTENSION_DELETE_METHD.$name;
            $deleteMethod();
            return true;
        } catch (Exception) {
            return false;
        }

    }

    /**
     * Check if already installed
     */
    private function isInstalled($name){

        if(in_array($name, $this->installedExtensionsList)){
            return true;
        }else{
            return false;
        }

    }

    /**
     * Will set an array of installed extensions
     */
    private function getInstalledExtensionsList(){

        $selectResult = mysql2_query_secure($this->selectQuery, $this->dbObject);
        $installedExt = [];

        while($installedExtRows = $selectResult->fetch_array())
        {
            $installedExt[] = $installedExtRows['id'];
        }

        $this->installedExtensionsList = $installedExt;

    }

}
