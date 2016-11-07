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

function affich_detail_simple($form_name, $list_fields, $list_col_cant_del, $default_fields, $table, $tab_options = array()) {
    global $protectedPost;

    if (AJAX) {
        parse_str($protectedPost['ocs']['0'], $params);
        $protectedPost += $params;
        ob_start();
    }
    $form_name = "affich_controllers";
    $table_name = $form_name;
    $tab_options = $protectedPost;
    $tab_options['form_name'] = $form_name;
    $tab_options['table_name'] = $table_name;
    echo open_form($form_name, '', '', 'form-horizontal');
    $queryDetails = "SELECT ";
    foreach ($list_fields as $value) {
        $queryDetails .= $value . ",";
    }
    // TODO : Buggy code $systemid not initialized
    $queryDetails = substr($queryDetails, 0, -1) . " FROM " . $table . " WHERE (hardware_id=$systemid)";
    ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
    echo close_form();
    if (AJAX) {
        ob_end_clean();
        tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
        ob_start();
    }
}

?>
