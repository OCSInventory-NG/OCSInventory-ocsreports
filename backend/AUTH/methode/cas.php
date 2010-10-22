<?php 
/*
 * Author: FranciX
 * http://forums.ocsinventory-ng.org/viewtopic.php?pid=30974
 * 
 * 
 * 
 */

require_once($_SESSION['OCS']['backend'].'require/lib/phpcas/CAS.php');
require_once($_SESSION['OCS']['backend'].'require/cas.config.php');
$cas=new phpCas();
$cas->client(CAS_VERSION_2_0,$cas_host,$cas_port,$cas_uri);
$cas->forceAuthentication();
$login = $cas->getUser();
$mdp = "";

if ($login){
	$login_successful = "OK";
	$cnx_origine="CAS";
	$user_group="CAS";
}
	
?>
