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

function search_profil(){
	global $l;
	require_once('require/function_files.php');
	//$Directory=$_SESSION['OCS']['plugins_dir']."main_sections/";
	$data=ScanDirectory($_SESSION['OCS']['CONF_PROFILS_DIR'],"txt");
//	$array_lbl=array("sadmin"=>$l->g(140),"dde_teledeploy"=>$l->g(143),"admin"=>$l->g(141),"ladmin"=>$l->g(142));
	$i=0;
	while ($data['name'][$i]){
	//	echo $Directory.$data['name'][$i]."<br>";
		if ($data['name'][$i] != '4all_config.txt' and substr($data['name'][$i],-11) == "_config.txt"){	
			$name=substr($data['name'][$i],0,-11);
			$temp=read_profil_file($name);
			$list_profil[$name]=replace_language($temp['INFO']['NAME']);
		}
		$i++;
	}	
	return $list_profil;
}

//Function to delete one or an array of user
function delete_list_user($list_to_delete){
	$table=array('tags'=>'login','operators'=>'id');
	
	foreach ($table as $table_name=>$field){
		$arg_sql=array($table_name,$field);
		$sql_delete="delete from %s where %s in ";
		$sql_delete=mysql2_prepare($sql_delete,$arg_sql,$list_to_delete);
		mysql2_query_secure($sql_delete['SQL'], $_SESSION['OCS']["writeServer"],$sql_delete['ARG']);
	}

}	


function add_user($data_user,$list_profil=''){
	global $l;
	
	if (trim($data_user['ID']) == "")
		$ERROR=$l->g(997);
		
	if (is_array($list_profil)){
		if (!array_key_exists($data_user['ACCESSLVL'], $list_profil))
			$ERROR=$l->g(998);
	}
	if (!isset($ERROR)){
		$sql="select id from operators where id= '%s'";
		$arg=$data_user['ID'];
		$res=mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
		$row=mysql_fetch_object($res);
		if (isset($row->id)){
			if ($data_user['MODIF'] != $row->id){
				return $l->g(999);
			}else{
				$sql_update="update operators 
								set firstname = '%s',
									lastname='%s',
									new_accesslvl='%s',
									email='%s',
									comments='%s',
									user_group='%s'";
				$arg_update=array($data_user['FIRSTNAME'],
								  $data_user['LASTNAME'],
								  $data_user['ACCESSLVL'],
								  $data_user['EMAIL'],
								  $data_user['COMMENTS'],
								  $data_user['USER_GROUP']);
				if (isset($data_user['PASSWORD']) and $data_user['PASSWORD'] != ''){
					$sql_update.=",passwd ='%s'";
					$arg_update[]=md5($data_user['PASSWORD']);
				}
				$sql_update.="	 where ID='%s'";
				$arg_update[]=$row->id;
				mysql2_query_secure($sql_update, $_SESSION['OCS']["writeServer"],$arg_update);		
				return $l->g(374);
			}
		}else{		
			$sql=" insert into operators (id,firstname,lastname,new_accesslvl,email,comments,user_group";
			if (isset($data_user['PASSWORD']))
				$sql.=",passwd";
			$sql.=") value ('%s','%s','%s','%s','%s','%s','%s'";
			
			$arg=array($data_user['ID'],$data_user['FIRSTNAME'],
								  $data_user['LASTNAME'],
								  $data_user['ACCESSLVL'],
								  $data_user['EMAIL'],
								  $data_user['COMMENTS'],
								  $data_user['USER_GROUP']);
			if (isset($data_user['PASSWORD'])){
				$sql.=",'%s'";
				$arg[]=md5($data_user['PASSWORD']);
			}
			$sql.=")";
			mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg);			
			return $l->g(373);
		}		
	}else
		return $ERROR;
}


