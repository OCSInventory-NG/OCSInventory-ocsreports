<?php

//fonction pour avoir tous les groupes
//$group_type = STATIC,DYNAMIC,SERVER
//return tableau [id]=group_name
function all_groups($group_type){
	//r�cup�ration des groupes demand�s
	if ($group_type == "SERVER"){
		$reqGetId = "SELECT id,name FROM hardware
					     WHERE deviceid = '_DOWNLOADGROUP_'";	
	}else{
		if ($group_type == "STATIC"){
			$reqGetId = "SELECT id,name FROM hardware,groups 
					     WHERE groups.hardware_id=hardware.id 
							and deviceid = '_SYSTEMGROUP_'
							and (request is null or trim(request) = '')
						    and (xmldef  is null or trim(xmldef) = '')";	
			if (!($_SESSION['OCS']['CONFIGURATION']['GROUPS'] == "YES"))	
				$reqGetId.= " and workgroup = 'GROUP_4_ALL'";	
			
		}else{
			$reqGetId = "SELECT id,name FROM hardware,groups 
					     WHERE groups.hardware_id=hardware.id 
							and deviceid = '_SYSTEMGROUP_'							
							and ((request is not null and trim(request) != '') 
								or (xmldef is not null and trim(xmldef) != ''))";				
		}		
	}	
	$resGetId = mysql2_query_secure( $reqGetId, $_SESSION['OCS']["readServer"]);
	while( $valGetId = mysql_fetch_array( $resGetId ) ){
		$list_group[$valGetId['id']]=$valGetId['name'];
	}
	return $list_group;
	
}



//fonction pour sortir les machines d'un groupe
function remove_of_group($id_group,$list_id){
	$sql_delcache="DELETE FROM groups_cache WHERE group_id='%s' and hardware_id in ";
	$arg_delcache[]=$id_group;
	$delcache=mysql2_prepare($sql_delcache,$arg_delcache,$list_id);
	
	mysql2_query_secure( $delcache['SQL'], $_SESSION['OCS']["writeServer"], $delcache['ARG']);
	$cached = mysql_affected_rows($_SESSION['OCS']["writeServer"]);	
	return $cached;
}

//fonction de remplacement d'un groupe
function replace_group($id_group,$list_id,$req,$group_type){

	//static group?
	if ($group_type == 'STATIC'){
		$static=1;
		$req="";
	}else
	$static=0;		
	//delete cache
	$sql_delcache="DELETE FROM groups_cache WHERE group_id='".$id_group."'";
	mysql_query( $sql_delcache, $_SESSION['OCS']["writeServer"] ) or die( mysql_error($_SESSION['OCS']["writeServer"]) );
	//update group
	$sql_updGroup="UPDATE groups set request='', xmldef='".generate_xml($req)."' where hardware_id=".$id_group;
	mysql_query( $sql_updGroup, $_SESSION['OCS']["writeServer"] ) or die( mysql_error($_SESSION['OCS']["writeServer"]) );
	$nb_computer=add_computers_cache($list_id,$id_group,$static);
	return $nb_computer;	
	
}

//fonction de cr�ation d'un groupe
function creat_group ($name,$descr,$list_id,$req,$group_type)
{

	if (trim($name) == "")
	return array('RESULT'=>'ERROR', 'LBL'=> "NAME IS NULL");
	if (trim($descr) == "")
	return array('RESULT'=>'ERROR', 'LBL'=> "DESCR IS NULL");
	//static group?
	if ($group_type == 'STATIC'){
		$static=1;
		$req="";
	}else
	$static=0;	
	//does $name group already exists
	$reqGetId = "SELECT id FROM hardware WHERE name='".$name."' and deviceid = '_SYSTEMGROUP_'";
	$resGetId = mysql_query( $reqGetId, $_SESSION['OCS']["readServer"]);
	if( $valGetId = mysql_fetch_array( $resGetId ) )
		return array('RESULT'=>'ERROR', 'LBL'=> "ALREADYEXIST");
	
	//insert new group
	$sql_insert="INSERT INTO hardware(deviceid,name,description,lastdate) VALUES( '_SYSTEMGROUP_' , '".$name."', '".$descr."', NOW())";	
	mysql_query( $sql_insert, $_SESSION['OCS']["writeServer"] ) or die( mysql_error($_SESSION['OCS']["writeServer"]));	
	//Getting hardware id
	$insertId = mysql_insert_id( $_SESSION['OCS']["writeServer"] );
	$xml=generate_xml($req);
		
	//Creating group
	$sql_group="INSERT INTO groups(hardware_id, xmldef, create_time) VALUES ( ".$insertId.", '".$xml."', UNIX_TIMESTAMP() )";
	// echo "<font color=green>.SQL=".$sql_group."</font>";
	mysql_query( $sql_group, $_SESSION['OCS']["writeServer"] ) or die( mysql_error($_SESSION['OCS']["writeServer"]) );
		addLog("CREATE GROUPE",$name);
	//Generating cache
	if ($list_id != '')	{	
		$nb_computer=add_computers_cache($list_id,$insertId,$static);
		return array('RESULT'=>'OK', 'LBL'=> $nb_computer);
	}

	return array('RESULT'=>'OK', 'LBL'=> 'GROUPE CREE');

	
}


