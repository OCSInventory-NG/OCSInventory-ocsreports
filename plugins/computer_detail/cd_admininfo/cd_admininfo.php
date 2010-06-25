<?php 
require_once('require/function_admininfo.php');

$list_tab=find_all_account_tab(1,1);	
if ($list_tab != ''){
	if ($protectedPost['Valid_modif_x'] != ""){
		foreach ($protectedPost as $field=>$value){
			if (substr_count($field, 'fields_')){
				$temp_field=explode('_',$field);
				//cas of checkbox
				if (isset($temp_field[2]))
				$data_fields_account[$temp_field[0] . "_" . $temp_field[1]].=$temp_field[2] . "&&&";	
				else
				$data_fields_account[$field]=$value;	
	
			}
		}
		updateinfo_computer($systemid,$data_fields_account);	
	}
	
	$info_account_id=admininfo_computer($systemid);
	
	if (!isset($protectedPost['onglet']) or $protectedPost['onglet'] =='' or !is_numeric($protectedPost['onglet']))
		 $protectedPost['onglet'] = $list_tab['FIRST'];
	unset($list_tab['FIRST']);
	$form_name='admin_info_computer';
	$table_name=$form_name;	
	
	echo "<br><form name='".$form_name."' id='".$form_name."' method='POST'>";
	onglet($list_tab,$form_name,"onglet",6);
	echo '<div class="mlt_bordure" >';
	$sql_admin_info="select ID,TYPE,NAME,COMMENT from accountinfo_config where ID_TAB = %s";
	$arg_admin_info=array($protectedPost['onglet']);
	$res_admin_info=mysql2_query_secure($sql_admin_info,$_SESSION['OCS']["readServer"],$arg_admin_info);
	
	$name_field=array();
	$tab_name=array();
	$type_field=array();
	$value_field=array();
	$config['COMMENT_BEHING']=array();
	$config['SELECT_DEFAULT']=array();
	$config['JAVASCRIPT']=array();
	$config['SIZE']=array();
	
	
	
	while ($val_admin_info = mysql_fetch_array( $res_admin_info )){
			if ($val_admin_info['TYPE'] == 2 
				or $val_admin_info['TYPE'] == 4
				or $val_admin_info['TYPE'] == 7){
				array_push($config['JAVASCRIPT'],'');
				array_push($config['SIZE'],'');
				if ($_SESSION['OCS']['CONFIGURATION']['ACCOUNTINFO'] == 'YES')
					array_push($config['COMMENT_BEHING'],"<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_adminvalues']."&head=1&tag=ACCOUNT_VALUE_" . $val_admin_info['NAME'] . "\",\"ACCOUNT_VALUE\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")>+++</a>");
					
				array_push($config['SELECT_DEFAULT'],'YES');
				$field_select_values=find_value_field($val_admin_info['NAME']);
				array_push($value_field,$field_select_values);
				//cas of checkbox
				if ($val_admin_info['TYPE'] == 4){
				$temp_val=explode('&&&',$info_account_id['fields_' . $val_admin_info['ID']]);
				$i=0;
				while (isset($temp_val[$i])){
					$protectedPost['fields_' . $val_admin_info['ID'] . '_' . $temp_val[$i]]='on';
					$i++;			
				}
				
				}else
				$protectedPost['fields_' . $val_admin_info['ID']]=$info_account_id['fields_' . $val_admin_info['ID']];		
	
			}elseif ($val_admin_info['TYPE'] == 6){	
				array_push($value_field,$info_account_id['fields_' . $val_admin_info['ID']]);
				array_push($config['COMMENT_BEHING'],datePick('fields_' . $val_admin_info['ID']));
				array_push($config['JAVASCRIPT'],"READONLY ".dateOnClick('fields_' . $val_admin_info['ID']));
				array_push($config['SELECT_DEFAULT'],'');
				array_push($config['SIZE'],'8');	
			}else{
				array_push($value_field,$info_account_id['fields_' . $val_admin_info['ID']]);
				array_push($config['COMMENT_BEHING'],'');
				array_push($config['SELECT_DEFAULT'],'');
				array_push($config['JAVASCRIPT'],'');
				array_push($config['SIZE'],'');
			}
			
			array_push($name_field,'fields_' . $val_admin_info['ID']);
			array_push($tab_name,$val_admin_info['COMMENT']);
			if ($_SESSION['OCS']['CONFIGURATION']['CHANGE_ACCOUNTINFO'] == 'YES')
				array_push($type_field,$convert_type[$val_admin_info['TYPE']]);
			else
				array_push($type_field,3);
	
	}	
	
		$tab_typ_champ=show_field($name_field,$type_field,$value_field,$config);
		tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=true,$form_name='NO_FORM');
	
	echo "</div>"; 
	echo "</form>";
}

