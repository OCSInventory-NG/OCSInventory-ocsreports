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

require_once 'lib/profile_functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$profile_id = $_GET['profile_id'];
	$profile_path = DOCUMENT_REAL_ROOT.'/config/profiles/'.$profile_id.'.xml';
	
	// TODO translate
	if (!file_exists($profile_path)) {
		$error = 'Unknown profile';
	} else if (!is_writable($profile_path)) {
		$error = $l->g(1407).' (config/profiles/'.$profile_id.'.xml)';
	} else if (!delete_profile($profile_id)) {
		$error = 'An error occurred while trying to delete the profile';
	} else {
		$message = sprintf('The profile %s was successfully deleted', $profile_id);
	}
	
	if (AJAX) {
		if ($error) {
			echo json_encode(array(
					'status' => 'error',
					'message' => $error
			));
		} else {
			echo json_encode(array(
					'status' => 'success',
					'message' => $message
			));
		}
	} else {
		if ($error) {
			msg_error($error);
		} else {
			msg_success($message);
		}
	}
} else {
	// TODO error or delete form ?
}

?>