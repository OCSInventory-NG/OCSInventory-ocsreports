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
 * It Set Management light
 * Admin your accountinfo
 * 
 */
if(AJAX){  
		parse_str($protectedPost['ocs']['0'], $params);	
		$protectedPost+=$params; 
		ob_start();

	$ajax = true;
}
else{
	$ajax=false;
}


require_once('require/function_admininfo.php');
		
$accountinfo_choise['COMPUTERS']=$l->g(729);
$accountinfo_choise['SNMP']=$l->g(1136);
if (!isset($protectedPost['onglet']) or $protectedPost['onglet']=='')
	 $protectedPost['onglet'] = 1;
$form_name='admin_info';
$table_name=$form_name;
$tab_options=$protectedPost;

$tab_options['form_name']=$form_name;
$tab_options['table_name']=$table_name;

$data_on[1]=$l->g(1059);
$data_on[2]=$l->g(1060);
//$yes_no=array($l->g(454),$l->g(455));
if (isset($protectedPost['MODIF']) 
	and is_numeric($protectedPost['MODIF']) 
	and !isset($protectedPost['Valid_modif'])){
	 $protectedPost['onglet'] = 2;
	 $accountinfo_detail= find_info_accountinfo($protectedPost['MODIF']);
	 $protectedPost['newfield']=$accountinfo_detail[$protectedPost['MODIF']]['name'];
	 $protectedPost['newlbl']=$accountinfo_detail[$protectedPost['MODIF']]['comment'];
	 $protectedPost['newtype']=$accountinfo_detail[$protectedPost['MODIF']]['type'];
	 $protectedPost['account_tab']=$accountinfo_detail[$protectedPost['MODIF']]['id_tab'];
	 $protectedPost['accountinfo']=$accountinfo_detail[$protectedPost['MODIF']]['account_type'];
	 $protectedPost['default_value']=$accountinfo_detail[$protectedPost['MODIF']]['default_value'];
	 $hidden=$protectedPost['MODIF'];
}

if (isset($protectedPost['MODIF_OLD']) 
		and is_numeric($protectedPost['MODIF_OLD']) 
		and $protectedPost['Valid_modif'] != ""){
		//UPDATE VALUE
		$msg=update_accountinfo($protectedPost['MODIF_OLD'],
								array('TYPE'=>$protectedPost['newtype'],
									  'NAME'=>$protectedPost['newfield'],
									  'COMMENT'=>$protectedPost['newlbl'],
								 	  'ID_TAB'=>$protectedPost['account_tab'],
									  'DEFAULT_VALUE'=>$protectedPost['default_value']),$protectedPost['accountinfo']);
		$hidden=$protectedPost['MODIF_OLD'];		
	}elseif( $protectedPost['Valid_modif'] != "" ) {
	//ADD NEW VALUE	
		$msg=add_accountinfo($protectedPost['newfield'],
						$protectedPost['newtype'],
						$protectedPost['newlbl'],
						$protectedPost['account_tab'],
						$protectedPost['accountinfo'],
						$protectedPost['default_value']);	
	}
	
if (isset($msg['ERROR']))
	msg_error($msg['ERROR']);
if (isset($msg['SUCCESS'])){
	msg_success($msg['SUCCESS']);
	$protectedPost['onglet'] = 1;	
}	

echo open_form($form_name);
show_tabs($data_on,$form_name,"onglet",2);
echo '<div class="right-content mlt_bordure" >';

	$table="accountinfo";

if ((isset($protectedPost['ACCOUNTINFO_CHOISE']) and $protectedPost['ACCOUNTINFO_CHOISE'] == 'SNMP' and $protectedPost['onglet'] == 1)
	or (isset($protectedPost['accountinfo']) and $protectedPost['accountinfo'] == 'SNMP' and $protectedPost['onglet'] == 2)){
		$array_tab_account=find_all_account_tab('TAB_ACCOUNTSNMP');
		$account_field="TAB_ACCOUNTSNMP";
	}
	else{
		$array_tab_account=find_all_account_tab('TAB_ACCOUNTAG');
		$account_field="TAB_ACCOUNTAG";
	}

