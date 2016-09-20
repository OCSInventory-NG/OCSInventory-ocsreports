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

/**
 * This function extract the archive content, put it in the ocs reports for initialization
 */
function install($archiveName){

	if (file_exists(PLUGINS_DL_DIR.$archiveName)){
		$archive = new ZipArchive();
		$archive->open(PLUGINS_DL_DIR.$archiveName);
		$arrayplugin = explode(".", $archiveName);
		$plugindir = $arrayplugin[0];
		if(!file_exists(MAIN_SECTIONS_DIR."ms_".$plugindir)){
			$archive->extractTo(MAIN_SECTIONS_DIR."ms_".$plugindir);
			$archive->close();
			return true;
		}
		else {
			return false;
		}
	}
}

/**
 * This function check if the plugin is already in the DATABASE and installed.
 * If not an entry is created in the table "plugins" with the plugins infos.
 */
function check($plugarray){

	$conn = new PDO('mysql:host='.SERVER_WRITE.';dbname='.DB_NAME.';charset=utf8', COMPTE_BASE, PSWD_BASE);

	foreach ($plugarray as $key => $value){

		$query = $conn->query("SELECT EXISTS( SELECT * FROM `plugins` WHERE name = '".$value."' ) AS name_exists");
		$anwser = $query->fetch();

		// If the plugin isn't in the database ... add it
		if($anwser[0] == false){
				
			if(file_exists(MAIN_SECTIONS_DIR."ms_".$value."/install.php")){
				
				require MAIN_SECTIONS_DIR."ms_".$value."/install.php";
				
				if( !function_exists ("plugin_version_".$value)
						or !function_exists ("plugin_init_".$value) 
							or !function_exists ("plugin_delete_".$value)){

					rrmdir(MAIN_SECTIONS_DIR."ms_".$value);
					return false;
					
				}
				
				// Retrieve infos from the plugin_version_plugname functions and add it to the database
				
				$fonc = "plugin_version_".$value;
				$infoplugin = $fonc();
				
				$conn->query("INSERT INTO `".DB_NAME."`.`plugins` (`id`, `name`, `version`, `licence`, `author`, `verminocs`, `activated`, `reg_date`)
					VALUES (NULL, '".$infoplugin['name']."', '".$infoplugin['version']."', '".$infoplugin['license']."', '".$infoplugin['author']."', '".$infoplugin['verMinOcs']."', '1', CURRENT_TIMESTAMP);");
				
				// Initialize the plugins requirement (New menus, Set permissions etc etc)
				
				$init = "plugin_init_".$value;
				$infoplugin = $init();
				
				return true;
			}
			else{
				
				rrmdir(MAIN_SECTIONS_DIR."ms_".$value);
				
				return false;
			}
				
		}

	}

}

/**
 * Scan the DL plugins dir.
 * If the plugin isn't installed in the ocsreports, this function call the "install" function
 */
function scan_downloaded_plugins(){

	if(!file_exists(PLUGINS_DL_DIR)){
		mkdir(PLUGINS_DL_DIR,'0775',true);
	}

	// Scan plugins download directory
	$directory = PLUGINS_DL_DIR;
	$scanned_directory = array_diff(scandir($directory), array('..', '.', 'README'));

	return $scanned_directory;

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

/**
 * This function check if a cd_pluginame dir exist.
 * In that case the cd_pluginame dir is moved into computer_detail directory 
 */
function mv_computer_detail($name){
	
	if(file_exists($old = MAIN_SECTIONS_DIR."ms_".$name."/cd_".$name)){
		$new = PLUGINS_DIR."/computer_detail/cd_".$name;
		rename($old, $new);
	}
	
}

/**
 * This function create a plugin archive for the server side part of a plugin which contain the map.pm
 * This part will be downloaded and installed by the communication server.
 */
function mv_server_side($name){
	
	$dir = MAIN_SECTIONS_DIR."ms_".$name."/APACHE/" ;
	
	if(file_exists($dir)){
		
		$archive = new ZipArchive();
		$archive->open(PLUGINS_SRV_SIDE.$name.".zip" , ZipArchive::CREATE);
		
		$scanned_directory = array_diff(scandir($dir), array('..', '.', 'README'));
		//var_dump($scanned_directory);
		
		foreach ($scanned_directory as $key=>$value){
			
			//var_dump($value);
			$archive->addFile($dir.$value,$value);
			
		}
		
		$archive->close();
		rrmdir($dir);
		
		return true;
	}
	else{
		return false;
	}
	
}

/**
 * This function check for required php dependencies
 * Can't install plugin if not installed
 */
function checkDependencies(){
    
    global $l;
    
    $missing_module = false;
    $str_msg = "";
    
    $modules_to_check = array(
        "ZipArchive",
        "SOAPClient"
    );
    
    foreach ($modules_to_check as $value) {
        if(!class_exists($value)){
            $missing_module = true;
            $str_msg .= " - ".$value."<br>";
        }
    }
    
    if($missing_module == true){
        msg_error($l->g(6007)."<br>".$str_msg);
        return false;
    }else{
        return true;
    }
   
}

/**
* This function check if directories are writable.
* Can't install plugin if not writable
*/
function checkWritable(){
    
    global $l;
    
    $missing_permissions = false;
    $str_msg = "";

    $sup_writable_dir = array(
        CD_CONFIG_DIR,
        CONFIG_DIR,
        PLUGINS_DIR."language",
        MAIN_SECTIONS_DIR,
        PLUGINS_SRV_SIDE
    );
    
    foreach ($sup_writable_dir as $value) {
        if(!is_writable($value)){
            $missing_permissions = true;
            $str_msg .= " - ".$value."<br>";
        }
    }
    
    if($missing_permissions == true){
        msg_error($l->g(6008)."<br>".$str_msg); 
        return false;
    }else{
        return true;
    }
    
}