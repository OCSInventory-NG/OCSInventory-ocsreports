<?php
//type of choice
$type_accountinfo=array('TEXT','TEXTAREA','SELECT',
					strtoupper($l->g(802)),'CHECKBOX',
					'BLOB (FILE)','DATE');
$sql_type_accountinfo=array('VARCHAR(255)','LONGTEXT','VARCHAR(255)',
							'VARCHAR(255)','VARCHAR(255)','BLOB','VARCHAR(10)');


$convert_type=array('0','1','2','3','5','8','0');

/*
 * When you add a new accountinfo
 * you need to add few fields on 
 * some tables
 * 
 * 
 */
					
function add_accountinfo($newfield,$newtype,$newlbl,$tab){	
	global $l,$sql_type_accountinfo;
	
	$ERROR=dde_exist($newfield);	
		
	if ($ERROR == ''){				
		$sql_insert_config="INSERT INTO accountinfo_config (TYPE,NAME,ID_TAB,COMMENT) values(%s,'%s',%s,'%s')";
		$arg_insert_config=array($newtype,
								 $newfield,
								 $tab,
								 $newlbl);
		mysql2_query_secure($sql_insert_config,$_SESSION['OCS']["writeServer"],$arg_insert_config);					
		
		$sql_add_column="ALTER TABLE accountinfo ADD COLUMN fields_%s %s default NULL";
		$arg_add_column=array(mysql_insert_id(),$sql_type_accountinfo[$newtype]);
		mysql2_query_secure($sql_add_column,$_SESSION['OCS']["writeServer"],$arg_add_column);			
		unset($newfield,$newlbl);
		return "<font color=green><b>".$l->g(1069)."</b></font>";			
	}else
		return "<font color=red><b>".$ERROR."</b></font>";
	
	
	
}
/*
 * Del an accountinfo
 * 
 * 
 */

function del_accountinfo($id){
	global $l;

	//DELETE INTO CONFIG TABLE
	$sql_delete_config="DELETE FROM accountinfo_config WHERE ID = '%s'";
	$arg_delete_config=array($id);
		mysql2_query_secure($sql_delete_config,$_SESSION['OCS']["writeServer"],$arg_delete_config);					
	//ALTER TABLE ACCOUNTINFO
	$sql_DEL_column="ALTER TABLE accountinfo DROP COLUMN fields_%s";
	$arg_DEL_column=array($id);
	mysql2_query_secure($sql_DEL_column,$_SESSION['OCS']["writeServer"],$arg_DEL_column);
		
}

/*
 * 
 * Find all categories of accountinfo 
 * if $onlyactiv exist, return only categories with data inside
 * 
 */


function find_all_account_tab($onlyactiv='',$first=''){
    
	$sql_tab_account="select IVALUE,TVALUE from config ";
	
	if ($onlyactiv != ''){
		$sql_tab_account .= ", accountinfo_config";
	}

	$sql_tab_account .= " where config.name like '%s'";
	
	if ($onlyactiv != ''){
		$sql_tab_account .= "and accountinfo_config.id_tab=config.ivalue";
	}
	
	$arg_tab_account=array('TAB_ACCOUNTAG%');
	
	$result_tab_account=mysql2_query_secure($sql_tab_account,$_SESSION['OCS']["readServer"],$arg_tab_account);					
	while ($val_tab_account = mysql_fetch_array( $result_tab_account )){
		if (!isset($array_tab_account['FIRST']) and $first != '')
		$array_tab_account['FIRST']=$val_tab_account['IVALUE'];
		$array_tab_account[$val_tab_account['IVALUE']]=$val_tab_account['TVALUE'];		
	}	
	 return $array_tab_account;	
}


function find_value_field($name){
	  
	$sql_tab_account="select IVALUE,TVALUE from config ";
	$sql_tab_account .= " where config.name like '%s'";
	$arg_tab_account=array('ACCOUNT_VALUE_' . $name . "%");
	
	$result_tab_account=mysql2_query_secure($sql_tab_account,$_SESSION['OCS']["readServer"],$arg_tab_account);					
	while ($val_tab_account = mysql_fetch_array( $result_tab_account )){
		$array_tab_account[$val_tab_account['IVALUE']]=$val_tab_account['TVALUE'];		
	}	
	 return $array_tab_account;		
	
}




/*
 * Find detail of an accountinfo.
 * You can have $id = accountinfo_id
 * or $id=array(accountinfo_id1,accountinfo_id2,accountinfo_id2...)
 * 
 */

function find_info_accountinfo($id){
	if (is_array($id)){
		$sql_info_account="select id,type,name,id_tab,comment from accountinfo_config where id in (%s)";
		$arg_info_account=array(implode(',',$id));		
		
	}else{
		$sql_info_account="select id,type,name,id_tab,comment from accountinfo_config where id=%s";
		$arg_info_account=array($id);		
	}
	
	$result_info_account=mysql2_query_secure($sql_info_account,$_SESSION['OCS']["readServer"],$arg_info_account);					
	while ($val_info_account = mysql_fetch_array( $result_info_account )){
		$array_info_account[$val_info_account['id']]=$val_info_account;		
	}	
	 return $array_info_account;	
	
}


/*
 * update an accountinfo
 * 
 * 
 */

function update_accountinfo($id,$array_new_values){
	global $l,$sql_type_accountinfo;
	//print_r($array_new_values);
	$error=dde_exist($array_new_values['NAME'],$id);
	if ($error == ''){
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
		$sql_update_config.="  WHERE ID = '%s'";
		array_push($arg_update_config,$id);
		mysql2_query_secure($sql_update_config,$_SESSION['OCS']["writeServer"],$arg_update_config);
		//update column type in accountinfo table
		$sql_update_column="ALTER TABLE accountinfo change fields_%s fields_%s %s";
		$arg_update_column=array($id,$id,$new_type_field);
		mysql2_query_secure($sql_update_column,$_SESSION['OCS']["writeServer"],$arg_update_column); 		
		return "<font color=green><b>".$l->g(1069)."</b></font>";							
	}else{
		return "<font color=red><b>".$error."</b></font>";
	}
}

/*
 * 
 * Function : is this name of accountinfo exist?
 * if you put a value to $id, you add a condition
 * and restraint your search to all other id
 * 
 */
function dde_exist($name,$id=''){
	global $l;
	
	if (trim($name) != ''){		
			$sql_verif="SELECT count(*) c FROM accountinfo_config WHERE NAME = '%s'";
			$arg_verif=array($name);
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
function admininfo_computer($id){
	global $l;
	if (!is_numeric($id))
		return $l->g(400);		
		
	$sql_account_data="SELECT * FROM accountinfo WHERE hardware_id=%s";
	$arg_account_data=array($id);
	$res_account_data=mysql2_query_secure($sql_account_data,$_SESSION['OCS']["readServer"],$arg_account_data);
	$val_account_data = mysql_fetch_array( $res_account_data );
	return $val_account_data;	
}

function updateinfo_computer($id,$values){
	global $l;
	if (!is_numeric($id))
		return $l->g(400);		
	$arg_account_data=array();	
	$sql_account_data="UPDATE accountinfo SET ";
	foreach ($values as $field=>$val){
		$sql_account_data .= " %s='%s', ";
		array_push($arg_account_data,$field);
		array_push($arg_account_data,$val);		
	}
	$sql_account_data = substr($sql_account_data,0,-2);
	$sql_account_data.=" WHERE hardware_id=%s";
	array_push($arg_account_data,$id);	
	mysql2_query_secure($sql_account_data,$_SESSION['OCS']["readServer"],$arg_account_data);
	return $l->g(1121);	
}



?>