if ($protectedPost['onglet'] == 1){		
	echo $l->g(56).": ".show_modif($accountinfo_choise,'ACCOUNTINFO_CHOISE',2,$form_name,array('DEFAULT' => "NO"));
	
	if ($protectedPost['ACCOUNTINFO_CHOISE'] == "SNMP")
		$account_choise = "SNMP";
	else
		$account_choise = "COMPUTERS";
		
	$tab_options['CACHE']='RESET';
	if (isset($protectedPost['del_check']) and $protectedPost['del_check'] != ''){		
		$list = $protectedPost['del_check'];
		$tab_values=explode(',',$list);
		$i=0;
		while($tab_values[$i]){
			del_accountinfo($tab_values[$i]);
			$i++;				
		}				
	}
	
	if(isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != '') {
		del_accountinfo($protectedPost['SUP_PROF']);
	}	
	$array_fields=array($l->g(1098)=>'NAME',
						$l->g(1063)=>'COMMENT',
						$l->g(66)=>'TYPE',
						$l->g(1061)=>'ID_TAB');						
	
	$queryDetails ="select ID,".implode(',',$array_fields)." from accountinfo_config where ACCOUNT_TYPE = '".$account_choise."'";
	
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	if (!(isset($protectedPost["pcparpage"])))
		 $protectedPost["pcparpage"]=10;

	$list_fields= $array_fields;

	$list_fields['SUP']='ID';
	$list_fields['CHECK']='ID'; 
	$list_fields['MODIF']='ID'; 
	$list_col_cant_del=array($l->g(1063)=>$l->g(1063),$l->g(66)=>$l->g(66),$l->g(1061)=>$l->g(1061),'SUP'=>'SUP','CHECK'=>'CHECK','MODIF'=>'MODIF');
	$default_fields=$list_col_cant_del; 
	$tab_options['REPLACE_VALUE'][$l->g(66)]=$type_accountinfo;
	$tab_options['REPLACE_VALUE'][$l->g(1061)]=$array_tab_account;
	$tab_options['LBL_POPUP']['SUP']='NAME';
	$tab_options['REQUEST']['SUP']="select name_accountinfo AS FIRST from accountinfo_config where ACCOUNT_TYPE = '".$account_choise."'";
	$tab_options['FIELD']['SUP']='NAME';
	$tab_options['EXIST']['SUP']='NAME';
	$tab_options['REQUEST']['CHECK']="select name_accountinfo AS FIRST from accountinfo_config where ACCOUNT_TYPE = '".$account_choise."'";
	$tab_options['FIELD']['CHECK']='NAME';
	$tab_options['EXIST']['CHECK']='NAME';
	$nb_result=ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	//traitement par lot
	del_selection($form_name);
	
	//}	
	
}elseif ($protectedPost['onglet'] == 2){		
	//NAME FIELD
	$config['JAVASCRIPT'][1]=$sql_field;
	$name_field=array("accountinfo","newfield");
	$tab_name= array($l->g(56).": ",$l->g(1070).": ");
	if (isset($protectedPost['MODIF_OLD']) or $protectedPost['MODIF']!=''){
		$hidden=($protectedPost['MODIF'] != '' ? $protectedPost['MODIF']:$protectedPost['MODIF_OLD']);
		$type_field= array(3,3);
		$value_field=array($protectedPost['accountinfo'],$protectedPost['newfield']);
	}
	else{
		$type_field= array(2,0);		
		$value_field=array($accountinfo_choise,$protectedPost['newfield']);
	}
	
	if ( isset($hidden) and is_numeric($hidden)){
		$tab_hidden['MODIF_OLD']=$hidden;		
	}
	
	//if (isset($protectedGet['admin'])){
	array_push($name_field,"newlbl");
	array_push($tab_name,$l->g(80).":");
	array_push($type_field,0);
	array_push($value_field,$protectedPost['newlbl']);
		
	array_push($name_field,"newtype");
	array_push($tab_name,$l->g(1071).":");
	array_push($type_field,2);
	array_push($value_field,$type_accountinfo);	
	

	array_push($name_field,"account_tab");
	array_push($tab_name,$l->g(1061).":");
	array_push($type_field,2);
	array_push($value_field,$array_tab_account);

	if ($protectedPost['newtype']==8){ //for QRCODE type
		array_push($name_field,"default_value");
		array_push($tab_name,$l->g(1099).":");
		array_push($type_field,2);
		array_push($value_field,$array_qr_values);
		
	}

	$tab_typ_champ=show_field($name_field,$type_field,$value_field,$config);
	$tab_typ_champ[1]['CONFIG']['SIZE']=30;
	$tab_typ_champ[2]['CONFIG']['SIZE']=30;
	$tab_typ_champ[4]['COMMENT_AFTER']="<a href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_adminvalues']."&head=1&tag=".$account_field."&form=".$form_name."\"><img src=image/plus.png></a>";
	$tab_typ_champ[0]['RELOAD']=$form_name;
	$tab_typ_champ[3]['RELOAD']=$form_name;
	tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden, array(
		'form_name' => 'NO_FORM',
		'show_frame' => false
	));
}
echo "</div>"; 
echo close_form();

if ($ajax){
	ob_end_clean();
	tab_req($list_fields,$default_fields,$list_col_cant_del,$queryDetails,$tab_options);
}
?>

