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
/*
 * Configuration page
 */
require_once('require/function_config_generale.php');

$def_onglets['INVENTORY'] = $l->g(728); //Inventory
$def_onglets['SERVER'] = $l->g(499); //Server
$def_onglets['IPDISCOVER'] = $l->g(312); //IP Discover
$def_onglets['TELEDEPLOY'] = $l->g(512); //Teledeploy
$def_onglets['REDISTRIB'] = $l->g(628); //redistribution servers
$def_onglets['GROUPS'] = $l->g(583); //Groups
$def_onglets['REGISTRY'] = $l->g(211); //Registry
$def_onglets['INV_FILE'] = $l->g(734); //Inventory file
$def_onglets['FILTER'] = $l->g(735); //Filter
$def_onglets['WEBSERVICES'] = $l->g(760); //Webservice
$def_onglets['GUI'] = $l->g(84); //GUI
$def_onglets['CNX'] = $l->g(1108); //connexion
$def_onglets['SNMP'] = $l->g(1136); //SNMP
$def_onglets['WOL'] = $l->g(1279); //WOL
$def_onglets['PLUGINSCONF'] = $l->g(6000); //Plugins Configuration

if (DEV_OPTION) {
    $def_onglets['DEV'] = $l->g(1302);
}

if ($protectedPost['Valid'] == $l->g(103)) {
    $etat = verif_champ();
    if ($etat == "") {
        update_default_value($protectedPost); //function in function_config_generale.php
        $MAJ = $l->g(1121);
    } else {
        $msg = "";
        foreach ($etat as $name => $value) {
            if (!is_array($value)) {
                $msg .= $name . " " . $l->g(759) . " " . $value . "<br>";
            } else {
                if (isset($value['FILE_NOT_EXIST'])) {
                    if ($name == 'DOWNLOAD_REP_CREAT') {
                        $msg .= $name . ": " . $l->g(1004) . " (" . $value['FILE_NOT_EXIST'] . ")<br>";
                    } else {
                        $msg .= $name . ": " . $l->g(920) . " " . $value['FILE_NOT_EXIST'] . "<br>";
                    }
                }
            }
        }
        msg_error($msg);
    }
}

if (isset($MAJ) && $MAJ != '') {
    msg_success($MAJ);
}
printEnTete($l->g(107));
$form_name = 'modif_onglet';
echo open_form($form_name);
show_tabs($def_onglets, $form_name, "onglet", 10);
echo '<div class="col col-md-10">';
switch ($protectedPost['onglet']) {
    case 'CNX':
        pageConnexion($form_name);
        break;
    case 'GUI':
        pageGUI($form_name);
        break;
    case 'INVENTORY':
        pageinventory($form_name);
        break;
    case 'SERVER':
        pageserveur($form_name);
        break;
    case 'IPDISCOVER':
        pageipdiscover($form_name);
        break;
    case 'TELEDEPLOY':
        pageteledeploy($form_name);
        break;
    case 'REDISTRIB':
        pageredistrib($form_name);
        break;
    case 'GROUPS':
        pagegroups($form_name);
        break;
    case 'REGISTRY':
        pageregistry($form_name);
        break;
    case 'INV_FILE':
        pagefilesInventory($form_name);
        break;
    case 'FILTER':
        pagefilter($form_name);
        break;
    case 'WEBSERVICES':
        pagewebservice($form_name);
        break;
    case 'SNMP':
        pagesnmp($form_name);
        break;
    case 'DEV':
        pagesdev($form_name);
        break;
    case 'PLUGINSCONF':
        pagesplugin($form_name);
        break;
    case 'WOL':
        pageswol($form_name);
        break;
    default:
        pageinventory($form_name);
}

echo "<input type='hidden' id='RELOAD_CONF' name='RELOAD_CONF' value=''>";
echo close_form();
echo '</div>';
