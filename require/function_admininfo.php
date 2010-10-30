<?php
//type of choice
$type_accountinfo=array('TEXT','TEXTAREA','SELECT',
					strtoupper($l->g(802)),'CHECKBOX',
					'BLOB (FILE)','DATE','RADIOBUTTON');
$sql_type_accountinfo=array('VARCHAR(255)','LONGTEXT','VARCHAR(255)',
							'VARCHAR(255)','VARCHAR(255)','BLOB','VARCHAR(10)','VARCHAR(255)');


$convert_type=array('0','1','2','3','5','8','0','11');


function accountinfo_tab($id){
	global $type_accountinfo;
	$info_tag=find_info_accountinfo($id);
	if ($info_tag[$id]['type'] == 2 
		or $info_tag[$id]['type'] == 4
		or $info_tag[$id]['type'] == 7){
		$info=find_value_field('ACCOUNT_VALUE_'.$info_tag[$id]['name']);		
		return $info;
	}elseif ( $info_tag[$id]['type'] == 5)
		return false;
	
	return true;
	//if ()
	
}




function max_order($table,$field){
	$sql="SELECT max(%s) as max_id FROM %s";
	$arg=array($field,$table);
	$result=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$arg);			
	$val = mysql_fetch_array( $result );
	return $val['max_id']+1;
}



/*
 * When you add a new accountinfo
 * you need to add few fields on 
 * some tables
 * 
 * 
 */
					
function add_accountinfo($newfield,$newtype,$newlbl,$tab,$type='COMPUTERS'){	
	global $l,$sql_type_accountinfo;
	if ($type == 'COMPUTERS')
		$table="accountinfo";
	elseif ($type == 'SNMP')
		$table="snmp_accountinfo";
	else{
		//msg_error($type);
		return array('ERROR'=>$type);
	}
		
	$ERROR=dde_exist($newfield,'',$type);
	$id_order=max_order('accountinfo_config','SHOW_ORDER');	
		
	if ($ERROR == ''){				
		$sql_insert_config="INSERT INTO accountinfo_config (TYPE,NAME,ID_TAB,COMMENT,SHOW_ORDER,ACCOUNT_TYPE) values(%s,'%s',%s,'%s',%s,'%s')";
		$arg_insert_config=array($newtype,
								 $newfield,
								 $tab,
								 $newlbl,$id_order,$type);
		mysql2_query_secure($sql_insert_config,$_SESSION['OCS']["writeServer"],$arg_insert_config);					
		
		$sql_add_column="ALTER TABLE ".$table." ADD COLUMN fields_%s %s default NULL";
		$arg_add_column=array(mysql_insert_id(),$sql_type_accountinfo[$newtype]);
		mysql2_query_secure($sql_add_column,$_SESSION['OCS']["writeServer"],$arg_add_column);			
		unset($newfield,$newlbl,$_SESSION['OCS']['TAG_LBL']);
		//msg_success($l->g(1069));
		return array('SUCCESS'=>$l->g(1069));
	}else
		return array('ERROR'=>$ERROR);
		//msg_error($ERROR);
	
	
	
}
/*
 * Del an accountinfo
 * 
 * 
 */

function del_accountinfo($id){
	global $l;

	//SNMP or COMPUTERS?
	$sql_found_account_type="SELECT account_type FROM accountinfo_config WHERE id = '%s'";
	$arg_found_account_type=$id;
	$result= mysql2_query_secure($sql_found_account_type,$_SESSION['OCS']["readServer"],$arg_found_account_type);		
	$val = mysql_fetch_array( $result );
	if ($val['account_type'] == "SNMP")
		$table="snmp_accountinfo";
	elseif ($val['account_type'] == "COMPUTERS")
		$table="accountinfo";
	else
		return FALSE;

	//DELETE INTO CONFIG TABLE
	$sql_delete_config="DELETE FROM accountinfo_config WHERE ID = '%s'";
	$arg_delete_config=$id;
	mysql2_query_secure($sql_delete_config,$_SESSION['OCS']["writeServer"],$arg_delete_config);					
	
	//ALTER TABLE ACCOUNTINFO
	$sql_DEL_column="ALTER TABLE ".$table." DROP COLUMN fields_%s";
	$arg_DEL_column=$id;
	mysql2_query_secure($sql_DEL_column,$_SESSION['OCS']["writeServer"],$arg_DEL_column);
	unset($_SESSION['OCS']['TAG_LBL']);	
}

/*
 * 
 * Find all categories of accountinfo 
 * if $onlyactiv exist, return only categories with data inside
 * 
 */


