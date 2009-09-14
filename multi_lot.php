<?php
require ('fichierConf.class.php');
$form_name='admin_search';
$ban_head='no';
require_once("header.php");
if ($protectedPost['onglet'] != $protectedPost['old_onglet']){
	$onglet=$protectedPost['onglet'];
	$old_onglet=$protectedPost['old_onglet'];
	unset($protectedPost);
	$protectedPost['old_onglet']=$old_onglet;
	$protectedPost['onglet']=$onglet;
}
if ($protectedGet['origine']!= "mach" and $protectedGet['origine']!= "group"){
	if (isset($protectedGet['idchecked']) and $protectedGet['idchecked'] != ""){
		$choise_req_selection['REQ']=$l->g(584);
		$choise_req_selection['SEL']=$l->g(585);
		$select_choise=show_modif($choise_req_selection,'CHOISE',2,$form_name);	
	}
	echo "<font color=red><b>";
	if ($protectedPost['CHOISE'] == 'REQ' or $protectedGet['idchecked'] == '' or $protectedPost['CHOISE'] == ''){
		echo $l->g(901);
		$list_id=$_SESSION['ID_REQ'];
	}
	if ($protectedPost['CHOISE'] == 'SEL'){
		echo $l->g(902);
		$list_id=$protectedGet['idchecked'];
	}
	
	//gestion tableau
	if (is_array($list_id))
	$list_id=implode(",", $list_id);
}else
$list_id=$protectedGet['idchecked'];
echo "</b></font>";
if ($list_id != ""){
	if (strpos($protectedGet['img'], "config_search.png"))
		include ("opt_param.php");
	elseif (strpos($protectedGet['img'], "groups_search.png"))
		 include ("opt_groups.php");
 	elseif (strpos($protectedGet['img'], "tele_search.png"))
 		 include ("opt_pack.php");
 	elseif (strpos($protectedGet['img'], "sup_search.png"))
 		 include ("opt_sup.php");
 	elseif (strpos($protectedGet['img'], "cadena_ferme.png"))
 		 include ("opt_lock.php");
 	elseif(strpos($protectedGet['img'], "mass_affect.png"))
 		 include ("opt_tag.php");
 	else
 		return false;
}else
	echo "<br><br><b><font color=red size=4>".$l->g(954)."</font></b>";

?>
