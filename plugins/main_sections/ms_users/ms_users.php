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

if (AJAX) {
	parse_str($protectedPost['ocs']['0'], $params);
	$protectedPost += $params;
	ob_start();
	$ajax = true;
} else {
	$ajax = false;
	require_once 'views/users_views.php';
	
	show_users_left_menu('ms_users');
	
	echo '<div class="right-content">';
	echo '<div class="mlt_bordure">';
	echo '<h3>'.$l->g(1400).'</h3>';
}

require_once('require/function_search.php');
require_once('require/function_users.php');

// Définition des onglets
$profiles = get_profile_labels();
$data_on = $profiles;
$data_on[4]=$l->g(244);

if ($_SESSION['OCS']['profile']->getConfigValue('MANAGE_PROFIL') == 'YES')
	$data_on[5]=$l->g(1146);

$form_name = "admins";
$tab_options=$protectedPost;
$tab_options['form_name']=$form_name;
echo open_form($form_name);
$table_name="TAB_ACCESSLVL".$protectedPost['onglet'];

if ($protectedPost['onglet']==""){
	$protectedPost['onglet'] = current($data_on);
}

//suppression d'une liste de users
if (isset($protectedPost['del_check']) and $protectedPost['del_check'] != ''){
	delete_list_user($protectedPost['del_check']);
	$tab_options['CACHE']='RESET';
}

//suppression d'un user
if (isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != ''){
	delete_list_user($protectedPost['SUP_PROF']);
	$tab_options['CACHE']='RESET';
}

echo "<tr><td align=center>";
//affichage
$list_fields= array('ID'=>'ID',
		$l->g(1366)=>'FIRSTNAME',
		$l->g(996)=>'LASTNAME',
		$l->g(66)=>'NEW_ACCESSLVL',
		$l->g(51)=>'COMMENTS',
		$l->g(1117)=>'EMAIL',
		$l->g(607)=>'USER_GROUP',
		'SUP'=>'ID',
		'MOD_TAGS'=>'ID',
		'CHECK'=>'ID');
$list_col_cant_del=array('ID'=>'ID','SUP'=>'SUP','CHECK'=>'CHECK','MOD_TAGS'=>'MOD_TAGS' );
$default_fields= array('ID'=>'ID',
		$l->g(1366)=>'FIRSTNAME',
		$l->g(996)=>'LASTNAME',
		$l->g(66)=>'NEW_ACCESSLVL',
		$l->g(51)=>'COMMENTS',
		$l->g(607)=>'USER_GROUP',
		'SUP'=>'ID',
		'MOD_TAGS'=>'ID',
		'CHECK'=>'ID');
$queryDetails = 'SELECT ';
foreach ($list_fields as $key=>$value){
	if($key != 'SUP' and $key != 'CHECK' and $key != 'MOD_TAGS')
		$queryDetails .= $value.',';
}
$queryDetails=substr($queryDetails,0,-1);
$queryDetails .= " FROM operators";
// Tab options
$tab_options['FILTRE']=array('LASTNAME'=>'LASTNAME','ID'=>'ID','NEW_ACCESSLVL'=>'NEW_ACCESSLVL');
$tab_options['LIEN_LBL'][$l->g(1366)]='index.php?'.PAG_INDEX.'='.$pages_refs['ms_user_details'].'&user_id=';
$tab_options['LIEN_CHAMP'][$l->g(1366)]="ID";

// Tags edit
$tab_options['LBL']['MOD_TAGS']='Tags';
$tab_options['NO_TRI']['MOD_TAGS']=1;

$sql_user_groups="select IVALUE,TVALUE from config where name like 'USER_GROUP_%' ";
$res_user_groups = mysqli_query($_SESSION['OCS']["readServer"], $sql_user_groups );
while ($val_user_groups = mysqli_fetch_array( $res_user_groups ))
	$user_groups[$val_user_groups['IVALUE']]=$val_user_groups['TVALUE'];

$tab_options['REPLACE_VALUE'][$l->g(607)]=$user_groups;
$tab_options['LBL']['SUP']=$l->g(122);
$tab_options['LBL']['CHECK']=$l->g(1119);

$tab_options['table_name']=$table_name;
ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
//traitement par lot
$img['image/delete.png']=$l->g(162);
del_selection($form_name);

echo close_form();

if (AJAX) {
	ob_end_clean();
	tab_req($list_fields,$default_fields,$list_col_cant_del,$queryDetails,$tab_options);
} else {
	echo '</div>';
	echo '</div>';
}

?>
