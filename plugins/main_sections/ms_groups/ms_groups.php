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



if ((array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')){  
		parse_str($protectedPost['ocs']['0'], $params);	
		$protectedPost+=$params; 
		
	ob_start();
	$ajax = true;
}
else{
	$ajax=false;
}
/*
 * Page des groupes
 * 
 */ 
require_once('require/function_groups.php');
require_once('require/function_computers.php');
//ADD new static group
if($protectedPost['Valid_modif_x']){
	$result=creat_group ($protectedPost['NAME'],$protectedPost['DESCR'],'','','STATIC');
	if ($result['RESULT'] == "ERROR"){
		msg_error($result['LBL']);
		
	}elseif ($result['RESULT'] == "OK"){
		msg_success($result['LBL']);
		unset($protectedPost['add_static_group']);	
	}
	$tab_options['CACHE']='RESET';
}
//reset add static group
if ($protectedPost['Reset_modif_x'] or ($protectedPost['onglet'] != $protectedPost['old_onglet'])) 
 unset($protectedPost['add_static_group']); 
$tab_options=$protectedPost;
//view only your computers
if ($_SESSION['OCS']['profile']->getRestriction('GUI') == 'YES'){
	$mycomputers=computer_list_by_tag();
	if ($mycomputers == "ERROR"){
		msg_error($l->g(893));
		require_once(FOOTER_HTML);
		die();
	}
}
//View for all profils?
if (isset($protectedPost['CONFIRM_CHECK']) and  $protectedPost['CONFIRM_CHECK'] != "")
	$result=group_4_all($protectedPost['CONFIRM_CHECK']);

//if delete group
if ($protectedPost['SUP_PROF'] != ""){
	$result=delete_group($protectedPost['SUP_PROF']);	
	if ($result['RESULT'] == "ERROR")
	msg_error($result['LBL']);
	$tab_options['CACHE']='RESET';
}

$form_name='groups';
$tab_options['form_name']=$form_name;
echo open_form($form_name);
//view all groups
if ($_SESSION['OCS']['profile']->getConfigValue('GROUPS')=="YES"){
	$def_onglets['DYNA']=$l->g(810); //Dynamic group
	$def_onglets['STAT']=$l->g(809); //Static group centraux
	if ($_SESSION['OCS']["use_redistribution"] == 1)
		$def_onglets['SERV']=mb_strtoupper($l->g(651));
	if ($protectedPost['onglet'] == "")
	$protectedPost['onglet']="STAT";	
	//show onglet
	onglet($def_onglets,$form_name,"onglet",0);
	echo '<div class="mlt_bordure" >';


}else{	
	$protectedPost['onglet']="STAT";
}

$list_fields= array('GROUP_NAME'=>'h.NAME',
					'GROUP_ID' =>'h.ID',
						'DESCRIPTION'=>'h.DESCRIPTION',
						'CREATE'=>'h.LASTDATE',
						'NBRE'=>'NBRE');
//only for admins
if ($_SESSION['OCS']['profile']->getConfigValue('GROUPS')=="YES"){
	if ($protectedPost['onglet'] == "STAT")
		$list_fields['CHECK']= 'ID';
	$list_fields['SUP']= 'ID';	
	$tab_options['LBL_POPUP']['SUP']='NAME';
	$tab_options['LBL']['SUP']=$l->g(122);
}
//changement de nom � l'affichage des champs
$tab_options['LBL']['CHECK']=$l->g(52);
$tab_options['LBL']['GROUP_NAME']=$l->g(49);

$table_name="LIST_GROUPS";
$tab_options['table_name']=$table_name;
$default_fields= array('GROUP_NAME'=>'GROUP_NAME','DESCRIPTION'=>'DESCRIPTION','CREATE'=>'CREATE','NBRE'=>'NBRE','SUP'=>'SUP','CHECK'=>'CHECK');
$list_col_cant_del=array('GROUP_NAME'=>'GROUP_NAME','SUP'=>'SUP','CHECK'=>'CHECK');
$query=prepare_sql_tab($list_fields,array('SUP','CHECK','NBRE'));
$tab_options['ARG_SQL']=$query['ARG'];
$querygroup=$query['SQL'];

//requete pour les groupes de serveurs
if ($protectedPost['onglet'] == "SERV"){
	$querygroup .= " from hardware h,download_servers ds where ds.group_id=h.id and h.deviceid = '_DOWNLOADGROUP_'";	
	//calcul du nombre de machines par groupe de serveur
	$sql_nb_mach="SELECT count(*) nb, group_id
					from download_servers group by group_id";
}else{ //requete pour les groupes 'normaux'
	$querygroup .= " from hardware h,groups g ";
	$querygroup .="where g.hardware_id=h.id and h.deviceid = '_SYSTEMGROUP_' ";
	if ($protectedPost['onglet'] == "DYNA")
		$querygroup.=" and ((g.request is not null and trim(g.request) != '') 
							or (g.xmldef is not null and trim(g.xmldef) != ''))";
	elseif ($protectedPost['onglet'] == "STAT")
		$querygroup.=" and (g.request is null or trim(g.request) = '')
					    and (g.xmldef  is null or trim(g.xmldef) = '') ";
	if($_SESSION['OCS']['profile']->getConfigValue('GROUPS')!="YES")
		$querygroup.=" and h.workgroup='GROUP_4_ALL' ";

	//calcul du nombre de machines par groupe
	$sql_nb_mach="SELECT count(*) nb, group_id
					from groups_cache gc,hardware h where h.id=gc.hardware_id ";
	if($_SESSION['OCS']['profile']->getRestriction('GUI') == "YES")
			$sql_nb_mach.=" and gc.hardware_id in ".$mycomputers;		
	$sql_nb_mach .=" group by group_id";

}
$querygroup.=" group by h.ID";
$result = mysql2_query_secure($sql_nb_mach, $_SESSION['OCS']["readServer"]) ;
while($item = mysqli_fetch_object($result)){
	//on force les valeurs du champ "nombre" � l'affichage
	$tab_options['VALUE']['NBRE'][$item -> group_id]=$item -> nb;
}
	
