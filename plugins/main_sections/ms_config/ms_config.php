<?php 

/*
 * 
 * Configuration page
 * 
 * 
 */

require_once('require/function_config_generale.php');
if( $_SESSION['OCS']["lvluser"] != SADMIN )
	die("FORBIDDEN");

$def_onglets[$l->g(728)]=$l->g(728); //Inventaire
$def_onglets[$l->g(499)]=$l->g(499); //Serveur
$def_onglets[$l->g(312)]=$l->g(312); //IP Discover
$def_onglets[$l->g(512)]=$l->g(512); //T�l�d�ploiement
$def_onglets[$l->g(628)]=$l->g(628); //Serveur de redistribution
$def_onglets[$l->g(583)]=$l->g(583); //Groupes
$def_onglets[$l->g(211)]=$l->g(211); //Registre
$def_onglets[$l->g(734)]=$l->g(734); //Fichiers d'inventaire
$def_onglets[$l->g(735)]=$l->g(735); //Filtres
$def_onglets[$l->g(760)]=$l->g(760); //Webservice
$def_onglets[$l->g(84)]=$l->g(84); //GUI
$def_onglets['connexion']="Connexion Ldap"; //connexion a l'applicatioon

//IT SET MANAGEMENT
$sql_It_set="select IVALUE from config where name='IT_SET_MANAGEMENT'";
$result_It_set = mysql_query($sql_It_set, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
while($value=mysql_fetch_array($result_It_set)){
	if ($value['IVALUE'] == 1)
		$def_onglets[$l->g(1027)]=$l->g(1027);	
}


if ($protectedPost['Valid'] == $l->g(103)){
	$etat=verif_champ();
	if ($etat == "")
	$MAJ=update_default_value($protectedPost); //function in function_config_generale.php
	else{
		$msg="";
		foreach ($etat as $name=>$value){
			$msg.=$name." ".$l->g(759)." ".$value."<br>";
		}
		//print_r($etat);
	echo "<font color=RED ><center><b>".$msg."</b></center></font>";
		
	}
	
}
echo "<font color=green ><center><b>".$MAJ."</b></center></font>";
$form_name='modif_onglet';
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";

onglet($def_onglets,$form_name,'onglet',7);
echo '<div class="mlt_bordure" >';
if ($protectedPost['onglet'] == "connexion" ){
	
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
if ($protectedPost['onglet'] == $l->g(1027)){
	
	pageitsetmanagement($form_name);
}

echo "</div></form>";