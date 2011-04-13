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

require_once('require/function_telediff.php');
require_once('require/function_search.php');
require_once('require/function_telediff_wk.php');
$form_name="pack_affect";
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
$list_id=multi_lot($form_name,$l->g(601));
//TELEDIFF_WK
$activate=option_conf_activate('TELEDIFF_WK');
//use teledeploy workflow?
if ($activate){
	//yes
	$msg_wk = $l->g(1105) . "<br>" . $l->g(1110) . "<br>";
	//find all config of workflow
	$conf_Wk=look_config_default_values(array('IT_SET_PERIM','IT_SET_NAME_TEST',
									   'IT_SET_NAME_LIMIT','IT_SET_TAG_NAME',
									   'IT_SET_NIV_TEST','IT_SET_NIV_REST','IT_SET_NIV_TOTAL'));
	//find id field of status
	$sql_status="select ID from downloadwk_fields where FIELD='STATUS'";
	$res_status = mysql2_query_secure( $sql_status, $_SESSION['OCS']["readServer"] );
	$val_status = mysql_fetch_array($res_status);
	
	//find distinct id of status to affect a package
	$sql_id_stat="select NAME,ID from downloadwk_statut_request where NAME= '%s' or NAME='%s' or NAME = '%s'";
	$arg_id_stat=array($conf_Wk['tvalue']['IT_SET_NIV_TEST'],$conf_Wk['tvalue']['IT_SET_NIV_REST'],$conf_Wk['tvalue']['IT_SET_NIV_TOTAL']);
	$res_id_stat = mysql2_query_secure( $sql_id_stat, $_SESSION['OCS']["readServer"],$arg_id_stat );
	while( $val_id_stat = mysql_fetch_array($res_id_stat)) {
		$id_stat[$val_id_stat['NAME']]=$val_id_stat['ID'];
	}
	
	//find all package can be affected TEST status
	$sql_affect_pack="select fileid from download_available d_a 
										left join downloadwk_pack dwk_p	on d_a.id_wk=dwk_p.id 
										left join downloadwk_statut_request dwk_stat on dwk_stat.id=dwk_p.fields_".$val_status['ID']."
										where d_a.id_wk = 0 or dwk_stat.name = '%s'";
	$res_affect_pack = mysql2_query_secure( $sql_affect_pack, $_SESSION['OCS']["readServer"],$conf_Wk['tvalue']['IT_SET_NIV_TEST'] );
	while( $val_affect_pack = mysql_fetch_array($res_affect_pack)) {
		$fileid_test[$val_affect_pack["fileid"]]=$val_affect_pack["fileid"];
	}
	
	//find all package can be affected REST status
	$res_affect_pack = mysql2_query_secure( $sql_affect_pack, $_SESSION['OCS']["readServer"],$conf_Wk['tvalue']['IT_SET_NIV_REST'] );
	while( $val_affect_pack = mysql_fetch_array($res_affect_pack)) {
		$fileid_rest[$val_affect_pack["fileid"]]=$val_affect_pack["fileid"];
	}
	
	//find all package can be affected TOTAL status
	$res_affect_pack = mysql2_query_secure( $sql_affect_pack, $_SESSION['OCS']["readServer"],$conf_Wk['tvalue']['IT_SET_NIV_TOTAL'] );
	while( $val_affect_pack = mysql_fetch_array($res_affect_pack)) {
		$fileid_total[$val_affect_pack["fileid"]]=$val_affect_pack["fileid"];
	}
	$fileid_show=array();
	//can affect on groups or tag?
	if ($conf_Wk['ivalue']['IT_SET_PERIM'] == 1){
		$perim='TAG';
		require_once('require/function_admininfo.php');
		$allvalue_multi=accountinfo_tab($conf_Wk['tvalue']['IT_SET_TAG_NAME']);
		$info=find_info_accountinfo($conf_Wk['tvalue']['IT_SET_TAG_NAME']);
		if ($conf_Wk['tvalue']['IT_SET_TAG_NAME'] == 1){
			$field_acc='TAG';			
		}else
			$field_acc='fields_'.$conf_Wk['tvalue']['IT_SET_TAG_NAME'];

		if (is_array($allvalue_multi)){
			$conf_Wk['tvalue']['IT_SET_NAME_TEST'] = array_search($conf_Wk['tvalue']['IT_SET_NAME_TEST'], $allvalue_multi);
			$conf_Wk['tvalue']['IT_SET_NAME_LIMIT']= array_search($conf_Wk['tvalue']['IT_SET_NAME_LIMIT'], $allvalue_multi);
		}
		if ($list_id != ''){
			$sql="select %s,hardware_id from accountinfo where hardware_id in (%s)";
			$arg=array($field_acc,$list_id);
			$res = mysql2_query_secure( $sql, $_SESSION['OCS']["readServer"],$arg );
			while($val = mysql_fetch_array($res)){
				$fileid_show=array_merge($fileid_total,$fileid_show);
				if ($val[$field_acc] == $conf_Wk['tvalue']['IT_SET_NAME_TEST'])
					$fileid_show=array_merge($fileid_test,$fileid_show);
				if ($val[$field_acc] == $conf_Wk['tvalue']['IT_SET_NAME_LIMIT'])
					$fileid_show=array_merge($fileid_rest,$fileid_show);
				if ($val[$field_acc] != $conf_Wk['tvalue']['IT_SET_NAME_TEST']
					and $val[$field_acc] != $conf_Wk['tvalue']['IT_SET_NAME_LIMIT']){
						$fileid_show = array();
						break;
					}			
			}
			
		
			if ($fileid_show == array())
				$fileid_show=$fileid_total;
			
		}

	}else{
		$perim='GROUPS';
		//origine => group	
		if ($protectedGet['origine'] == "group"){
			//search info of this group
			$queryMachine   = "SELECT REQUEST,
						  CREATE_TIME,
						  NAME,
						  XMLDEF,
						  DESCRIPTION,LASTDATE,OSCOMMENTS,DEVICEID FROM hardware h left join groups g on g.hardware_id=h.id 
				  WHERE ID='%s' AND (deviceid ='_SYSTEMGROUP_' or deviceid='_DOWNLOADGROUP_')";
			$argMachine=$protectedGet['idchecked'];
			$result   = mysql2_query_secure($queryMachine, $_SESSION['OCS']["readServer"],$argMachine);
			$item     = mysql_fetch_object($result);

				
			$arg_affect_pack=array();
			//This group is define as TEST zone
			if ($item->NAME == $conf_Wk['tvalue']['IT_SET_NAME_TEST']){
				$restrict=$conf_Wk['tvalue']['IT_SET_NIV_TEST'];
				$msg_wk.=$l->g(1192)."<br>";
				array_push($arg_affect_pack,$restrict);
				$fileid_show=array_merge($fileid_test,$fileid_show);
			}		
			//This group is define as RESTRICT zone
			if ($item->NAME == $conf_Wk['tvalue']['IT_SET_NAME_LIMIT']){
				$restrict=$conf_Wk['tvalue']['IT_SET_NIV_REST'];
				$msg_wk.=$l->g(1193)."<br>";
				array_push($arg_affect_pack,$restrict);
				$fileid_show=array_merge($fileid_rest,$fileid_show);
			}
			//This group is not define for the teledeploy
			if (!isset($restrict)){				
				$msg_wk.=$l->g(1194) . " " . $conf_Wk['tvalue']['IT_SET_NIV_REST'] . " " . $l->g(1195);
			}
			$fileid_show=array_merge($fileid_total,$fileid_show);
			
		}

	}
		
	if (isset($msg_wk))
		msg_info($msg_wk);
	
}


