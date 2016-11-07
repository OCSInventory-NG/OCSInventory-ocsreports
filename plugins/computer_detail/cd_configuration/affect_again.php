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
/*
 * NOT USE YET
 */

$form_name = 'admin_search';
if ($protectedPost['onglet'] != $protectedPost['old_onglet']) {
	$onglet = $protectedPost['onglet'];
	$old_onglet = $protectedPost['old_onglet'];
	unset($protectedPost);
	$protectedPost['old_onglet'] = $old_onglet;
	$protectedPost['onglet'] = $onglet;
}
if ($protectedGet['origine'] != "mach") {
	if (is_defined($protectedGet['idchecked'])) {
		$choise_req_selection['REQ'] = $l->g(584);
		$choise_req_selection['SEL'] = $l->g(585);
		$select_choise = show_modif($choise_req_selection, 'CHOISE', 2, $form_name);
	}
	echo "<font color=red><b>";
	if ($protectedPost['CHOISE'] == 'REQ' || $protectedGet['idchecked'] == '' || $protectedPost['CHOISE'] == '') {
		echo $l->g(901);
		$list_id = $_SESSION['OCS']['ID_REQ'];
	}
	if ($protectedPost['CHOISE'] == 'SEL') {
		echo $l->g(902);
		$list_id = $protectedGet['idchecked'];
	}

	//gestion tableau
	if (is_array($list_id)) {
		$list_id = implode(",", $list_id);
	}
} else {
	$list_id = $protectedGet['idchecked'];
}

echo "</b></font>";
if (strpos($protectedGet['img'], "config_search.png")) {
	include ("opt_param.php");
}
if (strpos($protectedGet['img'], "groups_search.png")) {
	include ("opt_groups.php");
}
if (strpos($protectedGet['img'], "tele_search.png")) {
	include ("opt_pack.php");
}
if (strpos($protectedGet['img'], "delete.png")) {
	include ("opt_sup.php");
}
?>