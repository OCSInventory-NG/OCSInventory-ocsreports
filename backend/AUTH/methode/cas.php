<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft FranciX 2010
//http://forums.ocsinventory-ng.org/viewtopic.php?pid=30974
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================


require_once(BACKEND.'require/lib/phpcas/CAS.php');
require_once(BACKEND.'require/cas.config.php');
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
