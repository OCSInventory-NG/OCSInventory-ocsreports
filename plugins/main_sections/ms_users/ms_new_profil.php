<?php
require_once('require/function_users.php');
$form_name='admin_profil';
$table_name=$form_name;
$data_on[1]=$l->g(1059);
$data_on[2]=$l->g(1060);
$array_profil=search_profil();

echo "<br><form name='".$form_name."' id='".$form_name."' method='POST'>";
echo "<font color='RED'><b>".$l->g(1152)."</b></font>";
onglet($data_on,$form_name,"onglet",2);
echo '<div class="mlt_bordure" >';
if ($protectedPost['onglet'] == 1){
	$tab_options['CACHE']='RESET';
	//delete few fields
	if ((isset($protectedPost['del_check']) and $protectedPost['del_check'] != '')
		or (isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != '')){	
			
		if ((isset($protectedPost['del_check']) and $protectedPost['del_check'] != ''))
			$name_del=$protectedPost['del_check'];
		else
			$name_del=$protectedPost['SUP_PROF'];		
		
		delete_config_file($name_del);
		if ($protectedGet['form'])
			reloadform_closeme($protectedGet['form']);
		$array_profil=search_profil();
	}


	$queryDetails ="";
	$tab_options['ARG_SQL']=array();
	
	if (is_array($array_profil)){
		foreach ($array_profil as $name=>$lbl){
			$queryDetails .= "select '%s' as NAME,'%s' as LBL union ";
			array_push($tab_options['ARG_SQL'],$name);
			array_push($tab_options['ARG_SQL'],$lbl);
		}
	}
	
	$queryDetails = substr($queryDetails,0,-6);
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	if (!(isset($protectedPost["pcparpage"])))
		 $protectedPost["pcparpage"]=5;

	$list_fields[$l->g(49)]='NAME';
	$list_fields[$l->g(80)]='LBL';
	$list_fields['SUP']='NAME';
	$list_fields['CHECK']='NAME'; 
	$list_col_cant_del=$list_fields;
	$default_fields=$list_col_cant_del; 
	$are_result=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,100,$tab_options);
	//traitement par lot
	if ($are_result){
		del_selection($form_name);
		if ($protectedGet['form'])
		reloadform_closeme($protectedGet['form']);
	}
	
	}elseif ($protectedPost['onglet'] == 2){
		$name_field=array("new_profil","lbl_profil","ref_profil");
		$tab_name=array($l->g(1149).": ",$l->g(1151).": ",$l->g(1150).": ");
		if (isset($protectedPost['Valid_modif_x'])){
			$msg="";
			if (stripos($protectedPost['new_profil'], ' ')){
				$msg .= $l->g(1178).' : <i>' . $tab_name[0] . "</i> " . $l->g(1179) . " <br>";				
			}
			if (array_key_exists( $protectedPost['new_profil'] , $array_profil )){				
				$msg .= $l->g(1178).' : <i>' . $tab_name[0] . "</i> " . $l->g(363) . " <br>";		
			}
			$i=0;
			while ($name_field[$i]){
				if (trim($protectedPost[$name_field[$i]]) == ''){
					$msg .= $l->g(1178).' : <i>' . $tab_name[$i] . "</i> " . $l->g(1180) . " <br>";				
				}
				$i++;
			}
			if ($msg != '')
				echo "<font color=red><b>" . $msg . "</b></font>";
			else{
				create_profil($protectedPost['new_profil'],$protectedPost['lbl_profil'],$protectedPost['ref_profil']);
				if ($protectedGet['form'])
					reloadform_closeme($protectedGet['form'],true);
			}
		}
		
		$type_field= array(0,0,2);
		$value_field=array($protectedPost['new_profil'],$protectedPost['lbl_profil'],search_profil());
		
		$tab_typ_champ=show_field($name_field,$type_field,$value_field);
		$tab_typ_champ[0]['CONFIG']['SIZE']=20;
		$tab_typ_champ[1]['CONFIG']['SIZE']=20;
		tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden);
	}
	

echo '</div></form>';




?>