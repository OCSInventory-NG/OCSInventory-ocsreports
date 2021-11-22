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

require_once('require/function_files.php');
require_once('require/function_ipdiscover.php');

$form_name = 'ipdiscover_analyse';
$table_name = $form_name;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;
echo open_form($form_name);
$pas = preg_replace('/[^a-zA-Z0-9\._]/', "",$protectedGet['rzo']);
$values = look_config_default_values(array('IPDISCOVER_IPD_DIR'), '', array('IPDISCOVER_IPD_DIR' => array('TVALUE' => VARLIB_DIR)));
$fname = $values['tvalue']['IPDISCOVER_IPD_DIR'];
$file_name = $fname . "/ipd/" . $pas . ".ipd";
//reset cache?
if (is_defined($protectedPost['reset'])) {
    unlink($file_name);
    reloadform_closeme('', true);
} else {
    if (!is_readable($file_name))
        runCommand("-cache -net=" . $pas, $fname);
    $tabBalises = Array($l->g(34) => "IP",
        $l->g(95) => "MAC",
        $l->g(49) => "NAME",
        $l->g(232) => "DATE",
        $l->g(66) => "TYPE");
    $ret = parse_xml_file($file_name, $tabBalises, "HOST");
    if ($ret != array()) {
        $sql = "select ";
        $i = 0;
        while ($ret[$i]) {
            foreach ($ret[$i] as $key => $value) {
                $sql .= "'" . $value . "' as " . $key . ",";
            }
            $sql = substr($sql, 0, -1) . " union select ";
            $i++;
        }
        $sql = substr($sql, 0, -13);
        $default_fields = $tabBalises;
        $list_col_cant_del = $default_fields;
        $tab_options['NO_NAME']['NAME'] = 1;
        $result_exist = ajaxtab_entete_fixe($tabBalises, $default_fields, $tab_options, $list_col_cant_del);
    }
    echo "<p><input type='submit' name='reset' value='" . $l->g(1261) . "' class='btn'></p>";
}
echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($tabBalises, $default_fields, $list_col_cant_del, $sql, $tab_options);
}
?>
