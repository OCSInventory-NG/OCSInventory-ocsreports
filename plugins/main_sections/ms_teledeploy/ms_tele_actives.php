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
require_once('require/function_telediff.php');
if ($_SESSION['OCS']['profile']->getRestriction('TELEDIFF_ACTIVATE') == 'NO') {
    $cant_active = false;
} else {
    $cant_active = true;
}
if (!$cant_active) {
    if (!empty($protectedPost['DEL_ALL'])) {
        $sql_listIDdel = "select distinct ID from download_enable where FILEID=%s";
        $arg_listIDdel = $protectedPost['DEL_ALL'];
        $res_listIDdel = mysql2_query_secure($sql_listIDdel, $_SESSION['OCS']["readServer"], $arg_listIDdel);
        while ($val_listIDdel = mysqli_fetch_array($res_listIDdel)) {
            $listIDdel[] = $val_listIDdel['ID'];
        }
        if ($listIDdel != '') {
            foreach ($listIDdel as $v) {
                desactive_packet('', $v);
            }
        }
        mysql2_query_secure("DELETE FROM download_enable WHERE FILEID=%s", $_SESSION['OCS']["writeServer"], $protectedPost['DEL_ALL']);
        echo "<script>window.opener.document.packlist.submit(); self.close();</script>";
    }
    if (!empty($protectedPost['SUP_PROF'])) {
        desactive_packet('', $protectedPost['SUP_PROF']);
        mysql2_query_secure("DELETE FROM download_enable WHERE ID=%s", $_SESSION['OCS']["writeServer"], $protectedPost['SUP_PROF']);
    }
}
$sql_details = "select distinct priority,fragments,size from download_available where fileid='%s'";
$res_details = mysql2_query_secure($sql_details, $_SESSION['OCS']["readServer"], $protectedGet['timestamp']);
$val_details = mysqli_fetch_array($res_details);
$tps = "<br>" . $l->g(992) . " : <b><font color=red>" . tps_estimated($val_details) . "</font></b>";
PrintEnTete($l->g(481) . $tps);
echo "<br>";
$form_name = "tele_actives";
//ouverture du formulaire
echo open_form($form_name);
$list_fields = array($l->g(460) => 'e.ID',
    'Timestamp' => 'e.FILEID',
    $l->g(470) => 'e.INFO_LOC',
    $l->g(471) => 'e.PACK_LOC',
    $l->g(49) => 'a.NAME',
    $l->g(440) => 'a.PRIORITY',
    $l->g(480) => 'a.FRAGMENTS',
    $l->g(462) => 'a.SIZE',
    $l->g(25) => 'a.OSNAME');
if (!$cant_active) {
    $list_fields['SUP'] = 'e.ID';
}
$table_name = "LIST_ACTIVES";
$default_fields = $list_fields;
$list_col_cant_del = array($l->g(460) => $l->g(460), 'SUP' => 'SUP');
$querypack = 'SELECT distinct ';
foreach ($list_fields as $key => $value) {
    if ($key != 'SUP') {
        $querypack .= $value . ',';
    }
}
$querypack = substr($querypack, 0, -1);
$querypack .= " from download_enable e RIGHT JOIN download_available a ON a.fileid = e.fileid
				where e.FILEID='" . $protectedGet['timestamp']. "'";
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;
$result_exist = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
if ($result_exist != "" && !$cant_active) {
    echo "<a href=# OnClick='confirme(\"\",\"" . $protectedGet['timestamp'] . "\",\"" . $form_name . "\",\"DEL_ALL\",\"" . $l->g(900) . "\");'><img src='image/delete.png' title='Supprimer' ></a>";
}
echo "<input type='hidden' id='DEL_ALL' name='DEL_ALL' value=''>";
echo close_form();
echo "<center>" . $l->g(552) . "</center>";
if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $querypack, $tab_options);
}
