<?php

/**
 * This function extract the archive content, put it in the ocs reports for initialization
 */
function install($archiveName){

	//var_dump(PLUGINS_DL_DIR.$archiveName);

	if (file_exists(PLUGINS_DL_DIR.$archiveName)){
		$archive = new ZipArchive();
		$archive->open(PLUGINS_DL_DIR.$archiveName);
		$arrayplugin = explode(".", $archiveName);
		$plugindir = $arrayplugin[0];
		$archive->extractTo(MAIN_SECTIONS_DIR."ms_".$plugindir);
		$archive->close();
		unlink(PLUGINS_DL_DIR.$archiveName);
	}
	else{
		echo "Une erreur est survenu lors de l'installation du plugin.";
	}
}

/**
 * This function check if the plugin is already in the DATABASE and installed.
 * If not an entry is created in the table "plugins" with the plugins infos.
 */
function check($plugarray){

	$conn = new PDO('mysql:host=localhost;dbname=ocsweb;charset=utf8', 'ocs', 'ocs');


	foreach ($plugarray as $key => $value){

		$query = $conn->query("SELECT EXISTS( SELECT * FROM `plugins` WHERE name = '".$value."' ) AS name_exists");
		$anwser = $query->fetch();

		// If the plugin isn't in the database ... add it
		if($anwser[0] == false){
				
			require MAIN_SECTIONS_DIR."ms_".$value."/install.php";
				
			// Retrieve infos from the plugin_version_plugname functions and add it to the database
				
			$fonc = "plugin_version_".$value;
			$infoplugin = $fonc();

			$conn->query("INSERT INTO `ocsweb`.`plugins` (`id`, `name`, `version`, `licence`, `author`, `verminocs`, `activated`, `reg_date`)
					VALUES (NULL, '".$infoplugin['name']."', '".$infoplugin['version']."', '".$infoplugin['license']."', '".$infoplugin['author']."', '".$infoplugin['verMinOcs']."', '1', CURRENT_TIMESTAMP);");

			// Initialize the plugins requirement (New menus, Set permissions etc etc)
				
			$init = "plugin_init_".$value;
			$infoplugin = $init();
				
		}

	}

}

/**
 * Scan the DL plugins dir.
 * If the plugin isn't installed in the ocsreports, this function call the "install" function
 */
function scan_downloaded_plugins(){

	if(!file_exists(PLUGINS_DL_DIR)){
		mkdir(PLUGINS_DL_DIR,'0777',true);
	}

	// Scan plugins download directory
	$directory = PLUGINS_DL_DIR;
	$scanned_directory = array_diff(scandir($directory), array('..', '.'));

	// Debug purposes
	//var_dump($scanned_directory);

	if (!empty($scanned_directory)){
		foreach ($scanned_directory as $key => $value){
			install($value);
		}
	}

}

/**
 * This function check and return all plugins installed in the Ocs Reports...
 *
 * @return ArrayObject
 */
function scan_for_plugins(){

	$scanned_plugins = array_diff(scandir(MAIN_SECTIONS_DIR),
			array('img', 'ms_all_soft', 'ms_debug', 'ms_groups' ,'ms_multi_search' ,'ms_scripts', 'ms_stats', 'ms_about',
					'ms_computer', 'ms_dict', 'ms_help', 'ms_plugins', 'ms_search_soft', 'ms_teledeploy', 'ms_admininfo',
					'ms_config', 'ms_doubles', 'ms_ipdiscover', 'ms_regconfig', 'ms_server_infos', 'ms_upload_file',
					'ms_all_computers', 'ms_console', 'ms_export', 'ms_logs', 'ms_repart_tag', 'ms_snmp', 'ms_users'
					, '.', '..'));

	// Debug purposes
	//var_dump($scanned_plugins);

	$plugins_name = array();

	foreach ($scanned_plugins as $key => $value){

		$exp = explode("_", $value);
		$plugins_name[] = $exp[1];
	}

	return $plugins_name;
}

