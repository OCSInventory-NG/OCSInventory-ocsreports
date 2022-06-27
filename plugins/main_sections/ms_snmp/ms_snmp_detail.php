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
require('require/function_opt_param.php');
require('require/function_graphic.php');
require_once('require/function_files.php');
require_once('require/function_snmp.php');
$form_name = 'SNMP_DETAILS';
//recherche des infos de la machine
$item = info_snmp($protectedGet['id']);
if (!is_array($item['data'])) {
    msg_error($item);
    require_once(FOOTER_HTML);
    die();
}
$systemid = $item['data']['snmp']->ID;
// SNMP SUMMARY
$lbl_affich = array('NAME' => $l->g(49), 'UPTIME' => $l->g(352), 'MACADDR' => $l->g(95), 'IPADDR' => $l->g(34),
    'CONTACT' => $l->g(1227), 'LOCATION' => $l->g(295), 'DOMAIN' => $l->g(33), 'TYPE' => $l->g(66),
    'SNMPDEVICEID' => $l->g(1297), 'SERIALNUMBER' => $l->g(36), 'COUNTER' => $l->g(55),
    'DESCRIPTION' => $l->g(53), 'LASTDATE' => $l->g(46)
);
$info['snmp'] = $item['data']['snmp'];
$col = 1;
?>

<div class="container">
    <div class="row">
        <?php 
foreach ($info['snmp'] as $k => $v){

    if ($k != 'ID') {
        if (isset($v)) {
            if (!isset($lbl_affich[$k])) {
                $label = $k;
            } else {
                $label = $lbl_affich[$k];
            }
            $value = $v;

            ?>
                    <?php if ($col >= 5): ?>
                        <div class="col-md-6">
                    <div class="col-xs-4">
                        <ul class="server-information-ul">
                            <li><?php echo $label; ?></li>
                        </ul>
                    </div>
                    <div class="col-xs-8">
                        <ul class="server-information-ul-li">
                            <li><?php echo $value; ?></li>
                        </ul>
                    </div>
                </div>
                <?php $col++; ?>
                    <?php endif; ?>


            <?php if ($col <= 5): ?>
                        <div class="col-md-6">
                    <div class="col-xs-4">
                        <ul class="server-information-ul">
                            <li><?php echo $label; ?></li>
                        </ul>
                    </div>
                    <div class="col-xs-8">
                        <ul class="server-information-ul-li">
                            <li><?php echo $value; ?></li>
                        </ul>
                    </div>
                </div>
                <?php
                        if ($col != 10) {
                    $col++;
                } else {
                    $col = 1;
                }
            endif;
        }
    }
}
?>

    </div>

</div>
<?php 
unset($item['data']['snmp']);
$second_tab = bandeau($item['data'], $lbl_affich, $item['lbl'], 'mvt_bordure');
if ($second_tab) {
    // TODO: I dont know what is this.
    echo $second_tab;
}
//get plugins when exist
$Directory = PLUGINS_DIR . "snmp_detail/";
$ms_cfg_file = $Directory . "snmp_config.txt";
if (!isset($_SESSION['OCS']['DETAIL_SNMP'])) {
    if (file_exists($ms_cfg_file)) {
        $search = array('ORDER' => 'MULTI2', 'LBL' => 'MULTI', 'ISAVAIL' => 'MULTI');
        $plugins_data = read_configuration($ms_cfg_file, $search);
        $_SESSION['OCS']['DETAIL_SNMP']['LIST_PLUGINS'] = $plugins_data['ORDER'];
        $_SESSION['OCS']['DETAIL_SNMP']['LIST_LBL'] = $plugins_data['LBL'];
        $_SESSION['OCS']['DETAIL_SNMP']['LIST_AVAIL'] = $plugins_data['ISAVAIL'];
    }
}
$list_plugins = $_SESSION['OCS']['DETAIL_SNMP']['LIST_PLUGINS'];
$list_lbl = $_SESSION['OCS']['DETAIL_SNMP']['LIST_LBL'];
$list_avail = $_SESSION['OCS']['DETAIL_SNMP']['LIST_AVAIL'];
foreach ($list_avail as $key => $value) {
    $sql = "select count(*) c from %s where SNMP_ID=%s";
    $arg = array($value, $systemid);
    $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
    $valavail = mysqli_fetch_array($result);
    if ($valavail['c'] == 0) {
        unset($list_lbl[$key]);
    }
}
foreach ($list_lbl as $key => $value) {
    if (substr($value, 0, 2) == 'g(') {
        unset($list_lbl[$key]);
        $list_lbl[$key] = $l->g(substr(substr($value, 2), 0, -1));
    }
}
//par défaut, on affiche les données admininfo
echo open_form($form_name, '', '', 'form-horizontal');
echo "<br/>";
echo "<br/>";
onglet($list_lbl, $form_name, "onglet_sd", 10);
$msq_tab_error = '<small>N/A</small>';
echo '<div class="col-md-12">';
if (isset($list_lbl[$protectedPost['onglet_sd']])) {
    if (file_exists($Directory . "/" . $protectedPost['onglet_sd'] . "/" . $protectedPost['onglet_sd'] . ".php")) {
        include ($Directory . "/" . $protectedPost['onglet_sd'] . "/" . $protectedPost['onglet_sd'] . ".php");
    }
}
echo "</div>";
echo close_form();
if (AJAX) {
    ob_end_clean();
}
