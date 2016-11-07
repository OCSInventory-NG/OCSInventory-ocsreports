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
$compartruevalue = array('<', '>', '=', '');
if (!in_array($protectedGet['comp'], $compartruevalue)) {
	die();
}

$values = look_config_default_values(array('EXPORT_SEP'));
if (is_defined($values['tvalue']['EXPORT_SEP'])) {
	$separator = $values['tvalue']['EXPORT_SEP'];
} else {
	$separator = ';';
}

if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES'])
		and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1) {
	$info_name_soft = array("table" => "type_softwares_name", "field" => "name", "search" => "id", "field_name_soft" => 'name_id');
	$alias_name_soft = "cachename";
} else {
	$info_name_soft = array("table" => "softwares", "field" => "name", "search" => "name", "field_name_soft" => 'name');
	$alias_name_soft = "sname";
}
$field_name_soft = $info_name_soft['table'] . "." . $info_name_soft['field'];

if ($info_name_soft['table'] != 'softwares' || $_SESSION['OCS']["usecache"] == 1) {
	$sql_list_soft['ARG'] = array();
	$sql_list_soft['SQL'] = "select " . $info_name_soft['search'] . " from " . $info_name_soft['table'] . " ";
	if (is_defined($protectedGet['soft'])) {
		$sql_list_soft['SQL'] .= " where name like '%s'";
		$sql_list_soft['ARG'] = array('%' . $protectedGet['soft'] . '%');
	}
	$sql['SQL'] = "select count(*) nb, " . $field_name_soft . " from softwares ";
	if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES']) && $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1) {
		$sql['SQL'] .= " left join " . $info_name_soft['table'] . " on " . $info_name_soft['table'] . "." . $info_name_soft['search'] . "=softwares." . $info_name_soft["field_name_soft"] . " ";
	}
	$sql['SQL'] .= " ,accountinfo a where a.hardware_id=softwares.hardware_id and softwares." . $info_name_soft["field_name_soft"] . " in (";
	$sql['SQL'] .= $sql_list_soft['SQL'] . " )";
	$sql['ARG'] = $sql_list_soft['ARG'];

	if ($_SESSION['OCS']['TAGS'] != "") {
		$sql['SQL'] .= " and a.tag in ";
		$sql = mysql2_prepare($sql['SQL'], $sql['ARG'], $_SESSION['OCS']['TAGS']);
	}
	$sql['SQL'] .= " group by " . $field_name_soft;
	if (is_defined($protectedGet['nb']) && is_defined($protectedGet['comp'])) {
		$sql['SQL'] .= " having nb %s %s";
		array_push($sql['ARG'], $protectedGet['comp']);
		array_push($sql['ARG'], $protectedGet['nb']);
	}
} else {
	$sql['SQL'] = "select count(*) nb, " . $field_name_soft . " from " . $info_name_soft['table'];
	$sql['SQL'] .= " , accountinfo a where a.hardware_id=" . $info_name_soft['table'] . ".hardware_id ";
	if (is_defined($protectedGet['soft'])) {
		$sql['SQL'] .= " and " . $field_name_soft . " like '%s'";
		$sql['ARG'] = array('%' . $protectedGet['soft'] . '%');
	}
	if ($_SESSION['OCS']['TAGS'] != "") {
		$sql['SQL'] .= " and a.tag in ";
		$sql = mysql2_prepare($sql['SQL'], $sql['ARG'], $_SESSION['OCS']['TAGS']);
	}
	$sql['SQL'] .= " group by " . $field_name_soft;

	if (is_defined($protectedGet['nb']) && is_defined($protectedGet['comp'])) {
		$sql['SQL'] .= " having nb %s %s";
		array_push($sql['ARG'], $protectedGet['comp']);
		array_push($sql['ARG'], $protectedGet['nb']);
	}
}

if (isset($protectedGet['all_computers']) && isset($protectedGet['nb']) && is_numeric($protectedGet['nb']) && $protectedGet['nb'] < 16 && isset($protectedGet['comp']) && $protectedGet['comp'] == "<") {

	$sql_liste_soft = "select count(" . $info_name_soft["field_name_soft"] . ") nb," . $info_name_soft["field_name_soft"] . "
						from softwares group by " . $info_name_soft["field_name_soft"] . " having nb<%s";
	$arg_liste_soft = $protectedGet['nb'];
	$result_liste_soft = mysql2_query_secure($sql_liste_soft, $_SESSION['OCS']["readServer"], $arg_liste_soft);
	$list_soft = "";
	while ($item_liste_soft = mysqli_fetch_object($result_liste_soft)) {
		$list_soft[] = $item_liste_soft->$info_name_soft["field_name_soft"];
	}
	$fields = array("a.tag" => $_SESSION['OCS']['TAG_LBL']['TAG'],
		$alias_name_soft => $l->g(20),
		"h.name" => $l->g(23),
		"h.userid" => $l->g(24),
		"h.description" => $l->g(53),
		"h.lastdate" => $l->g(728));

	$sql = prepare_sql_tab(array_keys($fields));
	$sql['SQL'] .= " from accountinfo a, (select hardware_id, " . $info_name_soft["field_name_soft"] . " as sname from softwares where " . $info_name_soft["field_name_soft"] . " in ";
	$sql = mysql2_prepare($sql['SQL'], $sql['ARG'], $list_soft);
	$sql['SQL'] .= ") s";
	if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES'])
			and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1) {
		$sql['SQL'] .= " left join (select id, name as " . $alias_name_soft . " from " . $info_name_soft['table'] . ") cache on cache.id=s.sname ";
	}
	$sql['SQL'] .= ",hardware h
					where a.hardware_id=h.id and s.hardware_id=h.id ";
	if (isset($_SESSION['OCS']['TAGS'])) {
		$sql['SQL'] .= " and a.tag in ";
		$sql = mysql2_prepare($sql['SQL'], $sql['ARG'], $_SESSION['OCS']['TAGS']);
	}
	$sql['SQL'] .= " order by h.name";
} else {
	$fields = array('nb' => $l->g(55), 'name' => $l->g(20));
}

$toBeWritten = implode($separator, $fields) . "\r\n";
if (ini_get("zlib.output-compression")) {
	ini_set("zlib.output-compression", "Off");
}
header("Pragma: public");
header("Expires: 0");
header("Cache-control: must-revalidate, post-check=0, pre-check=0");
header("Cache-control: private", false);
header("Content-type: application/force-download");
header("Content-Disposition: attachment; filename=\"export.csv\"");
header("Content-Transfer-Encoding: binary");
$result_search_soft = mysql2_query_secure($sql['SQL'], $_SESSION['OCS']["readServer"], $sql['ARG']);

while ($item_search_soft = mysqli_fetch_object($result_search_soft)) {
	foreach ($fields as $key => $values) {
		$trait = explode('.', $key);
		if (isset($trait[1])) {
			$fi = $trait[1];
		} else {
			$fi = $trait[0];
		}

		$toBeWritten .= $item_search_soft->$fi . $separator;
	}
	$toBeWritten .= "\r\n";
}
header("Content-Length: " . strlen($toBeWritten));
echo $toBeWritten;
die();
?>