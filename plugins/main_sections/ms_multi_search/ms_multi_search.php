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

printEnTete($l->g(393));

?>

<div class="row">
  	<div class="col-md-12">
		<div class="row">
			<div class="col-sm-4"><?php echo $l->g(1061) ?></div>
			<div class="col-sm-4"><?php echo $l->g(1062) ?></div>
			<div class="col-sm-4"><?php echo $l->g(116) ?></div>
		</div> 

		<?php echo open_form('addSearchCrit', '', '', '') ?>

		<div class="row">
			<div class="col-sm-4">
				<div class="form-group">
					<select class="form-control" name="table_select" onchange="this.form.submit()">
						<?php echo $search->getSelectOptionForTables($defaultTable)  ?>
					</select>
				</div> 
			</div>
			<div class="col-sm-4">
				<div class="form-group">
					<select class="form-control" name="columns_select">
						<?php 
							if(!is_null($defaultTable)){
								echo $search->getSelectOptionForColumns($defaultTable);
							}
						?>
					</select>
				</div> 
			</div>
			<div class="col-sm-4">
				<input type="submit" class="btn btn-info" value="<?php echo $l->g(116) ?>">
			</div>
		</div> 

		<input name="old_table" type="hidden" value="<?php echo $defaultTable ?>">

		<?php echo close_form(); ?>

	</div>
</div>

<?php 

// Add var to session datamap
if (isset($protectedPost['old_table']) && isset($protectedPost['table_select'])) {
	if ($protectedPost['old_table'] === $protectedPost['table_select']) {
		$search->addSessionsInfos($protectedPost);
	}
}

?>
<div name="multiSearchCritsDiv">
<?php

echo open_form('multiSearchCrits', '', '', '');

if (!empty($_SESSION['OCS']['multi_search'])) {
	foreach($_SESSION['OCS']['multi_search'] as $table => $infos){
		$search->processSearchFields($table, $infos);	
	}
}

echo close_form();

?>
</div>