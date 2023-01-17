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
require_once('require/admininfo/Admininfo.php');
require_once('require/snmp/Snmp.php');

$Admininfo = new Admininfo();
$snmp = new OCSSnmp();

$form_name = "lock_affect_snmp";

echo open_form($form_name, '', '', 'form-horizontal');

echo "<div class='row'>";
echo "<div class='col-md-12'>";

$list_id = $Admininfo->multi_lot_snmp($form_name, $l->g(601));

if(!empty($list_id)) {
	echo "<div class='col col-md-12'>";

	//cas of TAG INFO
	if (is_defined($protectedPost['Valid_modif'])) {
		$info_account_id = [];

		$reconciliation = $snmp->getReconciliationColumn($list_id['snmp_type']);
		$infos = $snmp->get_infos($list_id['snmp_type'], array($reconciliation), $list_id['id']);

		foreach($infos as $update) {
			// Create first NA TAG admindata if not exist
			$checkinfo_snmp = $Admininfo->checkinfo_snmp($update[$reconciliation], $list_id['snmp_type']);
			if($checkinfo_snmp == 0) $Admininfo->createinfo_snmp($update[$reconciliation], $list_id['snmp_type']);
		}

		$info_account_id_tmp = $Admininfo->admininfo_snmp();

		foreach($info_account_id_tmp as $values) {
			foreach($values as $value) {
				foreach($value as $key => $data) {
					$info_account_id[$key] = $data;
				}
			}
		}
		foreach ($protectedPost as $field => $value) {
			if (substr($field, 0, 5) == "check") {
				$temp = substr($field, 5);
				if (array_key_exists($temp, $info_account_id)) {
					//cas of checkboxtag_search
					foreach ($protectedPost as $field2 => $value2) {
						$casofcheck = explode('_', $field2);
						if (isset($casofcheck[1]) && $casofcheck[0] . '_' . $casofcheck[1] == $temp) {
							if (isset($casofcheck[2])) {
								$data_fields_account[$temp] .= $casofcheck[2] . "&&&";
							}
						}
					}
					if (!isset($data_fields_account[$temp])) {
						$data_fields_account[$temp] = $protectedPost[$temp];
					}
				}
			}
		}

		if (isset($data_fields_account)) {
			$Admininfo->updateinfo_snmp($list_id, null, $data_fields_account);
		}
	}

	$field_of_accountinfo = $Admininfo->witch_field_more('SNMP');
	$tab_typ_champ = array();
	$i = 0;
	$dont_show_type = array(8, 3);
	echo "</div>";
	echo "<div class='col-md-10 col-md-offset-1'>";
	foreach ($field_of_accountinfo['LIST_FIELDS'] as $id => $lbl) {
		if (!in_array($field_of_accountinfo['LIST_TYPE'][$id], $dont_show_type)) {
			if ($field_of_accountinfo['LIST_NAME'][$id] == "TAG") {
				$truename = "TAG";
			} else {
				$truename = "fields_" . $id;
			}
			if ($field_of_accountinfo['LIST_TYPE'][$id] == 14) {
				$tab_typ_champ[$i]['CONFIG']['MAXLENGTH'] = 10;
				$tab_typ_champ[$i]['CONFIG']['SIZE'] = 10;
				$tab_typ_champ[$i]['COMMENT_AFTER'] = calendars($truename, $l->g(1270)) . "</td></span><span class='input-group-addon' id='" . $truename . "-addon'><td><input type='checkbox' name='check" . $truename . "' id='check" . $truename . "' " . (isset($protectedPost['check' . $truename]) ? " checked " : "") . ">";
			} elseif (in_array($field_of_accountinfo['LIST_TYPE'][$id], array(2, 5, 11))) {
				$sql = "select ivalue as ID,tvalue as NAME from config where name like 'ACCOUNT_SNMP_VALUE_%s' order by 2";
				$arg = $field_of_accountinfo['LIST_NAME'][$id] . "%";
				$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
				while ($val = mysqli_fetch_array($result)) {
					$tab_typ_champ[$i]['DEFAULT_VALUE'][$val['ID']] = $val['NAME'];
				}
				$tab_typ_champ[$i]['COMMENT_AFTER'] = "</td><td><input type='checkbox' name='check" . $truename . "' id='check" . $truename . "' " . (isset($protectedPost['check' . $truename]) ? " checked " : "") . ">";
			} else {
				$tab_typ_champ[$i]['COMMENT_AFTER'] = "</td><td><input type='checkbox' name='check" . $truename . "' id='check" . $truename . "' " . (isset($protectedPost['check' . $truename]) ? " checked " : "") . ">";
				$tab_typ_champ[$i]['CONFIG']['MAXLENGTH'] = 100;
				$tab_typ_champ[$i]['CONFIG']['SIZE'] = 30;
			}
			$tab_typ_champ[$i]['INPUT_NAME'] = $truename;
			$tab_typ_champ[$i]['INPUT_TYPE'] = $field_of_accountinfo['LIST_TYPE'][$id];
			$tab_typ_champ[$i]['CONFIG']['JAVASCRIPT'] = ($java ?? '') . " onclick='document.getElementById(\"check" . $truename . "\").checked = true' ";

			$tab_name[$i] = $lbl;
			$i++;
		}
	}
	modif_values($tab_name, $tab_typ_champ, array('TAG_MODIF' => $protectedPost['MODIF'] ?? '', 'FIELD_FORMAT' => $type_field[$protectedPost['MODIF'] ?? ''] ?? ''), array(
		'title' => $l->g(895)
	));

	echo "</div>";
}

echo "</div>";
echo "</div>";

echo close_form();