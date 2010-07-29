<?php
function search_profil(){
	global $l;
	require_once('require/function_files.php');
	$Directory=$_SESSION['OCS']['plugins_dir']."/main_sections/";
	$data=ScanDirectory($Directory,"txt");
	$i=0;
	while ($data['name'][$i]){
		if ($data['name'][$i] != '4all_config.txt' and substr($data['name'][$i],-11) == "_config.txt"){	
			$name=substr($data['name'][$i],0,-11);
			$list_profil[$name]=$name;

			if ($list_profil[$name]=="sadmin"){
			    $list_profil[$name]=$l->g(140);
			}
			if ($list_profil[$name]=="dde_teledeploy"){
			    $list_profil[$name]=$l->g(141);
			}

			if ($list_profil[$name]=="admin"){
			    $list_profil[$name]=$l->g(142);
			}

			if ($list_profil[$name]=="ladmin"){
			    $list_profil[$name]=$l->g(143);
			}

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
		if ($_SESSION['OCS']['CONFIGURATION']['CHANGE_USER_GROUP'] == 'YES'){
			//search all profil type
			$list_profil=search_profil();
			$sql="select IVALUE,TVALUE from config where name like '%s'";
			$arg="USER_GROUP_%";
			$res=mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
			while ($row=mysql_fetch_object($res)){
				$list_groups[$row->IVALUE]=$row->TVALUE;			
			}
			$name_field=array("ID","ACCESSLVL","USER_GROUP");
			$tab_name=array($l->g(995).": ",$l->g(66).":",$l->g(607).":");
			$type_field= array(0,2,2);	
			
		}
		$name_field[]="FIRSTNAME";
		$name_field[]="LASTNAME";
		$name_field[]="EMAIL";
		$name_field[]="COMMENTS";
		//$name_field[]="USER_GROUP";
	
		
		$tab_name[]=$l->g(49).": ";
		$tab_name[]=$l->g(996).": ";
		$tab_name[]="Email: ";
		$tab_name[]=$l->g(51).": ";
		//$tab_name[]="Groupe de l'utilisateur: ";
		
		
		$type_field[]= 0; 
		$type_field[]= 0; 
		$type_field[]= 0; 
		$type_field[]= 0; 
		//$type_field[]= 2; 
		
		
		if ($id_user != '' or $_SESSION['OCS']['CONFIGURATION']['CHANGE_USER_GROUP'] == 'NO'){
			$tab_hidden['MODIF']=$id_user;
			$sql="select * from operators where id= '%s'";
			$arg=$id_user;
			$res=mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
			$row=mysql_fetch_object($res);
			if ($_SESSION['OCS']['CONFIGURATION']['CHANGE_USER_GROUP'] == 'YES'){
				$protectedPost['ACCESSLVL']=$row->NEW_ACCESSLVL;
				$protectedPost['USER_GROUP']=$row->USER_GOUP;
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

?>