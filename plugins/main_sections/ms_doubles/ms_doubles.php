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
require_once('require/function_computers.php');
require_once('require/function_admininfo.php');
//restriction for profils?
if (isset($_SESSION['OCS']['mesmachines'])) {
	$tab_id_mes_machines = computer_list_by_tag('', 'ARRAY');
	if ($tab_id_mes_machines == "ERROR") {
		echo $l->g(923);
		$tab_id_mes_machines = "";
	}
} else {
	$tab_id_mes_machines = "";
}
printEnTete($l->g(199));

	// sort an array by key
	function groupBy($key, $data) {
		$result = array();
		foreach ($data as $val) {
			if (array_key_exists($key, $val)) {
				$result[$val[$key]][] = $val;
			} else {
				$result[""][] = $val;
			}
		}
		return $result;
	}

// merge selected duplicates
if (isset($protectedPost['FUSION'])) {
	// if duplicates selection is coming from checkbox "all"
	if (isset($protectedPost['selected_grp_dupli'])) {
		foreach ($protectedPost['selected_grp_dupli'] as $dpl) {
			// oh boy
			$dpl = json_decode(html_entity_decode($dpl), true);
			$selectedDuplis[] = $dpl;    
			$dup_grp = groupBy($criteria ?? '', $dpl);
			foreach ($dup_grp as $grp) { 
				if (count($grp) >= 2) {
					$afus = array();
					$i = 0;
					foreach ($grp as $dupl) {
						$res = mysqli_query($_SESSION['OCS']["readServer"], "SELECT deviceid,id,lastcome FROM hardware WHERE id=" . $dupl['ID']) or die(mysqli_error($_SESSION['OCS']["readServer"]));
						$afus[] = mysqli_fetch_array($res, MYSQLI_ASSOC);
						$i++;
					}

					// if $afus is defined, there is something to merge
					if (isset($afus)) {
						msg_success("MERGE SUCCESS");
						fusionne($afus);
					} 
				}
			}
		}
	} 

	if (isset($protectedPost['selected_dupli']) && count($protectedPost['selected_dupli']) >= 2) {
		// need to reconstruct array from jsons
		foreach ($protectedPost['selected_dupli'] as $dpl) {
			$dpl = json_decode(html_entity_decode($dpl), true);
			$selectedDuplis[] = $dpl;
		}

		// grouping the reconstructed array by criteria to merge duplicates coherently
		$groupedDuplis = groupBy($criteria ?? '', $selectedDuplis);

		// iterate through each group of duplicates
		foreach ($groupedDuplis as $correspDuplis) {
			// there must be a least 2 duplis in each group to merge
			if (count($correspDuplis) >= 2) {
				$afus = array();
				$i = 0;
				foreach ($correspDuplis as $dupl) {
					$res = mysqli_query($_SESSION['OCS']["readServer"], "SELECT deviceid,id,lastcome FROM hardware WHERE id=" . $correspDuplis[$i]['ID']) or die(mysqli_error($_SESSION['OCS']["readServer"]));
					$afus[] = mysqli_fetch_array($res, MYSQLI_ASSOC);
					$i++;
				}

				if (isset($afus)) {
					msg_success("MERGE SUCCESS");
					fusionne($afus);
				} 
			} 
		}  
	}

	if (isset($protectedPost['selected_dupli']) && count($protectedPost['selected_dupli']) < 2) {
		echo "<script>alert('" . $l->g(922) . "');</script>";
	}
} 


// merge all duplicates
if (isset($protectedPost['FUSION_ALL'])) {
	// $grpDuplis as already been grouped by criteria and contains all duplicates
	foreach ($grpDuplis as $dup) {
		$afus = array();
		foreach ($dup as $d) {
			$res = mysqli_query($_SESSION['OCS']["readServer"], "SELECT deviceid,id,lastcome FROM hardware WHERE id=" . $d['ID']) or die(mysqli_error($_SESSION['OCS']["readServer"]));
			$afus[] = mysqli_fetch_array($res, MYSQLI_ASSOC);    
		}

		if (isset($afus)) {
			msg_success("MERGE SUCCESS");
			fusionne($afus);
		}
	}    
}