function admin_user($id_user=''){
	global $protectedPost,$l,$pages_refs; 
	if ($id_user!='')
		$update=3;
	else
		$update=0;
		if ($_SESSION['OCS']['CONFIGURATION']['CHANGE_USER_GROUP'] == 'YES'){
			//search all profil type
			$list_profil=search_profil();
			$list_groups_result=look_config_default_values("USER_GROUP_%",'LIKE');
			if (is_array($list_groups_result['name'])){
				foreach ($list_groups_result['name'] as $key=>$value){
					$list_groups[$list_groups_result['ivalue'][$key]]=$list_groups_result['tvalue'][$key];
				}
			}
			$name_field=array("ID","ACCESSLVL","USER_GROUP");
			$tab_name=array($l->g(995).": ",$l->g(66).":",$l->g(607).":");
			$type_field= array($update,2,2);	
			
		}
		$name_field[]="FIRSTNAME";
		$name_field[]="LASTNAME";
		$name_field[]="EMAIL";
		$name_field[]="COMMENTS";
		//$name_field[]="USER_GROUP";
	
		
		$tab_name[]=$l->g(49).": ";
		$tab_name[]=$l->g(996).": ";
		$tab_name[]=$l->g(1117).": ";
		$tab_name[]=$l->g(51).": ";
		//$tab_name[]="Groupe de l'utilisateur: ";
		
		
		$type_field[]= 0; 
		$type_field[]= 0; 
		$type_field[]= 0; 
		$type_field[]= 0; 
		//$type_field[]= 2; 
		
		
		if ($id_user != '' or $_SESSION['OCS']['CONFIGURATION']['CHANGE_USER_GROUP'] == 'NO'){
			$tab_hidden['MODIF']=$id_user;
			$sql="select ID,NEW_ACCESSLVL,USER_GROUP,FIRSTNAME,LASTNAME,EMAIL,COMMENTS from operators where id= '%s'";
			$arg=$id_user;
			$res=mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
			$row=mysql_fetch_object($res);
			if ($_SESSION['OCS']['CONFIGURATION']['CHANGE_USER_GROUP'] == 'YES'){
				$protectedPost['ACCESSLVL']=$row->NEW_ACCESSLVL;
				$protectedPost['USER_GROUP']=$row->USER_GROUP;
				$value_field=array($row->ID,$list_profil,$list_groups);
			}
			$value_field[]=$row->FIRSTNAME;
			$value_field[]=$row->LASTNAME;
			$value_field[]=$row->EMAIL;
			$value_field[]=$row->COMMENTS;
		}else{
			if ($_SESSION['OCS']['CONFIGURATION']['CHANGE_USER_GROUP'] == 'YES'){
				$value_field=array($protectedPost['ID'],$list_profil,$list_groups);
			}
			$value_field[]=$protectedPost['FIRSTNAME'];
			$value_field[]=$protectedPost['LASTNAME'];
			$value_field[]=$protectedPost['EMAIL'];
			$value_field[]=$protectedPost['COMMENTS'];				
		}
		if ($_SESSION['OCS']['cnx_origine'] == "LOCAL"){
			$name_field[]="PASSWORD";
			$type_field[]=0;
			$tab_name[]=$l->g(217).":";
			$value_field[]=$protectedPost['PASSWORD'];
		}
		$tab_typ_champ=show_field($name_field,$type_field,$value_field);
		foreach ($tab_typ_champ as $id=>$values){
			$tab_typ_champ[$id]['CONFIG']['SIZE']=40;
		}
		if ($_SESSION['OCS']['CONFIGURATION']['MANAGE_USER_GROUP'] == 'YES'){
			$tab_typ_champ[2]["CONFIG"]['DEFAULT']="YES";
		//	$tab_typ_champ[1]['COMMENT_BEHING']="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_admin_profil']."&head=1\",\"admin_profil\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")><img src=image/plus.png></a>";
			$tab_typ_champ[2]['COMMENT_BEHING']="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_adminvalues']."&head=1&tag=USER_GROUP\",\"admin_user_group\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")><img src=image/plus.png></a>";
		}
		
		if (isset($tab_typ_champ)){
			tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden);
		}	
		
}

