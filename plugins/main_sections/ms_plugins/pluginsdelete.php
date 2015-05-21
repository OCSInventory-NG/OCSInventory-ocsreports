<?php

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
	
	$conn = new PDO('mysql:host='.SERVER_WRITE.';dbname='.DB_NAME.'', COMPTE_BASE, PSWD_BASE);
	$query = $conn->query("SELECT * FROM `plugins` WHERE id = '".$pluginid."'");
	$anwser = $query->fetch();
	
	if (!class_exists('plugins')) {
		require 'plugins.class.php';
	}
	
	require (MAIN_SECTIONS_DIR."ms_".$anwser['name']."/install.php");
	
	$fonc = "plugin_delete_".$anwser['name'];
	$fonc();
	
	rrmdir(MAIN_SECTIONS_DIR."ms_".$anwser['name']);
		
	$conn->query("DELETE FROM `".DB_NAME."`.`plugins` WHERE `plugins`.`id` = ".$pluginid." ");
	
}

?>