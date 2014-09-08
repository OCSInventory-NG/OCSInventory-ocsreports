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

/* page de r�cup�ration en local des droits
 * et des tags sur lesquels l'utilisateur
 * a des droits
 * 
 * on doit renvoyer un tableau array('accesslvl'=>%%,'tag_show'=>array(%,%,%,%,%...))
 * si une erreur est rencontr�e, on retourne un code erreur
 * 
 */
	

require_once ('require/function_files.php');
//nom de la page
$name="local.php";
connexion_local_read();
mysqli_select_db($link_ocs,$db_ocs);

//recherche du niveau de droit de l'utilisateur
$reqOp="SELECT new_accesslvl as accesslvl FROM operators WHERE id='%s'";
$argOp=array($_SESSION['OCS']["loggeduser"]);
$resOp=mysql2_query_secure($reqOp,$link_ocs,$argOp);
$rowOp=mysqli_fetch_object($resOp);

if (isset($rowOp->accesslvl)) {
	$lvluser = $rowOp->accesslvl;

	$profile_config = DOCUMENT_REAL_ROOT.'config/profiles/'.$lvluser.'.xml';
	
	if (!file_exists($profile_config)) {
		migrate_config_2_2();
	}
	
	$profile_serializer = new XMLProfileSerializer();
	$profile = $profile_serializer->unserialize($lvluser, file_get_contents($profile_config));
	
	$restriction = $profile->getRestriction('GUI');
	
	//Si l'utilisateur a des droits limit�s
	//on va rechercher les tags sur lesquels il a des droits
	if ($restriction == 'YES') {
		$sql="select tag from tags where login='%s'";
		$arg=array($_SESSION['OCS']["loggeduser"]);
		$res=mysql2_query_secure($sql, $link_ocs,$arg);
		while ($row=mysqli_fetch_object($res)){	
			$list_tag[$row->tag]=$row->tag;
		}
		if (!isset($list_tag))
			$ERROR=$l->g(893);
	} elseif ($restriction != 'NO') {
		$ERROR=$restriction;
	}
} else {
	$ERROR=$l->g(894);
}

?>