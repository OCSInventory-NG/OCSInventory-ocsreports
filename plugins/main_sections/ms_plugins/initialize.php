<?php

if (!class_exists('plugins')) {
	require 'plugins.class.php';
}

if (!function_exists('rrmdir')) {
	require 'functions_delete.php';
}

require 'functions_check.php';

// Look for the plugin download directory or create it
if(!file_exists(PLUGINS_DL_DIR)){
	if (!mkdir(PLUGINS_DL_DIR, "0755", true)) {
		die('Echec lors de la création du répertoire plugins...');
	}
}

scan_downloaded_plugins();

$forCheck = scan_for_plugins();

// Debug purposes
//var_dump($forCheck);

check($forCheck);

foreach ($forCheck as $value){
	mv_computer_detail($value);
}

?>