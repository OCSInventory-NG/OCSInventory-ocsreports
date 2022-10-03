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
require("require/search/SoftwareSearch.php");
require("require/search/DatabaseSearch.php");
require("require/search/AccountinfoSearch.php");
require("require/search/TranslationSearch.php");
require("require/search/GroupSearch.php");
require("require/search/LegacySearch.php");
require("require/search/Search.php");
require("require/search/SnmpSearch.php");
require("require/search/SQLCache.php");
require_once('require/admininfo/Admininfo.php');
require_once('require/snmp/Snmp.php');

$Admininfo = new Admininfo();
$OcsSnmp = new OCSSnmp();

// Get tables and columns infos
$softwareSearch = new SoftwareSearch();

// Get tables and columns infos
$databaseSearch = new DatabaseSearch($softwareSearch);

// Get columns infos datamap structure
$accountInfoSearch = new AccountinfoSearch();

// Get columns infos datamap structure
$translationSearch = new TranslationSearch();

// Get columns infos datamap structure
$groupSearch = new GroupSearch();

// Get search object to perform action and show result
//$legacySearch = new LegacySearch();

$search = new Search($translationSearch, $databaseSearch, $accountInfoSearch, $groupSearch, $softwareSearch);

$snmpSearch = new SnmpSearch($search, $accountInfoSearch, $databaseSearch, $translationSearch, $OcsSnmp);

$sqlCache = new SQLCache($search, $softwareSearch);

echo open_form('tab_multi', '', '', '');

$multisearchChoise['COMPUTERS'] = strtoupper($l->g(729));
// If SNMP is enable
$isEnable = look_config_default_values(array("SNMP" => "SNMP"))['ivalue']['SNMP'];
if($isEnable) $multisearchChoise['SNMP'] = $l->g(1136);

if (empty($protectedPost['onglet']) && !isset($protectedGet['onglet'])) {
    $protectedPost['onglet'] = "COMPUTERS";
} elseif(isset($protectedGet['onglet']) && empty($protectedPost['onglet'])) {
	$protectedPost['onglet'] = "SNMP";
}

//show onglet
echo "<p>";
onglet($multisearchChoise, "tab_multi", "onglet", 7);
echo "</p>";

echo close_form();

$_SESSION['OCS']['DATE_FORMAT_LANG'] = $l->g(1270);

if (isset($protectedPost['table_select'])) {
	$defaultTable = $protectedPost['table_select'];
} else {
	$defaultTable = null;
}

