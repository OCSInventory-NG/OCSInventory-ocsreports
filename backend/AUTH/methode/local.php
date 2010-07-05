<?php
/*
 * connexion en local
 * 
 */
 
 
connexion_local_read();
$reqOp="SELECT id,user_group FROM operators WHERE id='%s' and passwd ='%s'";
$arg_reqOp=array($login,md5($mdp));	
$resOp=mysql2_query_secure($reqOp,$_SESSION['OCS']["readServer"],$arg_reqOp);
$rowOp=mysql_fetch_object($resOp);
if (isset($rowOp -> id)){
	$login_successful = "OK";
	$user_group=$rowOp -> user_group;
}else
$login_successful = $l->g(216);
$cnx_origine="LOCAL";

?>