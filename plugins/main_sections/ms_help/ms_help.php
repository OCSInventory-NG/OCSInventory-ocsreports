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
if ($_SESSION['OCS']['RESTRICTION']['SUPPORT']=='NO' and $_SESSION['OCS']['SUPPORT'] == 1){
	$data_on[4]=$l->g(1281);
	$data_on[5]=$l->g(1294);
}


if (isset($protectedGet['TAB']) and isset($data_on[$protectedGet['TAB']]) and !isset($protectedPost['onglet']))
$protectedPost['onglet']=$protectedGet['TAB'];


if ($protectedPost['onglet'] == "" or !isset($protectedPost['onglet']))
$protectedPost['onglet']=1;

$form_name="help";

if ($protectedPost['onglet'] == "" or !isset($protectedPost['onglet']))
$protectedPost['onglet']=1;
$html_support='www.ocsinventory-ng.com';
$port_support=80;
$http='http://';
$sock = @fsockopen($html_support, $port_support,$errno, $errstr, 1);
if($sock)
		fclose($sock);
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
	
}
elseif($protectedPost['onglet'] == 4){
		echo "<iframe  width=\"100%\" height=\"100%\" src=\"https://support.ocsinventory-ng.com\">
	</iframe>";
	
}elseif($protectedPost['onglet'] == 5){
	if (isset($_SESSION['OCS']['SUPPORT_KEY'])){
		$msg = $l->g(1286)."<br>";
		$msg .= "<img src='image/logo OCS-ng-96.png'><br>";
		$msg .= "<b><font color=GREEN>".$l->g(1287)."</font></b> - ".$l->g(1288).": <b><font color=GREEN>".$_SESSION['OCS']['SUPPORT_VALIDITYDATE']."</font></b><br> ";
		$msg .= $l->g(1289).": <br><b><big>".$_SESSION['OCS']['SUPPORT_KEY']."</big></b><br>";
		$msg .= $l->g(1290).": <b><big>".$_SESSION['OCS']['SUPPORT_EMAIL']."</big></b><br>";
		$msg .= $l->g(1291).": <b><big>".$_SESSION['OCS']['SUPPORT_DELIV']."</big></b><br>";
		msg_info($msg);
	}else{	
		$msg = $l->g(1286)." <br>";
		$msg .= "<img src='image/logo OCS-ng-96.png'><br>";
		$msg .= "<b><font color=RED>".$l->g(1292)."</font></b><br> ";
		msg_info($msg);
	}
	echo "<a href='http://".$html_support."' target='_blank'>".$l->g(1295)."</a>";
	
}
echo "</div>";
echo close_form();

?>