function admin_profil($form){
	global $protectedPost,$l,$pages_refs;
	$yes_no=array('YES'=>$l->g(455),'NO'=>$l->g(454));
	$telediff_wk=array('LOGIN'=>'LOGIN','USER_GROUP'=>'USER_GROUP','NO'=>$l->g(454));
	$info_field=array('NAME'=>array('INFO'=>array('LBL'=>$l->g(1153) . ": ",'VALUE'=>'')),
					  'GUI'=> array('RESTRICTION'=>array('LBL'=>$l->g(1154) . ": ",'VALUE'=>$yes_no)),
					  'TELEDIFF_WK'=>array('RESTRICTION'=>array('LBL'=>$l->g(1155) . ": ",'VALUE'=>$telediff_wk),
					  					   'CONFIGURATION'=>array('LBL'=>$l->g(1156) . ": ",'VALUE'=>$yes_no)),
					  'TELEDIFF_WK_FIELDS'=>array('RESTRICTION'=>array('LBL'=>$l->g(1157) . ": ",'VALUE'=>$yes_no)),
					  'TELEDIFF_ACTIVATE'=>array('RESTRICTION'=>array('LBL'=>$l->g(1158) . ": ",'VALUE'=>$yes_no)),
					  'TELEDIFF_VISIBLE'=>array('RESTRICTION'=>array('LBL'=>$l->g(1301) . ": ",'VALUE'=>$yes_no)),
					  'EXPORT_XML'=>array('RESTRICTION'=>array('LBL'=>$l->g(1305),'VALUE'=>$yes_no)),
					  'WOL'=> array('RESTRICTION'=>array('LBL'=>$l->g(1281) . ": ",'VALUE'=>$yes_no)),
					  'MACADD'=>array('ADMIN_BLACKLIST'=>array('LBL'=>$l->g(1159) . ": ",'VALUE'=>$yes_no)),
					  'SERIAL'=>array('ADMIN_BLACKLIST'=>array('LBL'=>$l->g(1160) . ": ",'VALUE'=>$yes_no)),
					  'IPDISCOVER'=>array('ADMIN_BLACKLIST'=>array('LBL'=>$l->g(1161) . ": ",'VALUE'=>$yes_no),
					  'CONFIGURATION'=>array('LBL'=>$l->g(1172) . ": ",'VALUE'=>$yes_no)),
				          'TELEDIFF'=>array('CONFIGURATION'=>array('LBL'=>$l->g(1162) . ": ",'VALUE'=>$yes_no)),
					  'CONFIG'=>array('CONFIGURATION'=>array('LBL'=>$l->g(1163) . ": ",'VALUE'=>$yes_no)),
					  'GROUPS'=>array('CONFIGURATION'=>array('LBL'=>$l->g(1164) . ": ",'VALUE'=>$yes_no)),
					  'CONSOLE'=>array('CONFIGURATION'=>array('LBL'=>$l->g(1165) . ": ",'VALUE'=>$yes_no)),
					  'ALERTE_MSG'=>array('CONFIGURATION'=>array('LBL'=>$l->g(1166) . ": ",'VALUE'=>$yes_no)),
					  'ACCOUNTINFO'=>array('CONFIGURATION'=>array('LBL'=>$l->g(1167) . ": ",'VALUE'=>$yes_no)),
					  'CHANGE_ACCOUNTINFO'=>array('CONFIGURATION'=>array('LBL'=>$l->g(1168) . ": ",'VALUE'=>$yes_no)),
					  'CHANGE_USER_GROUP'=>array('CONFIGURATION'=>array('LBL'=>$l->g(1169) . ": ",'VALUE'=>$yes_no)),
					  'MANAGE_PROFIL'=>array('CONFIGURATION'=>array('LBL'=>$l->g(1170) . ": ",'VALUE'=>$yes_no)),
					  'MANAGE_USER_GROUP'=>array('CONFIGURATION'=>array('LBL'=>$l->g(1171) . ": ",'VALUE'=>$yes_no)),
					  'MANAGE_SMTP_COMMUNITIES'=>array('CONFIGURATION'=>array('LBL'=>$l->g(1205) . ": ",'VALUE'=>$yes_no)),
					  'DELETE_COMPUTERS'=>array('CONFIGURATION'=>array('LBL'=>$l->g(1272) . ": ",'VALUE'=>$yes_no)));
		
	$lbl_cat=array('INFO'=>$l->g(1173),
					   'PAGE_PROFIL'=>$l->g(1174),
					   'RESTRICTION'=>$l->g(1175),
					   'ADMIN_BLACKLIST'=>$l->g(1176),
					   'CONFIGURATION'=>$l->g(1177));
	if ($protectedPost['Valid_modif_profil_x']){
		//read profil file
		$forprofil=read_profil_file($protectedPost['PROFILS']);
		//read all profil value
		$forall=read_config_file();
		//build new tab with new values
		foreach($info_field as $if_name=>$if_value){
			foreach ($if_value as $if_cat=>$if_val){
				if(isset($protectedPost[$if_name]) and $protectedPost['cat'] == $if_cat){
					$new_value[$if_cat][$if_name]=$protectedPost[$if_name];						
				}else
					$new_value[$if_cat][$if_name]=$forprofil[$if_cat][$if_name];						
			}			
		}
		foreach ($forall['URL'] as $name=>$value){
			if (isset($protectedPost[$name]) and $protectedPost['cat'] == "PAGE_PROFIL")
					$new_value["PAGE_PROFIL"][$name]='';	
		}				
		
		if (!isset($new_value['PAGE_PROFIL']))
			$new_value['PAGE_PROFIL']=$forprofil['PAGE_PROFIL'];
		update_config_file($protectedPost['PROFILS'],$new_value);	
		msg_success($l->g(1274));	
	}
	
	$array_profil=search_profil();
	echo $l->g(1196). ": " .show_modif($array_profil,"PROFILS",2,$form);
	echo "<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_new_profil']."&head=1&form=".$form."\",\"new_profil\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=650,height=550\")><img src=image/plus.png></a>";
	
	if (isset($protectedPost['PROFILS']) and $protectedPost['PROFILS'] != ''){
		$forall=read_config_file();			
		$forprofil=read_profil_file($protectedPost['PROFILS'],'WRITE')	;
		if (is_array($forprofil) and is_array($forall)){
			foreach ($forprofil as $key=>$value){
				if (isset($lbl_cat[$key]))
					$data_on[$key]=$lbl_cat[$key];
			}		
			onglet($data_on,$form,"cat",10);
			if (isset($forprofil[$protectedPost['cat']]) and $protectedPost['cat'] != 'PAGE_PROFIL'){
				$name_field=array();
				$type_field=array();
				$tab_name=array();
				$value_field=array();
				foreach($info_field as $if_name=>$if_value){
						foreach ($if_value as $if_cat=>$if_val){
							if ($protectedPost['cat'] == $if_cat){
								if(isset($forprofil[$if_cat][$if_name]))
									$protectedPost[$if_name]=$forprofil[$if_cat][$if_name];	
								array_push($name_field,$if_name);
								array_push($tab_name,$if_val['LBL']);
								if(is_array($if_val['VALUE'])){
									array_push($type_field,2);
									if (!isset($protectedPost[$if_name]))
										array_push($if_val['VALUE'],'');
									array_push($value_field,$if_val['VALUE']);
								}else{
									array_push($type_field,0);
									array_push($value_field,replace_language($forprofil[$if_cat][$if_name]));
								}
							}						
						}				
				}
				$tab_typ_champ=show_field($name_field,$type_field,$value_field);
				tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif_profil");	
				
			}elseif ($protectedPost['cat'] == 'PAGE_PROFIL'){
				$champs="<table align=center><tr><td align=center>";
				$i=0;
				ksort($forall['URL']);
				foreach ($forall['URL'] as $key=>$value){
					$champs.= "<input type='checkbox' name='".$key."' id='".$key."' ";
					if (isset($forprofil[$protectedPost['cat']][$key]))
						$champs.= " checked ";
					$champs.= " ></td><td>".$key."</td><td align=center>";
					$i++;
					if ($i == 4){
						$champs.= "</td></tr><tr><td align=center>";
						$i=0;
					}
				}
				$champs.="</td></tr></table>";		
				tab_modif_values($champs,'','','','',$name_button="modif_profil");		
			}
		}		
	}
	
}



?>
