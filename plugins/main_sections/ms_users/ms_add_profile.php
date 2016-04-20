<?php
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Erwan GOALOU 2010 (erwan(at)ocsinventory-ng(pt)org)
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================

global $l;

if (!AJAX) {
	require_once 'views/users_views.php';
	require_once 'views/profile_create_form.php';
	
	show_users_left_menu('ms_add_profile');
	
	echo '<div class="right-content">';

	if (!is_writable(DOCUMENT_REAL_ROOT.'/config/profiles')) {
		msg_error($l->g(2116));
	} else {
		show_profile_create_form();
	}
	
	echo '</div>';
} else {
	require_once 'lib/profile_functions.php';
	
	if (!is_writable(DOCUMENT_REAL_ROOT.'/config/profiles')) {
		$response = array(
				'status' => 'error',
				'message' => $l->g(2116)
		);
	} else if ($errors = validate_profile_create_form($_POST)) {
		$response = array(
				'status' => 'error',
				'message' => $l->g(1404),
				'errors' => $errors
		);
	} else if ($profile_id = create_profile($_POST)) {
		$response = array(
				'status' => 'success',
				'message' => $l->g(1405),
				'profile_id' => $profile_id
		);
	} else {
		$response = array(
				'status' => 'error',
				'message' => $l->g(1406)
		);
	}

	header('Content-type: application/json');
	echo json_encode($response);
}

?>