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

/*
 * Add tags for users
 * 
 */
 

$form_name='taguser';
printEnTete($l->g(616)." ".$protectedGet["id"] );
if( $protectedPost['newtag'] != "" ) {
	$tab_options['CACHE']='RESET';
	$sql="insert into tags (tag,login) values ('%s','%s')";
	$arg=array($protectedPost["newtag"],$protectedGet["id"]);
	mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
	unset($protectedPost['newtag']);
}
//suppression d'une liste de tag
if (isset($protectedPost['del_check']) and $protectedPost['del_check'] != ''){
	$sql="DELETE FROM tags WHERE tag in ";
	$arg_sql=array();
	$sql=mysql2_prepare($sql,$arg_sql,$protectedPost['del_check']);
	$sql['SQL'].=" AND login='%s'";
	array_push($sql['ARG'],$protectedGet["id"]);
	mysql2_query_secure($sql['SQL'],$_SESSION['OCS']["writeServer"],$sql['ARG']);
	$tab_options['CACHE']='RESET';	
}

if(isset($protectedPost['SUP_PROF'])) {
	$sql="DELETE FROM tags WHERE tag='%s' AND login='%s'";
	$arg=array($protectedPost['SUP_PROF'],$protectedGet["id"]);
	mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
}
echo "<br><form name='".$form_name."' id='".$form_name."' method='POST'>";

if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
if (!(isset($protectedPost["pcparpage"])))
		 $protectedPost["pcparpage"]=5;
$list_fields= array($_SESSION['OCS']['TAG_LBL']['TAG']=>'tag',
					'SUP'=>'tag',
					'CHECK'=>'tag');
$list_col_cant_del=array('ID'=>'ID','SUP'=>'SUP','CHECK'=>'CHECK');
$default_fields=$list_fields; 
$sql=prepare_sql_tab($list_fields,array('SUP','CHECK'));
$sql['SQL'].= " FROM tags where login='%s'";
array_push($sql['ARG'],$protectedGet['id']);
$tab_options['ARG_SQL']=$sql['ARG'];
$tab_options['ARG_SQL_COUNT']=$protectedGet['id'];
$tab_options['FILTRE']=array($_SESSION['OCS']['TAG_LBL']['TAG']=>$_SESSION['OCS']['TAG_LBL']['TAG']);
//BEGIN SHOW ACCOUNTINFO
require_once('require/function_admininfo.php');
$info_tag=find_info_accountinfo('1','COMPUTERS');
if (is_array($info_tag)){
	foreach ($info_tag as $key=>$value){
		$info_value_tag= accountinfo_tab($value['id']);		
		if (is_array($info_value_tag)){
			$tab_options['REPLACE_VALUE'][$value['comment']]=$info_value_tag;
		}		
	}
}
//END SHOW ACCOUNTINFO
tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$form_name,100,$tab_options);
//traitement par lot
del_selection($form_name);
	
if (is_array($info_value_tag)){
	$type=2;
}else{
	$type=0;
	$info_value_tag=$protectedPost['newtag'];
}
	
$select_choise=show_modif($info_value_tag,'newtag',$type);	
echo $l->g(617)." ".$_SESSION['OCS']['TAG_LBL']['TAG'].": ".$select_choise;
echo "<input type='submit' name='ADD_TAG' value='" . $l->g(13) . "'>";
echo "</form>";
?>

