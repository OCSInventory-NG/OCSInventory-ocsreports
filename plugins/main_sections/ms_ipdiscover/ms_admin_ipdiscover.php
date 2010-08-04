<?php
require_once('require/function_ipdiscover.php');
$form_name='admin_ipdiscover';
$table_name='admin_ipdiscover';
echo "<form name='".$form_name."' id='".$form_name."' action='' method='post'>";
if (isset($protectedGet['value']) and $protectedGet['value'] != ''){
	$protectedPost['onglet'] = 'ADMIN_RSX';
	$protectedPost['MODIF']=$protectedGet['value'];
}else{
	$data_on['ADMIN_RSX']=$l->g(1140);
	$data_on['ADMIN_TYPE']=$l->g(836);
	
	if ($protectedPost['onglet'] != $protectedPost['old_onglet'])
	unset($protectedPost['MODIF']);	
	
	onglet($data_on,$form_name,"onglet",10);
}
echo '<div class="mlt_bordure" >';
if ($protectedPost['onglet'] == 'ADMIN_RSX'){
	
	if (!verif_base_methode('OCS')){
		if (isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != ''){
			delete_subnet($protectedPost['SUP_PROF']);
			$tab_options['CACHE']='RESET';		
		}
		
		
		if (isset($protectedPost['Valid_modif_x'])){
			$result=add_subnet($protectedPost['ADD_IP'],$protectedPost['RSX_NAME'],$protectedPost['ID_NAME'],$protectedPost['ADD_SX_RSX']);
			if ($result)
				echo "<center><font color=red size=3><b>".$result."</b></font></center>";
			else{
				echo "<center><font color=green size=3><b>".$l->g(1141)."</b></font></center>";
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
				
			}else
				$title=$l->g(303);
			$list_id_subnet=look_config_default_values('ID_IPDISCOVER_%','LIKE');
			foreach ($list_id_subnet['tvalue'] as $key=>$value){
				$list_subnet[$value]=$value;
			}
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
	}
	
	
}elseif($protectedPost['onglet'] == 'ADMIN_TYPE'){
	if (isset($protectedPost['Reset_modif_x'])){
			unset($protectedPost['MODIF']);
	}
	
	if (isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != ''){
		delete_type($protectedPost['SUP_PROF']);
		$tab_options['CACHE']='RESET';		
	}
	
	if (isset($protectedPost['Valid_modif_x'])){
		$result=add_type($protectedPost['TYPE_NAME']);
		if ($result){
			echo "<font color=red><b>".$result."</b></font>";
			$protectedPost['ADD_TYPE']="VALID";
		}
		else{
			$tab_options['CACHE']='RESET';	
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
}
 
 
echo '</div>';
echo "</form>";

?>