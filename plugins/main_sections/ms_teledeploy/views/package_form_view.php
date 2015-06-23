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

function show_package_form($data, $errors) {
	global $l;
	
	// TODO translations

	echo '<h3>'.$l->g(435).'</h3>';
	
	echo open_form('create_pack', '', 'enctype="multipart/form-data"');
	
	// Hidden fields
	show_form_input('TIMESTAMP', array(
		'type' => 'hidden',
		'value' => $data['TIMESTAMP']
	));

	echo '<div class="form-body">';
	
	// Left column
	echo '<div class="form-column">';
	show_file_upload_frame($data['TIMESTAMP'], $data, $errors);
	show_basic_info_frame($data, $errors);
	echo '</div>';

	// Right column
	echo '<div class="form-column">';
	show_deploy_speed_frame($data, $errors);
	show_redistrib_frame($data, $errors);
	show_user_messages_frame($data, $errors);
	echo '</div>';
	
	echo '</div>';

	// TODO labels
	echo '<div class="form-buttons">';
	show_form_submit('create_package', 'Create package');
	echo '</div>';
	
	echo close_form();
}

function show_file_upload_frame($timestamp, $data, $errors) {
	global $l;
	
	$package_tmp_file = get_download_root().$timestamp.'/tmp/package';

	echo '<div class="form-frame">';
	echo '<h4>Package archive</h4>';
	
	if (file_exists($package_tmp_file)) {
		echo '<div class="file-field" style="display: none">';
	} else {
		echo '<div class="file-field">';
	}
	
	show_form_field($data, $errors, 'input', 'FILE', $l->g(549), array(
		'type' => 'file',
		'attrs' => array(
			'accept' => 'application/zip,application/gzip',
			'multiple' => 'multiple'
		)
	));
	
	echo '</div>';
	
	echo '<div class="progress progress-striped package-progress-bar" style="display: none">';
	echo '<div class="progress-bar progress-bar-success"></div>';
	echo '</div>';

	if (file_exists($package_tmp_file)) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime_type = finfo_file($finfo, $package_tmp_file);
		finfo_close($finfo);
		
		if ($mime_type == 'application/zip') {
			$ext = 'zip';
		} else if ($mime_type == 'application/x-gzip') {
			$ext = 'tar.gz';
		}
		
		$size = filesize($package_tmp_file);
		
		echo '<div class="file-info">';
		echo '<div class="file-type"><b>File type : </b><span>'.$ext.'</span></div>';
		echo '<div class="file-size"><b>File size : </b><span>'.$size.'</span></div>';
		echo '</div>';
	} else {
		echo '<div class="file-info" style="display:none">';
		echo '<div class="file-type"><b>File type : </b><span></span></div>';
		echo '<div class="file-size"><b>File size : </b><span></span></div>';
		echo '</div>';
	}
	
	echo '</div>';
}

function show_basic_info_frame($data, $errors) {
	global $l;

	echo '<div class="form-frame">';
	echo '<h4>Basic info</h4>';
	
	show_form_field($data, $errors, 'input', 'NAME', $l->g(49));
	show_form_field($data, $errors, 'textarea', 'DESCRIPTION', $l->g(53));
	show_form_field($data, $errors, 'select', 'OS', $l->g(25), array(
		'type' => 'radio',
		'options' => array(
			'WINDOWS' => 'Windows',
			'LINUX' => 'UNIX / Linux',
			'MAC' => 'Mac OS'
		)
	));
	show_form_field($data, $errors, 'select', 'ACTION', $l->g(443), array(
		'type' => 'radio',
		'options' => array(
			'STORE' => $l->g(457),
			'EXECUTE' => $l->g(456),
			'LAUNCH' => $l->g(458)
		),
	));
	
	switch ($data['ACTION']) {
		case 'EXECUTE':
			$action_input_label = $l->g(444);
			break;
		case 'STORE':
			$action_input_label = $l->g(445);
			break;
		case 'LAUNCH':
			$action_input_label = $l->g(446);
			break;
		default:
			$action_input_label = '';
			break;
	}
	show_form_field($data, $errors, 'input', 'ACTION_INPUT', $action_input_label);
	
	echo '<span style="display: none" class="action-input-EXECUTE">'.$l->g(444).' :</span>';
	echo '<span style="display: none" class="action-input-STORE">'.$l->g(445).' :</span>';
	echo '<span style="display: none" class="action-input-LAUNCH">'.$l->g(446).' :</span>';
	
	echo '</div>';
}

