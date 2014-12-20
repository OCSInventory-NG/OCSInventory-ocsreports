<?php

require_once 'require/function_users.php';

function validate_profile_create_form($data) {
	$errors = array();
	$profiles = get_profile_labels();
	
	// TODO error translations
	// TODO check for field sizes
	
	// Check mandatory data
	$mandatory_fields = array('name', 'label', 'duplicate_profile');
	foreach ($mandatory_fields as $field) {
		if (!$data[$field]) {
			$errors[$field] []= 'This field is mandatory';
		}
	}
	
	// Check dropdown lists
	if ($data['duplicate_profile'] and !isset($profiles[$data['duplicate_profile']])) {
		$errors['duplicate_profile'] []= 'Invalid value';
	}
	
	// Check profile name regex
	if ($data['name'] and !preg_match('/^[0-9A-Za-z]+$/', $data['name'])) {
		$errors['name'] []= 'This field should only contain numbers and alphanumeric characters';
	}
	
	// Check profile name doesn't exist
	if ($data['name'] and isset($profiles[$data['name']])) {
		$errors['name'] []= 'A profile with this name already exists. Please pick a new one';
	}
	
	return $errors;
}

function validate_profile_edit_form($profile_id, $data) {
	$errors = array();

	$yes_no = array('YES', 'NO');
	$telediff_wk = array('LOGIN', 'USER_GROUP', 'NO');

	$urls = $_SESSION['OCS']['url_service'];
	
	// TODO error translations

	foreach ($data['restrictions'] as $key => $val) {
		if (($key == 'TELEDIFF_WK' and !in_array($val, $telediff_wk)) or ($key != 'TELEDIFF_WK' and !in_array($val, $yes_no))) {
			$errors['restrictions_'.$key.'_'] []= 'Invalid value';
		}
	}
	
	foreach ($data['config'] as $key => $val) {
		if (!in_array($val, $yes_no)) {
			$errors['config_'.$key.'_'] []= 'Invalid value';
		}
	}
	
	foreach ($data['blacklist'] as $key => $val) {
		if (!in_array($val, $yes_no)) {
			$errors['blacklist_'.$key.'_'] []= 'Invalid value';
		}
	}
	
	foreach ($data['pages'] as $key => $val) {
		if (!$urls->getUrl($key)) {
			$errors['blacklist_'.$key.'_'] []= 'Invalid value';
		}
	}
	
	return $errors;
}

function create_profile($data) {
	$profiles = get_profiles();
	$newProfile = clone($profiles[$data['duplicate_profile']]);

	$newProfile->setName($data['name']);
	$newProfile->setLabel($data['label']);
	
	$serializer = new XMLProfileSerializer();
	$xml = $serializer->serialize($newProfile);
	
	if (file_put_contents(DOCUMENT_REAL_ROOT.'/config/profiles/'.$newProfile->getName().'.xml', $xml)) {
		return $newProfile->getName();
	} else {
		return false;
	}
}

function update_profile($profile_id, $data) {
	$yes_no = array('YES', 'NO');
	$telediff_wk = array('LOGIN', 'USER_GROUP', 'NO');

	$urls = $_SESSION['OCS']['url_service'];
	
	$profiles = get_profiles();
	$profile = $profiles[$profile_id];
	$updatedProfile = new Profile($profile_id, $data['new_label'] ?: $profile->getLabel());

	foreach ($data['restrictions'] as $key => $val) {
		$updatedProfile->setRestriction($key, $val);
	}
	
	foreach ($data['config'] as $key => $val) {
		$updatedProfile->setConfig($key, $val);
	}
	
	foreach ($data['blacklist'] as $key => $val) {
		if ($val == 'YES') {
			$updatedProfile->addToBlacklist($key);
		}
	}
	
	foreach ($data['pages'] as $key => $val) {
		if ($urls->getUrl($key) and $val == 'on') {
			$updatedProfile->addPage($key);
		}
	}
	
	$serializer = new XMLProfileSerializer();
	$xml = $serializer->serialize($updatedProfile);

	if (file_put_contents(DOCUMENT_REAL_ROOT.'/config/profiles/'.$profile->getName().'.xml', $xml)) {
		return $profile->getName();
	} else {
		return false;
	}
}

?>