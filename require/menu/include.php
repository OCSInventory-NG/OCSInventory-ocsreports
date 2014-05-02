<?php

require_once 'Menu.php';
require_once 'MenuElem.php';
require_once 'MenuRendererInterface.php';
require_once 'BaseMenuRenderer.php';
require_once 'BootstrapMenuRenderer.php';
require_once 'XMLMenuSerializer.php';
require_once 'TxtMenuSerializer.php';

function migrate_menus_2_2() {
	require_once('require/function_files.php');

	$txt_serializer = new TxtMenuSerializer();
	$xml_serializer = new XMLMenuSerializer();
	
	$filename = 'config/main_menu.xml';
	$menu = $txt_serializer->unserialize(read_config_file());
	$xml = $xml_serializer->serialize($menu);
	
	if (is_writable('config')) {
		file_put_contents($filename, $xml);
	} else {
		throw new Exception("Could not write to the new config file ($filename)");
	}
}

?>