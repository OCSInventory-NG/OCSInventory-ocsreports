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
 * Show sd_powersupplies data
 */
if (AJAX) {
    ob_end_clean();
    parse_str($protectedPost['ocs']['0'], $params);
    $protectedPost += $params;
    ob_start();
}
print_item_header($l->g(1223));
if (!isset($protectedPost['SHOW'])) {
    $protectedPost['SHOW'] = 'NOSHOW';
}
$table_name = "sd_powersupplies";
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;
$list_fields = array($l->g(64) => 'MANUFACTURER',
    'REFERENCE' => 'REFERENCE',
    $l->g(66) => 'TYPE',
    $l->g(36) => 'SERIALNUMBER',
    $l->g(53) => 'DESCRIPTION',
    $l->g(277) => 'REVISION');
$sql = prepare_sql_tab($list_fields);
$list_col_cant_del = $list_fields;
$default_fields = $list_fields;
$sql['SQL'] = $sql['SQL'] . " FROM %s WHERE (snmp_id=%s)";
$sql['ARG'][] = 'snmp_powersupplies';
$sql['ARG'][] = $systemid;
$tab_options['ARG_SQL'] = $sql['ARG'];
ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $sql['SQL'], $tab_options);
    ob_start();
}
?>
