<?php 

/*
 * 
 * Configuration page
 * 
 * 
 */

require_once('require/function_config_generale.php');


$def_onglets[$l->g(728)]=$l->g(728); //Inventory
$def_onglets[$l->g(499)]=$l->g(499); //Server
$def_onglets[$l->g(312)]=$l->g(312); //IP Discover
$def_onglets[$l->g(512)]=$l->g(512); //Teledeploy
$def_onglets[$l->g(628)]=$l->g(628); //redistribution servers
$def_onglets[$l->g(583)]=$l->g(583); //Groups
$def_onglets[$l->g(211)]=$l->g(211); //Registry
$def_onglets[$l->g(734)]=$l->g(734); //Inventory file
$def_onglets[$l->g(735)]=$l->g(735); //Filter
$def_onglets[$l->g(760)]=$l->g(760); //Webservice
$def_onglets[$l->g(84)]=$l->g(84); //GUI
$def_onglets[$l->g(1108)]=$l->g(1108); //connexion
$def_onglets[$l->g(1136)]=$l->g(1136); //SNMP




if ($protectedPost['Valid'] == $l->g(103)){
	$etat=verif_champ();
	if ($etat == ""){
		update_default_value($protectedPost); //function in function_config_generale.php
		$MAJ=$l->g(1121);
	}else{
		$msg="";
		foreach ($etat as $name=>$value){
			$msg.=$name." ".$l->g(759)." ".$value."<br>";
		}
		//print_r($etat);
	msg_error($msg);
		
	}
	
}
if (isset($MAJ) and $MAJ != '')
	msg_success($MAJ);
$form_name='modif_onglet';
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";

onglet($def_onglets,$form_name,'onglet',7);
echo '<div class="mlt_bordure" >';
if ($protectedPost['onglet'] == $l->g(1108) ){
	
	pageConnexion($form_name);
	
}

if ($protectedPost['onglet'] == $l->g(84) ){
	
	pageGUI($form_name);
	
}
if ($protectedPost['onglet'] == $l->g(728) or $protectedPost['onglet'] == ""){
	
	pageinventory($form_name);
	
}
if ($protectedPost['onglet'] == $l->g(499) ){
	
 	pageserveur($form_name);
	
}
if ($protectedPost['onglet'] == $l->g(312)){	
	
	pageipdiscover($form_name);
}
if ($protectedPost['onglet'] == $l->g(512)){
	
	pageteledeploy($form_name);
}
if ($protectedPost['onglet'] == $l->g(628)){
	
	pageredistrib($form_name);
}
if ($protectedPost['onglet'] == $l->g(583)){
	
	pagegroups($form_name);
}
if ($protectedPost['onglet'] == $l->g(211)){
	
	pageregistry($form_name);
}
if ($protectedPost['onglet'] == $l->g(734)){
	
	pagefilesInventory($form_name);
}
if ($protectedPost['onglet'] == $l->g(735)){
	
	pagefilter($form_name);
}
if ($protectedPost['onglet'] == $l->g(760)){
	
	pagewebservice($form_name);
}
if ($protectedPost['onglet'] == $l->g(1136)){
	
	pagesnmp($form_name);
}

echo "</div></form>";