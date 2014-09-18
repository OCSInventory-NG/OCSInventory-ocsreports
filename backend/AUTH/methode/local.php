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

 
connexion_local_read();
$reqOp="SELECT id,PASSWORD_VERSION FROM operators WHERE id='%s'";
$arg_reqOp=array($login);
$resOp=mysql2_query_secure($reqOp,$_SESSION['OCS']["readServer"],$arg_reqOp);
$rowOp=mysqli_fetch_object($resOp);
$oldpassword = false;
if ($_SESSION['OCS']['PASSWORD_VERSION']===false || $rowOp->PASSWORD_VERSION < $_SESSION['OCS']['PASSWORD_VERSION']){
	$oldpassword = true;
}

if($oldpassword && $rowOp->PASSWORD_VERSION === '0' ){
	$reqOp="SELECT id,user_group FROM operators WHERE id='%s' and passwd ='%s'";
	$arg_reqOp=array($login,md5($protectedMdp));
	$resOp=mysql2_query_secure($reqOp,$_SESSION['OCS']["readServer"],$arg_reqOp);
	$rowOp=mysqli_fetch_object($resOp);
	if (isset($rowOp -> id)){
		$login_successful = "OK";
		$user_group=$rowOp -> user_group;
		$type_log='CONNEXION';
		if(version_compare(PHP_VERSION, '5.3.7') >= 0){
			require_once('require/function_users.php');
			updatePassword($login,$mdp);
		}
	}else{
		
		$login_successful = $l->g(180);
		$type_log='BAD CONNEXION';
	}
}else{
	$reqOp="SELECT id,user_group,passwd FROM operators WHERE id='%s'";	
	$arg_reqOp=array($login);
	$resOp=mysql2_query_secure($reqOp,$_SESSION['OCS']["readServer"],$arg_reqOp);
	$rowOp=mysqli_fetch_object($resOp);
	if (isset($rowOp -> id) && password_verify($mdp, $rowOp -> passwd)){
		if($oldpassword){
			require_once('require/function_users.php');
			updatePassword($login,$mdp);
		}
		$login_successful = "OK";
		$user_group=$rowOp -> user_group;
		$type_log='CONNEXION';
	}else{
		$login_successful = $l->g(180);
		$type_log='BAD CONNEXION';
	}
}
$value_log='USER:'.$login;
$cnx_origine="LOCAL";
addLog( $type_log,$value_log);
?>