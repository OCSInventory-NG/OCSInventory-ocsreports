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
require_once('require/function_opt_param.php');
require_once('require/function_config_generale.php');
require_once('require/function_search.php');

$form_name = "param_affect";
echo open_form($form_name, '', '', 'form-horizontal');

$list_id = multi_lot($form_name, $l->g(601));
echo "<a class='btn btn-info' href='index.php?function=computer&head=1&systemid=".$list_id."&cat=config'>".$l->g(188)."</a></br></br>";

$def_onglets['SERV'] = $l->g(499); //Serveur
$def_onglets['INV'] = $l->g(728); //Inventaire
$def_onglets['TELE'] = $l->g(512); //Télédéploiement
$def_onglets['RSX'] = $l->g(1198); //ipdiscover
//update values
if (isset($protectedPost['Valid']) && $protectedPost['Valid'] == $l->g(103)) {
    if ($list_id) {
        //more then one value
        if (strstr($list_id, ',') != "") {
            $tab_hadware_id = explode(",", $list_id);
            $add_lbl = " (" . count($tab_hadware_id) . " " . $l->g(652) . ")";
        } else {
            $list_hardware_id = $list_id;
        }
    }
    if (isset($list_hardware_id) || isset($tab_hadware_id)) {
        foreach ($protectedPost as $key => $value) {
            if ($key != "systemid" && $key != "origine") {
                if ($value == "SERVER DEFAULT" || $value == "des" || trim($value) == "" || $value === 0) {
                    erase($key);
                } elseif ($value == "CUSTOM") {
                    insert($key, $protectedPost[$key . '_edit']);
                } elseif ($value == "ALWAYS") {
                    insert($key, 0);
                } elseif ($value == "NEVER") {
                    insert($key, -1);
                } elseif ($value == "ON") {
                    insert($key, 1);
                } elseif ($value == "OFF") {
                    insert($key, 0);
                } elseif (($key == "IPDISCOVER" && $value != "des" && $value != "OFF") || ($key == "SNMP_NETWORK")) {
                    insert($key, 2, $value);
                } elseif (($key == "SCAN_TYPE_IPDISCOVER" && $value != "Default") || ($key == "SCAN_TYPE_SNMP" && $value != "Default")) {
                    insert($key, 2, $value);
                } elseif (($key == "SCAN_ARP_BANDWIDTH" && ($value != "" && $value != 0 && $value != "Default"))) {
                    insert($key, 2, $value);
                }
            }
        }
        $MAJ = $l->g(711);
        echo "<div class='col col-md-12'>";
        if (isset($add_lbl)) { 
            msg_success($MAJ . $add_lbl);
        } else {
            msg_success($MAJ);
        }
        echo "</div>";
        if (isset($protectedGet['origine']) && $protectedGet['origine'] == 'machine') {
            $form_to_reload = 'config_mach';
        } elseif (isset($protectedGet['origine']) && $protectedGet['origine'] == 'group') {
            $form_to_reload = 'config_group';
        }
        if (isset($form_to_reload)) {
            echo "<script language='javascript'> window.opener.document." . $form_to_reload . ".submit();</script>";
        }
    } else {
        echo "<script>alert('" . $l->g(983) . "')</script>";
    }
}

$default = look_config_default_values(array('DOWNLOAD', 'DOWNLOAD_CYCLE_LATENCY', 'DOWNLOAD_PERIOD_LENGTH',
    'DOWNLOAD_FRAG_LATENCY', 'DOWNLOAD_PERIOD_LATENCY',
    'DOWNLOAD_TIMEOUT', 'PROLOG_FREQ'));
$optdefault = $default["ivalue"];

//not a sql query
if (isset($protectedGet['origine']) && is_numeric($protectedGet['idchecked'])) {
    //looking for value of systemid
    $sql_value_idhardware = "select NAME,IVALUE,TVALUE from devices where name != 'DOWNLOAD' and hardware_id=%s";
    $arg_value_idhardware = $protectedGet['idchecked'];
    $result_value = mysql2_query_secure($sql_value_idhardware, $_SESSION['OCS']["readServer"], $arg_value_idhardware);
    while ($value = mysqli_fetch_array($result_value)) {
        $optvalue[$value["NAME"]] = $value["IVALUE"];
        $optvalueTvalue[$value["NAME"]] = $value["TVALUE"];
    }
    $champ_ignored = 0;
} elseif ($list_id) {
    $tab_hadware_id = explode(",", $list_id);
    $champ_ignored = 1;
}

if ($list_id) {
    onglet($def_onglets, $form_name, 'onglet', 7);
    echo '<div class="col-md-12">';
    if ($protectedPost['onglet'] == 'INV') {
        include ('ms_custom_frequency.php');
    }
    if ($protectedPost['onglet'] == 'SERV') {
        include ('ms_custom_prolog.php');
    }
    if ($protectedPost['onglet'] == 'TELE') {
        include ('ms_custom_download.php');
    }
    if ($protectedPost['onglet'] == 'RSX') {
        include ('ms_custom_ipdiscover.php');
    }
    echo "</div>";
}

echo close_form();
?>