if($protectedPost['onglet'] == "COMPUTERS") {
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
	
				<input name="onglet" type="hidden" value="COMPUTERS">
				<input name="old_table" type="hidden" value="<?php echo $defaultTable ?>">
				<div><a href="?function=save_query_list"><?php echo $l->g(2140) ?></a></div>
				<?php echo close_form();?>
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
	
	if(isset($protectedGet['fields'])){
	  	$search->link_index($protectedGet['fields'], $protectedGet['values'], $protectedGet['comp'], $protectedGet['values2']);
	}
	
	if(isset($protectedGet['prov']) && !isset($protectedPost['table_select']) && !isset($protectedPost['columns_select']) && !isset($protectedPost['search_ok'])){
		if($protectedGet['prov'] == 'cveNamePublisherVersion'){
			$search->link_multi($protectedGet['prov'], $protectedGet['value']);
		}elseif($protectedGet['prov'] == 'cveNameVersion'){
			$search->link_multi($protectedGet['prov'], $protectedGet['value']);
		}elseif($protectedGet['prov'] == 'cveName'){
			$search->link_multi($protectedGet['prov'], $protectedGet['value']);
		}elseif($protectedGet['prov'] == 'allsoft'){
			$search->link_multi("cveNamePublisherVersion", $protectedGet['value']);
		}elseif($protectedGet['prov'] == 'ipdiscover1'){
			$search->link_multi($protectedGet['prov'], $protectedGet['value']);
		}elseif($protectedGet['prov'] == 'stat'){
			$options['idPackage'] = $databaseSearch->get_package_id($protectedGet['id_pack']);
			$options['stat'] = $protectedGet['stat'];
			$search->link_multi($protectedGet['prov'], $protectedGet['value'] ?? null, $options);
		}elseif($protectedGet['prov'] == 'saas'){
			$search->link_multi($protectedGet['prov'], $protectedGet['value']);
		}elseif($protectedGet['prov'] == 'querysave'){
			$search->link_multi($protectedGet['prov'], $protectedGet['value']);
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
			$i = 0;
			foreach ($infos as $uniqid => $values) {
				?>
				<div class="row" name="<?php echo $uniqid ?>">
				<?php
				if($i != 0){
					$htmlComparator = $search->returnFieldHtmlAndOr($uniqid, $values, $infos, $table, $values['comparator'] ?? null);
					if($htmlComparator != ""){
					echo "<div class='col-sm-5'></div><div class='col-sm-1'>
										<div class='form-group'>
													".$htmlComparator."
										</div>
									</div></br></br></br>";
					}
				} ?>
					<div class="col-sm-3">
						<div class="btn btn-info disabled" style="cursor:default;"><?php
				if(strpos($values['fields'], 'fields_') !== false){
				  	$fields = $accountInfoSearch->getAccountInfosList();
				  	echo $translationSearch->getTranslationFor($table)." : ".$fields['COMPUTERS'][$values['fields']];
				}else{
				  	echo $translationSearch->getTranslationFor($table)." : ".$translationSearch->getTranslationFor($values['fields']);
				}
						?></div>
					</div>
	
					<div class="col-sm-3">
						<div class="form-group">
							<select class="form-control" name="<?php echo $search->getOperatorUniqId($uniqid, $table); ?>" onchange="isnull('<?php echo $search->getOperatorUniqId($uniqid, $table); ?>', '<?php echo $search->getFieldUniqId($uniqid, $table); ?>', '<?php echo $search->getSearchedFieldType($table, $values['fields']); ?>');" id="<?php echo $search->getOperatorUniqId($uniqid, $table);?>">
							<?php 	if((strpos($values['fields'], 'fields_') !== false) || ($values['fields'] == "CATEGORY_ID") || ($values['fields'] == 'CATEGORY') || (($search->getSearchedFieldType($table, $values['fields']) == 'datetime'))) {
										echo $search->getSelectOptionForOperators($values['operator'], $table, $values['fields']);
									} else {
										echo $search->getSelectOptionForOperators($values['operator'], $table);
									} ?>
							</select>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							<?php 	if((strpos($values['fields'], 'fields_') !== false) || array_key_exists($values['fields'], $search->correspondance)){
										echo $search->returnFieldHtml($uniqid, $values, $table, $values['fields']);
									}else {
										echo $search->returnFieldHtml($uniqid, $values, $table, null, $values['operator']);
									} ?>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							<a href="?function=visu_search&delete_row=<?php echo $uniqid."_".$table ?>">
								<button type="button" class="btn btn-danger" aria-label="Close" style="padding: 10px;">
									<span class="glyphicon glyphicon-remove"></span>
								</button>
							</a>
						</div>
					</div>
				</div>
				<?php
		  $i++;
			}
		}
	}
	
	if(!empty($_SESSION['OCS']['multi_search'])){
		?>
		
		<div class="col-sm-12">
			<input name="onglet" type="hidden" value="COMPUTERS">
			<input id="search_ok" name="search_ok" type="hidden" value="OK">
			<input type="submit" class="btn btn-success" value="<?php echo $l->g(13) ?>">
		</div>
		
		<?php
		
		echo close_form();
		
		?>
		</div>
		<br/>
		<br/>
		<hr/>
		<div class="row">
			<div class="col-sm-12">
		<?php
		
		$isValid = true;
		
		foreach ($_SESSION['OCS']['multi_search'] as $key => $value) {
			foreach ($value as $k => $v) {
				if (isset($v['value']) && (is_null($v['value'])) && $v['operator'] != "ISNULL") {
					$isValid = false;
				}
			}
		}
		
		if((isset($protectedPost['search_ok']) || isset($protectedGet['prov']) || isset($protectedGet['fields'])) && $isValid){
			unset($_SESSION['OCS']['SEARCH_SQL_GROUP']);
			/**
			 * Generate Search fields
			 */
			$search->generateSearchQuery($_SESSION['OCS']['multi_search']);
			$sql = $search->baseQuery.$search->searchQuery.$search->columnsQueryConditions;
		
			$_SESSION['OCS']['multi_search_query'] = $sql;
			$_SESSION['OCS']['multi_search_args'] = $search->queryArgs;
		
			$form_name = "affich_multi_crit";
			$table_name = $form_name;
			$tab_options = $protectedPost;
			$tab_options['form_name'] = $form_name;
			$tab_options['table_name'] = $table_name;
		
			echo open_form($form_name, '', '', 'form-horizontal');
		
			$list_fields = $search->fieldsList;
			$list_col_cant_del = $search->defaultFields;
			$default_fields = $search->defaultFields;
		
			$_SESSION['OCS']['SEARCH_SQL_GROUP'][] = $sqlCache->generateCacheSql($_SESSION['OCS']['multi_search']);
		
			$tab_options['ARG_SQL'] = $search->queryArgs;
			$tab_options['CACHE'] = 'RESET';
		
			//BEGIN SHOW ACCOUNTINFO
			$option_comment['comment_be'] = $l->g(1210)." ";
			$tab_options['REPLACE_VALUE'] = $Admininfo->replace_tag_value('',$option_comment);
			$tab_options['REPLACE_VALUE'][$l->g(66)] = $Admininfo->type_accountinfo;
			// $tab_options['REPLACE_VALUE'][$l->g(1061)] = $array_tab_account;
		
		
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
		
			$list_fonct["image/groups_search.png"]=$l->g(583);
			$list_pag["image/groups_search.png"]=$pages_refs["ms_custom_groups"];
		
			$list_pag["image/cadena_ferme.png"]=$pages_refs["ms_custom_lock"];
			$list_pag["image/mass_affect.png"]=$pages_refs["ms_custom_tag"];
		
			$list_fonct["asset_cat"]=$l->g(2126);
			$list_pag["asset_cat"]=$pages_refs["ms_asset_cat"];
		
			$list_fonct["save_query"]=$l->g(2138);
			$list_pag["save_query"]=$pages_refs["ms_save_query"];
		
			$list_id = $databaseSearch->getIdList($search);
			$_SESSION['OCS']['ID_REQ']=id_without_idgroups($list_id);
		
			?>
			<div class='row' style='margin: 0'>
				<?php add_trait_select($list_fonct,$list_id,$form_name,$list_pag); ?>
			</div>
			<?php
		
		}
		
		echo close_form();
		
		?>
			</div>
		</div>
		<?php
	}
} elseif($protectedPost['onglet'] == "SNMP") {
	/**
	 * ========================
	 * = TYPE SELECTION PANEL =
	 * ========================
	 */
	echo '<div class="panel panel-default">';

	printEnTete($l->g(1136)." : ".$l->g(9));

	echo '<div class="row">';
	echo '<div class="col-md-12">';

	echo open_form('addSearchCrit', '', '', '');

	// Add var to snmp session datamap
	if (isset($protectedPost['old_table']) && isset($protectedPost['columns_select']) && !isset($protectedPost['search_ok'])) {
		if(!AJAX){
			$snmpSearch->addSessionsInfos($protectedPost);
		}
	}

	if(isset($protectedGet['delete_row'])){
		if(!AJAX){
			$snmpSearch->removeSessionsInfos($protectedGet['delete_row']);
		}
	}

	if (!empty($_SESSION['OCS']['SNMP']['multi_search'])) {
		$disabled = 'disabled';
		$defaultTable = key($_SESSION['OCS']['SNMP']['multi_search']);
	} else {
		$disabled = null;
	}

	echo '<div class="row">';
	echo '<div class="col-sm-2"></div>';
	echo '<div class="col-sm-3">';
	echo '<div class="form-group">';
	echo '<select class="form-control" name="table_select" onchange="this.form.submit()" '.$disabled.'>';
	echo $snmpSearch->getSelectOptionForTables($defaultTable);
	echo '</select>';
	echo '</div>';
	echo '</div>';

	$snmpSearch->getSelectOptionForColumns($defaultTable);

	echo '<div class="col-sm-3">';
	echo '<div class="form-group">';
	echo '<select class="form-control" name="columns_select">';

	if (!is_null($defaultTable)) {
		echo $snmpSearch->getSelectOptionForColumns($defaultTable);
	}

	echo '</select>';
	echo '</div>';
	echo '</div>';
	echo '<div class="col-sm-2">';
	echo '<input type="submit" class="btn btn-info" value="'.$l->g(116).'">';
	echo '</div>';
	echo '<div class="col-sm-2"></div>';
	echo '</div>';

	echo '<input name="old_table" type="hidden" value="'.$defaultTable.'">';
	echo '<input name="old_table_name" type="hidden" value="'.$databaseSearch->getTypeName($defaultTable).'">';
	echo '<input name="onglet" type="hidden" value="SNMP">';
	echo close_form();
	echo '</div>';
	echo '</div>';
	echo '</div>';
	/**
	 * ============================
	 * = END TYPE SELECTION PANEL =
	 * ============================
	 */

	/**
	 * ======================
	 * = CRITERIA INSERTION =
	 * ======================
	 */
	echo '<div name="multiSearchCritsDiv">';
	echo open_form('multiSearchCrits', '', '', '');

	if (!empty($_SESSION['OCS']['SNMP']['multi_search'])) {
		if(isset($protectedPost['search_ok'])){
			$snmpSearch->updateSessionsInfos($protectedPost);
		}

		foreach ($_SESSION['OCS']['SNMP']['multi_search'] as $table => $infos) {
			$i = 0;
			foreach ($infos as $uniqid => $values) {
				echo '<div class="row" name="'.$uniqid.'">';

				if($i != 0){
					$htmlComparator = $search->returnFieldHtmlAndOr($uniqid, $values, $infos, $table, $values['comparator'] ?? null);
					if($htmlComparator != ""){
					echo "<div class='col-sm-5'></div><div class='col-sm-1'>
						  <div class='form-group'>".$htmlComparator."</div>
						  </div></br></br></br>";
					}
				}
				// DISPLAY TABLE : COLUMN
				echo '<div class="col-sm-3">';
				echo '<div class="btn btn-info disabled" style="cursor:default;">';

				if($values['fields'] == "LASTDATE" || $values['fields'] == "ID") {
					echo $values['table']." : ".$translationSearch->getTranslationFor($values['fields']);
				} elseif(strpos($values['fields'], 'fields_') !== false || $values['fields'] == "TAG") {
					$fields = $accountInfoSearch->getAccountInfosList();
					echo $translationSearch->getTranslationFor('snmp_accountinfo')." : ".$fields['SNMP'][$values['fields']];
				} else {
					echo $values['table']." : ".$values['fields'];
				}

				echo '</div></div>';

				// DISPLAY OPERATORS
				echo '<div class="col-sm-3">';
				echo '<div class="form-group">';
				echo '<select class="form-control" name="'.$search->getOperatorUniqId($uniqid, $table).'" onchange="isnull(\''.$search->getOperatorUniqId($uniqid, $table).'\', \''.$search->getFieldUniqId($uniqid, $table).'\', \''.$snmpSearch->getSearchedFieldType($table, $values['fields']).'\');" id="'.$search->getOperatorUniqId($uniqid, $table).'">';
				
				if((strpos($values['fields'], 'fields_') !== false) || (($snmpSearch->getSearchedFieldType($table, $values['fields']) == 'datetime')) || (($accountInfoSearch->getSearchAccountInfo($values['fields']) == '14'))) {
					echo $snmpSearch->getSelectOptionForOperators($values['operator'], $table, $values['fields']);
				} else {
					echo $snmpSearch->getSelectOptionForOperators($values['operator'], $table);
				}

				echo '</select>';
				echo '</div></div>';

				// DISPLAY INPUT FIELD
				echo '<div class="col-sm-3">';
				echo '<div class="form-group">';

				if((strpos($values['fields'], 'fields_') !== false)){
					echo $snmpSearch->returnFieldHtml($uniqid, $values, $table, $values['fields']);
				} else {
					echo $snmpSearch->returnFieldHtml($uniqid, $values, $table, null, $values['operator']);
				}

				echo '</div></div>';

				// DISPLAY DELETE CROSS
				echo '<div class="col-sm-3">';
				echo '<div class="form-group">';
				echo '<a href="?function=visu_search&delete_row='.$uniqid."_".$table.'&onglet=SNMP">';
				echo '<button type="button" class="btn btn-danger" aria-label="Close" style="padding: 10px;">';
				echo '<span class="glyphicon glyphicon-remove"></span>';
				echo '</button></a>';
				echo '</div></div>';

				echo '</div>';
				$i++;
			}
		}

		// SEARCH BUTTON
		echo '<div class="col-sm-12">';
		if(!is_null($disabled)) echo '<input name="table_select" type="hidden" value="'.$defaultTable.'">';
		echo '<input id="onglet" name="onglet" type="hidden" value="SNMP">';
		echo '<input id="search_ok" name="search_ok" type="hidden" value="OK">';
		echo '<input type="submit" class="btn btn-success" value="'.$l->g(13).'">';
		echo '</div>';
	}

	echo close_form();
	echo '</div>';

	if(!empty($_SESSION['OCS']['SNMP']['multi_search'])){
		echo '<br><br><hr>';

		$isValid = true;
		
		foreach ($_SESSION['OCS']['SNMP']['multi_search'] as $key => $value) {
			foreach ($value as $k => $v) {
				if (isset($v['value']) && (is_null($v['value'])) && $v['operator'] != "ISNULL") {
					$isValid = false;
				}
			}
		}

		echo '<div class="row">';
		echo '<div class="col-sm-12">';

		if(isset($protectedPost['search_ok']) && $isValid){
			/**
			 * =========================
			 * = GENERATE SEARCH QUERY =
			 * =========================
			 */
			$snmpSearch->generateSearchQuery($_SESSION['OCS']['SNMP']['multi_search'], $defaultTable);
			$sql = $snmpSearch->baseQuery.$snmpSearch->searchQuery.$snmpSearch->searchQueryAccount.$snmpSearch->columnsQueryConditions;

			$form_name = "affich_multi_crit";
			$table_name = $form_name;
			$tab_options = $protectedPost;
			$tab_options['form_name'] = $form_name;
			$tab_options['table_name'] = $table_name;

			echo open_form($form_name, '', '', 'form-horizontal');
		
			$list_fields = $snmpSearch->fieldsList;
			$list_col_cant_del = $snmpSearch->listColCantDel;
			$default_fields = $snmpSearch->defaultFields;
	
			$tab_options['ARG_SQL'] = $snmpSearch->queryArgs;
			$tab_options['CACHE'] = 'RESET';

			ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
		
			echo close_form();
		}

		echo '</div></div>';
	}
}

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $sql, $tab_options);
}
