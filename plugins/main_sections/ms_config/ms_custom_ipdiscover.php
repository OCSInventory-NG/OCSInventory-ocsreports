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
debut_tab(array('CELLSPACING' => '5',
	'WIDTH' => '80%',
	'BORDER' => '0',
	'ALIGN' => 'Center',
	'CELLPADDING' => '0',
	'BGCOLOR' => '#C7D9F5',
	'BORDERCOLOR' => '#9894B5'));
$mode = 0;

if ($optvalueTvalue['IPDISCOVER'] && $optvalue['IPDISCOVER'] == 1) {
	$select_value = $optvalueTvalue['IPDISCOVER'];
	echo "<br><center><b>" . $l->g(519) . ": " . $optvalueTvalue['IPDISCOVER'] . "</b></center>";
	$mode = 1;
} else if ($optvalue['IPDISCOVER'] == 2) {
	$select_value = $optvalueTvalue['IPDISCOVER'];
	echo "<br><center><b>" . $l->g(520) . ": " . $optvalueTvalue['IPDISCOVER'] . "</b></center>";
	$mode = 3;
} else if ($optvalue['IPDISCOVER'] === "0") {
	$select_value = "OFF";
	echo "<br><center><b>" . $l->g(521) . "</b></center>";
	$mode = 2;
} elseif (isset($protectedGet['idchecked'])) {
	echo "<br><center><b>" . $l->g(522) . "</b></center>";
} elseif (!isset($protectedGet['idchecked'])) {
	$mode = 2;
}
$lesRez['des'] = $l->g(523);
$lesRez['OFF'] = $l->g(524);
if (isset($protectedGet['idchecked']) && is_numeric($protectedGet['idchecked'])) {
	$sql = "SELECT ipaddress FROM networks WHERE hardware_id=%s";
	$arg = $protectedGet['idchecked'];
	$resInt = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
	while ($valInt = mysqli_fetch_array($resInt)) {
		$sql = "SELECT ipsubnet FROM networks WHERE ipaddress='%s' AND hardware_id=%s";
		$arg = array($valInt["ipaddress"], $protectedGet["idchecked"]);
		$res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
		while ($val = mysqli_fetch_array($res)) {
			$lesRez[$val["ipsubnet"]] = $val["ipsubnet"];
		}
	}
}

ligne('IPDISCOVER', $l->g(518), 'select', array('SELECT_VALUE' => $lesRez, 'VALUE' => $select_value));

if (!isset($optvalue['SNMP_SWITCH'])) {
	$optvalueselected = 'SERVER DEFAULT';
} elseif ($optvalue['SNMP_SWITCH'] == 0) {
	$optvalueselected = 'OFF';
} elseif ($optvalue['SNMP_SWITCH'] == 1) {
	$optvalueselected = 'ON';
}
$champ_value['VALUE'] = $optvalueselected;
$champ_value['ON'] = 'ON';
$champ_value['OFF'] = 'OFF';
$champ_value['SERVER DEFAULT'] = $l->g(488);
if (!isset($protectedGet['origine'])) {
	$champ_value['IGNORED'] = $l->g(718);
	$champ_value['VALUE'] = 'IGNORED';
}
ligne("SNMP_SWITCH", $l->g(1197), 'radio', $champ_value);
ligne('SNMP_NETWORK', $l->g(1198), 'long_text', array('VALUE' => $optvalueTvalue['SNMP_NETWORK'], 'COLS' => 40, 'ROWS' => 1));
unset($champ_value);

fin_tab();
?>