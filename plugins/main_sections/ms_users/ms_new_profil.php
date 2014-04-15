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
	parse_str($protectedPost['ocs']['0'], $params);
	$protectedPost+=$params;
	ob_start();
	$ajax = true;
}
else{
	$ajax=false;
}


require_once('require/function_users.php');
$form_name='admin_profil';
$table_name=$form_name;
$tab_options=$protectedPost;
$tab_options['form_name']=$form_name;
$data_on[1]=$l->g(1059);
$data_on[2]=$l->g(1060);
$array_profil=search_profil();
if (!is_writable($_SESSION['OCS']['CONF_PROFILS_DIR']))
	$no_delete=true;
	
echo "<br>";
echo open_form($form_name);
msg_warning($l->g(1152));
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
			/*if (!is_writable($_SESSION['OCS']['CONF_PROFILS_DIR'].$name."_config.txt")) {
				$no_delete=true;
				$tab_options['REPLACE_VALUE']['SUP'][$name]="&nbsp;";
				$tab_options['REPLACE_VALUE']['CHECK'][$name]="&nbsp;";
			}*/
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
	if (!$no_delete){
		$list_fields['SUP']='NAME';
		$list_fields['CHECK']='NAME'; 
	}else
		msg_warning($_SESSION['OCS']['CONF_PROFILS_DIR']." ".$l->g(1006).". ".$l->g(1275));

	$list_col_cant_del=$list_fields;
	$default_fields=$list_col_cant_del; 
	
	$tab_options['table_name']=$table_name;
	$are_result=ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	//traitement par lot
	if ($are_result){
		if (!$no_delete){
			del_selection($form_name);
		}
		if ($protectedGet['form'])
		reloadform_closeme($protectedGet['form']);
	}
	
	}elseif ($protectedPost['onglet'] == 2){
		if ($no_delete){
			$title=$_SESSION['OCS']['CONF_PROFILS_DIR']." ".$l->g(1006).". ".$l->g(1275);
			$showbutton=false;
			$type_field= array(3,3,2);
		}else{
			$title="";
			$showbutton=true;
			$type_field= array(0,0,2);
		}
			
		$name_field=array("new_profil","lbl_profil","ref_profil");
		$tab_name=array($l->g(1149).": ",$l->g(1151).": ",$l->g(1150).": ");
		if (isset($protectedPost['Valid_modif_x'])){
			$msg="";
			if(preg_match('/[^0-9A-Za-z]/',$protectedPost['new_profil'])){
//				$msg .= $l->g(1178).': <i>' . $tab_name[0] . "</i> " . $l->g(1179) . " <br>";
				$msg .= $l->g(1178).': <i>' . substr($tab_name[0],0,-2) . "</i> " . $l->g(1179) . " <br>";
			}
			if (array_key_exists( $protectedPost['new_profil'] , $array_profil )){
//				$msg .= $l->g(1178).': <i>' . $tab_name[0] . "</i> " . $l->g(363) . " <br>";
				$msg .= $l->g(1178).': <i>' . substr($tab_name[0],0,-2) . "</i> " . $l->g(363) . " <br>";
			}
			$i=0;
			while ($name_field[$i]){
				if (trim($protectedPost[$name_field[$i]]) == ''){
//					$msg .= $l->g(1178).': <i>' . $tab_name[$i] . "</i> " . $l->g(1180) . " <br>";
					$msg .= $l->g(1178).': <i>' . substr($tab_name[$i],0,-2) . "</i> " . $l->g(1180) . " <br>";
				}
				$i++;
			}
			if ($msg != '')
				 msg_error($msg);
			else{
				msg_success($l->g(1276));
				create_profil($protectedPost['new_profil'],$protectedPost['lbl_profil'],$protectedPost['ref_profil']);
				if ($protectedGet['form'])
					reloadform_closeme($protectedGet['form'],true);
				else
					msg_success($l->g(1274));
			}
		}
		
		
		$value_field=array($protectedPost['new_profil'],$protectedPost['lbl_profil'],search_profil());
		$config['JAVASCRIPT'][0]=$sql_field;
		$tab_typ_champ=show_field($name_field,$type_field,$value_field,$config);
		$tab_typ_champ[0]['CONFIG']['SIZE']=20;
		$tab_typ_champ[1]['CONFIG']['SIZE']=20;
		tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title,$comment="",$name_button="modif",$showbutton);
	}
	

echo "</div>";
echo close_form();


if ($ajax){
	ob_end_clean();
	tab_req($list_fields,$default_fields,$list_col_cant_del,$queryDetails,$tab_options);
}

?>