//function to add computer in groups_cache
function add_computers_cache($list_id,$groupid,$static){
	require_once('function_computers.php');
	//Generating cache
	if( lock($groupid) ) {	
		$reqCache = "INSERT IGNORE INTO groups_cache(hardware_id, group_id, static) 
						SELECT id, %s, %s from hardware where id in " ;
		$argCache=array($groupid,$static);
		$cache=mysql2_prepare($reqCache,$argCache,$list_id);	
		mysql2_query_secure( $cache['SQL'], $_SESSION['OCS']["writeServer"], $cache['ARG']);
		$cached = mysql_affected_rows($_SESSION['OCS']["writeServer"]);	
		unlock($groupid);
		return $cached;
	}	
}
//generation du xml en fonction des requetes
function generate_xml($req){
	//si il exite une requete
	if (isset($req[0])){
		//cr�ation du d�but du xml
		$xml="<xmldef>";
		//echo "xml=".$xml;
		$i=0;
		//concat�nation des diff�rentes requetes
		while (isset($req[$i])){
			$xml.="<REQUEST>".clean($req[$i])."</REQUEST>";
			$i++;
		}
		$xml.="</xmldef>";
	}else //si aucune requete n'exite, on renvoie un xml vide
	$xml="";
		
	return $xml;	
}

function clean( $txt ) {
		$cherche = array(	"&"  , "<"  , ">"  , "\""    , "'");
		$replace = array( "&amp;","&lt;","&gt;", "&quot;", "&apos;");
		return str_replace($cherche, $replace, $txt);		
	
}

function delete_group($id_supp){
	if ($id_supp == "")
	return array('RESULT'=>'ERROR', 'LBL'=> "ID IS NULL");
	if (!is_numeric($id_supp))
	return array('RESULT'=>'ERROR', 'LBL'=> "ID IS NOT NUMERIC");
	
	$sql_verif_group="select id from hardware where id=%s and DEVICEID='_SYSTEMGROUP_' or DEVICEID='_DOWNLOADGROUP_'";
	$arg_verif_group=$id_supp;
	$res_verif_group = mysql2_query_secure( $sql_verif_group, $_SESSION['OCS']["readServer"],$arg_verif_group);
	if( $val_verif_group = mysql_fetch_array( $res_verif_group ) ){	
		$del[]="DELETE FROM accountinfo where HARDWARE_ID=%s";
		$del[]="DELETE FROM groups_cache WHERE group_id=%s";
		$del[]="DELETE FROM groups WHERE hardware_id=%s";
		$del[]="DELETE FROM download_servers where group_id=%s";
		$del[]="DELETE FROM download_enable where group_id=%s";
		$del[]="DELETE FROM hardware where id=%s";
		foreach ($del as $key=>$value)		
			mysql2_query_secure($value, $_SESSION['OCS']["writeServer"],$arg_verif_group);
		addLog("DELETE GROUPE",$id_supp);
		return array('RESULT'=>'OK', 'LBL'=> "GROUPE SUPPRIME");
	}else
	return array('RESULT'=>'ERROR', 'LBL'=> "GROUP DOES'NT EXIST");
	
	
}

function group_4_all($id_group){
	if ($id_group == "")
	return array('RESULT'=>'ERROR', 'LBL'=> "ID IS NULL");
	if (!is_numeric($id_group))
	return array('RESULT'=>'ERROR', 'LBL'=> "ID IS NOT NUMERIC");
	
	$sql_verif="select WORKGROUP from hardware where id=%s";
	$arg_verif=$id_group;
	$res = mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"],$arg_verif);
	$item = mysql_fetch_object($res);
	if ($item->WORKGROUP != "GROUP_4_ALL"){	
		$sql_update="update hardware set workgroup= 'GROUP_4_ALL' where id=%s";
		$return_result['LBL']="Groupe visible pour tous";
	}else{
		$sql_update="update hardware set workgroup= '' where id=%s";
		$return_result['LBL']="Groupe invisible";
	}
	mysql2_query_secure($sql_update, $_SESSION['OCS']["writeServer"],$arg_verif);	
	$return_result['RESULT']="OK";
	addLog("ACTION VISIBILITY OF GROUPE",$id_group);
	return $return_result;
}
?>
