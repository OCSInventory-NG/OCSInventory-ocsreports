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
require_once('require/function_table_html.php');
require_once('require/function_files.php');

$tab_options = $protectedPost;
$Directory = $_SESSION['OCS']['LOG_DIR'] . "/";
$data = scanDirectory($Directory, "csv");
if (is_array($data)) {
    $form_name = "logs";
    echo open_form($form_name);
    $sql = "";
    $arg = array();
    foreach ($data['name'] as $id => $value) {
        if ($id == 0) {
            $name = 'as name';
            $date_create = 'as date_create';
            $date_modif = 'as date_modif';
            $size = 'as size';
        } else {
            $name = '';
            $date_create = '';
            $date_modif = '';
            $size = '';
        }
        $sql .= "select '%s' " . $name . ",'%s' " . $date_create . ",'%s' " . $date_modif . ",'%s' " . $size . " union ";
        array_push($arg, $value);
        array_push($arg, rtrim($data['date_create'][$id], "."));
        array_push($arg, rtrim($data['date_modif'][$id], "."));
        array_push($arg, round($data['size'][$id] / 1024, 3) . " " . $l->g(516));
    }
    $sql = substr($sql, 0, -6);

    $list_fields = array('name' => 'name',
        $l->g(951) => 'date_create',
        $l->g(952) => 'date_modif',
        $l->g(953) => 'size'
    );
    $list_col_cant_del = $list_fields;
    $default_fields = $list_fields;

    //	$sql= "select '%s' as function,%s from deploy";
    $tab_options['ARG_SQL'] = $arg;
    $tab_options['LBL']['name'] = $l->g(950);
    $tab_options['LIEN_LBL']['name'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_csv'] . '&no_header=1&log=';
    $tab_options['LIEN_CHAMP']['name'] = 'name';
    $tab_options['LIEN_TYPE']['name'] = 'POPUP';
    $tab_options['POPUP_SIZE']['name'] = "width=900,height=600";
    printEntete($l->g(928));
    $table_name = $form_name;
    $tab_options['form_name'] = $form_name;
    $tab_options['table_name'] = $table_name;
    ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
    echo close_form();
} else {
    msg_warning($l->g(766));
}
if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $sql, $tab_options);
}
