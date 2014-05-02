<?php

require_once 'Urls.php';
require_once 'XMLUrlsSerializer.php';
require_once 'TxtUrlsSerializer.php';
require_once 'XMLJsSerializer.php';
require_once 'TxtJsSerializer.php';

function migrate_config_2_2() {
	if (!is_writable('config')) {
		throw new Exception("The config directory is not writable");
	}
	
	require_once('require/function_files.php');
	
	$config = read_config_file();
	
	migrate_urls_2_2($config);
	migrate_js_2_2($config);
	migrate_menus_2_2($config);
}

function migrate_urls_2_2($config) {
	$txt_urls_serializer = new TxtUrlsSerializer();
	$xml_urls_serializer = new XMLUrlsSerializer();
	
	$filename = 'config/urls.xml';
	$urls = $txt_urls_serializer->unserialize($config);
	$xml = $xml_urls_serializer->serialize($urls);
	
	file_put_contents($filename, $xml);
}

function migrate_js_2_2($config) {
	$txt_urls_serializer = new TxtJsSerializer();
	$xml_urls_serializer = new XMLJsSerializer();
	
	$filename = 'config/js.xml';
	$js = $txt_urls_serializer->unserialize($config);
	$xml = $xml_urls_serializer->serialize($js);
	
	file_put_contents($filename, $xml);
}

?>