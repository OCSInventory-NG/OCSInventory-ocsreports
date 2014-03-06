<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Erwan GOALOU 2010 (erwan(at)ocsinventory-ng(pt)org)
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================


/*
 * 
 * Configuration page
 * 
 * 
 */

require_once('require/function_config_generale.php');


$def_onglets['INVENTORY']=$l->g(728); //Inventory
$def_onglets['SERVER']=$l->g(499); //Server
$def_onglets['IPDISCOVER']=$l->g(312); //IP Discover
$def_onglets['TELEDEPLOY']=$l->g(512); //Teledeploy
$def_onglets['REDISTRIB']=$l->g(628); //redistribution servers
$def_onglets['GROUPS']=$l->g(583); //Groups
$def_onglets['REGISTRY']=$l->g(211); //Registry
$def_onglets['INV_FILE']=$l->g(734); //Inventory file
$def_onglets['FILTER']=$l->g(735); //Filter
$def_onglets['WEBSERVICES']=$l->g(760); //Webservice
$def_onglets['GUI']=$l->g(84); //GUI
$def_onglets['CNX']=$l->g(1108); //connexion
$def_onglets['SNMP']=$l->g(1136); //SNMP
$def_onglets['WOL']=$l->g(1279); //WOL

if (DEV_OPTION)
	$def_onglets['DEV']=$l->g(1302);

if ($protectedPost['Valid'] == $l->g(103)){
	$etat=verif_champ();
	if ($etat == ""){
		update_default_value($protectedPost); //function in function_config_generale.php
		$MAJ=$l->g(1121);
	}else{
		$msg="";
		foreach ($etat as $name=>$value){
			if (!is_array($value))
				$msg.=$name." ".$l->g(759)." ".$value."<br>";
			else{
				if (isset($value['FILE_NOT_EXIST'])){
					if ($name == 'DOWNLOAD_REP_CREAT'){
						$msg.= $name.": ".$l->g(1004)." (".$value['FILE_NOT_EXIST'].")<br>";
					}else{
						$msg.= $name.": ".$l->g(920)." ".$value['FILE_NOT_EXIST']."<br>";
					}
				}
				
			}
		}
		msg_error($msg);		
	}
	
}

if (isset($MAJ) and $MAJ != '')
	msg_success($MAJ);
$form_name='modif_onglet';
echo open_form($form_name);

onglet($def_onglets,$form_name,'onglet',8);
echo '<div class="mlt_bordure" >';
switch ($protectedPost['onglet']){
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
	case 'WOL':
		pageswol($form_name);
	break;
	default:
		pageinventory($form_name);	
}

echo "<input type='hidden' id='RELOAD_CONF' name='RELOAD_CONF' value=''>";
echo "</div>";
echo close_form();