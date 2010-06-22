<?php
//type of choice
$type_accountinfo=array('TEXT','TEXTAREA','SELECT',
					strtoupper($l->g(802)),'CHECKBOX',
					'BLOB (FILE)','DATE');
$sql_type_accountinfo=array('VARCHAR(255)','LONGTEXT','VARCHAR(255)',
							'VARCHAR(255)','VARCHAR(255)','BLOB','DATE');

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
 * Find all categories of accoutinfo 
 * 
 * 
 */


function find_all_account_tab(){
    
	$sql_tab_account="select IVALUE,TVALUE from config where name like '%s'";
	$arg_tab_account=array('TAB_ACCOUNTAG%');
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
	global $l;
	//print_r($array_new_values);
	$error=dde_exist($array_new_values['NAME'],$id);
	if ($error == ''){
		//Update
		$sql_update_config="UPDATE accountinfo_config SET ";
		$arg_update_config=array();
		foreach ($array_new_values as $field=>$value){
			$sql_update_config.="%s='%s', ";
			array_push($arg_update_config,$field);
			array_push($arg_update_config,$value);
		}
		$sql_update_config = substr($sql_update_config,0,-2);
		$sql_update_config.="  WHERE ID = '%s'";
		array_push($arg_update_config,$id);
		mysql2_query_secure($sql_update_config,$_SESSION['OCS']["writeServer"],$arg_update_config);
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
			print_r($val_verif);
			//this name is already exist
			if ($val_verif['c'] > 0)
				return $l->g(1067);				
		}else
			//name can't be null
			return $l->g(1068);		

		return;
	
}

?>