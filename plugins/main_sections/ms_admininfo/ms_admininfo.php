<?php
/*
 * It Set Management light
 * Admin your accountinfo
 * 
 */
require_once('require/function_admininfo.php');
$array_tab_account=find_all_account_tab();
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
	 $hidden=$protectedPost['MODIF'];
}

echo "<br><form name='".$form_name."' id='".$form_name."' method='POST'>";
onglet($data_on,$form_name,"onglet",2);
echo '<div class="mlt_bordure" >';

	$table="accountinfo";



if ($protectedPost['onglet'] == 1){
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
	
	$queryDetails ="select ID,".implode(',',$array_fields)." from accountinfo_config";

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
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,100,$tab_options);
	//traitement par lot
	del_selection($form_name);
	
	//}	
	
}elseif ($protectedPost['onglet'] == 2){
	
	
	if (isset($protectedPost['MODIF_OLD']) 
		and is_numeric($protectedPost['MODIF_OLD']) 
		and $protectedPost['Valid_modif_x'] != ""){
		//UPDATE VALUE
		echo update_accountinfo($protectedPost['MODIF_OLD'],
								array('TYPE'=>$protectedPost['newtype'],
									  'NAME'=>$protectedPost['newfield'],
									  'COMMENT'=>$protectedPost['newlbl'],
								 	  'ID_TAB'=>$protectedPost['account_tab']));
		$hidden=$protectedPost['MODIF_OLD'];		
		
	}elseif( $protectedPost['Valid_modif_x'] != "" ) {
	//ADD NEW VALUE	
		echo add_accountinfo($protectedPost['newfield'],$protectedPost['newtype'],$protectedPost['newlbl'],$protectedPost['account_tab']);		
	}
	
		
	//NAME FIELD
	$name_field=array("newfield");
	$tab_name= array($l->g(1070).": ");
	if (isset($protectedPost['MODIF_OLD']) or $protectedPost['MODIF']!='')
		$type_field= array(3);
	else
		$type_field= array(0);
		
	$value_field=array($protectedPost['newfield']);
	
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
	$tab_typ_champ[0]['CONFIG']['SIZE']=30;
	$tab_typ_champ[1]['CONFIG']['SIZE']=30;
	$tab_typ_champ[3]['COMMENT_BEHING']="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_adminvalues']."&head=1&tag=TAB_ACCOUNTAG\",\"TAB_ACCOUNTAG\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")>+++</a>";

	tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=true,$form_name='NO_FORM');
}
echo "</div>"; 
echo "</form>";
?>

