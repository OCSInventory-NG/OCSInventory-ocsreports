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
/** This function delete a directory recusively with all his files and sub-dirs
 * 
 * @param string $dir : Directory path
 */
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

/**
 * This functions remove a plugin from the OCS webconsole and database.
 * Delete all created menu entries and all plugin related code
 * 
 * @param integer $pluginid : Plugin id in DB
 */
function delete_plugin($pluginid, $dyn_cal = true){

    global $l;

    $conn = new PDO('mysql:host='.SERVER_WRITE.';dbname='.DB_NAME.'', COMPTE_BASE, PSWD_BASE);
    $arg = (int) $pluginid;
    if($arg == 0){
       $arg = (string) $pluginid;
    }

    // if not int get name - 
    if(is_int($arg)){
        $query = $conn->query("SELECT * FROM `plugins` WHERE id = '".$pluginid."'");
        $anwser = $query->fetch();
    }else{
        $anwser['name'] = $pluginid;
    }

    if (!class_exists('plugins')) {
            require 'plugins.class.php';
    }

    if (!function_exists('exec_plugin_soap_client')) {
            require 'functions_webservices.php';
    }

    if (file_exists(MAIN_SECTIONS_DIR."ms_".$anwser['name'])){

            if ($anwser['name'] != "" && $anwser['name'] != null){
                    require_once (MAIN_SECTIONS_DIR."ms_".$anwser['name']."/install.php");
                    
                    if($dyn_cal){
                        $fonc = "plugin_delete_".$anwser['name'];
                        $fonc();
                    }
            }

            rrmdir(MAIN_SECTIONS_DIR."ms_".$anwser['name']);
    }
    if (file_exists(PLUGINS_DIR."computer_detail/cd_".$anwser['name'])){
            rrmdir(PLUGINS_DIR."computer_detail/cd_".$anwser['name']);
    }

    if(file_exists(PLUGINS_SRV_SIDE.$anwser['name'].".zip")){
            unlink(PLUGINS_SRV_SIDE.$anwser['name'].".zip");
            exec_plugin_soap_client($anwser['name'], 0);
    }

    if(is_int($arg)){
        $conn->query("DELETE FROM `".DB_NAME."`.`plugins` WHERE `plugins`.`id` = ".$arg." ");
    }else{
        $conn->query("DELETE FROM `".DB_NAME."`.`plugins` WHERE `plugins`.`name` = '".$arg."' ");
    }
	
}

?>
