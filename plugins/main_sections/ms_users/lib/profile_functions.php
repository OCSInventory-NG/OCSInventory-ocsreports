<?php

require_once 'require/function_users.php';

function validate_profile_create_form($data) {
	$errors = array();
	$profiles = get_profile_labels();
	
	// TODO check for field sizes
	
	// Check mandatory data
	$mandatory_fields = array('name', 'label', 'duplicate_profile');
	foreach ($mandatory_fields as $field) {
		if (!$data[$field]) {
			$errors[$field] []= $l->g(1391);
		}
	}
	
	// Check dropdown lists
	if ($data['duplicate_profile'] and !isset($profiles[$data['duplicate_profile']])) {
		$errors['duplicate_profile'] []= $l->g(1392);
	}
	
	// Check profile name regex
	if ($data['name'] and !preg_match('/^[0-9A-Za-z]+$/', $data['name'])) {
		$errors['name'] []= $l->g(1394);
	}
	
	// Check profile name doesn't exist
	if ($data['name'] and isset($profiles[$data['name']])) {
		$errors['name'] []= $l->g(1395);
	}
	
	return $errors;
}

function validate_profile_edit_form($profile_id, $data) {
	$errors = array();

	$yes_no = array('YES', 'NO');

	$urls = $_SESSION['OCS']['url_service'];
	
	foreach ($data['config'] as $key => $val) {
		if (!in_array($val, $yes_no)) {
			$errors['config_'.$key.'_'] []= $l->g(1392);
		}
	}
	
	foreach ($data['blacklist'] as $key => $val) {
		if (!in_array($val, $yes_no)) {
			$errors['blacklist_'.$key.'_'] []= $l->g(1392);
		}
	}
	
	foreach ($data['pages'] as $key => $val) {
		if (!$urls->getUrl($key)) {
			$errors['blacklist_'.$key.'_'] []= $l->g(1392);
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