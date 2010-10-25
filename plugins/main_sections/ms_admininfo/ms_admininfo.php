<?php
/*
 * It Set Management light
 * Admin your accountinfo
 * 
 */
require_once('require/function_admininfo.php');
		
$accountinfo_choise['COMPUTERS']=$l->g(729);
$accountinfo_choise['SNMP']=$l->g(1136);
if (!isset($protectedPost['onglet']) or $protectedPost['onglet']=='')
	 $protectedPost['onglet'] = 1;
$form_name='admin_info';
$table_name=$form_name;
$data_on[1]=$l->g(1059);
$data_on[2]=$l->g(1060);

//$yes_no=array($l->g(454),$l->g(455));
if (isset($protectedPost['MODIF']) 
	and is_numeric($protectedPost['MODIF']) 
	and !isset($protectedPost['Valid_modif_x'])){
	 $protectedPost['onglet'] = 2;
	 $accountinfo_detail= find_info_accountinfo($protectedPost['MODIF']);
	 $protectedPost['newfield']=$accountinfo_detail[$protectedPost['MODIF']]['name'];
	 $protectedPost['newlbl']=$accountinfo_detail[$protectedPost['MODIF']]['comment'];
	 $protectedPost['newtype']=$accountinfo_detail[$protectedPost['MODIF']]['type'];
	 $protectedPost['account_tab']=$accountinfo_detail[$protectedPost['MODIF']]['id_tab'];
	 $protectedPost['accountinfo']=$accountinfo_detail[$protectedPost['MODIF']]['account_type'];
	 $hidden=$protectedPost['MODIF'];
}

if (isset($protectedPost['MODIF_OLD']) 
		and is_numeric($protectedPost['MODIF_OLD']) 
		and $protectedPost['Valid_modif_x'] != ""){
		//UPDATE VALUE
		$msg=update_accountinfo($protectedPost['MODIF_OLD'],
								array('TYPE'=>$protectedPost['newtype'],
									  'NAME'=>$protectedPost['newfield'],
									  'COMMENT'=>$protectedPost['newlbl'],
								 	  'ID_TAB'=>$protectedPost['account_tab']),$protectedPost['accountinfo']);
		$hidden=$protectedPost['MODIF_OLD'];		
	}elseif( $protectedPost['Valid_modif_x'] != "" ) {
	//ADD NEW VALUE	
		$msg=add_accountinfo($protectedPost['newfield'],
						$protectedPost['newtype'],
						$protectedPost['newlbl'],
						$protectedPost['account_tab'],
						$protectedPost['accountinfo']);	
	}
	
if (isset($msg['ERROR']))
	msg_error($msg['ERROR']);
if (isset($msg['SUCCESS'])){
	msg_success($msg['SUCCESS']);
	$protectedPost['onglet'] = 1;	
}	

echo "<br><form name='".$form_name."' id='".$form_name."' method='POST'>";
onglet($data_on,$form_name,"onglet",2);
echo '<div class="mlt_bordure" >';

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
	echo $l->g(56)." : ".show_modif($accountinfo_choise,'ACCOUNTINFO_CHOISE',2,$form_name,array('DEFAULT' => "NO"));
	
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
	
	$nb_result=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,100,$tab_options);
	//traitement par lot
	del_selection($form_name);
	
	//}	
	
}elseif ($protectedPost['onglet'] == 2){		
	//NAME FIELD
	
	$name_field=array("accountinfo","newfield");
	$tab_name= array($l->g(56).": ",$l->g(1070).": ");
	if (isset($protectedPost['MODIF_OLD']) or $protectedPost['MODIF']!=''){
		$type_field= array(3,3);
		$value_field=array($accountinfo_choise[$protectedPost['accountinfo']],$protectedPost['newfield']);
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


	$tab_typ_champ=show_field($name_field,$type_field,$value_field);
	$tab_typ_champ[1]['CONFIG']['SIZE']=30;
	$tab_typ_champ[2]['CONFIG']['SIZE']=30;
	$tab_typ_champ[4]['COMMENT_BEHING']="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_adminvalues']."&head=1&tag=".$account_field."\",\"".$account_field."\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")><img src=image/plus.png></a>";
	$tab_typ_champ[0]['RELOAD']=$form_name;
	tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=true,$form_name='NO_FORM');
}
echo "</div>"; 
echo "</form>";
?>

