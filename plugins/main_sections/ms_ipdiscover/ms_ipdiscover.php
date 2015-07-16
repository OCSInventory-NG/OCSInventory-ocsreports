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
if(AJAX){  
		parse_str($protectedPost['ocs']['0'], $params);	
		$protectedPost+=$params; 
		
	ob_start();
	$ajax = true;
}
else{
	$ajax=false;
}



require_once('require/function_ipdiscover.php');
require_once("require/function_graphic.php");
if (!isset($_SESSION['OCS']["mac"]))
	loadMac();
	
printEntete($l->g(312));

 $form_name='ipdiscover';
 $tab_options=$protectedPost;
 $tab_options['form_name']=$form_name;
echo open_form($form_name);
 	//delete a subnet
 	if (isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != '' and $_SESSION['OCS']['profile']->getConfigValue('IPDISCOVER') == "YES"){
 		$sql_del="delete from subnet where netid='%s'";
 		$arg_del=$protectedPost['SUP_PROF'];
 		mysql2_query_secure($sql_del, $_SESSION['OCS']["writeServer"],$arg_del);
		//delete cache
		unset($_SESSION['OCS']["ipdiscover"]);
		require_once(BACKEND.'ipdiscover/ipdiscover.php');
 		$tab_options['CACHE']='RESET';
 	}
 	if (isset($_SESSION['OCS']["ipdiscover"])){
		$dpt=array_keys($_SESSION['OCS']["ipdiscover"]);
		array_unshift($dpt,"");
		foreach ($dpt as $key=>$value){
			$list_index[$key]=$value;
		}
		asort($list_index);
		echo $l->g(562)." ".show_modif($list_index,'DPT_CHOISE',2,$form_name,array('DEFAULT' => "NO"));
 	}else
 		msg_info(mb_strtoupper($l->g(1134)));
 		
	 if (isset($protectedPost['DPT_CHOISE']) and $protectedPost['DPT_CHOISE'] != '0'){
	 	
	 	$array_rsx=find_all_subnet($dpt[$protectedPost['DPT_CHOISE']]);
	 	
	 	$tab_options['VALUE']['LBL_RSX']=$_SESSION['OCS']["ipdiscover"][$dpt[$protectedPost['DPT_CHOISE']]];
		$arg_sql=array();
	 	$sql=" select * from (select inv.RSX as ID,
					  inv.c as 'INVENTORIE',
					  non_ident.c as 'NON_INVENTORIE',
					  ipdiscover.c as 'IPDISCOVER',
					  ident.c as 'IDENTIFIE',
					  CASE WHEN ident.c IS NULL and ipdiscover.c IS NULL THEN 100 WHEN ident.c IS NULL THEN 0 ELSE round(100-(non_ident.c*100/(ident.c+non_ident.c)),1) END as 'pourcentage'
			  from (SELECT COUNT(DISTINCT hardware_id) as c,'IPDISCOVER' as TYPE,tvalue as RSX
					FROM devices 
					WHERE name='IPDISCOVER' and tvalue in  ";
	 	$arg=mysql2_prepare($sql,$arg_sql,$array_rsx);
	 	$arg['SQL'] .= " GROUP BY tvalue) 
				ipdiscover right join
				   (SELECT count(distinct(hardware_id)) as c,'INVENTORIE' as TYPE,ipsubnet as RSX
					FROM networks left join subnet on networks.ipsubnet=subnet.netid
					WHERE ipsubnet in  ";
	 	$arg=mysql2_prepare($arg['SQL'],$arg['ARG'],$array_rsx);
	 	$arg['SQL'] .= " and status='Up' GROUP BY ipsubnet) 
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
				non_ident on non_ident.RSX=inv.RSX 
				) toto";
	$tab_options['ARG_SQL']=$arg['ARG'];
		$list_fields= array('LBL_RSX' => 'LBL_RSX',
							'RSX'=>'ID',
							'INVENTORIE'=>'INVENTORIE',
							'NON_INVENTORIE'=>'NON_INVENTORIE',
							'IPDISCOVER'=>'IPDISCOVER',
							'IDENTIFIE'=>'IDENTIFIE');
	if ($_SESSION['OCS']['profile']->getConfigValue('IPDISCOVER') == "YES")
	$list_fields['SUP']='ID';	
	$list_fields['PERCENT_BAR']='pourcentage';
	$table_name="IPDISCOVER";
	$tab_options['table_name']=$table_name;
	$default_fields= $list_fields;
	$list_col_cant_del=array('RSX'=>'RSX','SUP'=>'SUP');
	$tab_options['LIEN_LBL']['INVENTORIE']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_custom_info'].'&head=1&prov=inv&value=';
	$tab_options['LIEN_CHAMP']['INVENTORIE']='ID';
	$tab_options['LIEN_TYPE']['INVENTORIE']='POPUP';
	$tab_options['POPUP_SIZE']['INVENTORIE']="width=900,height=600";
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

	//you can modify your subnet if ipdiscover is local define
	if ( $_SESSION['OCS']["ipdiscover_methode"] == "OCS" and $_SESSION['OCS']['profile']->getConfigValue('IPDISCOVER') == "YES"){
		$tab_options['LIEN_LBL']['LBL_RSX']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_admin_ipdiscover'].'&head=1&value=';
		$tab_options['LIEN_CHAMP']['LBL_RSX']='ID';
		$tab_options['LIEN_TYPE']['LBL_RSX']='POPUP';
		$tab_options['POPUP_SIZE']['LBL_RSX']="width=700,height=500";
	}
	
	
	$tab_options['NO_LIEN_CHAMP']['IDENTIFIE']=array(0);
	$tab_options['NO_TRI']['LBL_RSX']='LBL_RSX';
	$val_count=count_noinv_network_devices($dpt[$protectedPost['DPT_CHOISE']]);

	$strEnTete = "<p>".$_SESSION['OCS']["ipdiscover_id"]." ".$dpt[$protectedPost['DPT_CHOISE']]." <br>";
		$strEnTete .= "(<font color='red'>".$val_count."</font> ".$l->g(219).")</p>";
		printEnTete($strEnTete);
	$result_exist=ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	}
echo close_form();


if ($ajax){
	ob_end_clean();
	tab_req($list_fields,$default_fields,$list_col_cant_del,$arg['SQL'],$tab_options);
}


?>
