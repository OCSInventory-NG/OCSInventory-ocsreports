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
$def_onglets['GROUPS'] = $l->g(583); //Groups
$def_onglets['REGISTRY'] = $l->g(211); //Registry
$def_onglets['GUI'] = $l->g(84); //GUI
$def_onglets['SNMP'] = $l->g(1136); //SNMP
$def_onglets['WOL'] = $l->g(1279); //WOL

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

if (is_defined($MAJ)) {
    msg_success($MAJ);
}

$champs = array('ADVANCE_CONFIGURATION' => 'ADVANCE_CONFIGURATION');
$values = look_config_default_values($champs);

printEnTete($l->g(107));

$form_name = 'modif_onglet';
echo open_form($form_name, '', '', 'form-horizontal');

if($values['ivalue']['ADVANCE_CONFIGURATION']){
  $def_onglets['REDISTRIB'] = $l->g(628); //redistribution servers
  $def_onglets['INV_FILE'] = $l->g(734); //Inventory file
  $def_onglets['FILTER'] = $l->g(735); //Filter
  $def_onglets['CNX'] = $l->g(1108); //connexion
  $def_onglets['VULN'] = $l->g(1460); //cve-search integration
}

show_tabs($def_onglets,$form_name,"onglet",true);
echo '<div class="col col-md-10">';
switch ($protectedPost['onglet']) {
    case 'VULN':
        pageVulnerability();
        break;
    case 'CNX':
        pageConnexion();
        break;
    case 'GUI':
        pageGUI($values['ivalue']['ADVANCE_CONFIGURATION']);
        break;
    case 'INVENTORY':
        pageinventory($values['ivalue']['ADVANCE_CONFIGURATION']);
        break;
    case 'SERVER':
        pageserveur($values['ivalue']['ADVANCE_CONFIGURATION']);
        break;
    case 'IPDISCOVER':
        pageipdiscover($values['ivalue']['ADVANCE_CONFIGURATION']);
        break;
    case 'TELEDEPLOY':
        pageteledeploy($values['ivalue']['ADVANCE_CONFIGURATION']);
        break;
    case 'REDISTRIB':
        pageredistrib();
        break;
    case 'GROUPS':
        pagegroups();
        break;
    case 'REGISTRY':
        pageregistry();
        break;
    case 'INV_FILE':
        pagefilesInventory();
        break;
    case 'FILTER':
        pagefilter();
        break;
    case 'SNMP':
        pagesnmp();
        break;
    case 'DEV':
        pagesdev();
        break;
    case 'WOL':
        pageswol();
        break;
    default:
        pageinventory($values['ivalue']['ADVANCE_CONFIGURATION']);
}

?>
</br>
<input type='hidden' id='RELOAD_CONF' name='RELOAD_CONF' value=''>
<input type="submit" name="Valid" value="<?php echo $l->g(103) ?>" class="btn btn-success">
<input type="submit" name="Reset" value="<?php echo $l->g(1364) ?>" class="btn btn-danger">
<?php
echo close_form();
echo '</div>';
