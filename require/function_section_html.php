<?php

function show_menu() {
	global $l;
	
	if (!file_exists('config/main_menu.xml')) {
		try {
			migrate_config_2_2();
		} catch (Exception $e) {
			echo $e->getMessage();
			msg_error($l->g(2029));
			exit;
		}
	}
	
	// Build menu
	$menu_serializer = new XMLMenuSerializer();
	$menu = $menu_serializer->unserialize(file_get_contents('config/main_menu.xml'));

	$urls_serializer = new XMLUrlsSerializer();
	$urls = $urls_serializer->unserialize(file_get_contents('config/urls.xml'));
	
	$renderer = new BootstrapMenuRenderer($urls);
	echo $renderer->render($menu);
}

//Find the lbl of the menu_elem
function find_lbl($id){
	global $l;
	if (substr($id,0,2) == 'g(')
		$lbl= ucfirst($l->g(substr(substr($id,2),0,-1)));
	else
		$lbl=$id;
	return strip_tags_array($lbl);
}


?>