/* * **********************  hostname double ************************************** */
$sql_doublon['hostname'] = "SELECT NAME val FROM hardware ";
$arg_doublon['hostname'] = array();
if (is_defined($tab_id_mes_machines)) {
	$sql = mysql2_prepare($sql_doublon['hostname'] . ' WHERE id IN ', $arg_doublon['hostname'], $tab_id_mes_machines);
	$sql_doublon['hostname'] = $sql['SQL'];
	$arg_doublon['hostname'] = $sql['ARG'];
}
$sql_doublon['hostname'] .= "  GROUP BY NAME HAVING COUNT(NAME)>1";
/* * **********************  serial number double ************************************** */
$sql_doublon['ssn'] = "SELECT SSN val FROM bios,hardware h WHERE h.id=bios.hardware_id AND SSN NOT IN (SELECT serial FROM blacklist_serials) ";
$arg_doublon['ssn'] = array();
if (is_defined($tab_id_mes_machines)) {
	$sql = mysql2_prepare($sql_doublon['ssn'] . ' AND hardware_id IN ', $arg_doublon['ssn'], $tab_id_mes_machines);
	$sql_doublon['ssn'] = $sql['SQL'];
	$arg_doublon['ssn'] = $sql['ARG'];
}
$sql_doublon['ssn'] .= " GROUP BY SSN HAVING COUNT(SSN)>1";
/* * **********************  macaddress double ************************************** */
$sql_doublon['macaddress'] = "SELECT h.id, MACADDR val
							FROM (SELECT hardware_id,MACADDR FROM networks GROUP BY hardware_id,MACADDR) networks,hardware h
							WHERE h.id=networks.hardware_id
							AND  MACADDR NOT IN (SELECT macaddress FROM blacklist_macaddresses)";
$arg_doublon['macaddress'] = array();
if (is_defined($tab_id_mes_machines)) {
	$sql = mysql2_prepare($sql_doublon['macaddress'] . ' AND hardware_id IN ', $arg_doublon['macaddress'], $tab_id_mes_machines);
	$sql_doublon['macaddress'] = $sql['SQL'];
	$arg_doublon['macaddress'] = $sql['ARG'];
}
/* * ***************************request execution**************************************** */
$sql_doublon['macaddress'] .= " GROUP BY MACADDR HAVING COUNT(MACADDR)>1";
foreach ($sql_doublon as $name => $sql_value) {
	$res = mysql2_query_secure($sql_value, $_SESSION['OCS']["readServer"], $arg_doublon[$name]);
	while ($val = mysqli_fetch_object($res)) {
		$doublon[$name][] = $val->val;
	}
}
//search id of computers => serial number
if (isset($doublon['ssn'])) {
	$sql_id_doublon['ssn'] = " SELECT DISTINCT hardware_id id,SSN info1 FROM bios,hardware h WHERE h.id=bios.hardware_id AND SSN IN ";
	$arg_id_doublon['ssn'] = array();
	$sql = mysql2_prepare($sql_id_doublon['ssn'], $arg_id_doublon['ssn'], $doublon['ssn']);
	$arg_id_doublon['ssn'] = $sql['ARG'];
	$sql_id_doublon['ssn'] = $sql['SQL'];
} else {
	$count_id['ssn'] = 0;
}
////search id of computers => macaddresses
if (isset($doublon['macaddress'])) {
$sql_id_doublon['macaddress'] = "SELECT DISTINCT CONCAT(hardware_id,MACADDR), hardware_id id,MACADDR info1
								FROM networks,hardware h
								WHERE h.id=networks.hardware_id AND MACADDR IN ";
	$arg_id_doublon['macaddress'] = array();
	$sql = mysql2_prepare($sql_id_doublon['macaddress'], $arg_id_doublon['macaddress'], $doublon['macaddress']);
	$sql['SQL'] .= " GROUP BY networks.id, MACADDR";
	$arg_id_doublon['macaddress'] = $sql['ARG'];
	$sql_id_doublon['macaddress'] = $sql['SQL'];
} else {
	$count_id['macaddress'] = 0;
}
//search id of computers => hostname
if (isset($doublon['hostname'])) {
	$sql_id_doublon['hostname'] = " SELECT id, NAME info1 
									FROM hardware h,accountinfo a 
									WHERE a.hardware_id=h.id 
									AND NAME IN ";
	$arg_id_doublon['hostname'] = array();
	$sql = mysql2_prepare($sql_id_doublon['hostname'], $arg_id_doublon['hostname'], $doublon['hostname']);
	$arg_id_doublon['hostname'] = $sql['ARG'];
	$sql_id_doublon['hostname'] = $sql['SQL'];
} else {
	$count_id['hostname'] = 0;
}
//search id of computers => hostname + serial number
$sql_id_doublon['hostname_serial'] = "SELECT DISTINCT CONCAT(h.id,h.name,b.ssn), h.id,h.name info1,b.ssn info2
									FROM hardware h
									LEFT JOIN bios b ON b.hardware_id = h.id
									LEFT JOIN hardware h2 ON h.name=h2.name
									LEFT JOIN  bios b2 ON b2.ssn = b.ssn
									WHERE  b2.hardware_id = h2.id
									AND h.id <> h2.id AND b.ssn NOT IN (SELECT serial FROM blacklist_serials) ";
