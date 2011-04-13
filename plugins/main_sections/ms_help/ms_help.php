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


$form_name="help";
if ($protectedPost['onglet'] == "" or !isset($protectedPost['onglet']))
//$protectedPost['onglet']='ABOUT';
$protectedPost['onglet']=1;

//d�finition des onglets
//$data_on['ABOUT']='A propos';
$data_on[1]=$l->g(1122);
$data_on[2]=$l->g(1123);
$data_on[3]=$l->g(1124);
echo "<form action='' name='".$form_name."' id='".$form_name."' method='POST'>";
onglet($data_on,$form_name,"onglet",7);
echo '<div class="mlt_bordure" >';
if ($protectedPost['onglet'] == 'ABOUT'){
	$msg = "GNU General Public License, version 2.0 <br>";
	$msg .= "<img src='image/logo OCS-ng-48_not_registry.png'><br>";
	$msg .= "<b><font color=RED>AUCUN SUPPORT DECLARE</font></b> - <b><font color=RED>SUPPORT NON ENREGISTREE </font></b><br> ";
	//$msg .= "<a href=#>Prendre un contrat de support</a><br>";
	$msg .= "<input type=button name='choise_support' value='Choisir un support'><br>";
	$msg .= "<input type=button name='IMPORT' value='Enregistrer un support'>";
	msg_info($msg);
	
	$msg = "GNU General Public License, version 2.0 <br>";
	$msg .= "<img src='image/logo OCS-ng-48_registry.png'><br>";
	$msg .= "<b><font color=GREEN>SUPPORT DECLARE</font></b> - Validité du support: <b><font color=GREEN>21/04/2012</font></b><br> ";
	$msg .= "N° d'identification support: <b><big>12DVCT43ZS-GNEAO24I-MAQW23</big></b><br>";
	//$msg .= "<a href='#'>Ouvrir un incident</a><br>";
	$msg .= "<input type=button name='IMPORT' value='Contacter votre support'><br>";
	$msg .= "<input type=button name='IMPORT' onclic value='Enregistrer un support'>";
	msg_info($msg);
}
if ($protectedPost['onglet'] == 2){
	echo "<iframe width=\"647\" height=\"400\" src=\"http://webchat.freenode.net/?channels=ocsinventory-ng&uio=d4\">
		</iframe>";
}elseif($protectedPost['onglet'] == 1){
	echo "<iframe  width=\"100%\" height=\"100%\" src=\"http://wiki.ocsinventory-ng.org/\">
	</iframe>";
}elseif($protectedPost['onglet'] == 3){
		echo "<iframe  width=\"100%\" height=\"100%\" src=\"http://forums.ocsinventory-ng.org\">
	</iframe>";
	
}
echo "</div>";
echo "</form>";

?>



