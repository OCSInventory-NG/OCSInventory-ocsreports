<?php

require_once('require/function_users.php');
if (isset($protectedPost['Valid_modif_x'])){
	$protectedPost['ACCESSLVL']=$_SESSION['OCS']['lvluser'];
	$protectedPost['ID']=$_SESSION['OCS']["loggeduser"];
	$protectedPost['MODIF']=$_SESSION['OCS']["loggeduser"];
	$msg=add_user($protectedPost);	
	if ($msg != $l->g(374))
		echo "<script>alert('".$msg."')</script>";
}
$form_name="pass";
echo "<br><form name=".$form_name." action=# method=post>";
admin_user('USER',$_SESSION['OCS']["loggeduser"]);
echo "</form>";
?>	