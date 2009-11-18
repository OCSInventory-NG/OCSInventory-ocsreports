<?php
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
connexion_local();
mysql_select_db($db_ocs,$link_ocs);
//recherche du niveau de droit de l'utilisateur
$reqOp="SELECT new_accesslvl as accesslvl FROM operators WHERE id='".$_SESSION['OCS']["loggeduser"]."'";
$resOp=mysql_query($reqOp, $link_ocs) or die(mysql_error($link_ocs));
$rowOp=mysql_fetch_object($resOp);
if (isset($rowOp -> accesslvl)){
	$lvluser=$rowOp -> accesslvl;
	$ms_cfg_file=$_SESSION['OCS']['main_sections_dir'].$lvluser."_config.txt";
	$search=array('RESTRICTION'=>'SINGLE');
	$res=read_configuration($ms_cfg_file,$search);
	$restriction=$res['RESTRICTION'];
	//Si l'utilisateur a des droits limit�s
	//on va rechercher les tags sur lesquels il a des droits
	if ($restriction == 'YES'){
		$sql="select tag from tags where login='".$_SESSION['OCS']["loggeduser"]."'";
		$res=mysql_query($sql, $link_ocs) or die(mysql_error($link_ocs));
		while ($row=mysql_fetch_object($res)){	
			$list_tag[$row->tag]=$row->tag;
		}
		if (!isset($list_tag))
			$ERROR=$l->g(893);
	}elseif (($restriction != 'NO')) 
		$ERROR=$restriction;
}else
	$ERROR=$l->g(894);


?>