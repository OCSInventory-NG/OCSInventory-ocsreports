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

require_once('require/function_config_generale.php');

/**
 * @param unknown $name : PluginName
 * @param unknown $action : Possible actions => delete (0) and install (1)
 */
function exec_plugin_soap_client($name, $action){
	
	$champs=array('OCS_SERVER_ADDRESS'=>'OCS_SERVER_ADDRESS');
	$values=look_config_default_values($champs);
	
	$address = $values['tvalue']['OCS_SERVER_ADDRESS'];
	
	ini_set("safe_mode", "0");
	
	$command = "perl ".MAIN_SECTIONS_DIR."ms_plugins/client.pl ".$address." ".$name." ".$action;
 	exec($command);	
	
	ini_set("safe_mode", "1");
	
}

?>
