<?php

require_once 'require/function_users.php';

function show_profile_edit_form($profile_id) {
	global $l;

	$yes_no = array(
			'YES' => $l->g(455),
			'NO' => $l->g(454)
	);
	
	$profiles = get_profiles();
	$profile = $profiles[$profile_id];

	echo open_form('edit-profile', '#');

	?>
	
	<h3>Edit profile (<?php echo $profile->getLabelTranslated() ?>)</h3>
	
	<?php show_form_input('name', array('type' => 'hidden', 'value' => $profile->getName())) ?>
	<?php show_form_field(array(), array(), 'input', 'new_label', 'New label') ?>
	
	<div class="form-frame form-frame-profile-restrictions">
		<h4>Restrictions</h4>
		<?php show_restrictions_frame($profile, $yes_no) ?>
	</div>
	
	<div class="form-frame form-frame-profile-config">
		<h4>Configuration</h4>
		<?php show_config_frame($profile, $yes_no) ?>
	</div>
	
	<div class="form-frame form-frame-profile-backlist">
		<h4>Blacklist</h4>
		<?php show_blacklist_frame($profile, $yes_no) ?>
	</div>
	
	<div class="form-frame form-frame-profile-pages">
		<h4>Pages</h4>
		<?php show_pages_frame($profile, $yes_no) ?>
	</div>
	
	<div class="form-buttons">
		<input type="submit" value="<?php echo $l->g(1363) ?>"/>
		<input type="reset" value="<?php echo $l->g(1364) ?>"/>
	</div>
	
	<?php
		
	echo close_form();
}

function show_select_field($data, $name, $label, $options) {
	$field_options = array(
			'field_class' => 'big-label',
			'options' => $options
	);

	show_form_field($data, array(), 'select', $name, $label, $field_options);
}

function show_restrictions_frame($profile, $yes_no) {
	global $l;
	
	$restrictions = $profile->getRestrictions();
	
	$telediff_wk = array(
			'LOGIN' => 'LOGIN',
			'USER_GROUP' => 'USER_GROUP',
			'NO' => $l->g(454)
	);

	show_select_field($restrictions, 'restrictions[GUI]', $l->g(1154), $yes_no);
	show_select_field($restrictions, 'restrictions[TELEDIFF_WK]', $l->g(1155), $telediff_wk);
	show_select_field($restrictions, 'restrictions[TELEDIFF_WK_FIELDS]', $l->g(1157), $yes_no);
	show_select_field($restrictions, 'restrictions[TELEDIFF_ACTIVATE]', $l->g(1158), $yes_no);
	show_select_field($restrictions, 'restrictions[TELEDIFF_VISIBLE]', $l->g(1301), $yes_no);
	show_select_field($restrictions, 'restrictions[EXPORT_XML]', $l->g(1305), $yes_no);
	show_select_field($restrictions, 'restrictions[WOL]', $l->g(1281), $yes_no);
}

function show_config_frame($profile, $yes_no) {
	global $l;
	
	$config = $profile->getConfig();
	
	$field_options = array('field_class' => 'big-label');
	
	$telediff_wk = array(
			'LOGIN' => 'LOGIN',
			'USER_GROUP' => 'USER_GROUP',
			'NO' => $l->g(454)
	);

	show_select_field($config, 'config[TELEDIFF_WK]', $l->g(1156), $yes_no);
	show_select_field($config, 'config[IPDISCOVER]', $l->g(1172), $yes_no);
	show_select_field($config, 'config[TELEDIFF]', $l->g(1162), $yes_no);
	show_select_field($config, 'config[CONFIG]', $l->g(1163), $yes_no);
	show_select_field($config, 'config[GROUPS]', $l->g(1164), $yes_no);
	show_select_field($config, 'config[CONSOLE]', $l->g(1165), $yes_no);
	show_select_field($config, 'config[ALERTE_MSG]', $l->g(1166), $yes_no);
	show_select_field($config, 'config[ACCOUNTINFO]', $l->g(1167), $yes_no);
	show_select_field($config, 'config[CHANGE_ACCOUNTINFO]', $l->g(1168), $yes_no);
	show_select_field($config, 'config[CHANGE_USER_GROUP]', $l->g(1169), $yes_no);
	show_select_field($config, 'config[MANAGE_PROFIL]', $l->g(1170), $yes_no);
	show_select_field($config, 'config[MANAGE_USER_GROUP]', $l->g(1171), $yes_no);
	show_select_field($config, 'config[MANAGE_SMTP_COMMUNITIES]', $l->g(1205), $yes_no);
	show_select_field($config, 'config[DELETE_COMPUTERS]', $l->g(1272), $yes_no);
}

function show_blacklist_frame($profile, $yes_no) {
	global $l;

	$macadd = $profile->hasInBlacklist('MACADD') ? 'YES' : 'NO';
	$serial = $profile->hasInBlacklist('SERIAL') ? 'YES' : 'NO';
	$ipdiscover = $profile->hasInBlacklist('IPDISCOVER') ? 'YES' : 'NO';
	
	$blacklist = array(
		'MACADD' => $macadd,
		'SERIAL' => $serial,
		'IPDISCOVER' => $ipdiscover	
	);
	
	$field_options = array('field_class' => 'big-label');
	
	$telediff_wk = array(
			'LOGIN' => 'LOGIN',
			'USER_GROUP' => 'USER_GROUP',
			'NO' => $l->g(454)
	);

	show_select_field($blacklist, 'blacklist[MACADD]', $l->g(1159), $yes_no);
	show_select_field($blacklist, 'blacklist[SERIAL]', $l->g(1160), $yes_no);
	show_select_field($blacklist, 'blacklist[IPDISCOVER]', $l->g(1161), $yes_no);
}

function show_pages_frame($profile, $yes_no) {
	global $l;
	
	$urls = $_SESSION['OCS']['url_service'];
	
	foreach ($urls->getUrls() as $key => $url) {
		show_form_field(array(), array(), 'input', 'pages['.$key.']', $key, array('type' => 'checkbox', 'value' => $profile->hasPage($key) ? 'on' : 'off'));
	}
}


?>