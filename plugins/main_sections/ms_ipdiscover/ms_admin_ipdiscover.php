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


if ((array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')){
	ob_start();
	$ajax = true;
}
else{
	$ajax=false;
}






require_once('require/function_ipdiscover.php');
require_once('require/function_files.php');
$form_name='admin_ipdiscover';
$table_name='admin_ipdiscover_'.$protectedPost['onglet'];
$tab_options=$protectedPost;
$tab_options['form_name']=$form_name;
$tab_options['table_name']=$table_name;
echo open_form($form_name);
if (isset($protectedGet['value']) and $protectedGet['value'] != ''){
	if (!in_array($protectedGet['value'],$_SESSION['OCS']["subnet_ipdiscover"])){
		msg_error($l->g(837));
		require_once(FOOTER_HTML);
		die();	
	}
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
				require_once(BACKEND.'ipdiscover/ipdiscover.php');
				if (isset($protectedGet['value']) and $protectedGet['value'] != '')
					reloadform_closeme("ipdiscover",true);
			}	
			$tab_options['CACHE']='RESET';
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
				if (!isset($protectedPost['RSX_NAME']))
					$protectedPost['RSX_NAME']=$result->NAME;
				if (!isset($protectedPost['ID_NAME']))
					$protectedPost['ID_NAME']=$result->ID;
				if (!isset($protectedPost['ADD_IP']))
					$protectedPost['ADD_IP']=$result->NETID;
				if (!isset($protectedPost['ADD_SX_RSX']))
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
			
			$list_subnet = array(0 => "") + $list_subnet;
			
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
			$result_exist=ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
			
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
		$tab_options['LBL_POPUP']['SUP']='NAME';
		$tab_options['LBL']['SUP']=$l->g(122);
		$default_fields=$list_fields;
		$list_col_cant_del=$list_fields;
		$result_exist=ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
		
		echo "<input type = submit value='".$l->g(116)."' name='ADD_TYPE'>";	
	}
}elseif($protectedPost['onglet'] == 'ADMIN_SMTP' 
		and $_SESSION['OCS']['CONFIGURATION']['MANAGE_SMTP_COMMUNITIES'] == 'YES'){

		if (isset($protectedPost['Valid_modif_x'])){
			$msg_result=add_community($protectedPost['MODIF'],$protectedPost['NAME'],$protectedPost['VERSION'],
						  $protectedPost['USERNAME'],$protectedPost['AUTHKEY'],
						  $protectedPost['AUTHPASSWD']);	
			if (isset($msg_result['SUCCESS'])){
				unset($protectedPost['MODIF'],$protectedPost['ADD_COMM']);	
				$msg_ok=$msg_result['SUCCESS'];
				$tab_options['CACHE']='RESET';
			}else{
				$msg_error=$msg_result['ERROR'];				
			}

		}
			
		if (isset($protectedPost['Reset_modif_x'])){
			unset($protectedPost['MODIF'],$protectedPost['ADD_COMM']);			
		}
		
		if (isset($protectedPost['SUP_PROF']) and is_numeric($protectedPost['SUP_PROF'])){
			del_community($protectedPost['SUP_PROF']);	
			$msg_ok=$l->g(1212);
			
		}
	
		if(isset($msg_ok))
			msg_success($msg_ok);
		
		if (isset($msg_error))
			msg_error($msg_error);
			
		if ($protectedPost['ADD_COMM'] == $l->g(116) or is_numeric($protectedPost['MODIF'])){
				
			$list_version=array('-1'=>'2c','1'=>'1','2'=>'2','3'=>'3');
			$title=$l->g(1207);
			if (isset($protectedPost['MODIF']) and is_numeric($protectedPost['MODIF']) and !isset($protectedPost['NAME'])){
				$info_com=find_community_info($protectedPost['MODIF']);
				$default_values=array('ID'=>$protectedPost['MODIF'],
									  'NAME'=>$info_com->NAME,
									  'VERSION' =>$list_version,
									  'USERNAME'  =>$info_com->USERNAME,
									  'AUTHKEY'=>$info_com->AUTHKEY,
									  'AUTHPASSWD'=>$info_com->AUTHPASSWD);
				if ($info_com->VERSION == "2c")
					$protectedPost['VERSION']=-1;
				else
					$protectedPost['VERSION']=$info_com->VERSION;
				
			}else{
				$default_values=array('ID'=>$protectedPost['ID'],
									  'NAME'=>$protectedPost['NAME'],
									  'VERSION' =>$list_version,
									  'USERNAME'  =>$protectedPost['USERNAME'],
									  'AUTHKEY'=>$protectedPost['AUTHKEY'],
									  'AUTHPASSWD'=>$protectedPost['AUTHPASSWD']);
			}
			form_add_community($title,$default_values,$form_name);			
			
		}else{		
			$sql="select * from snmp_communities";
			$list_fields= array($l->g(277)=> 'VERSION',
						$l->g(49)=>'NAME',
						$l->g(24)=>'USERNAME',
						$l->g(2028)=>'AUTHKEY',
						$l->g(217)=>'AUTHPASSWD',
						'MODIF'=>'ID',
						'SUP'=>'ID');
			//$list_fields['SUP']='ID';	
			$default_fields=$list_fields;
			$list_col_cant_del=$list_fields;
			$tab_options['LBL_POPUP']['SUP']='NAME';
			$tab_options['LBL']['SUP']=$l->g(122);
			$result_exist=ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
				
			
			echo "<input type = submit value='".$l->g(116)."' name='ADD_COMM'>";	
				$protectedPost['ADD_COMM'] = $l->g(116);
				
		}
		
		
		
		

} 
 
echo '</div>';
echo close_form();


if ($ajax){
	ob_end_clean();
	tab_req($list_fields,$default_fields,$list_col_cant_del,$sql,$tab_options);
}

?>