/*
	$list_fields=array();
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';


	$form_name="affich_tag";
	$table_name=$form_name;
	if (isset($protectedPost['Valid_modif_x'])){
		if ($protectedPost['TAG_MODIF'] == $_SESSION['OCS']['TAG_LBL'])
		$lbl_champ='TAG';
		else
		$lbl_champ=$protectedPost['TAG_MODIF'];
		$sql=" update accountinfo set ".$lbl_champ."='";
		if ($protectedPost['FIELD_FORMAT'] == "date")
		$sql.= dateToMysql($protectedPost['NEW_VALUE'])."'";
		else
		$sql.= $protectedPost['NEW_VALUE']."'";
		$sql.=" where hardware_id=".$systemid; 
		mysql_query($sql, $_SESSION['OCS']["writeServer"]);
		//reg�n�ration du cache
		$tab_options['CACHE']='RESET';
	}
	
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";

	$queryDetails = "SELECT * FROM accountinfo WHERE hardware_id=$systemid";
	$resultDetails = mysql_query($queryDetails, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
	$item=mysql_fetch_array($resultDetails,MYSQL_ASSOC);
	$i=0;
	$queryDetails = "";
	while (@mysql_field_name($resultDetails,$i)){
		if(mysql_field_type($resultDetails,$i)=="date"){
			//echo dateFromMysql($item[mysql_field_name($resultDetails,$i)])." => ".mysql_field_name($resultDetails,$i);
			$value = "'".dateFromMysql($item[mysql_field_name($resultDetails,$i)])."'";
		}else
			$value = mysql_field_name($resultDetails,$i);
		$lbl=mysql_field_name($resultDetails,$i);	
		if ($lbl != 'HARDWARE_ID'){
			if ($lbl == 'TAG')
			$lbl=$_SESSION['OCS']['TAG_LBL'];
			$queryDetails .= "SELECT hardware_id as ID,'".$lbl."' as libelle, ".$value." as valeur FROM accountinfo WHERE hardware_id=".$systemid." UNION ";
		}
		$type_field[$lbl]=mysql_field_type($resultDetails,$i);
		$i++;
	}
	$queryDetails=substr($queryDetails,0,-6);
	$list_fields['Information']='libelle';
	$list_fields['Valeur']='valeur';
	//$list_fields['SUP']= 'ID';
	$list_fields['MODIF']= 'libelle';
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;

	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,80,$tab_options);
	//print_r($type_field);
	if (isset($protectedPost['MODIF']) and $protectedPost['MODIF'] != ''){
		switch ($type_field[$protectedPost['MODIF']]){
			case "int" : $java = $chiffres;
							break;
			case "string"  : $java = $majuscule;
							break;
			case "date"  : $java = "READONLY ".dateOnClick('NEW_VALUE');
							break;
			default : $java;
		}
		
		$truename=$protectedPost['MODIF'];
		if ($protectedPost['MODIF'] == $_SESSION['OCS']['TAG_LBL'])
			$truename='TAG';			
		if ($type_field[$protectedPost['MODIF']]=="date"){
		$tab_typ_champ[0]['COMMENT_BEHING'] =datePick('NEW_VALUE');
		$tab_typ_champ[0]['DEFAULT_VALUE']=dateFromMysql($item[$truename]);
		}else
		$tab_typ_champ[0]['DEFAULT_VALUE']=$item[$truename];
		$tab_typ_champ[0]['INPUT_NAME']="NEW_VALUE";
		$tab_typ_champ[0]['INPUT_TYPE']=0;
		$tab_typ_champ[0]['CONFIG']['JAVASCRIPT']=$java;
		$tab_typ_champ[0]['CONFIG']['MAXLENGTH']=100;
		$tab_typ_champ[0]['CONFIG']['SIZE']=40;
		$data_form[0]=$protectedPost['MODIF'];
		tab_modif_values($data_form,$tab_typ_champ,array('TAG_MODIF'=>$protectedPost['MODIF'],'FIELD_FORMAT'=>$type_field[$protectedPost['MODIF']]),$l->g(895),"");
		
	}
	echo "</form>";*/
?>