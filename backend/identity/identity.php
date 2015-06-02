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
//$list_methode=array(0=>"ldap.php");
if (!isset($_SESSION['OCS']["lvluser"])){
	$i=0;
	//methode pour le calcul des droits
	while ($list_methode[$i]){
		require_once('methode/'.$list_methode[$i]);
		//on garde les erreurs pr�sentes
		//entre chaque m�thode
		if (isset($ERROR)){
			$tab_error[$list_methode[$i]]=$ERROR;
			unset($ERROR);
		}
		//on garde les tags qu'a le droit de voir l'utilisateur
		if (isset($list_tag)){
			$tab_tag[$list_methode[$i]]=$list_tag;
			unset($list_tag);
		}
		$i++;
	}
	
}

if (!isset($tab_tag) and $restriction != 'NO'){
	$LIST_ERROR="";
	foreach ($tab_error as $script=>$error){
			$LIST_ERROR.=$error;	
			addLog('ERROR_IDENTITY',$error);	
	}	
	$_SESSION['OCS']["mesmachines"] = "NOTAG";
}elseif(isset($tab_tag)){
	foreach ($list_methode as $prio=>$script){
		if (isset($tab_tag[$script])){
			foreach ($tab_tag[$script] as $tag=>$lbl){
				$list_tag[$tag]=$tag;	
				$lbl_list_tag[$tag]=$lbl;		
			}
		}
	}
	
	$mesMachines = "a.TAG IN ('".@implode("','",$list_tag)."') ";	
	$_SESSION['OCS']["mesmachines"] = $mesMachines;
	$_SESSION['OCS']["mytag"]=$lbl_list_tag;
	$_SESSION['OCS']['TAGS']=$list_tag;
}

if (isset($lvluser))
$_SESSION['OCS']["lvluser"]=$lvluser;


?>