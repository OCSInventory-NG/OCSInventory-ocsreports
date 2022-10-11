<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */
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

	<h3><?php echo $l->g(1412) ?> (<?php echo $profile->getLabelTranslated() ?>)</h3>

	<?php show_form_input('name', array('type' => 'hidden', 'value' => $profile->getName())) ?>
	<?php show_form_field(array(), array(), 'input', 'new_label', $l->g(1413)) ?>

	<div class="form-frame-profile">
		<div class="form-column">
			<h4>Restrictions</h4>
			<?php show_restrictions_frame($profile, $yes_no) ?>

			<br><br><br>
			<h4>Blacklist</h4>
			<?php show_blacklist_frame($profile, $yes_no) ?>
		</div>

		<div class="form-column">
			<h4>Configuration</h4>
			<?php show_config_frame($profile, $yes_no) ?>
		</div>
	</div>

	<div class="form-frame-profile-pages">
		<h4>Pages</h4>
		<?php show_pages_frame($profile, $yes_no) ?>
	</div>

	<div class="form-buttons">
		<input type="submit" value="<?php echo $l->g(1363) ?>" class="btn btn-success"/>
		<input type="reset" value="<?php echo $l->g(1364) ?>" class="btn btn-danger"/>
	</div>

	<?php

	echo close_form();
}

function show_select_field($data, $key, $name, $label, $options) {
    $field_options = array(
        'field_class' => 'big-label small-input',
        'options' => $options,
        'value' => $data[$name]
    );

    show_form_field($data, array(), 'select', $key . '[' . $name . ']', $label, $field_options);
}

function show_restrictions_frame($profile, $yes_no) {
    global $l;

    $restrictions = $profile->getRestrictions();

    show_select_field($restrictions, 'restrictions', 'GUI', $l->g(1154), $yes_no);
    show_select_field($restrictions, 'restrictions', 'TELEDIFF_ACTIVATE', $l->g(1158), $yes_no);
    show_select_field($restrictions, 'restrictions', 'TELEDIFF_VISIBLE', $l->g(1301), $yes_no);
    show_select_field($restrictions, 'restrictions', 'EXPORT_XML', $l->g(1305), $yes_no);
    show_select_field($restrictions, 'restrictions', 'WOL', $l->g(1281), $yes_no);
}

function show_config_frame($profile, $yes_no) {
    global $l;

    $config = $profile->getConfig();

    show_select_field($config, 'config', 'IPDISCOVER', $l->g(1172), $yes_no);
    show_select_field($config, 'config', 'TELEDIFF', $l->g(1162), $yes_no);
    show_select_field($config, 'config', 'CONFIG', $l->g(1163), $yes_no);
    show_select_field($config, 'config', 'GROUPS', $l->g(1164), $yes_no);
    show_select_field($config, 'config', 'CONSOLE', $l->g(1165), $yes_no);
    show_select_field($config, 'config', 'ALERTE_MSG', $l->g(1166), $yes_no);
    show_select_field($config, 'config', 'ACCOUNTINFO', $l->g(1167), $yes_no);
    show_select_field($config, 'config', 'CHANGE_ACCOUNTINFO', $l->g(1168), $yes_no);
    show_select_field($config, 'config', 'CHANGE_USER_GROUP', $l->g(1169), $yes_no);
    show_select_field($config, 'config', 'MANAGE_SAVED_SEARCHES', $l->g(9940), $yes_no);
    show_select_field($config, 'config', 'MANAGE_PROFIL', $l->g(1170), $yes_no);
    show_select_field($config, 'config', 'MANAGE_USER_GROUP', $l->g(1171), $yes_no);
    show_select_field($config, 'config', 'MANAGE_SMTP_COMMUNITIES', $l->g(1205), $yes_no);
    show_select_field($config, 'config', 'DELETE_COMPUTERS', $l->g(1272), $yes_no);
    show_select_field($config, 'config', 'ARCHIVE_COMPUTERS', $l->g(1556), $yes_no);
    show_select_field($config, 'config', 'MANAGE_LAYOUTS', $l->g(9921), $yes_no);
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

    show_select_field($blacklist, 'blacklist', 'MACADD', $l->g(1159), $yes_no);
    show_select_field($blacklist, 'blacklist', 'SERIAL', $l->g(1160), $yes_no);
    show_select_field($blacklist, 'blacklist', 'IPDISCOVER', $l->g(1161), $yes_no);
}

function show_pages_frame($profile) {
    $urls = $_SESSION['OCS']['url_service']->getUrls();
    asort($urls);

    foreach ($urls as $key => $url) {
        if ($profile->hasPage($key)) {
            show_form_field(array(), array(), 'input', 'pages[' . $key . ']', $url['value'], array('type' => 'checkbox', 'field_class' => 'checkbox-field', 'value' => 'on'));
        } else {
            show_form_field(array(), array(), 'input', 'pages[' . $key . ']', $url['value'], array('type' => 'checkbox', 'field_class' => 'checkbox-field'));
        }
    }
}
?>
