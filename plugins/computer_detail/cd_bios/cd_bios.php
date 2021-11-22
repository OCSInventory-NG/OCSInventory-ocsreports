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
$tab_options = $protectedPost;
$tab_options['form_name'] = "affich_bios";
$tab_options['table_name'] = "affich_bios";
print_item_header($l->g(273));
if (!isset($protectedPost['SHOW'])) {
    $protectedPost['SHOW'] = 'NOSHOW';
}
if ($protectedPost['OTHER_BIS'] != '') {
    $sql = "INSERT INTO blacklist_serials (SERIAL) value ('%s')";
    $arg = array($protectedPost['OTHER_BIS']);
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
}
if ($protectedPost['OTHER'] != '') {
    $sql = "DELETE FROM blacklist_serials WHERE SERIAL='%s'";
    $arg = array($protectedPost['OTHER']);
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
}
echo open_form($form_name, '', '', 'form-horizontal');
$list_fields = array($l->g(36) => 'SSN',
    $l->g(64) => 'SMANUFACTURER',
    $l->g(65) => 'SMODEL',
    $l->g(66) => 'TYPE',
    $l->g(284) => 'BMANUFACTURER',
    $l->g(209) => 'BVERSION',
    $l->g(210) => 'BDATE',
    $l->g(216) => 'ASSETTAG',
    $l->g(1382) => 'MSN',
    $l->g(1383) => 'MMANUFACTURER',
    $l->g(1384) => 'MMODEL',
);
$sql = "select SSN from bios WHERE (hardware_id=%s)";
$arg = array($systemid);
$resultDetails = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
$item = mysqli_fetch_object($resultDetails);
$sql = "select ID from blacklist_serials where SERIAL='%s'";
$arg = array($item->SSN);
$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
if ($_SESSION['OCS']['ADMIN_BLACKLIST']['SERIAL'] == 'YES') {
    if (mysqli_num_rows($result) == 1) {
        $tab_options['OTHER'][$l->g(36)][$item->SSN] = $item->SSN;
        $tab_options['OTHER']['IMG'] = 'image/red.png';
    } else {
        $tab_options['OTHER_BIS'][$l->g(36)][$item->SSN] = $item->SSN;
        $tab_options['OTHER_BIS']['IMG'] = 'image/green.png';
    }
}
if ($show_all_column) {
    $list_col_cant_del = $list_fields;
} else {
    $list_col_cant_del[$l->g(36)] = $l->g(36);
}
$default_fields = $list_fields;
$queryDetails = "SELECT ";
foreach ($list_fields as $value) {
    $queryDetails .= $value . ",";
}
$queryDetails = substr($queryDetails, 0, -1) . " FROM bios WHERE (hardware_id=$systemid)";
ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
echo close_form();
if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
    ob_start();
}
