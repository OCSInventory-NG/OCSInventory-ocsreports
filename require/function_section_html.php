<?php

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