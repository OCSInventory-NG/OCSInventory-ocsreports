<?php
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Arthur Jaouen 2014 (arthur(at)factorfx(dot)com)
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================

function validate_package_form($data, $files) {
	$errors = array();
	
	// TODO error translations
	// TODO check for field sizes
	
	// Check mandatory data
	$mandatory_fields = array('NAME', 'DESCRIPTION', 'OS', 'ACTION', 'ACTION_INPUT', 'DEPLOY_SPEED');
	foreach ($mandatory_fields as $field) {
		if (!isset($data[$field]) or empty(trim($data[$field]))) {
			$errors[$field] []= 'This field is mandatory';
		}
	}
	
	// Check dropdown lists
	if (isset($data['OS']) and !in_array($data['OS'], array('WINDOWS', 'LINUX', 'MAC'))) {
		$errors['OS'] []= 'Invalid value';
	}
	if (isset($data['ACTION']) and !in_array($data['ACTION'], array('STORE', 'EXECUTE', 'LAUNCH'))) {
		$errors['ACTION'] []= 'Invalid value';
	}
	if (isset($data['DEPLOY_SPEED']) and !in_array($data['DEPLOY_SPEED'], array('LOW', 'MIDDLE', 'HIGH', 'CUSTOM'))) {
		$errors['DEPLOY_SPEED'] []= 'Invalid value';
	}
	
	// Check file upload
	if ($data['ACTION'] != 'EXECUTE' and (!isset($files['FILE']) or empty(trim($files['FILE']['name'])))) {
		$errors['FILE'] []= 'This field is mandatory';
	}
	
	// Check mandatory fields depending on others
	if ($data['DEPLOY_SPEED'] == 'CUSTOM') {
		$mandatory_fields = array('PRIORITY', 'NB_FRAGS');
		foreach ($mandatory_fields as $field) {
			if (!isset($data[$field]) or empty(trim($data[$field]))) {
				$errors[$field] []= 'This field is mandatory';
			}
		}
	}
	if ($data['OS'] == 'WINDOWS') {
		if ($data['NOTIFY_USER'] == 'on') {
			$mandatory_fields = array('NOTIFY_TEXT', 'NOTIFY_COUNTDOWN');
			foreach ($mandatory_fields as $field) {
				if (!isset($data[$field]) or empty(trim($data[$field]))) {
					$errors[$field] []= 'This field is mandatory';
				}
			}
		}
		if ($data['NEED_DONE_ACTION'] == 'on') {
			if (!isset($data['NEED_DONE_ACTION_TEXT']) or empty(trim($data['NEED_DONE_ACTION_TEXT']))) {
				$errors['NEED_DONE_ACTION_TEXT'] []= 'This field is mandatory';
			}
		}
	}
	if ($data['REDISTRIB_USE'] == 'on') {
		if (!isset($data['DOWNLOAD_SERVER_DOCROOT']) or empty(trim($data['DOWNLOAD_SERVER_DOCROOT']))) {
			$errors['DOWNLOAD_SERVER_DOCROOT'] []= 'This field is mandatory';
		}
	}
	
	// Check unique fields
	if ($data['NAME'] and package_name_exists(trim($data['NAME']))) {
		$errors['NAME'] []= 'This package name already exists';
	}
	
	return $errors;
}

?>