//Modif ajout�e pour la prise en compte 
//du chiffre � rajouter dans la colonne de calcul
//quand on a un seul groupe et qu'aucune machine n'est dedant.
if (!isset($tab_options['VALUE']['NBRE']))
$tab_options['VALUE']['NBRE'][]=0;
//on recherche les groupes visible pour cocher la checkbox � l'affichage
if ($protectedPost['onglet'] == "STAT"){
	$sql="select id from hardware where workgroup='GROUP_4_ALL'";
	$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
	while($item = mysqli_fetch_object($result)){
		$protectedPost['check'.$item ->id]="check";
	}
}
//on ajoute un javascript lorsque l'on clic sur la visibilit� du groupe pour tous
$tab_options['JAVA']['CHECK']['NAME']="NAME";
$tab_options['JAVA']['CHECK']['QUESTION']=$l->g(811);
$tab_options['FILTRE']=array('NAME'=>$l->g(679),'DESCRIPTION'=>$l->g(53));
//affichage du tableau
$result_exist=ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);

//if your profil is an admin groups, you can create one
if ($_SESSION['OCS']['profile']->getConfigValue('GROUPS')=="YES"){
	echo "</td></tr></table>";	
	if ($protectedPost['onglet'] == "STAT")
		echo "<BR><input type='submit' name='add_static_group' value='".$l->g(587)."'>";
}

//if user want add a new group
if (isset($protectedPost['add_static_group']) and $_SESSION['OCS']['profile']->getConfigValue('GROUPS')=="YES"){
	//NAME FIELD
	$name_field[]="NAME";
	$tab_name[]=$l->g(577);
	$type_field[]=0;
	$value_field[]=$protectedPost['NAME'];
	$name_field[]="DESCR";
	$tab_name[]=$l->g(53);
	$type_field[]=1;
	$value_field[]=$protectedPost['DESCR'];
	$tab_typ_champ=show_field($name_field,$type_field,$value_field);
	$tab_typ_champ[0]['CONFIG']['SIZE']=20;
	$tab_hidden['add_static_group']='add_static_group';
	tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=true,$form_name='NO_FORM');
}
echo '</div>';
//fermeture du formulaire
echo close_form();


if ($ajax){
	ob_end_clean();
//print_r($tab_options);
	tab_req($list_fields,$default_fields,$list_col_cant_del,$querygroup,$tab_options);
}

?>