function find_all_account_tab($tab_value,$onlyactiv='',$first=''){
    
	$sql_tab_account="select IVALUE,TVALUE from config ";
	
	if ($onlyactiv != ''){
		$sql_tab_account .= ", accountinfo_config";
	}

	$sql_tab_account .= " where config.name like '%s'";
	
	if ($onlyactiv != ''){
		$sql_tab_account .= " and accountinfo_config.id_tab=config.ivalue and accountinfo_config.account_type='".$onlyactiv."'";
	}
	
	$arg_tab_account=$tab_value.'%';
	
	$result_tab_account=mysql2_query_secure($sql_tab_account,$_SESSION['OCS']["readServer"],$arg_tab_account);					
	while ($val_tab_account = mysql_fetch_array( $result_tab_account )){
		if (!isset($array_tab_account['FIRST']) and $first != '')
		$array_tab_account['FIRST']=$val_tab_account['IVALUE'];
		$array_tab_account[$val_tab_account['IVALUE']]=$val_tab_account['TVALUE'];		
	}	
	 return $array_tab_account;	
}


function find_value_field($name){
	$array_tab_account=array();
	 $data= look_config_default_values($name.'%',true);
	 if (isset($data['name'])){
		 foreach ($data['name'] as $field=>$value)	{
			$array_tab_account[$data['ivalue'][$field]]=$data['tvalue'][$field];	 	
		 }
	 }
	 return $array_tab_account;			
}




/*
 * Find detail of an accountinfo.
 * You can have $id = accountinfo_id
 * or $id=array(accountinfo_id1,accountinfo_id2,accountinfo_id2...)
 * 
 */

function find_info_accountinfo($id = '',$type=''){
	$list_field=array('id','type','name','id_tab','comment','show_order','account_type');
	if ($type != ''){
		$where=" where account_type='".$type."' ";
		$and=" and account_type='".$type."' ";
	}else{
		$where="";
		$and="";		
	}
	
	if (is_array($id)){
		$sql_info_account="select " . implode(',',$list_field) . " from accountinfo_config where id in (%s) ".$and." order by show_order DESC";
		$arg_info_account=array(implode(',',$id));		
		
	}elseif ($id != ''){
		$sql_info_account="select " . implode(',',$list_field) . " from accountinfo_config where id=%s  ".$and." order by show_order DESC";
		$arg_info_account=array($id);		
	}else{
		$sql_info_account="select " . implode(',',$list_field) . " from accountinfo_config ".$where." order by show_order DESC";
		$arg_info_account=array();				
	}
	
	$result_info_account=mysql2_query_secure($sql_info_account,$_SESSION['OCS']["readServer"],$arg_info_account);					
	while ($val_info_account = mysql_fetch_array( $result_info_account )){
		$array_info_account[$val_info_account['id']]=$val_info_account;		
	}	
	 return $array_info_account;	
	
}

function witch_field_more($account_type = ''){
	$list_field=array('ID','TYPE','NAME','COMMENT');
	$sql_accountinfo="select " . implode(',',$list_field) . " from accountinfo_config ";
	if ($account_type != '')
		$sql_accountinfo.= " where account_type = '".$account_type."' ";
	$result_accountinfo = mysql2_query_secure($sql_accountinfo,$_SESSION['OCS']["readServer"]);
	
	while($item = mysql_fetch_object($result_accountinfo)){
		$list_fields[$item->ID]=$item->COMMENT;
		$list_name[$item->ID]=$item->NAME;
		$list_type[$item->ID]=$item->TYPE;
	}
	return array('LIST_FIELDS'=>$list_fields,'LIST_NAME'=>$list_name,'LIST_TYPE'=>$list_type);
}



function update_accountinfo_config($id,$array_new_values){
	//Update
		$sql_update_config="UPDATE accountinfo_config SET ";
		$arg_update_config=array();
		foreach ($array_new_values as $field=>$value){
			if ($field == "TYPE"){
				$new_type_field=$sql_type_accountinfo[$value];
			}
			$sql_update_config.="%s='%s', ";
			array_push($arg_update_config,$field);
			array_push($arg_update_config,$value);
		}
		$sql_update_config = substr($sql_update_config,0,-2);
		
		if (is_numeric($id)){		
			$sql_update_config.="  WHERE ID = '%s'";
			array_push($arg_update_config,$id);
		}else{
			$temp_id=explode(',',$id);
			$sql_update_config.="  WHERE ID IN (";
			foreach ($temp_id as $key=>$value){
				$sql_update_config.=$value . "%s,";
				array_push($arg_update_config,$value);
			}
			$sql_update_config = substr($sql_update_config,0,-1) . ")";
			
		}
		
		mysql2_query_secure($sql_update_config,$_SESSION['OCS']["writeServer"],$arg_update_config);
		unset($_SESSION['OCS']['TAG_LBL']);
	
}

