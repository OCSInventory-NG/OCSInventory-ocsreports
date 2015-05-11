<?php

require 'plugins.class.php';
require 'install.php';
require 'check.php';
require 'scan_downloaded_plugins.php';
require 'scan_plugins_dir.php';

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

?>