<?php
require_once('require/function_search.php');
PrintEnTete($l->g(976));
$form_name="lock_affect";
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''><div align=center>";
$list_id=multi_lot($form_name,$l->g(601));
if ($protectedPost['LOCK'] != '' and isset($protectedPost['LOCK'])){
	$_SESSION['OCS']["TRUE_mesmachines"]=$_SESSION['OCS']["mesmachines"];
	$_SESSION['OCS']["mesmachines"]=" a.hardware_id in (".$list_id.")";
	echo "<script language='javascript'> window.opener.document.multisearch.submit();self.close();</script>";
}

if ($protectedPost['CHOISE'] != ""){
	echo "<br><br><b>".$l->g(978)."</b>";
	echo "<br><br>".$l->g(979);
	echo "<br><br><input type='submit' value=" . $l->g(977) . " name='LOCK'>";
}
echo "</div></form>";//<input type=submit value='Supprimer TOUTES les machines?' name='delete'>

?>