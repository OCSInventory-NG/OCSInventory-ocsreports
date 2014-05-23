<?php

function get_download_root() {
	$document_root_conf = look_config_default_values(array('DOWNLOAD_PACK_DIR'));

	if (isset($document_root_conf["tvalue"]['DOWNLOAD_PACK_DIR'])) {
		return $document_root_conf["tvalue"]['DOWNLOAD_PACK_DIR']."/download/";
	} else {
		return DOCUMENT_ROOT."download/";
	}
}

function get_redistrib_download_root() {
	$document_root_conf = look_config_default_values(array('DOWNLOAD_REP_CREAT'));

	if (isset($document_root_conf["tvalue"]['DOWNLOAD_REP_CREAT'])) {
		return $document_root_conf["tvalue"]['DOWNLOAD_REP_CREAT'];
	} else {
		return DOCUMENT_ROOT."download/server/";
	}
}

function get_redistrib_distant_download_root() {
	$document_root_conf = look_config_default_values(array('DOWNLOAD_SERVER_DOCROOT'));
	return $document_root_conf["tvalue"]['DOWNLOAD_SERVER_DOCROOT'];
}

function package_exists($timestamp) {
	return file_exists(get_download_root().$timestamp.'/info');
}

function redistrib_package_exists($timestamp) {
	return file_exists(get_download_root().$timestamp.'/info');
}

function package_name_exists($name) {
	$query = "SELECT COUNT(*) FROM download_available WHERE NAME = '%s'";
	$res = mysql2_query_secure($query, $_SESSION['OCS']['readServer'], $name);
	$count = mysqli_fetch_row($res);
	return $count[0] > 0;
}

function get_package_info($timestamp) {
	$query = "SELECT FILEID, NAME, PRIORITY, FRAGMENTS, SIZE, OSNAME, COMMENT FROM download_available WHERE FILEID = %s";
	$res = mysql2_query_secure($query, $_SESSION['OCS']['readServer'], $timestamp);
	return mysqli_fetch_assoc($res);
}

function get_redistrib_package_info($timestamp) {
	$query = "SELECT FILEID, NAME, PRIORITY, FRAGMENTS, SIZE, OSNAME, COMMENT FROM download_available"
			." WHERE NAME LIKE '%%_redistrib' AND COMMENT LIKE '%%[PACK REDISTRIBUTION %s]%%'";
	$res = mysql2_query_secure($query, $_SESSION['OCS']['readServer'], $timestamp);
	
	if (mysqli_num_rows($res)) {
		return mysqli_fetch_assoc($res);
	} else {
		return false;
	}
}

?>