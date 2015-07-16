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
	require_once 'views/profile_edit_form.php';
	
	show_users_left_menu('ms_profiles');
	
	echo '<div class="right-content">';

	if (!is_writable(DOCUMENT_REAL_ROOT.'/config/profiles')) {
		msg_error($l->g(2116));
	} else {
		show_profile_edit_form($_GET['profile_id']);
	}
	
	echo '</div>';
} else {
	require_once 'lib/profile_functions.php';
	
	if (!is_writable(DOCUMENT_REAL_ROOT.'/config/profiles/'.$_GET['profile_id'].'.xml')) {
		$response = array(
				'status' => 'error',
				'message' => $l->g(1407).' (config/profiles/'.$_GET['profile_id'].'.xml)'
		);
	} else if ($errors = validate_profile_edit_form($_GET['profile_id'], $_POST)) {
		$response = array(
				'status' => 'error',
				'message' => $l->g(1404),
				'errors' => $errors
		);
	} else if (update_profile($_GET['profile_id'], $_POST)) {
		$response = array(
				'status' => 'success',
				'message' => $l->g(1408),
				'profile_id' => $_GET['profile_id']
		);
	} else {
		$response = array(
				'status' => 'error',
				'message' => $l->g(1409)
		);
	}

	header('Content-type: application/json');
	echo json_encode($response);
}

?>