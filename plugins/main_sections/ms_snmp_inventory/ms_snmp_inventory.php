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

require_once('require/snmp/Snmp.php');
require_once('require/function_machine.php');

$snmp = new OCSSnmp();

$typeList = $snmp->get_all_type();

//definition of onglet
foreach($typeList as $id => $values) {
    $def_onglets[$id] = $values['TYPENAME'];
    if ($protectedPost['onglet'] == "") {
        $protectedPost['onglet'] = $id;
    }
}

$count = count($def_onglets);
$form_name = "snmp_inventory";

$table_name = $form_name;

echo open_form($form_name, '', '', 'form-horizontal');

//show first lign of onglet
if($count < 15){
  show_tabs($def_onglets,$form_name,"onglet",true, $i);
}

if ($count >= 15) {
    echo "<div class='col col-md-2'>";
    echo show_modif($def_onglets, 'onglet', 2, $form_name) . "</div>";
}
echo '<div class="col col-md-10" >';

if($protectedPost['onglet'] != "") {
    print_item_header($typeList[$protectedPost['onglet']]['TYPENAME']);

    $tab_options = $protectedPost;
    $tab_options['form_name'] = $form_name;
    $tab_options['table_name'] = $table_name;
    $list_fields = $snmp->show_columns($typeList[$protectedPost['onglet']]['TABLENAME']);
    $list_col_cant_del = $list_fields;
    $default_fields = $list_fields;
    $tab_options['FILTRE'] = array_flip($list_fields);
    $queryDetails = "SELECT * FROM ".$typeList[$protectedPost['onglet']]['TABLENAME'];

    ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
}

echo '</div>';
echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
    ob_start();
}



