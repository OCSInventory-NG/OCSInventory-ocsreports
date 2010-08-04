<?php
/*
 * Created on 7 mai 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once('require/function_ipdiscover.php');

if (!isset($_SESSION['OCS']["mac"]))
	loadMac();
	
 $form_name='ipdiscover';
 echo "<form name='".$form_name."' id='".$form_name."' action='' method='post'>";
 	//suppression d'un sous-reseau
 	if (isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != '' and $_SESSION['OCS']['CONFIGURATION']['IPDISCOVER'] == "YES"){
 	//	$del=mysql_real_escape_string($protectedPost['SUP_PROF']);
 		$sql_del="delete from subnet where netid='%s'";
 		$arg_del=$protectedPost['SUP_PROF'];
 		mysql2_query_secure($sql_del, $_SESSION['OCS']["writeServer"],$arg_del);
		//suppression du cache pour prendre en compte la modif
		unset($_SESSION['OCS']["ipdiscover"]);
		require_once($_SESSION['OCS']['backend'].'/ipdiscover/ipdiscover.php');
 		$tab_options['CACHE']='RESET';
 	}
 	if (isset($_SESSION['OCS']["ipdiscover"]) and !$_SESSION['OCS']["ipdiscover"]['local.php']){
		ksort($_SESSION['OCS']["ipdiscover"]);
		$dpt=array_keys($_SESSION['OCS']["ipdiscover"]);
		array_unshift($dpt,"");
		unset($dpt[0]);
		foreach ($dpt as $key=>$value){
			$list_index[$key]=$value;
		}
		 echo $l->g(562)." ".show_modif($list_index,'DPT_CHOISE',2,$form_name);
 	}else
 		echo "<font color=red>" . strtoupper($l->g(1134)) . "</font><br>";
	 if (isset($protectedPost['DPT_CHOISE']) and $protectedPost['DPT_CHOISE'] != ''){
	 	
	 	$array_rsx=array_keys($_SESSION['OCS']["ipdiscover"][$dpt[$protectedPost['DPT_CHOISE']]]);
	 	
	 	//print_r($_SESSION['OCS']["ipdiscover"][$dpt[$protectedPost['DPT_CHOISE']]]);
	 	$tab_options['VALUE']['LBL_RSX']=$_SESSION['OCS']["ipdiscover"][$dpt[$protectedPost['DPT_CHOISE']]];
		$arg_sql=array();
	 	$sql=" select * from (select inv.RSX as ID,
					  inv.c as 'INVENTORIE',
					  non_ident.c as 'NON_INVENTORIE',
					  ipdiscover.c as 'IPDISCOVER',
					  ident.c as 'IDENTIFIE',
					  round(100-(non_ident.c*100/(ident.c+non_ident.c)),1) as 'pourcentage'
			  from (SELECT COUNT(DISTINCT hardware_id) as c,'IPDISCOVER' as TYPE,tvalue as RSX
					FROM devices 
					WHERE name='IPDISCOVER' and tvalue in  ";
	 	$arg=mysql2_prepare($sql,$arg_sql,$array_rsx);
	 	$arg['SQL'] .= " GROUP BY tvalue) 
				ipdiscover right join
				   (SELECT count(distinct(id)) as c,'INVENTORIE' as TYPE,ipsubnet as RSX
					FROM networks 
					WHERE ipsubnet in  ";
	 	$arg=mysql2_prepare($arg['SQL'],$arg['ARG'],$array_rsx);
	 	$arg['SQL'] .= " GROUP BY ipsubnet) 
				inv on ipdiscover.RSX=inv.RSX left join
					(SELECT COUNT(DISTINCT mac) as c,'IDENTIFIE' as TYPE,netid as RSX
					FROM netmap 
					WHERE mac IN (SELECT DISTINCT(macaddr) FROM network_devices) 
						and netid in  ";
	 	$arg=mysql2_prepare($arg['SQL'],$arg['ARG'],$array_rsx);
	 	$arg['SQL'] .= " GROUP BY netid) 
				ident on ipdiscover.RSX=ident.RSX left join
					(SELECT COUNT(DISTINCT mac) as c,'NON IDENTIFIE' as TYPE,netid as RSX
					FROM netmap n
					LEFT JOIN networks ns ON ns.macaddr=n.mac
					WHERE n.mac NOT IN (SELECT DISTINCT(macaddr) FROM network_devices) 
						and (ns.macaddr IS NULL OR ns.IPSUBNET <> n.netid) 
						and n.netid in  ";
	 	$arg=mysql2_prepare($arg['SQL'],$arg['ARG'],$array_rsx);
	 	$arg['SQL'] .= " GROUP BY netid) 
				non_ident on non_ident.RSX=ipdiscover.RSX 
				) toto";
	$tab_options['ARG_SQL']=$arg['ARG'];
		$list_fields= array('LBL_RSX' => 'LBL_RSX','RSX'=>'ID',
								'INVENTORIE'=>'INVENTORIE',
								'NON_INVENTORIE'=>'NON_INVENTORIE',
								'IPDISCOVER'=>'IPDISCOVER',
								'IDENTIFIE'=>'IDENTIFIE');
	if ($_SESSION['OCS']['CONFIGURATION']['IPDISCOVER'] == "YES")
	$list_fields['SUP']='ID';	
	$list_fields['PERCENT_BAR']='pourcentage';
	$table_name="IPDISCOVER";
	$default_fields= $list_fields;
	$list_col_cant_del=array('RSX'=>'RSX','SUP'=>'SUP');
	$tab_options['LIEN_LBL']['INVENTORIE']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_multi_search'].'&prov=ipdiscover&value=';
	$tab_options['LIEN_CHAMP']['INVENTORIE']='ID';
	$tab_options['NO_LIEN_CHAMP']['INVENTORIE']=array(0);
	
	$tab_options['LIEN_LBL']['IPDISCOVER']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_multi_search'].'&prov=ipdiscover1&value=';
	$tab_options['LIEN_CHAMP']['IPDISCOVER']='ID';
	$tab_options['NO_LIEN_CHAMP']['IPDISCOVER']=array(0);
	
	$tab_options['LIEN_LBL']['NON_INVENTORIE']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_custom_info'].'&prov=no_inv&head=1&value=';
	$tab_options['LIEN_CHAMP']['NON_INVENTORIE']='ID';
	$tab_options['LIEN_TYPE']['NON_INVENTORIE']='POPUP';
	$tab_options['POPUP_SIZE']['NON_INVENTORIE']="width=900,height=600";
	$tab_options['NO_LIEN_CHAMP']['NON_INVENTORIE']=array(0);
	
	$tab_options['LIEN_LBL']['IDENTIFIE']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_custom_info'].'&prov=ident&head=1&value=';
	$tab_options['LIEN_CHAMP']['IDENTIFIE']='ID';
	$tab_options['LIEN_TYPE']['IDENTIFIE']='POPUP';
	$tab_options['POPUP_SIZE']['IDENTIFIE']="width=900,height=600";
	
	$tab_options['REPLACE_WITH_CONDITION']['INVENTORIE']['&nbsp']='0';
	$tab_options['REPLACE_WITH_CONDITION']['IPDISCOVER']['&nbsp']='0';
	$tab_options['REPLACE_WITH_CONDITION']['NON_INVENTORIE']['&nbsp']='0';
	$tab_options['REPLACE_WITH_CONDITION']['IDENTIFIE']['&nbsp']='0';
	
	
	$tab_options['REPLACE_WITH_CONDITION']['PERCENT_BAR']['&nbsp']=array('IDENTIFIE'=>'0','NON_INVENTORIE'=>'100');
	
	
	
	$tab_options['LBL']['LBL_RSX']=$l->g(863);
	$tab_options['LBL']['RSX']=$l->g(869);
	$tab_options['LBL']['INVENTORIE']=$l->g(364);
	$tab_options['LBL']['NON_INVENTORIE']=$l->g(365);
	$tab_options['LBL']['IPDISCOVER']=$l->g(312);
	$tab_options['LBL']['IDENTIFIE']=$l->g(366);
	$tab_options['LBL']['PERCENT_BAR']=$l->g(1125);

	//mise a jour possible des r�seaux si on travaille sur le r�f�rentiel local
	if ( $_SESSION['OCS']["ipdiscover_methode"] == "OCS" and $_SESSION['OCS']['CONFIGURATION']['IPDISCOVER'] == "YES"){
		$tab_options['LIEN_LBL']['LBL_RSX']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_admin_ipdiscover'].'&head=1&value=';
		$tab_options['LIEN_CHAMP']['LBL_RSX']='ID';
		$tab_options['LIEN_TYPE']['LBL_RSX']='POPUP';
		$tab_options['POPUP_SIZE']['LBL_RSX']="width=700,height=500";
	}
	
	
	$tab_options['NO_LIEN_CHAMP']['IDENTIFIE']=array(0);
	$tab_options['NO_TRI']['LBL_RSX']='LBL_RSX';
	$arg_count=array();
		$sql_count="SELECT COUNT(DISTINCT mac) as total
					FROM netmap n 
					LEFT OUTER JOIN networks ns ON ns.macaddr = mac 
					WHERE mac NOT IN (SELECT DISTINCT(macaddr) FROM network_devices) 
						and ( ns.macaddr IS NULL OR ns.IPSUBNET <> n.netid)
						and netid in ";
	$detail_query=mysql2_prepare($sql_count,$arg_count,$array_rsx);
	$res_count = mysql2_query_secure($detail_query['SQL'], $_SESSION['OCS']["readServer"],$detail_query['ARG']);
	$val_count = mysql_fetch_array( $res_count );

	$strEnTete = $_SESSION['OCS']["ipdiscover_id"]." ".$dpt[$protectedPost['DPT_CHOISE']]." <br>";
		$strEnTete .= "<br>(<font color='red'>".$val_count["total"]."</font> ".$l->g(219).")";
		echo "<br><br>";	
		printEnTete($strEnTete);
		echo "<br><br>";
	$result_exist=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$arg['SQL'] ,$form_name,80,$tab_options); 

}


	

echo "</form>";
?>
