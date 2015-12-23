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

	plugin_soap_client($name, $action);
	
}

function plugin_soap_client($name, $action){
	
 	$champs=array('OCS_SERVER_ADDRESS'=>'OCS_SERVER_ADDRESS');
 	$values=look_config_default_values($champs);

 	$address = $values['tvalue']['OCS_SERVER_ADDRESS'];
	
 	if($action == 1){
 		$method = "InstallPlugins";
 	}else{
 		$method = "DeletePlugins";
 	}
	
 	$client = new SoapClient(null,
 								array(
 									'location' => "http://$address/plugins",
 									'uri' => "http://$address/Apache/Ocsinventory/Plugins/Modules",
 								));
 	 
 	$request = "<?xml version=\"1.0\" encoding=\"utf-8\" ?> 
		<soap:Envelope 
    		soap:encodingStyle='http://schemas.xmlsoap.org/soap/encoding/'
    		xmlns:soap='http://schemas.xmlsoap.org/soap/envelope/' 
 			xmlns:soapenc='http://schemas.xmlsoap.org/soap/encoding/'
    		xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' 
    		xmlns:xsd='http://www.w3.org/2001/XMLSchema'>
 		<soap:Body>
			<$method xmlns='http://$address/Apache/Ocsinventory/Plugins/Modules'><c-gensym3 xsi:type='xsd:string'>$name</c-gensym3></$method>
		</soap:Body>
		</soap:Envelope>";
 	
 	$test = $client->__doRequest($request, "http://$address/plugins", "http://$address/Apache/Ocsinventory/Plugins/Modules#$method", "1.1");
	
}

?>