function find_new_order($updown,$id,$type,$onglet){
	$tab_order=array();
	if (!is_numeric($id) or !is_numeric($onglet))
	  return false;
	$sql="select ID,SHOW_ORDER from accountinfo_config where account_type='%s' and id_tab=%s order by show_order";
	$arg=array($type,$onglet);
	$result = mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$arg);
	while($item = mysql_fetch_object($result)){
		$array_id[]=$item->ID;
		$array_order[]=$item->SHOW_ORDER;
	}
	foreach($array_id as $key=>$value){
		if ($array_id[$key] == $id){
			if ($updown == 'UP'){
					$tab_order['NEW']=$array_id[$key-1];
				$tab_order['NEW_VALUE']=$array_order[$key-1];		
			}else{		
				$tab_order['NEW']=$array_id[$key+1];	
				$tab_order['NEW_VALUE']=$array_order[$key+1];			
			}
			$tab_order['OLD']=$value;
			$tab_order['OLD_VALUE']=$array_order[$key];
		}		
	}
		
	return $tab_order;
	
}

/*
 * update an accountinfo
 * 
 * 
 */

function update_accountinfo($id,$array_new_values,$type){
	global $l,$sql_type_accountinfo;
	//print_r($array_new_values);
	$error=dde_exist($array_new_values['NAME'],$id,$type);
	if ($error == ''){
		//Update
		update_accountinfo_config($id,$array_new_values);
		//update column type in accountinfo table
		$sql_update_column="ALTER TABLE accountinfo change fields_%s fields_%s %s";
		$arg_update_column=array($id,$id,$new_type_field);
		mysql2_query_secure($sql_update_column,$_SESSION['OCS']["writeServer"],$arg_update_column); 
		return array('SUCCESS'=>$l->g(711));							
	}else{
		return array('ERROR'=>$error);
	}
}

/*
 * 
 * Function : is this name of accountinfo exist?
 * if you put a value to $id, you add a condition
 * and restraint your search to all other id
 * 
 */
function dde_exist($name,$id='',$type){
	global $l;
	
	if (trim($name) != ''){		
			$sql_verif="SELECT count(*) c FROM accountinfo_config WHERE NAME = '%s' and ACCOUNT_TYPE='%s'";
			$arg_verif=array($name,$type);
			if ($id != '' and is_numeric($id)){
				$sql_verif.=" AND ID != %s";
				array_push($arg_verif,$id);
			}			
			$res_verif=mysql2_query_secure($sql_verif,$_SESSION['OCS']["readServer"],$arg_verif);
			$val_verif = mysql_fetch_array( $res_verif );
			//this name is already exist
			if ($val_verif['c'] > 0)
				return $l->g(1067);				
		}else
			//name can't be null
			return $l->g(1068);		

		return;
	
}

/*
 * 
 *Find all accountinfo for  
 * a computer
 * 
 */
function admininfo_computer($id = ""){
	global $l;
	if (!is_numeric($id) and $id != "")
		return $l->g(400);		
	$arg_account_data=array();	
	$sql_account_data="SELECT * FROM accountinfo ";
	if (is_numeric($id)){
		$sql_account_data.= " WHERE hardware_id=%s";
		$arg_account_data=array($id);
	}else
		$sql_account_data.= " LIMIT 1 ";
	
	$res_account_data=mysql2_query_secure($sql_account_data,$_SESSION['OCS']["readServer"],$arg_account_data);
	$val_account_data = mysql_fetch_array( $res_account_data );
	return $val_account_data;	
}

function updateinfo_computer($id,$values,$list=''){
	global $l;
	if (!is_numeric($id) and $list == '')
		return $l->g(400);		
	$arg_account_data=array();	
	$sql_account_data="UPDATE accountinfo SET ";
	foreach ($values as $field=>$val){
		$sql_account_data .= " %s='%s', ";
		array_push($arg_account_data,$field);
		array_push($arg_account_data,$val);		
	}
	$sql_account_data = substr($sql_account_data,0,-2);
	if (is_numeric($id) and $list == '')
	$sql_account_data.=" WHERE hardware_id=%s";
	if ($list != '')
	$sql_account_data.=" WHERE hardware_id in (%s)";
	
	array_push($arg_account_data,$id);	
	mysql2_query_secure($sql_account_data,$_SESSION['OCS']["readServer"],$arg_account_data);
	return $l->g(1121);	
}

function updown($field,$type){
	global $form_name;
	if ($type == 'UP'){
		return "<a href=# OnClick='pag(\"" . $field . "\",\"UP\",\"".$form_name."\");'><img src='image/up.png'></a>";
	}elseif ($type == 'DOWN'){	
		return "<a href=# OnClick='pag(\"" . $field . "\",\"DOWN\",\"".$form_name."\");'><img src='image/down.png'></a>";
	}
}




?>