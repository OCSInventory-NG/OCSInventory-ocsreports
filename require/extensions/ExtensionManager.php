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
    public $installedExtensionsList = null;
    public $installableExtensionsList = null;
    public $errorMessage = null;
    
    /**
     * Constants
     */
    
    // Extensions directories
    const MAIN_SEC_EXT = 'main_sections';
    const COMP_DETAIL_EXT = 'computer_detail';
    const LANGUAGE_EXT = 'language';
    const APACHE_EXT = 'apache';
    const AGENT_EXT = 'agent';
    
    // Extensions fobiden name
    const FORBIDEN_EXT_NAME = [
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
     * Objects
     */
    private $dbObject = null;
    
    /**
     * Constructor
     */
    function __construct()
    {
        $this->dbObject = $_SESSION['OCS']["readServer"];
    }
    
    /**
     * Will set an array of valid extensions 
     */
    public function checkInstallableExtensions(){
        
        // Scan dir and get all sub directory in extensions directory
        $items = scandir(EXT_DL_DIR);
        $installableExtList = [];
        foreach ($items as $index => $name) {
            if(in_array($name, self::FORBIDEN_EXT_NAME) || !is_dir(EXT_DL_DIR.$name)){
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
        try{
            require EXT_DL_DIR.$name."/install.php";
            
            if(
                function_exists(self::EXTENSION_INSTALL_METHD.$name) &&
                function_exists(self::EXTENSION_DELETE_METHD.$name) &&
                function_exists(self::EXTENSION_UPGRADE_METHD.$name) &&
                function_exists(self::EXTENSION_HOOK_METHD.$name)
            ){
                return true;
            }
            
            return true;
        } catch (Exception $ex) {
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
            return false;
        }
        
    }
    
    /**
     * Extension removal
     */
    public function deleteExtension($name){
        
    }
    
    /**
     * Check if already installed
     */
    private function isInstalled($name){
        
    }
    
    /**
     * Init installed extensions on login
     */
    public function initInstalledExtensions(){
        
    }
    
    /**
     * Will set an array of installed extensions
     */
    private function getInstalledExtensionsList(){
        
    }
    
}