function show_deploy_speed_frame($data, $errors) {
	global $l;

	echo '<div class="form-frame">';
	echo '<h4>Deployment speed</h4>';
	
	show_form_field($data, $errors, 'select', 'DEPLOY_SPEED', 'Speed', array(// TODO translations
		'options' => array(
			'LOW' => 'Slow',
			'MIDDLE' => 'Average',
			'HIGH' => 'Fast',
			'CUSTOM' => 'Custom'
		)
	));
	
	if ($data['DEPLOY_SPEED'] != 'CUSTOM') {
		$deploy_attrs = array(
			'disabled' => 'disabled'
		);
	} else {
		$deploy_attrs = array();
	}
	
	show_form_field($data, $errors, 'select', 'PRIORITY', $l->g(440), array(
		'attrs' => $deploy_attrs,
		'options' => range(0, 10)
	));
	show_form_field($data, $errors, 'input', 'NB_FRAGS', $l->g(464), array(
		'attrs' => $deploy_attrs
	));
	
	echo '</div>';
}

function show_redistrib_frame($data, $errors) {
	global $l;

	echo '<div class="form-frame">';
	echo '<h4>';
	
	show_form_input('REDISTRIB_USE', array(
		'type' => 'checkbox',
		'value' => $data['REDISTRIB_USE']
	));
	show_form_label('REDISTRIB_USE', 'Redistribution'); // TODO translation
	
	echo '</h4>';
	
	if ($data['REDISTRIB_USE'] != 'on') {
		echo '<div class="redistrib-fields" style="display: none">';
	} else {
		echo '<div class="redistrib-fields">';
	}
	
	echo '<div class="field">';
	echo '<b>Directory for redistribution packages : </b><br/>'.get_redistrib_download_root();
	echo '</div>';
	
	show_form_field($data, $errors, 'input', 'DOWNLOAD_SERVER_DOCROOT', $l->g(1009));
	show_form_field($data, $errors, 'select', 'REDISTRIB_PRIORITY', $l->g(440), array(
		'options' => range(0, 10)
	));
	show_form_field($data, $errors, 'input', 'REDISTRIB_NB_FRAGS', $l->g(464));
	
	echo '</div>';
	
	echo '</div>';
}

function show_user_messages_frame($data, $errors) {
	global $l;
	
	if ($data['OS'] != 'WINDOWS') {
		$messages_style = ' style="display: none"';
	} else {
		$messages_style = '';
	}

	echo '<div class="form-frame form-frame-user-messages"'.$messages_style.'>';
	echo '<h4>';
	
	show_form_input('NOTIFY_USER', array(
		'type' => 'checkbox',
		'value' => $data['NOTIFY_USER']
	));
	show_form_label('NOTIFY_USER', $l->g(448));
	
	echo '</h4>';
	
	if ($data['NOTIFY_USER'] != 'on') {
		echo '<div class="notify-fields" style="display: none">';
	} else {
		echo '<div class="notify-fields">';
	}
	
	show_form_field($data, $errors, 'textarea', 'NOTIFY_TEXT', $l->g(449));
	show_form_field($data, $errors, 'input', 'NOTIFY_COUNTDOWN', $l->g(450));
	show_form_field($data, $errors, 'input', 'NOTIFY_CAN_CANCEL', $l->g(451), array(
		'type' => 'checkbox'
	));
	show_form_field($data, $errors, 'input', 'NOTIFY_CAN_DELAY', $l->g(452), array(
		'type' => 'checkbox'
	));
	
	echo '</div>';
	echo '</div>';
	
	echo '<div class="form-frame form-frame-user-messages"'.$messages_style.'>';
	echo '<h4>';
	
	show_form_input('NEED_DONE_ACTION', array(
		'type' => 'checkbox',
		'value' => $data['NEED_DONE_ACTION']
	));
	show_form_label('NEED_DONE_ACTION', 'Post-execution text'); // TODO translation
	
	echo '</h4>';
	
	if ($data['NEED_DONE_ACTION'] != 'on') {
		echo '<div class="done-action-fields" style="display: none">';
	} else {
		echo '<div class="done-action-fields">';
	}

	show_form_field($data, $errors, 'textarea', 'NEED_DONE_ACTION_TEXT', $l->g(449));

	echo '</div>';
	echo '</div>';
}

?>