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

require_once('require/function_telediff.php');
require_once('require/function_search.php');
$form_name = "pack_affect";
$table_name = "LIST_PACK_SEARCH";
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;
echo open_form($form_name, '', '', 'form-horizontal');
$list_id = multi_lot($form_name, $l->g(601));

//activation options
if ($protectedPost['MODIF'] != '' && isset($protectedPost['DWL_OPT']) && $protectedPost['DWL_OPT'] == "YES") {
	$tab_hidden['SELECT'] = $protectedPost['MODIF'];
	$tab_hidden['onglet'] = $protectedPost['onglet'];
	$tab_hidden['rule_choise'] = $protectedPost['rule_choise'];
	$action = array('REBOOT' => $l->g(1311), 'SHUTDOWN' => $l->g(1310));
	$min = array('00' => '00', '15' => '15', '30' => '30', '45' => '45');
	$hour = array('00' => '00',
		'01' => '01',
		'02' => '02',
		'03' => '03',
		'04' => '04',
		'05' => '05',
		'06' => '06',
		'07' => '07',
		'08' => '08',
		'09' => '09',
		'10' => '10',
		'11' => '11',
		'12' => '12');
	$i = 0;
	while ($i <= 1) {
		if ($i == 0) {
			$am_pm = '';
		} else {
			$am_pm = 'pm';
		}
		foreach ($hour as $k => $v) {
			foreach ($min as $km) {
				if ($am_pm == '' || ($am_pm == 'pm' && $k != '00' && $k != '12')) {
					$hour_min[$k . ":" . $km . $am_pm] = $am_pm . " " . $k . ":" . $km;
				}
			}
		}
		$i++;
	}
	$config['COMMENT_AFTER'][0] = datePick("INSTALL_DATE");
	$config['JAVASCRIPT'][0] = "READONLY " . dateOnClick("INSTALL_DATE");
	$config['SELECT_DEFAULT'][0] = '';
	$config['SIZE'][0] = '8';
	$tab_name = array($l->g(1295), $l->g(1294), $l->g(443));
	$name_field = array("INSTALL_DATE", "INSTALL_HEURE", "DOWNLOAD_POSTCMD");
	$type_field = array(0, 2, 2);
	$value_field = array($protectedPost['INSTALL_DATE'], $hour_min, $action);
	if ($protectedGet['origine'] != 'group') {
		array_push($tab_name, $l->g(1293));
		array_push($name_field, "TELE_FORCE");
		array_push($type_field, 5);
		array_push($value_field, array(''));
	}
	$tab_typ_champ = show_field($name_field, $type_field, $value_field, $config);
	$tab_typ_champ[2]['CONFIG']['DEFAULT'] = 'YES';
	modif_values($tab_name, $tab_typ_champ, $tab_hidden, array(
		'title' => $l->g(1309)
	));
} else {
	if (isset($protectedPost['MODIF'])) {
		$protectedPost['SELECT'] = $protectedPost['MODIF'];
		$protectedPost['Valid_modif'] = true;
	}
	if ($protectedPost['SELECT'] != '' && isset($protectedPost['Valid_modif'])) {
		if (isset($protectedPost['TELE_FORCE_0'])) {
			active_option('DOWNLOAD_FORCE', $list_id, $protectedPost['SELECT'], '1');
		}
		if (is_defined($protectedPost['INSTALL_DATE'])) {
			$date = explode('/', $protectedPost['INSTALL_DATE']);
			// Agent date format : 2016/06/30 02:15pm
			if ($l->g(269) == "%m/%d/%Y") {
				$install_date = $date[2] . "/" . $date[0] . "/" . $date[1] . " " . $protectedPost['INSTALL_HEURE'];
			} else {
				if ($l->g(269) == "%Y/%m/%d") {
					$install_date = $date[0] . "/" . $date[1] . "/" . $date[2] . " " . $protectedPost['INSTALL_HEURE'];
				} else {
					// default : %d/%m/%Y
					$install_date = $date[2] . "/" . $date[1] . "/" . $date[0] . " " . $protectedPost['INSTALL_HEURE'];
				}
			}
			active_option('DOWNLOAD_SCHEDULE', $list_id, $protectedPost['SELECT'], $install_date);
		}

		if (is_defined($protectedPost['DOWNLOAD_POSTCMD'])) {
			active_option('DOWNLOAD_POSTCMD', $list_id, $protectedPost['SELECT'], $protectedPost['DOWNLOAD_POSTCMD']);
		}

		if ($protectedGet['origine'] == "group") {
			$form_to_reload = 'config_group';
		} elseif ($protectedGet['origine'] == "mach") {
			$form_to_reload = 'config_mach';
		}
		if ($protectedPost['onglet'] == 'MACH') {
			$nb_affect = active_option('DOWNLOAD', $list_id, $protectedPost['SELECT']);
		}
		if ($protectedPost['onglet'] == 'SERV_GROUP') {
			$nb_affect = active_serv($list_id, $protectedPost['SELECT'], $protectedPost['rule_choise']);
		}
		msg_success($nb_affect . " " . $l->g(604));
		if (isset($form_to_reload)) {
			//add this $var => not delete this package on computer detail
			$_SESSION['OCS']["justAdded"] = true;
			echo "<script language='javascript'> window.opener.document." . $form_to_reload . ".submit();</script>";
		}
	}

	if ($protectedPost['sens_' . $table_name] == "") {
		$protectedPost['sens_' . $table_name] = 'DESC';
	}

	if ($protectedPost['onglet'] == "") {
		$protectedPost['onglet'] = 'MACH';
	}


	$def_onglets['MACH'] = $l->g(980);
	$def_onglets['SERV_GROUP'] = $l->g(981);

	//show tab
	if ($list_id) {
		show_tabs($def_onglets, $form_name, 'onglet', true);
		echo '<div class="col col-md-10" >';

		if ($protectedPost['onglet'] == 'SERV_GROUP') {
			$sql_rules = "select distinct rule,rule_name from download_affect_rules order by 1";
			$res_rules = mysqli_query($_SESSION['OCS']["readServer"], $sql_rules) or die(mysqli_error($_SESSION['OCS']["readServer"]));
			$nb_rule = 0;
			while ($val_rules = mysqli_fetch_array($res_rules)) {
				$first = $val_rules['rule'];
				$list_rules[$val_rules['rule']] = $val_rules['rule_name'];
				$nb_rule++;
			}
			if ($nb_rule > 1) {
				$select_choise = $l->g(668) . show_modif($list_rules, 'rule_choise', 2, $form_name);
				echo $select_choise;
			} elseif ($nb_rule == 1) {
				$protectedPost['rule_choise'] = $first;
				echo "<input type=hidden value='" . $first . "' name='rule_choise' id='rule_choise'>";
			} elseif ($nb_rule == 0) {
				msg_error($l->g(982));
			}
		}

		if ($protectedPost['onglet'] == 'MACH') {
			$selectValues = array('' => '', 'NO' => $l->g(454), 'YES' => $l->g(455));
			formGroup('select', 'DWL_OPT', $l->g(1292), '', '', $protectedPost['DWL_OPT'], '', $selectValues, $selectValues, 'onclick=document.' . $form_name . '.submit();');
		}

		if (($protectedPost['onglet'] == 'MACH' && $protectedPost['DWL_OPT'] != '') || ($protectedPost['onglet'] == 'SERV_GROUP' && $protectedPost['rule_choise'] != '')) {
			//recherche de toutes les règles pour les serveurs de redistribution
			$list_fields = array('FILE_ID' => 'e.FILEID',
				'INFO_LOC' => 'e.INFO_LOC',
				'CERT_FILE' => 'e.CERT_FILE',
				'CERT_PATH' => 'e.CERT_PATH',
				$l->g(1037) => 'a.NAME',
				$l->g(1039) => 'a.PRIORITY',
				$l->g(51) => 'a.COMMENT',
				$l->g(274) => 'a.OSNAME',
				$l->g(953) . " (KB)" => 'a.SIZE'
			);

			if (!isset($nb_rule) || $nb_rule > 0) {
				if ($protectedPost['onglet'] != 'SERV_GROUP') {
					$list_fields['PACK_LOC'] = 'e.PACK_LOC';
					$list_fields['ACTIVE_ID'] = 'e.ID';
					$list_fields['MODIF'] = 'e.ID';
				} else {
					$list_fields['ACTIVE_ID'] = 'e.FILEID';
					$list_fields['MODIF'] = 'e.FILEID';
				}
			}
			$default_fields = array($l->g(1037) => $l->g(1037), $l->g(1039) => $l->g(1039), $l->g(274) => $l->g(274), $l->g(953) . " (KB)" => $l->g(953) . " (KB)", 'SELECT' => 'SELECT');
			$list_col_cant_del = array($l->g(1037) => $l->g(1037), 'MODIF' => 'MODIF');

			if ($protectedPost['onglet'] != 'SERV_GROUP') {
				$default_fields['PACK_LOC'] = 'PACK_LOC';
				$list_col_cant_del['PACK_LOC'] = 'PACK_LOC';
			}

			if ($protectedPost['onglet'] == 'SERV_GROUP') {
				$distinct = true;
			} else {
				$distinct = false;
			}

			$sql = prepare_sql_tab($list_fields, array('SELECT'), $distinct);

			$sql['SQL'] .= " from download_available a, download_enable e ";
			if ($protectedPost['onglet'] == 'MACH') {
				$sql['SQL'] .= "where a.FILEID=e.FILEID and e.SERVER_ID is null ";
			} else {
				$sql['SQL'] .= ", hardware h where a.FILEID=e.FILEID and h.id=e.group_id and  e.SERVER_ID is not null ";
			}

			if (is_defined($fileid_show)) {
				$sql = mysql2_prepare($sql['SQL'], $sql['ARG'], $fileid_show, true);
			}
			if ($_SESSION['OCS']['profile']->getRestriction('TELEDIFF_VISIBLE', 'YES') == "YES") {
				$sql['SQL'] .= " and a.comment not like '%s'";
				array_push($sql['ARG'], '%[VISIBLE=0]%');
			}

			error_reporting(0);

			$tab_options['QUESTION']['SELECT'] = $l->g(699);
			$tab_options['FILTRE'] = array('e.FILEID' => 'Timestamp', 'a.NAME' => $l->g(49));
			$tab_options['ARG_SQL'] = $sql['ARG'];
			$tab_options['MODIF']['IMG'] = "image/prec16.png";

			$result_exist = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
		}
	}
}
echo "</div>";
echo close_form();

if (AJAX) {
	ob_end_clean();
	tab_req($list_fields, $default_fields, $list_col_cant_del, $sql['SQL'], $tab_options);
}
?>