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
print_item_header($l->g(63));
$form_name = "affich_rstorages";
$table_name = $form_name;
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;
echo open_form($form_name, '', '', 'form-horizontal');
$list_fields = array($l->g(49) => 'NAME',
    $l->g(64) => 'MANUFACTURER',
    $l->g(65) => 'MODEL',
    $l->g(53) => 'DESCRIPTION',
    $l->g(66) => 'TYPE',
    $l->g(67) . " (MB)" => 'DISKSIZE',
    $l->g(36) => 'SERIALNUMBER',
    $l->g(1229) => 'FIRMWARE');
if (isset($show_all_column)) {
    $list_col_cant_del = $list_fields;
} else {
    $list_col_cant_del = array($l->g(49) => $l->g(49));
}

$default_fields = $list_fields;
$tab_options['FILTRE'] = array('NAME' => $l->g(49), 'MANUFACTURER' => $l->g(64), 'TYPE' => $l->g(66));
$queryDetails = "SELECT * FROM storages WHERE (hardware_id=$systemid)";
ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
echo close_form();
if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
    ob_start();
}
?>
