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

require('require/function_telediff.php');

// Delete package
if (!empty($protectedPost['SUP_PROF'])) {
    if ($_SESSION['OCS']["justAdded"] == false) {
        desactive_packet($systemid, $protectedPost['SUP_PROF']);
    } else {
        $_SESSION['OCS']["justAdded"] = false;
    }
    addLog($l->g(512), $l->g(886) . " " . $protectedPost['SUP_PROF'] . " => " . $systemid);
    $tab_options['CACHE'] = 'RESET';
    unset($protectedPost['SUP_PROF']);
}

// Re-affect a package in ERR
if (!empty($protectedPost['AFFECT_AGAIN'])) {
    //delete all info of specific package
    desactive_download_option($systemid, $protectedPost['AFFECT_AGAIN']);
    active_option('DOWNLOAD', $systemid, $protectedPost['AFFECT_AGAIN']);
    $tab_options['CACHE'] = 'RESET';
    unset($protectedPost['AFFECT_AGAIN']);
}

if( $_SESSION['OCS']['profile']->getConfigValue('TELEDIFF')=="YES" ){
    echo "<br><a href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_custom_pack']."&head=1&idchecked=".$systemid."&origine=mach\" class='btn btn-success' role='button' >".$l->g(501)."</a><br><br> ";
}

print_item_header($l->g(481));

$form_name = "activ_packets";
$table_name = $form_name;
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;

echo open_form($form_name, '', '', 'form-horizontal');

$list_fields = array(
    $l->g(1037) => 'name',
    $l->g(475) => 'fileid',
    $l->g(499) => 'pack_loc',
    $l->g(1102) => 'tvalue',
    $l->g(9207) => 'comments',
);

if ($_SESSION['OCS']['profile']->getConfigValue('TELEDIFF') == "YES") {
    $list_fields['AFFECT_AGAIN'] = 'status';
    $list_fields['SUP'] = 'ivalue';
}

$list_col_cant_del = $list_fields;
$default_fields = $list_col_cant_del;

$queryDetails = "SELECT a.name, IFNULL(d.tvalue, '%s') as tvalue,d.ivalue,IFNULL(STR_TO_DATE(d.comments, '%s'), '%s') as comments,e.fileid, e.pack_loc,h.name as name_server,h.id,a.comment, CONCAT(d.ivalue,';',d.tvalue) as status
                FROM devices d left join download_enable e on e.id=d.ivalue
                    LEFT JOIN download_available a ON e.fileid=a.fileid
                    LEFT JOIN hardware h on h.id=e.server_id
                WHERE d.name='DOWNLOAD' and a.name != '' and pack_loc != '' AND d.hardware_id=%s
                UNION
                SELECT '%s', IFNULL(d.tvalue, '%s') as tvalue,d.ivalue,IFNULL(STR_TO_DATE(d.comments, '%s'), '%s') as comments,e.fileid, '%s',h.name,h.id,a.comment, CONCAT(d.ivalue,';',d.tvalue) as status
                FROM devices d left join download_enable e on e.id=d.ivalue
                    LEFT JOIN download_available a ON e.fileid=a.fileid
                    LEFT JOIN hardware h on h.id=e.server_id
                WHERE d.name='DOWNLOAD' and a.name is null and pack_loc is null  AND d.hardware_id=%s";

$arg = array($l->g(482), "%a %b %e %H:%i:%S %Y", $l->g(9208), $systemid, $l->g(1129), $l->g(482), "%a %b %e %H:%i:%S %Y", $l->g(9208), $l->g(1129), $systemid);

$tab_options['ARG_SQL'] = $arg;

$tab_options['NO_SEARCH']['pack_loc'] = 'pack_loc';

ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);

echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
    ob_start();
}

?>