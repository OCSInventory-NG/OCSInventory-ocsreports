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

require_once 'require/function_users.php';

if (isset($protectedPost['Valid_modif'])) {
	$protectedPost['ACCESSLVL']=$_SESSION['OCS']['lvluser'];
	$protectedPost['ID']=$_SESSION['OCS']["loggeduser"];
	$protectedPost['MODIF']=$_SESSION['OCS']["loggeduser"];
	
	$msg = add_user($_POST, get_profile_labels());
	if ($msg != $l->g(374)) {
		msg_error($msg);
	} else {
		msg_success($l->g(1186));
	}
}

$form_name="pass";
echo open_form($form_name);

admin_user($_SESSION['OCS']["loggeduser"], true);

echo close_form();