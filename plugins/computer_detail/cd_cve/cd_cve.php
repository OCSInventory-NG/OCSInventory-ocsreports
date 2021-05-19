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

print_item_header($l->g(1472));
if (!isset($protectedPost['SHOW']))
    $protectedPost['SHOW'] = 'NOSHOW';
$form_name = "affich_cve";
$table_name = $form_name;
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;
echo open_form($form_name, '', '', 'form-horizontal');
$list_fields = array(
    $l->g(69) => 'PUBLISHER',
    $l->g(20) => 'softname',
    $l->g(19) => 'VERSION',
    'CVSS' => 'CVSS',
    'CVE' => 'CVE',
    $l->g(1467) => 'LINK'
);

if ($show_all_column) {
    $list_col_cant_del = $list_fields;
} else {
    $list_col_cant_del[$l->g(8104)] = $l->g(8104);
}

$default_fields = array(
    $l->g(69) => 'PUBLISHER',
    $l->g(20) => 'softname',
    $l->g(19) => 'VERSION',
    'CVSS' => 'CVSS',
    'CVE' => 'CVE',
    $l->g(1467) => 'LINK'
);
$queryDetails = "SELECT v.VERSION, c.CVSS, c.CVE, c.LINK , p.PUBLISHER, n.NAME as softname
                    FROM cve_search c 
                    LEFT JOIN software_name n ON n.ID = c.NAME_ID
                    LEFT JOIN software_publisher p ON p.ID = c.PUBLISHER_ID
                    LEFT JOIN software_version v ON v.ID = c.VERSION_ID
                    LEFT JOIN software s ON s.NAME_ID = n.ID
                    INNER JOIN hardware h ON h.ID = s.HARDWARE_ID
                    WHERE h.ID=$systemid
                    GROUP BY c.LINK, c.CVSS, c.NAME_ID, c.CVE ";

ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);

 echo close_form();
if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
    ob_start();
}
?>
