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
//TELEDIFF_WK
$activate=option_conf_activate('TELEDIFF_WK');
if ($activate){
	echo "<font color = green><b>" . $l->g(1105) . "
			<br>" . $l->g(1108) . "</b></font>";
	//recherche du niveau d'affectation du paquet
	$conf_Wk=look_config_default_values(array('IT_SET_PERIM','IT_SET_NAME_TEST',
									   'IT_SET_NAME_LIMIT','IT_SET_TAG_NAME',
									   'IT_SET_NIV_TEST','IT_SET_NIV_REST'));
	//savoir comment sont définis les périmètres
	if ($conf_Wk['ivalue']['IT_SET_PERIM'] == 1){
		$perim='TAG';
		msg_warning($l->g(1190) . " " .$conf_Wk['tvalue']['IT_SET_NIV_REST']);
		msg_info($l->g(1191) . " " .$perim);
	}else{
		$perim='GROUPS';
		//si on vient de la page des groupes	
		if ($protectedGet['origine'] == "group"){
			//recherche des infos du groupe
			$queryMachine   = "SELECT REQUEST,
						  CREATE_TIME,
						  NAME,
						  XMLDEF,
						  DESCRIPTION,LASTDATE,OSCOMMENTS,DEVICEID FROM hardware h left join groups g on g.hardware_id=h.id 
				  WHERE ID='".$protectedGet['idchecked']."' AND (deviceid ='_SYSTEMGROUP_' or deviceid='_DOWNLOADGROUP_')";
			$result   = mysql_query( $queryMachine, $_SESSION['OCS']["readServer"] ) or mysql_error($_SESSION['OCS']["readServer"]);
			$item     = mysql_fetch_object($result);
			print_r($conf_Wk);
			$msg_wk="";
			//si ce groupe est défini comme un groupe de test
			if ($item->NAME == $conf_Wk['tvalue']['IT_SET_NAME_TEST']){
				$restrict=$conf_Wk['tvalue']['IT_SET_NIV_TEST'];
				$msg_wk="<br>".$l->g(1192);
			}		
			//si ce groupe est défini comme un groupe de périmètre restraint	
			if ($item->NAME == $conf_Wk['tvalue']['IT_SET_NAME_LIMIT']){
				if (!isset($restrict))
				$restrict=$conf_Wk['tvalue']['IT_SET_NIV_REST'];
				$msg_wk="<br>".$l->g(1193);
			}
			//si le groupe n'est pas pris en compte dans le 
			//système de workflow de télédiff
			if (!isset($restrict)){
				
				$msg_wk=$l->g(1194) . " " . $conf_Wk['tvalue']['IT_SET_NIV_REST'] . " " . $l->g(1195);
				
				
			}
			
		}

		
	}
	
		
	if ($msg_wk != '')
		msg_info($msg_wk);
	
}
$form_name="pack_affect";
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
$list_id=multi_lot($form_name,$l->g(601));

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
	if (isset($form_to_reload))
	echo "<script language='javascript'> window.opener.document.".$form_to_reload.".submit();</script>";
}
if ($protectedPost['sens'] == "")
	$protectedPost['sens']='DESC';


if ($protectedPost['onglet'] == "")
$protectedPost['onglet'] = 'MACH';


$def_onglets['MACH']=$l->g(980); //DYNAMICS GROUPS
$def_onglets['SERV_GROUP']=$l->g(981); //STATICS GROUPS

//show tab
if ($list_id){	
	onglet($def_onglets,$form_name,'onglet',7);
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
		$default_fields= array('PACK_NAME'=>'PACK_NAME','PRIORITY'=>'PRIORITY','OS_NAME'=>'OS_NAME','SIZE'=>'SIZE','SELECT'=>'SELECT');
		$list_col_cant_del=array('PACK_NAME'=>'PACK_NAME','SELECT'=>'SELECT');

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
		$tab_options['QUESTION']['SELECT']=$l->g(699);
		$tab_options['FILTRE']=array('e.FILEID'=>'Timestamp','a.NAME'=>$l->g(49));
		
		$result_exist=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$querypack,$form_name,100,$tab_options); 
	}
	echo "</td></tr></table>";
}

echo "</form>";
?>
