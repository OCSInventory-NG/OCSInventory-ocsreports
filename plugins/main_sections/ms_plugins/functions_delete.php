<?php
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Gilles DUBOIS 2015
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================

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
function delete_plugin($pluginid){
	
	global $l;
	
	$conn = new PDO('mysql:host='.SERVER_WRITE.';dbname='.DB_NAME.'', COMPTE_BASE, PSWD_BASE);
	$query = $conn->query("SELECT * FROM `plugins` WHERE id = '".$pluginid."'");
	$anwser = $query->fetch();
	
	if (!class_exists('plugins')) {
		require 'plugins.class.php';
	}
	
	if (!function_exists('exec_plugin_soap_client')) {
		require 'functions_webservices.php';
	}
	
	if ($anwser['name'] != "" and $anwser['name'] != null){
		require (MAIN_SECTIONS_DIR."ms_".$anwser['name']."/install.php");
		
		$fonc = "plugin_delete_".$anwser['name'];
		$fonc();
	}
	
	rrmdir(MAIN_SECTIONS_DIR."ms_".$anwser['name']);
	rrmdir(PLUGINS_DIR."computer_detail/cd_".$anwser['name']);
	
	if(file_exists(PLUGINS_SRV_SIDE.$anwser['name'].".zip")){
		unlink(PLUGINS_SRV_SIDE.$anwser['name'].".zip");
		exec_plugin_soap_client($anwser['name'], 0);
	}
	
	$conn->query("DELETE FROM `".DB_NAME."`.`plugins` WHERE `plugins`.`id` = ".$pluginid." ");
	
}

?>
