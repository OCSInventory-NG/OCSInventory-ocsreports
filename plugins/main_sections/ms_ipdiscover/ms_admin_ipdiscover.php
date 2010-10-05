<?php
require_once('require/function_ipdiscover.php');
require_once('require/function_files.php');
$form_name='admin_ipdiscover';
$table_name='admin_ipdiscover';
echo "<form name='".$form_name."' id='".$form_name."' action='' method='post'>";
if (isset($protectedGet['value']) and $protectedGet['value'] != ''){
	$protectedPost['onglet'] = 'ADMIN_RSX';
	$protectedPost['MODIF']=$protectedGet['value'];
}else{
	$data_on['ADMIN_RSX']=$l->g(1140);
	$data_on['ADMIN_TYPE']=$l->g(836);
	
	if ($_SESSION['OCS']['CONFIGURATION']['MANAGE_SMTP_COMMUNITIES'] == 'YES')
		$data_on['ADMIN_SMTP']=$l->g(1205);
	
	if ($protectedPost['onglet'] != $protectedPost['old_onglet'])
	unset($protectedPost['MODIF']);	
	
	onglet($data_on,$form_name,"onglet",10);
}
echo '<div class="mlt_bordure" >';
if ($protectedPost['onglet'] == 'ADMIN_RSX'){
	$method=verif_base_methode('OCS');
	if (!$method){
		if (isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != ''){
			delete_subnet($protectedPost['SUP_PROF']);
			$tab_options['CACHE']='RESET';		
		}
		
		
		if (isset($protectedPost['Valid_modif_x'])){
			$result=add_subnet($protectedPost['ADD_IP'],$protectedPost['RSX_NAME'],$protectedPost['ID_NAME'],$protectedPost['ADD_SX_RSX']);
			if ($result)
				msg_error($result);
			else{
				if (isset($protectedPost['MODIF']))
					msg_success($l->g(1121));
				else
					msg_success($l->g(1141));
				//erase ipdiscover cache
				unset($_SESSION['OCS']['DATA_CACHE'][$table_name],$_SESSION['OCS']["ipdiscover"],$protectedPost['ADD_SUB'],$protectedPost['MODIF']);
				require_once($_SESSION['OCS']['backend'].'/ipdiscover/ipdiscover.php');
				if (isset($protectedGet['value']) and $protectedGet['value'] != '')
					reloadform_closeme("ipdiscover",true);
			}	
		}	
		
		if (isset($protectedPost['Reset_modif_x'])){
			unset($protectedPost['ADD_SUB'],$protectedPost['MODIF']);
			if (isset($protectedGet['value']) and $protectedGet['value'] != '')
				reloadform_closeme("ipdiscover",true);
		}
		
		if (isset($protectedPost['ADD_SUB'])){
			echo "<input type='hidden' name='ADD_SUB' id='ADD_SUB' value='".$protectedPost['ADD_SUB']."'";		
		}	
		if ($protectedPost['MODIF'] != ''){
			echo "<input type='hidden' name='MODIF' id='MODIF' value='".$protectedPost['MODIF']."'";		
		}
		
		if (isset($protectedPost['ADD_SUB']) or $protectedPost['MODIF']){
			if ($protectedPost['MODIF']){
				$title=$l->g(931);
				
				$result=find_info_subnet($protectedPost['MODIF']);
				$protectedPost['RSX_NAME']=$result->NAME;
				$protectedPost['ID_NAME']=$result->ID;
				$protectedPost['ADD_IP']=$result->NETID;
				$protectedPost['ADD_SX_RSX']=$result->MASK;
				
				if (isset($protectedGet['value']) and $protectedGet['value'] != '')
					$protectedPost['ADD_IP']=$protectedGet['value'];					
				
			}else
				$title=$l->g(303);
			$list_id_subnet=look_config_default_values('ID_IPDISCOVER_%','LIKE');
			
			if (isset($list_id_subnet)){
				foreach ($list_id_subnet['tvalue'] as $key=>$value){
					$list_subnet[$value]=$value;
				}
			}else
				$list_subnet=array();
			array_unshift($list_subnet,"");	
			$default_values=array('RSX_NAME'=>$protectedPost['RSX_NAME'],
								  'ID_NAME' =>$list_subnet,
								  'ADD_IP'  =>$protectedPost['ADD_IP'],
								  'ADD_SX_RSX'=>$protectedPost['ADD_SX_RSX']);
			form_add_subnet($title,$default_values,$form_name);
		}else{
			$sql="select NETID,NAME,ID,MASK from subnet";
			$list_fields= array('NETID' => 'NETID',
							$l->g(49)=>'NAME',
							'ID'=>'ID',
							'MASK'=>'MASK',
							'MODIF'=>'NETID',
							'SUP'=>'NETID');
			//$list_fields['SUP']='ID';	
			$default_fields=$list_fields;
			$list_col_cant_del=$list_fields;
			$result_exist=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql,$form_name,80,$tab_options); 
			
			echo "<input type = submit value='".$l->g(116)."' name='ADD_SUB'>";				
		}
	}else 
		msg_warning($method);
	
	
}elseif($protectedPost['onglet'] == 'ADMIN_TYPE'){
	if (isset($protectedPost['Reset_modif_x'])){
			unset($protectedPost['MODIF']);
	}
	
	if (isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != ''){
		delete_type($protectedPost['SUP_PROF']);
		$tab_options['CACHE']='RESET';		
	}
	
	if (isset($protectedPost['Valid_modif_x'])){
		$result=add_type($protectedPost['TYPE_NAME'],$protectedPost['MODIF']);
		if ($result){
			msg_error($result);
			$protectedPost['ADD_TYPE']="VALID";
		}
		else{
			$tab_options['CACHE']='RESET';	
			unset($protectedPost['MODIF']);
			$msg_ok=$l->g(1121);
		}
	}	
	
	if ($protectedPost['MODIF'] != ''){
			echo "<input type='hidden' name='MODIF' id='MODIF' value='".$protectedPost['MODIF']."'";		
	}
	if (isset($protectedPost['ADD_TYPE']) or $protectedPost['MODIF']){
		if ($protectedPost['MODIF']){
				$info=find_info_type('',$protectedPost['MODIF']);
				$protectedPost['TYPE_NAME']=$info->NAME;
		}
		$tab_typ_champ[0]['DEFAULT_VALUE']=$protectedPost['TYPE_NAME'];
		$tab_typ_champ[0]['INPUT_NAME']="TYPE_NAME";
		$tab_typ_champ[0]['CONFIG']['SIZE']=60;
		$tab_typ_champ[0]['CONFIG']['MAXLENGTH']=255;
		$tab_typ_champ[0]['INPUT_TYPE']=0;
		$tab_name[0]=$l->g(938).": ";
		$tab_hidden['pcparpage']=$protectedPost["pcparpage"];
		tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title,$comment="");	
	}else{
		if (isset($msg_ok))
			msg_success($msg_ok);
		$sql="select ID,NAME from devicetype";
		$list_fields= array('ID' => 'ID',
							$l->g(49)=>'NAME',
							'MODIF'=>'ID',
							'SUP'=>'ID');
		//$list_fields['SUP']='ID';	
		$default_fields=$list_fields;
		$list_col_cant_del=$list_fields;
		$result_exist=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql,$form_name,80,$tab_options); 
		
		echo "<input type = submit value='".$l->g(116)."' name='ADD_TYPE'>";	
	}
}elseif($protectedPost['onglet'] == 'ADMIN_SMTP' 
		and $_SESSION['OCS']['CONFIGURATION']['MANAGE_SMTP_COMMUNITIES'] == 'YES'){
	$file='snmp_com.txt';
	$error="";
	$search=array('ID'=>'MULTI2',     
				  'NAME'=>'MULTI2',     
				  'VERSION'=>'MULTI2',     
				  'USERNAME'=>'MULTI2',    
				  'AUTHKEY'=>'MULTI2',   
				  'AUTHPASSWD'=>'MULTI2');
	$snmp_dir=look_config_default_values('SNMP_DIR');
	if (isset($snmp_dir['tvalue']['SNMP_DIR']) and $snmp_dir['tvalue']['SNMP_DIR'] != '')
		$ms_cfg_file=$snmp_dir['tvalue']['SNMP_DIR'];
	else
		$ms_cfg_file= $_SERVER["DOCUMENT_ROOT"].'/snmp/';
		
	if (!file_exists($ms_cfg_file))
		$error=$l->g(920) . " (".$ms_cfg_file.") <br>";	
	if (!is_writable($ms_cfg_file))
		$error=$ms_cfg_file." ".$l->g(1006).". ".$l->g(1147);
	
	$ms_cfg_file.="/".$file;
		
	if (!is_writable($ms_cfg_file) and file_exists($ms_cfg_file))
		$error.=$ms_cfg_file." ".$l->g(1006)."<br>";	
	
	if ($error != ''){
		msg_error($error);		
	}else{		
		if ($protectedPost['Valid_modif_x']){
			$new_ms_cfg_file='';
			if (($protectedPost['VERSION'] != '3a' and trim($protectedPost['NAME']) != '') or
				($protectedPost['VERSION'] == '3a' and trim($protectedPost['NAME']) != '' and 
					 trim($protectedPost['USERNAME']) != '' and
					 trim($protectedPost['AUTHKEY']) != '' and
					 trim($protectedPost['AUTHPASSWD']) != '')){
				$snmp_value=format_value_community($protectedPost);
			}else{
				$error=$l->g(988);				
			}
			if (is_array($snmp_value)){
				if ($protectedPost['MODIF']){
					del_community($protectedPost['MODIF'],$ms_cfg_file,$tabvalue,$search);
					$snmp_value['ID']=$protectedPost['MODIF'];
					$msg_ok=$l->g(1209);
				}else
					$msg_ok=$l->g(1208);
				$new_ms_cfg_file=add_community($snmp_value);
				$file=fopen($ms_cfg_file,"a+");
				fwrite($file,$new_ms_cfg_file);	
				fclose( $file );
				unset($protectedPost['MODIF'],$protectedPost['ADD_COMM']);					
			}else
				msg_error($error);
		
		}
		
		if (isset($protectedPost['Reset_modif_x'])){
			unset($protectedPost['MODIF'],$protectedPost['ADD_COMM']);			
		}
		
		if (isset($protectedPost['SUP_PROF']) and is_numeric($protectedPost['SUP_PROF'])){
			del_community($protectedPost['SUP_PROF'],$ms_cfg_file,$tabvalue,$search);			
		}
		
		if (file_exists($ms_cfg_file)){		
			$field_com=read_configuration($ms_cfg_file,$search,'ID');
			if (is_array($field_com)){
				if (isset($msg_ok))
					msg_success($msg_ok);
			$sql="select ";
	
			foreach ($field_com['NAME'] as $i=>$poub){
				foreach ($search as $key=>$value){
					$sql.= "'".$field_com[$key][$i]. "' as ".$key.",";		
					$list_fields[$key]=$key;			
				}
				$sql=substr($sql,0,-1)." union select ";

			}
			$sql=substr($sql,0,-13);
			$list_fields['MODIF']='ID';
			$list_fields['SUP']='ID';
			$default_fields=$list_fields;
			$list_col_cant_del=$list_fields;
			$tab_options['LBL_POPUP']['SUP']='NAME';
			$tab_options['NO_NAME']['NAME']=1;
			$result_exist=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql,$form_name,80,$tab_options); 		
			echo "<input type = submit value='".$l->g(116)."' name='ADD_COMM'>";	
			}
		}
		
		if (!file_exists($ms_cfg_file) or $protectedPost['ADD_COMM'] == $l->g(116) or is_numeric($protectedPost['MODIF'])){
			if (isset($field_com['ID']))
				$protectedPost['ID']=max($field_com['ID'])+1;
			else
				$protectedPost['ID']=0;
				
			$list_version=array('2C'=>'2C','1a'=>'1','2a'=>'2','3a'=>'3');
			$title=$l->g(1207);
			if (isset($protectedPost['MODIF']) and is_numeric($protectedPost['MODIF']) and !isset($protectedPost['NAME'])){
				$default_values=array('ID'=>$protectedPost['MODIF'],
									  'NAME'=>$field_com['NAME'][$protectedPost['MODIF']],
									  'VERSION' =>$list_version,
									  'USERNAME'  =>$field_com['USERNAME'][$protectedPost['MODIF']],
									  'AUTHKEY'=>$field_com['AUTHKEY'][$protectedPost['MODIF']],
									  'AUTHPASSWD'=>$field_com['AUTHPASSWD'][$protectedPost['MODIF']]);
				if ($field_com['VERSION'][$protectedPost['MODIF']] != '2C')
					$protectedPost['VERSION']=$field_com['VERSION'][$protectedPost['MODIF']].'a';
				else
					$protectedPost['VERSION']=$field_com['VERSION'][$protectedPost['MODIF']];
				
			}else{
				$default_values=array('ID'=>$protectedPost['ID'],
									  'NAME'=>$protectedPost['NAME'],
									  'VERSION' =>$list_version,
									  'USERNAME'  =>$protectedPost['USERNAME'],
									  'AUTHKEY'=>$protectedPost['AUTHKEY'],
									  'AUTHPASSWD'=>$protectedPost['AUTHPASSWD']);
			}
			form_add_community($title,$default_values,$form_name);			
			
		}		
	}	
} 
 
echo '</div>';
echo "</form>";

?>