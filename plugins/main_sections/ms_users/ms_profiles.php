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
require_once('require/function_users.php');
require_once('require/tables/Table.php');
require_once('require/tables/Column.php');
require_once('require/tables/CheckboxColumn.php');
require_once('require/tables/ActionsColumn.php');
require_once('require/tables/LinkColumn.php');

require_once(MAIN_SECTIONS_DIR . 'ms_users/lib/profile_functions.php');

global $l;

// Remove a profile ?
if (isset($protectedGet['action']) && $protectedGet['action'] == 'delete') {
    remove_profile($protectedGet['profile_id']);
}

// SETUP
$form_name = 'ms_profiles';
$profiles = get_profiles();

$detail_url = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_profile_details'] . '&profile_id=';
$delete_url = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_profiles'] . '&action=delete&profile_id=';

$table = new Table($form_name);
$table->addColumn(new CheckboxColumn('name'));
$table->addColumn(new LinkColumn('name', $l->g(1402), $detail_url, array('required' => true, 'idProperty' => 'name')));
$table->addColumn(new LinkColumn('label_translated', $l->g(1411), $detail_url, array('required' => true, 'idProperty' => 'name')));
$table->addColumn(new ActionsColumn(array(
    $detail_url => 'glyphicon glyphicon-edit',
    $delete_url => 'glyphicon glyphicon-remove',
        ), 'name'));

if (AJAX) {
    parse_str($protectedPost['ocs']['0'], $params);
    $protectedPost += $params;

    $data = array();

    foreach ($profiles as $profile) {
        $profileData = array();
        foreach ($table->getColumns() as $name => $col) {
            $profileData[$name] = $col->format($profile);
        }
        $data [] = $profileData;
    }

    // JSON OUTPUT
    $response = array(
        'customized' => false,
        'draw' => $_POST['draw'],
        'data' => $data,
        'recordsFiltered' => count($profiles),
        'recordsTotal' => count($profiles)
    );

    echo json_encode($response);
} else {
	$ajax = false;
	
	require_once('views/users_views.php');
	require_once('require/function_search.php');
	require_once('require/tables/TableRenderer.php');
	
	// HTML OUTPUT
	echo "<div class='col col-md-2'>";
	show_users_left_menu('ms_profiles');
	echo "</div>";
	
	echo '<div class="col col-md-10">';
	
	echo '<h3>'.$l->g(1401).'</h3>';

    $table_renderer = new TableRenderer();
    $table_renderer->show($table, $profiles);

    echo '</div>';
}
?>