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

require_once('require/function_users.php');

require_once('require/tables/Table.php');
require_once('require/tables/Column.php');
require_once('require/tables/CheckboxColumn.php');
require_once('require/tables/ActionsColumn.php');
require_once('require/tables/LinkColumn.php');

// SETUP
$form_name = 'ms_profiles';
$profiles = get_profiles();
$profile_url = 'index.php?'.PAG_INDEX.'='.$pages_refs['ms_profile_details'].'&profile_id=';

$table = new Table($form_name);
$table->addColumn(new CheckboxColumn('name'));
$table->addColumn(new LinkColumn('name', 'Identifier', $profile_url, array('required' => true)));
$table->addColumn(new LinkColumn('label_translated', 'Display name', $profile_url, array('required' => true)));
$table->addColumn(new ActionsColumn(array(
		'edit' => 'glyphicon glyphicon-edit',
		'delete' => 'glyphicon glyphicon-remove',
)), 'name');

if (AJAX) {
	$ajax = true;
	
	parse_str($protectedPost['ocs']['0'], $params);
	$protectedPost += $params;
	
	$data = array();
	
	foreach ($profiles as $profile) {
		$profileData = array();
		foreach ($table->getColumns() as $name => $col) {
			$profileData[$name] = $col->format($profile);
		}
		$data []= $profileData;
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
	show_users_left_menu('ms_profiles');
	
	echo '<div class="right-content">';
	echo '<div class="mlt_bordure">';
	
	echo '<h3>Profiles</h3>';// TODO translate

	$table_renderer = new TableRenderer();
	$table_renderer->show($table, $profiles);
	
	echo '</div>';
	echo '</div>';
}

?>
