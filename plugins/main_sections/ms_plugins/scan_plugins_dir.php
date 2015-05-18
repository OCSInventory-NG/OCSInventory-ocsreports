<?php

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

?>