<?php
/*
 * Created on 4 juil. 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

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
		echo "<br><font color=red><b>Vous ne pouvez affecter des paquets 
					que de niveau supérieur à ".$conf_Wk['tvalue']['IT_SET_NIV_REST']."
					<br>Rappel: la configuration du workflow de télédéploiement est actuellement sur ".$perim."</b></font>";
	
	
	
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
				$msg_wk="<br>Groupe pouvant recevoir des paquets de TEST";
			}		
			//si ce groupe est défini comme un groupe de périmètre restraint	
			if ($item->NAME == $conf_Wk['tvalue']['IT_SET_NAME_LIMIT']){
				if (!isset($restrict))
				$restrict=$conf_Wk['tvalue']['IT_SET_NIV_REST'];
				$msg_wk.="<br>Groupe pouvant recevoir des paquets de périmètre RESTRAINT";
			}
			//si le groupe n'est pas pris en compte dans le 
			//système de workflow de télédiff
			if (!isset($restrict)){
				$msg_wk="Seuls les paquets de niveau supérieur à ".$conf_Wk['tvalue']['IT_SET_NIV_REST']." peuvent être affectés";
				
				
			}
			
			
			//if ($conf_Wk['tvalue']['IT_SET_NIV_TEST']==
			
			
			//on doit vérifier que l'on peut affecter les paquets de TEST ou de RESTRAINT 
		//	$protectedGet['idchecked']
			
		/*	echo "<br><font color=green><b>Seuls les paquets en statut > au ".$conf_Wk['tvalue']['IT_SET_NIV_TEST']."
					<br>peuvent être affectés sur ce groupe
					<br>Rappel: la configuration du workflow de télédéploiement est actuellement sur ".$perim."</b></font>";*/
			
		}

		
	}
	
		
	if ($msg_wk != '')
	echo "<br><font color=green><b>".$msg_wk."</b></font>";
	
}
$form_name="pack_affect";
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
$list_id=multi_lot($form_name,$l->g(601));

if ($protectedPost['SELECT'] != ''){
	if ($protectedPost['onglet'] == 'MACH')
	$nb_affect=active_mach($list_id,$protectedPost['SELECT']);
	if ($protectedPost['onglet'] == 'SERV_GROUP')
	$nb_affect=active_serv($list_id,$protectedPost['SELECT'],$protectedPost['rule_choise']);
	
	echo "<br><font color=green>".$nb_affect." ".$l->g(604)."</font>";

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
			echo "<font color=RED size=4>".$l->g(982)."</font>";
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
								'PACK_NAME'=>'a.NAME',
								'PRIORITY'=>'a.PRIORITY',
								'COMMENT'=>'a.COMMENT',
								'OS_NAME'=>'a.OSNAME',
								'SIZE'=>'a.SIZE'				
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
