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
    $ajax = true;
} else {
    $ajax = false;
}
print_item_header($l->g(1266));
if (!isset($protectedPost['SHOW'])) {
    $protectedPost['SHOW'] = 'NOSHOW';
}
$form_name = "affich_vm";
$table_name = $form_name;
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;
echo open_form($form_name, '', '', 'form-horizontal');
$list_fields = array($l->g(49) . ' VM' => 'vm.NAME',
    $l->g(1046) . ' VM' => 'vm.STATUS',
    $l->g(25) . ' VM' => 'vm.SUBSYSTEM',
    $l->g(66) . ' VM' => 'vm.VMTYPE',
    'UUID' => 'vm.UUID',
    $l->g(54) . ' VM' => 'vm.VCPU',
    $l->g(26) . ' VM' => 'vm.MEMORY',
    'NAME' => 'h.name',
    $l->g(25) => "h.osname",
);
$list_col_cant_del = $list_fields;
$default_fields = $list_fields;
$sql = prepare_sql_tab($list_fields);
$sql['SQL'] .= ",h.ID FROM virtualmachines vm left join hardware h on h.uuid=vm.uuid  WHERE (hardware_id=%s)";
array_push($sql['ARG'], $systemid);
$tab_options['ARG_SQL'] = $sql['ARG'];
$tab_options['ARG_SQL_COUNT'] = $systemid;
ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
echo close_form();
if ($ajax) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $sql['SQL'], $tab_options);
    ob_start();
}
?>