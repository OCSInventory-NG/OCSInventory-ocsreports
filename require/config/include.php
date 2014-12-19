<?php

require_once 'Urls.php';
require_once 'XMLUrlsSerializer.php';
require_once 'TxtUrlsSerializer.php';

require_once 'XMLJsSerializer.php';
require_once 'TxtJsSerializer.php';

require_once 'Profile.php';
require_once 'TxtProfileSerializer.php';
require_once 'XMLProfileSerializer.php';

function migrate_config_2_2() {
	global $l;
	
	if (!is_writable(DOCUMENT_REAL_ROOT.'/config')) {
		msg_error($l->g(2029));
		exit;
	}
	
	require_once('require/function_files.php');
	
	$config = read_config_file();
	migrate_urls_2_2($config);
	migrate_js_2_2($config);
	migrate_profiles_2_2();
	migrate_menus_2_2($config);
}

function migrate_urls_2_2($config) {
	$txt_serializer = new TxtUrlsSerializer();
	$xml_serializer = new XMLUrlsSerializer();
	$filename = DOCUMENT_REAL_ROOT.'/config/urls.xml';
	$urls = $txt_serializer->unserialize($config);
	$xml = $xml_serializer->serialize($urls);
	
	file_put_contents($filename, $xml);
}

function migrate_js_2_2($config) {
	$txt_serializer = new TxtJsSerializer();
	$xml_serializer = new XMLJsSerializer();
	
	$filename = DOCUMENT_REAL_ROOT.'/config/js.xml';
	$js = $txt_serializer->unserialize($config);
	$xml = $xml_serializer->serialize($js);
	
	file_put_contents($filename, $xml);
}

function migrate_profiles_2_2() {
	if (!file_exists(DOCUMENT_REAL_ROOT.'/config/profiles')) {
		mkdir(DOCUMENT_REAL_ROOT.'/config/profiles');
	}
	
	if (!is_writable(DOCUMENT_REAL_ROOT.'/config/profiles')) {
		msg_error($l->g(2116));
		exit;
	}
	
	$txt_serializer = new TxtProfileSerializer();
	$xml_serializer = new XMLProfileSerializer();
	
	foreach(scandir($_SESSION['OCS']['CONF_PROFILS_DIR']) as $file) {
		if (preg_match('/^(.+)_config\.txt$/', $file, $matches) and $matches[1] != '4all') {
			$profile_name = $matches[1];
			$profile_data = read_profil_file($profile_name);
			
			$profile = $txt_serializer->unserialize($profile_name, $profile_data);
			$xml = $xml_serializer->serialize($profile);
			
			file_put_contents(DOCUMENT_REAL_ROOT.'/config/profiles/'.$profile_name.'.xml', $xml);
		}
	}
}

?>