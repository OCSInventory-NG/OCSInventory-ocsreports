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

print_item_header($l->g(1052));

$form_name = "affich_packets";
$table_name = $form_name;
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;
echo open_form($form_name, '', '', 'form-horizontal');
$list_fields = array($l->g(475) => 'PKG_ID',
    $l->g(49) => 'NAME',
    $l->g(440) => 'PRIORITY',
    $l->g(464) => 'FRAGMENTS',
    $l->g(462) . " Ko" => 'SIZE',
    $l->g(25) => 'OSNAME',
    'COMMENT' => 'COMMENT');
$list_col_cant_del = array($l->g(475) => $l->g(475), $l->g(49) => $l->g(49));
$default_fields = $list_col_cant_del;
$pack_sup = $l->g(561);
$queryDetails = "SELECT PKG_ID,NAME,PRIORITY,FRAGMENTS,round(SIZE/1024,2) as SIZE,OSNAME,COMMENT
                                        FROM download_history h LEFT JOIN download_available a ON h.pkg_id=a.fileid
                                        where hardware_id=%s and name is not null";
$arg = array($systemid);
if ($_SESSION['OCS']['profile']->getRestriction('TELEDIFF_VISIBLE', 'YES') == "YES") {
    $queryDetails .= " and a.comment not like '%s'";
    array_push($arg, '%[VISIBLE=0]%');
}
$queryDetails .= "	union SELECT PKG_ID,'%s','%s','%s','%s','%s','%s'
                                        FROM download_history h LEFT JOIN download_available a ON h.pkg_id=a.fileid where hardware_id=%s and name is null";
$i = 0;
while ($i < 6) {
    array_push($arg, $pack_sup);
    $i++;
}
array_push($arg, $systemid);
$tab_options['ARG_SQL'] = $arg;
ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);

echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
    ob_start();
}

?>