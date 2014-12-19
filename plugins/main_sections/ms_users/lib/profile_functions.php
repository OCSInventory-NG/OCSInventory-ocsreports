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

?>