$arg_id_doublon['hostname_serial'] = array();
if (is_defined($tab_id_mes_machines)) {
	$sql = mysql2_prepare($sql_id_doublon['hostname_serial'] . ' AND h.id IN ', $arg_id_doublon['hostname_serial'], $tab_id_mes_machines);
	$sql_id_doublon['hostname_serial'] = $sql['SQL'];
	$arg_id_doublon['hostname_serial'] = $sql['ARG'];
}
//search id of computers => hostname + mac address
$sql_id_doublon['hostname_macaddress'] = "SELECT DISTINCT h.id,n.macaddr info1, h.name info2
										FROM hardware h
										LEFT JOIN networks n ON n.hardware_id = h.id
										LEFT JOIN hardware h2 ON h.name=h2.name
										LEFT JOIN  networks n2 ON n2.MACADDR = n.MACADDR
										WHERE  n2.hardware_id = h2.id
										AND h.id <> h2.id AND n.MACADDR NOT IN (SELECT macaddress FROM blacklist_macaddresses)";
$arg_id_doublon['hostname_macaddress'] = array();
if (is_defined($tab_id_mes_machines)) {
	$sql = mysql2_prepare($sql_id_doublon['hostname_macaddress'] . ' AND h.id IN ', $arg_id_doublon['hostname_macaddress'], $tab_id_mes_machines);
	$sql_id_doublon['hostname_macaddress'] = $sql['SQL'];
	$arg_id_doublon['hostname_macaddress'] = $sql['ARG'];
}
$sql_id_doublon['macaddress_serial'] = "SELECT DISTINCT h.id, n1.macaddr info1, b.ssn info2
										FROM hardware h
										LEFT JOIN bios b ON b.hardware_id = h.id
										LEFT JOIN networks n1 ON b.hardware_id=n1.hardware_id
										LEFT JOIN networks n2 ON n1.macaddr = n2.macaddr
										LEFT JOIN bios b2 ON b2.ssn = b.ssn
										WHERE n1.hardware_id = h.id
										AND b2.hardware_id = n2.hardware_id
										AND b2.hardware_id <> b.hardware_id
										AND b.ssn NOT IN (SELECT serial FROM blacklist_serials)
										AND n1.macaddr NOT IN (SELECT macaddress FROM blacklist_macaddresses)";
