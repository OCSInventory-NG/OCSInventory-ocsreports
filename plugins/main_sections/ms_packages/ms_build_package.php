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

// TODO translate

require_once 'models/Package.php';
require_once 'lib/package_functions.php';

if (!AJAX) {
	// Display upload form
	require_once 'views/package_form.php';

	$default = 'localhost/download';
	$config = look_config_default_values(array('DOWNLOAD_URI_INFO', 'DOWNLOAD_URI_FRAG'));
	
	$activate_info_url = $config['tvalue']['DOWNLOAD_URI_INFO'] ?: $default;
	$activate_frag_url = $config['tvalue']['DOWNLOAD_URI_FRAG'] ?: $default;
	
	show_package_form($activate_info_url, $activate_frag_url);
} else {
	if (isset($_FILES['packageFile'])) {
		// Handle file upload
		$timestamp = time();
		while (file_exists(get_download_root().$timestamp)) {
			$timestamp++;
		}
		
		$res = handle_package_upload($_FILES['packageFile'], $timestamp);
	} else if (isset($_POST['timestamp'])) {
		// Handle package info submit
		$package = Package::buildFromRequest($_POST);
		
		if (!$package->isTemp()) {
			$res = array(
				'status' => 'error',
				'message' => 'No temporary package could be found with this timestamp : '.$_POST['timestamp'].'. Please try refreshing the page and reuploading the package file'
			);
		} else if ($errors = $package->validate()) {
			$res = array(
				'status' => 'error',
				'message' => 'Some errors were found while validating the package',
				'errors' => $errors
			);
		} else if (!$package->create()) {
			$res = array(
				'status' => 'error',
				'message' => 'Some errors were found while creating the package : '.mysqli_error()
			);
		} else {
			if (isset($_POST['activate']) && $_POST['activate'] == 'on') {
				require_once('require/function_telediff.php');
				
				// Handle activation
				$info_url = $_POST['info_url'];
				$fragments_url = $_POST['fragments_url'];
				
				$openssl_ok = function_exists('openssl_open');
				
				$activate_warning = '';
				
				if (!$openssl_ok) {
					$activate_warning = 'OpenSSL for PHP is not properly installed';
				} else if (!$info_url || !$fragments_url) {
					$activate_warning = 'invalid info file URL or fragments URL';
				} else {
					if ($info_url_handle = fopen('https://'.$info_url.'/'.$package->getTimestamp().'/info', 'r')) {
						fclose($info_url_handle);
					} else {
						$activate_warning = $l->g(466).' https://'.$info_url.'/'.$package->getTimestamp().'/<br>';
					}
					
					if ($fragments_url_handle = fopen('http://'.$fragments_url.'/'.$package->getTimestamp().'/'.$package->getTimestamp().'-1', 'r')) {
						fclose($fragments_url_handle);
					} else {
						$activate_warning .= $l->g(467).' http://'.$fragments_url.'/'.$package->getTimestamp().'/<br>';
					}
					
					if (!$activate_warning) {
						activ_pack($package->getTimestamp(), $info_url, $fragments_url);
					}
				}
				
				if ($activate_warning) {
					$res = array(
						'status' => 'warning',
						'message' => 'The package could not be activated :<br>'.$activate_warning,
						'timestamp' => $package->getTimestamp()
					);
				} else {
					$res = array(
						'status' => 'success',
						'timestamp' => $package->getTimestamp()
					);
				}
			} else {
				$res = array(
					'status' => 'success',
					'timestamp' => $package->getTimestamp()
				);
			}
		}
	}
	
	header('Content-type: application/json');
	echo json_encode($res);
}

?>