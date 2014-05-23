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

function show_activate_form($timestamp, $data, $errors) {
	global $l;
	
	// TODO translations
	$package = get_package_info($timestamp);
	$redistrib_pack = get_redistrib_package_info($timestamp);

	echo '<h3>Package '.$package['NAME'].' ('.$package['FILEID'].')</h3>';
	
	echo '<a href="#" target="_blank">Download</a>';
	if ($redistrib_pack) {
		echo ' - <a href="#" target="_blank">Download redistribution package</a>';
	}
	echo ' - <a href="#" target="_blank">View info file</a>';
	

	echo open_form('activate_pack');

	echo '<div class="form-frame">';
	
	show_form_field($data, $errors, 'select', 'METHOD', 'Activation method', array(
		'type' => 'radio',
		'options' => array(
			''
		)
	));
	
	echo '</div>';
	
	echo close_form();
}

?>