$arg_id_doublon['macaddress_serial'] = array();
if (is_defined($tab_id_mes_machines)) {
	$sql = mysql2_prepare($sql_id_doublon['macaddress_serial'] . ' AND h.id IN ', $arg_id_doublon['macaddress_serial'], $tab_id_mes_machines);
	$sql_id_doublon['macaddress_serial'] = $sql['SQL'];
	$arg_id_doublon['macaddress_serial'] = $sql['ARG'];
}
foreach($sql_id_doublon as $name=>$sql_value){
	$res = mysql2_query_secure($sql_value, $_SESSION['OCS']["readServer"],$arg_id_doublon[$name]);
	$count_id[$name] = 0;
	while( $val = mysqli_fetch_object( $res ) ) {
		//if restriction => count only computers of profil
		//else, all computers
		if (is_array($tab_id_mes_machines) and in_array ($val->id,$tab_id_mes_machines)){
			$list_id[$name][$val->id]=$val->id;
			$count_id[$name]++;
		} elseif ($tab_id_mes_machines == ""){
			$list_id[$name][$val->id]=$val->id;
			$count_id[$name]++;
		}		
		$list_info[$name][]=$val->info1;
	}
}
$form_name='doublon';
$table_name='DOUBLON';
$tab_options=$protectedPost;
$tab_options['form_name']=$form_name;
$tab_options['table_name']=$table_name;
echo open_form($form_name, '', '', 'form-horizontal');
echo "<div class='col col-md-12'>";
function returnTrad($lbl){
	global $l;
	switch($lbl) {
		case "hostname_serial": return $l->g(193);
		case "hostname_macaddress": return $l->g(194);
		case "macaddress_serial": return $l->g(195);
		case "hostname": return $l->g(196);
		case "ssn": return $l->g(197);
		case "macaddress": return $l->g(198);
	}
}
// show number of duplis for each category (hostname, serial, etc.)
foreach ($count_id as $lbl=>$count_value){
	echo "<div class='row'>";
	echo "<div class='col col-md-4 col-md-offset-3'>";
	echo "<span>".returnTrad($lbl)."</span>";
	echo "</div>";
	echo "<div class='col col-md-2 text-left'>";

	if ($count_value != 0) {
		echo "<a href=# onclick='pag(\"".$lbl."\",\"detail\",\"".$form_name."\");' alt='".$l->g(41)."'>";
	}
	
	echo $count_value;

	if ($count_value != 0) {
		echo "</a>";
	}
	
	echo "</div>";
	echo "</div>";

	if (isset($protectedPost['detail']) && $protectedPost['detail'] == $lbl and $count_value == 0)
	unset($protectedPost['detail']);
}
echo "</table>";
echo "<input type=hidden name=detail id=detail value='".($protectedPost['detail'] ?? '')."'>";
//show details for category
if (!empty($protectedPost['detail'])) {
	// category reminder 
	echo "<h2>". $l->g(9502) ." ".returnTrad($protectedPost['detail'])." </h2>";

	//BEGIN SHOW ACCOUNTINFO
	require_once('require/function_admininfo.php');
	$accountinfo_value = interprete_accountinfo($list_fields ?? array(), $tab_options);
	if (array($accountinfo_value['TAB_OPTIONS'])) {
		$tab_options = $accountinfo_value['TAB_OPTIONS'];
	}
	if (array($accountinfo_value['DEFAULT_VALUE'])) {
		$default_fields = $accountinfo_value['DEFAULT_VALUE'];
	}
	$list_fields = $accountinfo_value['LIST_FIELDS'];

	//END SHOW ACCOUNTINFO
	$list_fields2 = array(
		$l->g(95) => 'n.macaddr',
		$l->g(36) => 'b.ssn',
		$l->g(23) . ": id" => 'h.ID',
		$l->g(23) . ": " . $l->g(46) => 'h.LASTDATE',
		'NAME' => 'h.name',
		$l->g(82) . ": " . $l->g(33) => 'h.WORKGROUP',
		$l->g(23) . ": " . $l->g(25) => 'h.OSNAME',
		$l->g(23) . ": " . $l->g(24) => 'h.USERID',
		$l->g(23) . ": " . $l->g(26) => 'h.MEMORY',
		$l->g(23) . ": " . $l->g(569) => 'h.PROCESSORS',
		$l->g(23) . ": " . $l->g(34) => 'h.IPADDR',
		$l->g(23) . ": " . $l->g(53) => 'h.DESCRIPTION',
		$l->g(23) . ": " . $l->g(354) => 'h.FIDELITY',
		$l->g(23) . ": " . $l->g(820) => 'h.LASTCOME',
		$l->g(23) . ": " . $l->g(351) => 'h.PROCESSORN',
		$l->g(23) . ": " . $l->g(350) => 'h.PROCESSORT',
		$l->g(23) . ": " . $l->g(357) => 'h.USERAGENT',
		$l->g(23) . ": " . $l->g(50) => 'h.SWAP',
		$l->g(23) . ": " . $l->g(111) => 'h.WINPRODKEY',
		$l->g(23) . ": " . $l->g(553) => 'h.WINPRODID'
	);

	$list_fields = array_merge($list_fields, $list_fields2);
	$list_fields['CHECK'] = 'h.ID';
	$list_col_cant_del = array('NAME' => 'NAME', 'CHECK' => 'CHECK', $l->g(35));
	$default_fields2 = array(
		$l->g(95) => $l->g(95), 
		$l->g(36) => $l->g(36),
		$l->g(23) . ": " . $l->g(46) => $l->g(23) . ": " . $l->g(46),
		$l->g(23) . ": " . $l->g(34) => $l->g(23) . ": " . $l->g(34)
	);
	$default_fields = array_merge($default_fields, $default_fields2);

	if ($_SESSION['OCS']['profile']->getConfigValue('DELETE_COMPUTERS') == "YES") {
		$list_fields['SUP'] = 'h.ID';
		$list_col_cant_del['SUP'] = 'SUP';
	}

	$sql = prepare_sql_tab($list_fields, array('SUP', 'CHECK'));
	$sql['SQL'] .= " FROM hardware h";
	$sql['SQL'] .= " LEFT JOIN accountinfo a ON h.id=a.hardware_id";
	$sql['SQL'] .= " LEFT JOIN bios b ON h.id=b.hardware_id";
	$sql['SQL'] .= " LEFT JOIN networks n ON h.id=n.hardware_id";
	$sql['SQL'] .= " WHERE h.id IN ";

	$sql=mysql2_prepare($sql['SQL'],$sql['ARG'],$list_id[$protectedPost['detail']]);

	$groupby = "";

	if (($protectedPost['detail'] == "macaddress" || $protectedPost['detail'] == "macaddress_serial" || $protectedPost['detail'] == "hostname_macaddress") && count($list_info)>0){
		$sql['SQL'] .= " AND n.macaddr IN ";
		$sql=mysql2_prepare($sql['SQL'],$sql['ARG'],$list_info[$protectedPost['detail']]);
		$groupby = ",n.macaddr";
	}

	$sql['SQL'] .= " GROUP BY h.id".$groupby;

	// BEGIN MODIF DUPLICATES

	$duplicates = mysql2_query_secure($sql['SQL'], $_SESSION['OCS']["readServer"], $sql['ARG']);
	$duplicates = mysqli_fetch_all($duplicates, MYSQLI_ASSOC);

	$criteria = match ($protectedPost['detail']) {
		"hostname_serial" => 'name',
		"hostname_macaddress", "macaddress_serial" => 'macaddr',
		"hostname" => 'name',
		"ssn" => 'ssn',
		"macaddress" => 'macaddr',
	};
	$grpDuplis = groupBy($criteria ?? '', $duplicates);
	$i = 0;
	// iterate through each group of duplicates to build collapsible
	foreach ($grpDuplis as $item) {
		echo "<div class='panel-group'><div class='panel-heading panel-heading-duplicate panel-ocs-duplicate'>";
		// check all boxes for this duplicated item
		?>
			<div class='col-md-2'><input type='checkbox' id='selected_grp_dupli' name='selected_grp_dupli[]' value="<?php echo htmlspecialchars(json_encode($item)); ?>" onClick="disabled_checkbox('selected_grp_dupli');"></div>
		<?php
		echo "<div class='col-md-8'><a data-toggle='collapse' class='duplicate-collapse' href='#collapse". $i ."'><b>".strtoupper($item[0][$criteria]). "</b></a></div>
		<div class='col-md-2'></div>
		</div>
		<div id='collapse". $i ."' class='panel-collapse collapse'>";

		// iterate through duplicate's infos
		foreach ($item as $itemagain) {
			echo "<div class='panel-body'>"; 
			?>
				<div class='col-md-2'><input type='checkbox' id='selected_dupli' name='selected_dupli[]' value="<?php echo htmlspecialchars(json_encode($itemagain)); ?>"></div>
			<?php
			echo "<div class='col-md-8'><b>".strtoupper($itemagain['name']). "</b></div><div class='col-md-2'></div><br><br>";
			echo "<div class='col-md-12 duplicate-details'>";
			foreach ($itemagain as $key => $info) {
				if(strpos($key, "fields_")) {
					$admininfoId = explode("_", $key);
					$admininfo = find_info_accountinfo($admininfoId[1]);

					if($admininfo[$admininfoId[1]]['type'] == "11" || $admininfo[$admininfoId[1]]['type'] == 2) {
						$adminvalue = find_value_field("ACCOUNT_VALUE_".$admininfo[$admininfoId[1]]['name'], $admininfo[$admininfoId[1]]['type']);
						$adminvalue = $adminvalue[$info];
					} elseif($admininfo[$admininfoId[1]]['type'] == "5") {
						$checkbox = explode("&&&", $info);
						$adminvalue = implode(",",$checkbox);
					} else {
						$adminvalue = $info;
					}

					echo "<div class='col-md-3 duplicate-info'><b>".strtoupper($admininfo[$admininfoId[1]]['name']) ." :</b> ". $adminvalue ." </div>";
				} else {
					echo "<div class='col-md-3 duplicate-info'><b>". strtoupper($key) ." :</b> ". $info ." </div>";
				}
			}
			echo "</div>";
			echo "</div>";
		}

		echo "</div></div>";
		$i++;
	}

	echo "<br><input type='submit' value='". $l->g(9500)."' name='FUSION' class='btn btn-success'><br><br>";
	# removed the merge all button until duplicates are listed correctly
	# echo "<input type='submit' value='". $l->g(9501)."' name='FUSION_ALL' class='btn btn-success'><br /><br />";
	echo "<input type=hidden name=old_detail id=old_detail value='".$protectedPost['detail']."'>";
}
echo close_form();
// END MODIF DUPLICATES
if (AJAX) {
	ob_end_clean();
}
