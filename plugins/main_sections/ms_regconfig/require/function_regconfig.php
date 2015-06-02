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

$list_registry_key=array('HKEY_CLASSES_ROOT',
						 'HKEY_CURRENT_USER',
						 'HKEY_LOCAL_MACHINE',
						 'HKEY_USERS',
						 'HKEY_CURRENT_CONFIG',
						 'HKEY_DYN_DATA (Windows 9X only)');


function add_update_key($form_values,$update=false){
	global $l;

		foreach($form_values as $key => $value){	
			if (trim($value) == ""){
				msg_error($l->g(988));
				return FALSE;
			}
		}
	
		if ($update){
			$req = "UPDATE regconfig SET ".	
				"NAME='%s',".
				"REGTREE='%s',".
				"REGKEY='%s',".
				"REGVALUE='%s' ".
				"where ID='%s'";
			$arg_req=array($form_values["NAME"],$form_values["REGTREE"],
						   $form_values["REGKEY"],$form_values["REGVALUE"],
						   $update);
		}else{
			$sql_verif="select ID from regconfig 
						where REGTREE='%s' 
							and REGKEY='%s'
							and REGVALUE='%s'";
			$arg_verif=array($form_values["REGTREE"],$form_values["REGKEY"],$form_values["REGVALUE"]);
			$res=mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"],$arg_verif);
			$row=mysql_fetch_object($res);
			if (!is_numeric($row->ID)){				
			$req = "INSERT INTO regconfig (NAME,REGTREE,REGKEY,REGVALUE)
					VALUES('%s','%s','%s','%s')";
			$arg_req=array($form_values["NAME"],$form_values["REGTREE"],
						   $form_values["REGKEY"],$form_values["REGVALUE"]);
			}else{
				msg_error($l->g(987));
				return FALSE;
			}
			
		}
		
		if (isset($req)){
			mysql2_query_secure($req,$_SESSION['OCS']["writeServer"],$arg_req);
			if ($update)
				msg_success($l->g(1185));
			else
				msg_success($l->g(1184));
			return TRUE;
		}		
}
/*
 * function to delete a registry key
 * $id=> id of registry key
 */
function delkey($id){
	//find the registry key
	$sql="select name from regconfig where id =%s";
	$arg=$id;
	$res=mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
	$row=mysql_fetch_object($res);
	$name=$row->name;
	//delete key
	$sql_reg="delete from regconfig where id =%s ";
	mysql2_query_secure($sql_reg,$_SESSION['OCS']["writeServer"],$arg);
	//delete cache
	$sql_reg="delete from registry_name_cache where name ='%s' ";
	mysql2_query_secure($sql_reg,$_SESSION['OCS']["writeServer"],$name);
}





?>