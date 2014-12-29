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
require_once('require/tables/Action.php');
require_once('require/tables/LinkColumn.php');

global $l;

// SETUP
$form_name = 'ms_profiles';
$profiles = get_profiles();

$detail_url = 'index.php?'.PAG_INDEX.'='.$pages_refs['ms_profile_details'].'&profile_id=%s';
$delete_url = 'ajax.php?'.PAG_INDEX.'='.$pages_refs['ms_delete_profile'].'&profile_id=%s';

$table = new Table($form_name);
//$table->addColumn(new CheckboxColumn('name'));
$table->addColumn(new LinkColumn('name', $l->g(1402), $detail_url, array(
		'required' => true,
		'sortable' => false,
		'idProperty' => 'name'
)));
$table->addColumn(new LinkColumn('label_translated', $l->g(1411), $detail_url, array(
		'required' => true,
		'sortable' => false,
		'idProperty' => 'name'
)));
$table->addColumn(new ActionsColumn(array(
		new Action($detail_url, 'edit'),
		(new Action($delete_url, 'remove'))
			->setMethod('POST')
			->setConfirm('Are you sure to delete this profile ?')
			->setAjax(true),
), 'name'));

if (AJAX) {
	require_once('require/tables/AjaxTableRenderer.php');
	
	// JSON OUTPUT
	$table_renderer = new AjaxTableRenderer();
	$table_renderer->show($table, $profiles);
} else {
	require_once('views/users_views.php');
	require_once('require/function_search.php');
	require_once('require/tables/TableRenderer.php');
	
	// HTML OUTPUT
	show_users_left_menu('ms_profiles');
	
	echo '<div class="right-content">';
	echo '<div class="mlt_bordure">';
	
	echo '<h3>'.$l->g(1401).'</h3>';

	$table_renderer = new TableRenderer();
	$table_renderer->show($table, $profiles);
	
	echo '</div>';
	echo '</div>';
}

?>
