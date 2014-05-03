<?php

require_once 'Menu.php';
require_once 'MenuElem.php';
require_once 'MenuRendererInterface.php';
require_once 'BaseMenuRenderer.php';
require_once 'BootstrapMenuRenderer.php';
require_once 'XMLMenuSerializer.php';
require_once 'TxtMenuSerializer.php';

function migrate_menus_2_2($config) {
	$txt_serializer = new TxtMenuSerializer();
	$xml_serializer = new XMLMenuSerializer();
	
	$filename = 'config/main_menu.xml';
	$menu = $txt_serializer->unserialize($config);
	$xml = $xml_serializer->serialize($menu);
	
	file_put_contents($filename, $xml);
}

function show_menu() {
	global $l;

	if (!file_exists('config/main_menu.xml')) {
		migrate_config_2_2();
	}

	// Build menu
	$profile = $_SESSION['OCS']['profile'];

	$menu_serializer = new XMLMenuSerializer();
	$menu = $menu_serializer->unserialize(file_get_contents('config/main_menu.xml'));

	$urls_serializer = new XMLUrlsSerializer();
	$urls = $urls_serializer->unserialize(file_get_contents('config/urls.xml'));

	$renderer = new BootstrapMenuRenderer($profile, $urls);
	echo $renderer->render($menu);
}

?>