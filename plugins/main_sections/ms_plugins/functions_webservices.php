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
	
    global $l;

    if(class_exists('SoapClient')) {
            plugin_soap_client($name, $action);
    }else{
            msg_error($l->g(6006));
    }
    
}

function plugin_soap_client($name, $action){
    
    global $l;
	
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
                'location' => "http://$address/ocsplugins",
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

    $output = $client->__doRequest($request, "http://$address".PLUGIN_WS_URL, "http://$address/Apache/Ocsinventory/Plugins/Modules#$method", "1.1");
    
    //TODO : parse the output and check if the communication serveur has been successfully installed
    $xml_response = $output;
    // Clean soap xml output
    $clean_xml = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $xml_response);
    $xml = simplexml_load_string($clean_xml);
    
    // TODO : Create a specific page for ALL help links 
    $help_link = "https://github.com/OCSInventory-NG/OCSInventory-ocsreports/wiki/Plugins-Engine-:-Web-service-error-codes";
    
    if($action == 1){
        
        $soap_return_value = $xml->Body->InstallPluginsResponse->Result;
        
        if($soap_return_value != "Install_OK"){
            msg_warning($l->g(6010)." ".$soap_return_value."<br><a href=$help_link>".$l->g(6011)." </a>");
        }

    }
	
}

?>
