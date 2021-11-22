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
print_item_header($l->g(82));
if (!isset($protectedPost['SHOW'])) {
    $protectedPost['SHOW'] = 'NOSHOW';
}
$tab_options = $protectedPost;
if ($protectedPost['OTHER_BIS'] != '') {
    //verify @mac
    if (preg_match('/([0-9A-F]{2}:){5}[0-9A-F]{2}$/i', $protectedPost['OTHER_BIS'])) {
        $sql = "INSERT INTO blacklist_macaddresses (macaddress) value ('%s')";
        $arg = $protectedPost['OTHER_BIS'];
        mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
        $tab_options['CACHE'] = 'RESET';
    }
}
if ($protectedPost['OTHER'] != '') {
    //verify @mac
    if (preg_match('/([0-9A-F]{2}:){5}[0-9A-F]{2}$/i', $protectedPost['OTHER'])) {
        $sql = "DELETE FROM blacklist_macaddresses WHERE macaddress='%s'";
        $arg = $protectedPost['OTHER'];
        mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
        $tab_options['CACHE'] = 'RESET';
    }
}
$form_name = "affich_networks";
$table_name = $form_name;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;
echo open_form($form_name, '', '', 'form-horizontal');
$list_fields = array($l->g(53) => 'DESCRIPTION',
    $l->g(66) => 'TYPE',
    $l->g(268) => 'SPEED',
    $l->g(7016) => 'MTU',
    $l->g(95) => 'MACADDR',
    $l->g(81) => 'STATUS',
    $l->g(34) => 'IPADDRESS',
    $l->g(208) => 'IPMASK',
    $l->g(207) => 'IPGATEWAY',
    $l->g(331) => 'IPSUBNET',
    $l->g(281) => 'IPDHCP');
if ($_SESSION['OCS']['ADMIN_BLACKLIST']['MACADD'] == "YES") {
    $sql = "select MACADDR from networks WHERE (hardware_id=%s)";
    $arg = $systemid;
    $resultDetails = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
    while ($item = mysqli_fetch_object($resultDetails)) {
        $sql = "select ID from blacklist_macaddresses where macaddress='%s'";
        $arg = $item->MACADDR;
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
        if (mysqli_num_rows($result) == 1) {
            $tab_options['OTHER'][$l->g(95)][$item->MACADDR] = $item->MACADDR;
            $tab_options['OTHER']['IMG'] = 'image/red.png';
        } else {
            $tab_options['OTHER_BIS'][$l->g(95)][$item->MACADDR] = $item->MACADDR;
            $tab_options['OTHER_BIS']['IMG'] = 'image/green.png';
        }
    }
}
if ($show_all_column) {
    $list_col_cant_del = $list_fields;
} else {
    $list_col_cant_del[$l->g(34)] = $l->g(34);
}
$default_fields = $list_fields;
$queryDetails = "SELECT ";
foreach ($list_fields as $value) {
    $queryDetails .= $value . ",";
}
$queryDetails = substr($queryDetails, 0, -1) . " FROM networks WHERE (hardware_id=$systemid)";
ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
echo close_form();
if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
    ob_start();
}
