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

 require_once(BACKEND.'require/connexion.php');
 
$list_methode=array(0=>"local.php");

if (!isset($_SESSION['OCS']["ipdiscover"])){
	$i=0;
	//methode pour le calcul des droits
	while ($list_methode[$i]){
		require_once('methode/'.$list_methode[$i]);
		//on garde les erreurs pr�sentes
		//entre chaque m�thode
		/*if (isset($INFO)){
			$tab_info[$list_methode[$i]]=$INFO;
			unset($INFO);
		}*/
		//on garde les droits de l'utilisateur sur l'ipdiscover
		if (isset($list_ip)){
			$tab_ip[$list_methode[$i]]=$list_ip;
			unset($list_ip);
		}
		$i++;
	}
	
}
unset($list_ip);
if (isset($tab_ip)){
	foreach ($list_methode as $prio=>$script){
		if (isset($tab_ip[$script])){
			foreach ($tab_ip[$script] as $ip=>$lbl){
					$list_ip[$ip]=$lbl;			
			}
		}
	}	
	if (isset($list_ip)){
		$_SESSION['OCS']["ipdiscover"]=$list_ip;	
		$_SESSION['OCS']["ipdiscover_methode"]=$base;	
		$_SESSION['OCS']["ipdiscover_id"]=$id_subnet;
	}
}
if (isset($tab_info) and !isset($_SESSION['OCS']["ipdiscover"])){
	$_SESSION['OCS']["ipdiscover"]=$tab_info;	
	$_SESSION['OCS']["ipdiscover_methode"]=$base;
}
?>
