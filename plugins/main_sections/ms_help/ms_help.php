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
$data_on[1]=$l->g(1122);
$data_on[2]=$l->g(1123);
$data_on[3]=$l->g(1124);
$data_on[4]=$l->g(1296);

if (isset($protectedGet['TAB']) and isset($data_on[$protectedGet['TAB']]) and !isset($protectedPost['onglet']))
$protectedPost['onglet']=$protectedGet['TAB'];


if ($protectedPost['onglet'] == "" or !isset($protectedPost['onglet']))
$protectedPost['onglet']=1;

$form_name="help";

if ($protectedPost['onglet'] == "" or !isset($protectedPost['onglet']))
$protectedPost['onglet']=1;
//d�finition des onglets
//$data_on['ABOUT']='A propos';

echo open_form($form_name);
onglet($data_on,$form_name,"onglet",7);
echo '<div class="mlt_bordure" >';
if ($protectedPost['onglet'] == 2){
	echo "<iframe width=\"647\" height=\"400\" src=\"http://webchat.freenode.net/?channels=ocsinventory-ng&uio=d4\">
		</iframe>";
}elseif($protectedPost['onglet'] == 1){
	echo "<iframe  width=\"100%\" height=\"100%\" src=\"http://wiki.ocsinventory-ng.org\">
	</iframe>";
}elseif($protectedPost['onglet'] == 3){
		echo "<iframe  width=\"100%\" height=\"100%\" src=\"http://forums.ocsinventory-ng.org\">
	</iframe>";
}elseif($protectedPost['onglet'] == 4){
		echo "<iframe  width=\"100%\" height=\"100%\" src=\"http://ocsinventory-ng.factorfx.com\">
	</iframe>";
}
echo "</div>";
echo close_form();

?>



