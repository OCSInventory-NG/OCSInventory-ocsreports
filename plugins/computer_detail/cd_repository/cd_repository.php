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
print_item_header($l->g(1421));
if (!isset($protectedPost['SHOW'])) {
    $protectedPost['SHOW'] = 'NOSHOW';
}
$form_name = "affich_repo";
$table_name = $form_name;
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;
echo open_form($form_name, '', '', 'form-horizontal');
$list_fields = array($l->g(49)  => 'NAME',
    $l->g(1427) => 'BASEURL',
    $l->g(600)  => 'EXCLUDE',
    $l->g(614)  => 'EXCLUDED',
    $l->g(1422) => 'EXPIRE',
    $l->g(1423) => 'FILENAME',
    $l->g(1424) => 'MIRRORS',
    $l->g(498)  => 'PKGS',
    $l->g(18)   => 'REVISION',
    $l->g(953)  => 'SIZE',
    $l->g(1425) => 'TAG',
    $l->g(1426) => 'UPDATED',
);

$default_fields = $list_fields;
$list_col_cant_del = $list_fields;
$sql = prepare_sql_tab($list_fields);
$sql['SQL'] .= "FROM repository WHERE (hardware_id = %s)";
array_push($sql['ARG'], $systemid);
$tab_options['ARG_SQL'] = $sql['ARG'];
ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
echo close_form();
if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $sql['SQL'], $tab_options);
    ob_start();
}