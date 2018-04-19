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

if (AJAX) {
    parse_str($protectedPost['ocs']['0'], $params);
    $protectedPost += $params;
    ob_start();
}

require('require/function_search.php');
require('require/function_computers.php');
require("require/search/DatabaseSearch.php");
require("require/search/AccountinfoSearch.php");
require("require/search/TranslationSearch.php");
require("require/search/LegacySearch.php");
require("require/search/Search.php");

// Get tables and columns infos
$databaseSearch = new DatabaseSearch();

// Get columns infos datamap structure
$accountInfoSearch = new AccountinfoSearch();

// Get columns infos datamap structure
$translationSearch = new TranslationSearch();

// Get search object to perform action and show result
//$legacySearch = new LegacySearch();

$search = new Search($translationSearch, $databaseSearch, $accountinfoSearch);

if (isset($protectedPost['table_select'])) {
	$defaultTable = $protectedPost['table_select'];
} else {
	$defaultTable = null;
}

?>
<div class="panel panel-default">

	<?php printEnTete($l->g(9)); ?>

	<div class="row">
		<div class="col-md-12">


			<?php echo open_form('addSearchCrit', '', '', '') ?>

			<div class="row">
				<div class="col-sm-2"></div>
				<div class="col-sm-3">
					<div class="form-group">
						<select class="form-control" name="table_select" onchange="this.form.submit()">
							<?php echo $search->getSelectOptionForTables($defaultTable)  ?>
						</select>
					</div> 
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						<select class="form-control" name="columns_select">
							<?php 
								if (!is_null($defaultTable)) {
									echo $search->getSelectOptionForColumns($defaultTable);
								}
							?>
						</select>
					</div> 
				</div>
				<div class="col-sm-2">
					<input type="submit" class="btn btn-info" value="<?php echo $l->g(116) ?>">
				</div>
				<div class="col-sm-2"></div>
			</div> 

			<input name="old_table" type="hidden" value="<?php echo $defaultTable ?>">

			<?php echo close_form(); ?>

		</div>
	</div>
</div>

<?php 

// Add var to session datamap
if (isset($protectedPost['old_table']) && isset($protectedPost['table_select']) && !isset($protectedPost['search_ok'])) {
	if ($protectedPost['old_table'] === $protectedPost['table_select']) {
		if(!AJAX){
			$search->addSessionsInfos($protectedPost);
		}	
	}
}

if(isset($protectedGet['delete_row'])){
	if(!AJAX){
		$search->removeSessionsInfos($protectedGet['delete_row']);
	}
}

if ( isset($protectedPost['del_check']) ){
	if(!AJAX){
		if(strlen($protectedPost['del_check'] == 1)){
			deleteDid($protectedPost['del_check']);
		}else{
			$delIdArray = explode(",", $protectedPost['del_check']);
			foreach ($delIdArray as $index) {
				deleteDid($index);
			}
		}

	}
}

?>
<div name="multiSearchCritsDiv">
<?php

echo open_form('multiSearchCrits', '', '', '');

if (!empty($_SESSION['OCS']['multi_search'])) {
	if(isset($protectedPost['search_ok'])){
		$search->updateSessionsInfos($protectedPost);
	}

	foreach ($_SESSION['OCS']['multi_search'] as $table => $infos) {
		foreach ($infos as $uniqid => $values) {
			?>
			<div class="row" name="<?php echo $uniqid ?>">
				<div class="col-sm-3">
					<div class="btn btn-info disabled" style="cursor:default;"><?php 
						echo $translationSearch->getTranslationFor($table)." : ".$translationSearch->getTranslationFor($values['fields']);
					?></div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						<select class="form-control" name="<?php echo $search->getOperatorUniqId($uniqid, $table); ?>">
							<?php echo $search->getSelectOptionForOperators($values['operator'])  ?>
						</select>
					</div> 
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						<?php echo $search->returnFieldHtml($uniqid, $values, $table) ?>
					</div> 
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						<a href="?function=visu_search&delete_row=<?php echo $uniqid."_".$table ?>">
							<button type="button" class="btn btn-danger" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</a>
					</div> 
				</div>
			</div>
			<?php	
		}
	}
}
?>

<div class="col-sm-12">
	<input id="search_ok" name="search_ok" type="hidden" value="OK">
	<input type="submit" class="btn btn-success" value="<?php echo $l->g(13) ?>">
</div>

<?php

echo close_form();

if(!empty($_SESSION['OCS']['multi_search'])){
?>
</div>
<br/>
<br/>
<div class="row">
	<div class="col-sm-12">
<?php 

/**
 * Generate Search fields
 */

$search->generateSearchQuery($_SESSION['OCS']['multi_search']);
$sql = $search->baseQuery.$search->searchQuery.$search->columnsQueryConditions;

$form_name = "affich_multi_crit";
$table_name = $form_name;
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;

echo open_form($form_name, '', '', 'form-horizontal');

$list_fields = $search->fieldsList;
$list_col_cant_del = $search->defaultFields;
$default_fields = $search->defaultFields;

$tab_options['ARG_SQL'] = $search->queryArgs;
$tab_options['CACHE'] = 'RESET';

ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);

if ($_SESSION['OCS']['profile']->getConfigValue('DELETE_COMPUTERS') == "YES"){
	$list_fonct["image/delete.png"]=$l->g(122);
	$list_pag["image/delete.png"]=$pages_refs["ms_custom_sup"];
		$tab_options['LBL_POPUP']['SUP']='name';
}
$list_fonct["image/cadena_ferme.png"]=$l->g(1019);
$list_fonct["image/mass_affect.png"]=$l->g(430);
if ($_SESSION['OCS']['profile']->getConfigValue('CONFIG') == "YES"){
	$list_fonct["image/config_search.png"]=$l->g(107);
	$list_pag["image/config_search.png"]=$pages_refs['ms_custom_param'];
}
if ($_SESSION['OCS']['profile']->getConfigValue('TELEDIFF') == "YES"){
	$list_fonct["image/tele_search.png"]=$l->g(428);
	$list_pag["image/tele_search.png"]=$pages_refs["ms_custom_pack"];
}
$list_pag["image/groups_search.png"]=$pages_refs["ms_custom_groups"];

$list_fonct["image/groups_search.png"]=$l->g(583);
$list_pag["image/groups_search.png"]=$pages_refs["ms_custom_groups"];

$list_pag["image/cadena_ferme.png"]=$pages_refs["ms_custom_lock"];
$list_pag["image/mass_affect.png"]=$pages_refs["ms_custom_tag"];

$list_id = $databaseSearch->getIdList($search);

?>
<div class='row' style='margin: 0'>
	<?php add_trait_select($list_fonct,$list_id,$form_name,$list_pag); ?>
</div>
<?php

echo close_form();

?>
	</div>
</div>
<?php

}

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $sql, $tab_options);
}