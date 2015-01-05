<?php

function get_download_root() {
	$document_root_conf = look_config_default_values(array('DOWNLOAD_PACK_DIR'));

	if (isset($document_root_conf["tvalue"]['DOWNLOAD_PACK_DIR'])) {
		return $document_root_conf["tvalue"]['DOWNLOAD_PACK_DIR']."/download/";
	} else {
		return DOCUMENT_ROOT."/download/";
	}
}

function get_redistrib_download_root() {
	$document_root_conf = look_config_default_values(array('DOWNLOAD_REP_CREAT'));

	if (isset($document_root_conf["tvalue"]['DOWNLOAD_REP_CREAT'])) {
		return $document_root_conf["tvalue"]['DOWNLOAD_REP_CREAT'];
	} else {
		return DOCUMENT_ROOT."/download/server/";
	}
}

function get_redistrib_distant_download_root() {
	$document_root_conf = look_config_default_values(array('DOWNLOAD_SERVER_DOCROOT'));
	return $document_root_conf["tvalue"]['DOWNLOAD_SERVER_DOCROOT'];
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

function handle_package_upload($file_data, $timestamp) {// TODO translate
	// Handle file upload
	$tmp_file = $file_data['tmp_name'];
	
	// Check for errors
	$error = null;
	$ext = null;
	switch ($file_data['error']) {
		case UPLOAD_ERR_OK:
			// Check extension
			if (substr($file_data['name'], -strlen('.zip')) === '.zip') {
				$ext = '.zip';
			} else if (substr($file_data['name'], -strlen('.tar.gz')) === '.tar.gz') {
				$ext = '.tar.gz';
			} else if (substr($file_data['name'], -strlen('.apk')) === '.apk') {
				$ext = '.apk';
			} else {
				$error = 'Invalid file type (should be zip, tar.gz or apk)';
			}
			
			break;
		case UPLOAD_ERR_NO_FILE:
			$error = 'No file sent';
			break;
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			$error = 'Exceeded file size limit';
			break;
		default:
			$error = 'Unknown error';
			break;
	}
	
	if (!$error) {
		// Move the file in a tmp dir in its future download location
		$package_dir = get_download_root().$timestamp;
		if (!file_exists($package_dir) and (!is_writable(get_download_root()) or !mkdir($package_dir))) {
			$error = 'Could not create dir '.$package_dir.', please fix your filesystem before trying again ';
		}
		
		$package_tmp_dir = $package_dir.'/tmp';
		if (!$error and !file_exists($package_tmp_dir) and (!is_writable($package_dir) or !mkdir($package_tmp_dir))) {
			$error = 'Could not create dir '.$package_tmp_dir.', please fix your filesystem before trying again ';
		}
		
		if ($ext == '.apk') {
			$package_tmp_file = $package_tmp_dir.'/'.$timestamp.'.apk';
		} else {
			$package_tmp_file = $package_tmp_dir.'/package';
		}
		
		if (!$error and (!is_writable($package_dir) or !move_uploaded_file($tmp_file, $package_tmp_file))) {
			$error = 'Could not create file '.$package_tmp_file.', please fix your filesystem before trying again ';
		}

		if ($ext == '.apk') {
			require_once("libraries/zip.lib.php");
			
			$zipfile = new zipfile();
			$zipfile->addFile(file_get_contents($package_tmp_file), $timestamp.'.apk');
			if (!file_put_contents($package_tmp_dir.'/package', $zipfile->file())) {
				$error = 'Could not create file '.$package_tmp_dir.'/package, please fix your filesystem before trying again ';
			}
			
			unlink($package_tmp_file);
			
			// Everything went well
			$size = filesize($package_tmp_dir.'/package');
		} else if (!$error) {
			// Everything went well
			$size = filesize($package_tmp_file);
		}
	}
	
	if ($error) {
		return array(
			'status' => 'error',
			'message' => $error
		);
	} else {
		return array(
			'status' => 'success',
			'type' => substr($ext, 1),
			'size' => $size,
			'timestamp' => $timestamp
		);
	}
}

?>