if ($protectedPost['SELECT'] != ''){
	if ($protectedGet['origine'] == "group"){
		$form_to_reload='config_group';
	}elseif ($protectedGet['origine'] == "mach"){
		$form_to_reload='config_mach';
	}
	if ($protectedPost['onglet'] == 'MACH')
	$nb_affect=active_mach($list_id,$protectedPost['SELECT']);
	if ($protectedPost['onglet'] == 'SERV_GROUP')
	$nb_affect=active_serv($list_id,$protectedPost['SELECT'],$protectedPost['rule_choise']);
	msg_success($nb_affect." ".$l->g(604));
	if (isset($form_to_reload)){
		//add this $var => not delete this package on computer detail
		$_SESSION['OCS']["justAdded"]=true;
		echo "<script language='javascript'> window.opener.document.".$form_to_reload.".submit();</script>";
	}
}
if ($protectedPost['sens'] == "")
	$protectedPost['sens']='DESC';


if ($protectedPost['onglet'] == "")
$protectedPost['onglet'] = 'MACH';


$def_onglets['MACH']=$l->g(980); 
$def_onglets['SERV_GROUP']=$l->g(981); 

//show tab
if ($list_id){	
	onglet($def_onglets,$form_name,'onglet',7);
		echo '<div class="mlt_bordure" >';
	echo "<table ALIGN = 'Center' class='onglet'><tr><td align =center><tr><td align =center>";
	if ($protectedPost['onglet'] == 'SERV_GROUP'){
		$sql_rules="select distinct rule,rule_name from download_affect_rules order by 1";
			$res_rules = mysql_query( $sql_rules, $_SESSION['OCS']["readServer"] ) or die(mysql_error($_SESSION['OCS']["readServer"]));
			$nb_rule=0;
			while( $val_rules = mysql_fetch_array($res_rules)) {
				$first=$val_rules['rule'];
				$list_rules[$val_rules['rule']]=$val_rules['rule_name'];
				$nb_rule++;
			}
		if ($nb_rule>1){
		$select_choise=$l->g(668).show_modif($list_rules,'rule_choise',2,$form_name);	
		echo $select_choise;
		}elseif($nb_rule == 1){
			$protectedPost['rule_choise']=$first;
			echo "<input type=hidden value='".$first."' name='rule_choise' id='rule_choise'>";
		}elseif ($nb_rule == 0){
			msg_error($l->g(982));
		}
	}
	
	if(($protectedPost['onglet'] == 'MACH') 
		or ($protectedPost['onglet'] == 'SERV_GROUP' and $protectedPost['rule_choise'] != '')){
			//recherche de toutes les r�gles pour les serveurs de redistribution
		$list_fields= array('FILE_ID'=>'e.FILEID',
								'INFO_LOC'=>'e.INFO_LOC',
								'CERT_FILE'=>'e.CERT_FILE',
								'CERT_PATH'=>'e.CERT_PATH',
								//'PACK_LOC'=>'de.PACK_LOC',
								$l->g(1037)=>'a.NAME',
								$l->g(1039)=>'a.PRIORITY',
								$l->g(51)=>'a.COMMENT',
								$l->g(274)=>'a.OSNAME',
								$l->g(953)." (KB)"=>'a.SIZE'
								);
		
		if (!isset($nb_rule) or $nb_rule>0)	{
		if ($protectedPost['onglet'] != 'SERV_GROUP'){
			$list_fields['PACK_LOC']='e.PACK_LOC';	
			$list_fields['ACTIVE_ID']='e.ID';
			$list_fields['SELECT']='e.ID';
		}else{
			$list_fields['ACTIVE_ID']='e.FILEID';
			$list_fields['SELECT']='e.FILEID';
		}
	}		$table_name="LIST_PACK_SEARCH";//INSERT INTO devices(HARDWARE_ID, NAME, IVALUE) VALUES('".$val["h.id"]."', 'DOWNLOAD', $packid)
		$default_fields= array($l->g(1037)=>$l->g(1037),$l->g(1039)=>$l->g(1039),$l->g(274)=>$l->g(274),$l->g(953)." (KB)"=>$l->g(953)." (KB)",'SELECT'=>'SELECT');
		$list_col_cant_del=array($l->g(1037)=>$l->g(1037),'SELECT'=>'SELECT');

		if ($protectedPost['onglet'] != 'SERV_GROUP'){
			$default_fields['PACK_LOC']='PACK_LOC';
			$list_col_cant_del['PACK_LOC']='PACK_LOC';
		}

		$querypack = 'SELECT  ';
		if ($protectedPost['onglet'] == 'SERV_GROUP')
			$querypack .= ' distinct ';
		foreach ($list_fields as $key=>$value){
			if($key != 'SELECT')
			$querypack .= $value.',';		
		} 
		$querypack=substr($querypack,0,-1);
		$querypack .= " from download_available a, download_enable e ";
		if ($protectedPost['onglet'] == 'MACH')
		$querypack .= "where a.FILEID=e.FILEID and e.SERVER_ID is null ";
		else
		$querypack .= ", hardware h where a.FILEID=e.FILEID and h.id=e.group_id and  e.SERVER_ID is not null ";
		
		if (isset($fileid_show) and $fileid_show != array()){
			$sql=mysql2_prepare($querypack. " and a.FILEID IN ",'',$fileid_show,true);
			$tab_options['ARG_SQL']=$sql['ARG'];
			$querypack=$sql['SQL'];
		}
		
		$tab_options['QUESTION']['SELECT']=$l->g(699);
		$tab_options['FILTRE']=array('e.FILEID'=>'Timestamp','a.NAME'=>$l->g(49));
		
		$result_exist=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$querypack,$form_name,100,$tab_options); 
	}
	echo "</td></tr></table></div>";
}

echo "</form>";
?>
