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

require_once('require/function_snmp.php');

$form_name = "show_all_snmp";
$table_name = $form_name;
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;

//delete snmp
if ($protectedPost['SUP_PROF'] != '') {
	deleteDid_snmp($protectedPost['SUP_PROF']);
	$tab_options['CACHE'] = 'RESET';
}

if (is_defined($protectedPost['del_check'])) {
	deleteDid_snmp($protectedPost['del_check']);
	$tab_options['CACHE'] = 'RESET';
}

echo open_form($form_name, '', '', 'form-horizontal');
$list_fields = array('TAG' => 'TAG',
	'NAME_SNMP' => 'NAME',
	$l->g(352) => 'UPTIME',
	$l->g(95) => 'MACADDR',
	$l->g(34) => 'IPADDR',
	$l->g(1227) => 'CONTACT',
	$l->g(295) => 'LOCATION',
	$l->g(33) => 'DOMAIN',
	$l->g(66) => 'TYPE',
	$l->g(1228) => 'SNMPDEVICEID'
);

$tab_options['FILTRE'] = array_flip($list_fields);
$tab_options['FILTRE']['NAME'] = $l->g(49);
asort($tab_options['FILTRE']);
$list_fields['SUP'] = 'ID';
$list_fields['CHECK'] = 'ID';

$list_col_cant_del = array('SUP' => 'SUP', 'CHECK' => 'CHECK');
$default_fields = array('TAG' => 'TAG', 'NAME_SNMP' => 'NAME_SNMP', $l->g(34) => $l->g(34), $l->g(95) => $l->g(95));
$sql = prepare_sql_tab($list_fields, $list_col_cant_del);
$tab_options['ARG_SQL'] = $sql['ARG'];
$queryDetails = $sql['SQL'] . ",ID from snmp s
						left join snmp_accountinfo s_a on s.id=s_a.snmp_id ";
$tab_options['LBL_POPUP']['SUP'] = 'NAME';
$tab_options['LBL']['SUP'] = $l->g(122);

$tab_options['LIEN_LBL']['NAME_SNMP'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_snmp_detail'] . '&head=1&id=';
$tab_options['LIEN_CHAMP']['NAME_SNMP'] = 'ID';
$tab_options['LBL']['NAME_SNMP'] = $l->g(49);
ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
$img['image/delete.png'] = $l->g(162);
del_selection($form_name);
echo close_form();
if (AJAX) {
	ob_end_clean();
	tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
}
?>