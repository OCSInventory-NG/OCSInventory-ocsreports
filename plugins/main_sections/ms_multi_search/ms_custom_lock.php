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

require_once('require/function_search.php');
PrintEnTete($l->g(976));
$form_name="lock_affect";
echo open_form($form_name);
echo "<div align=center>";
$list_id=multi_lot($form_name,$l->g(601));
if ($protectedPost['LOCK'] != '' and isset($protectedPost['LOCK'])){
	if (isset($_SESSION['OCS']["mesmachines"]))
		$_SESSION['OCS']["TRUE_mesmachines"]=$_SESSION['OCS']["mesmachines"];
	else
		$_SESSION['OCS']["TRUE_mesmachines"]=array();
	$_SESSION['OCS']["mesmachines"]=" a.hardware_id in (".$list_id.")";
	echo "<script language='javascript'> window.opener.document.multisearch.submit();self.close();</script>";
}

if ($protectedPost['CHOISE'] != ""){
	echo "<br><br><b>".$l->g(978)."</b>";
	echo "<br><br>".$l->g(979);
	echo "<br><br><input type='submit' value=" . $l->g(977) . " name='LOCK'>";
}
echo "</div>";
echo close_form();

?>