<?php
require ('fichierConf.class.php');
$form_name='admin_search';
$ban_head='no';
require_once("header.php");
if ($ESC_POST['onglet'] != $ESC_POST['old_onglet']){
	$onglet=$ESC_POST['onglet'];
	$old_onglet=$ESC_POST['old_onglet'];
	unset($ESC_POST);
	$ESC_POST['old_onglet']=$old_onglet;
	$ESC_POST['onglet']=$onglet;
}
if ($ESC_GET['origine']!= "mach" and $ESC_GET['origine']!= "group"){
	if (isset($ESC_GET['idchecked']) and $ESC_GET['idchecked'] != ""){
		$choise_req_selection['REQ']=$l->g(584);
		$choise_req_selection['SEL']=$l->g(585);
		$select_choise=show_modif($choise_req_selection,'CHOISE',2,$form_name);	
	}
	echo "<font color=red><b>";
	if ($ESC_POST['CHOISE'] == 'REQ' or $ESC_GET['idchecked'] == '' or $ESC_POST['CHOISE'] == ''){
		echo $l->g(901);
		$list_id=$_SESSION['ID_REQ'];
	}
	if ($ESC_POST['CHOISE'] == 'SEL'){
		echo $l->g(902);
		$list_id=$ESC_GET['idchecked'];
	}
	
	//gestion tableau
	if (is_array($list_id))
	$list_id=implode(",", $list_id);
}else
$list_id=$ESC_GET['idchecked'];
echo "</b></font>";
if ($list_id != ""){
if (strpos($ESC_GET['img'], "config_search.png"))
include ("opt_param.php");
if (strpos($ESC_GET['img'], "groups_search.png"))
include ("opt_groups.php");
if (strpos($ESC_GET['img'], "tele_search.png"))
include ("opt_pack.php");
if (strpos($ESC_GET['img'], "sup_search.png"))
include ("opt_sup.php");
if (strpos($ESC_GET['img'], "cadena_ferme.png")){
include ("opt_lock.php");
}
}else
echo "<br><br><b><font color=red size=4>".$l->g(954)."</font></b>";

?>
