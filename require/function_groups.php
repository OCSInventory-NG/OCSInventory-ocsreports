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
	while( $valGetId = mysqli_fetch_array( $resGetId ) ){
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
	$cached = mysqli_affected_rows($_SESSION['OCS']["writeServer"]);	
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
	$sql_delcache="DELETE FROM groups_cache WHERE group_id='%s'";
	$arg_delcache=$id_group;
	mysql2_query_secure( $sql_delcache, $_SESSION['OCS']["writeServer"],$arg_delcache);
	//update group
	$sql_updGroup="UPDATE groups set request='', xmldef='%s' where hardware_id=%s";
	$arg_updGroup=array(generate_xml($req),$id_group);
	mysql2_query_secure( $sql_updGroup, $_SESSION['OCS']["writeServer"],$arg_updGroup);
	$nb_computer=add_computers_cache($list_id,$id_group,$static);
	return $nb_computer;	
	
}

//create group function
function creat_group ($name,$descr,$list_id,$req,$group_type)
{
	global $l;
	if (trim($name) == "")
	return array('RESULT'=>'ERROR', 'LBL'=> $l->g(638));
	if (trim($descr) == "")
	return array('RESULT'=>'ERROR', 'LBL'=> $l->g(1234));
	//static group?
	if ($group_type == 'STATIC'){
		$static=1;
		$req="";
	}else
	$static=0;	
	//does $name group already exists
	$reqGetId = "SELECT id FROM hardware WHERE name='%s' and deviceid = '_SYSTEMGROUP_'";
	$argGetId=$name;
	$resGetId = mysql2_query_secure( $reqGetId, $_SESSION['OCS']["readServer"],$argGetId);
	if( $valGetId = mysqli_fetch_array( $resGetId ) )
		return array('RESULT'=>'ERROR', 'LBL'=> $l->g(621));
	
	//insert new group
	$sql_insert="INSERT INTO hardware(deviceid,name,description,lastdate) VALUES( '_SYSTEMGROUP_' , '%s', '%s', NOW())";	
	$arg_insert=array($name,$descr);
	mysql2_query_secure( $sql_insert, $_SESSION['OCS']["writeServer"],$arg_insert);	
	//Getting hardware id
	$insertId = mysqli_insert_id( $_SESSION['OCS']["writeServer"] );
	$xml=generate_xml($req);
		
	//Creating group
	$sql_group="INSERT INTO groups(hardware_id, xmldef, create_time) VALUES ( %s, '%s', UNIX_TIMESTAMP() )";
	$arg_group=array($insertId,$xml);
	mysql2_query_secure( $sql_group, $_SESSION['OCS']["writeServer"],$arg_group);
		addLog("CREATE GROUPE",$name);
	//Generating cache
	if ($list_id != '')	{	
		$nb_computer=add_computers_cache($list_id,$insertId,$static);
		return array('RESULT'=>'OK', 'LBL'=> $nb_computer);
	}

	return array('RESULT'=>'OK', 'LBL'=> $l->g(607)." ". $l->g(608));

	
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
		$cached = mysqli_affected_rows($_SESSION['OCS']["writeServer"]);	
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
	
	global $l;
	if ($id_supp == "")
	return array('RESULT'=>'ERROR', 'LBL'=> "ID IS NULL");
	if (!is_numeric($id_supp))
	return array('RESULT'=>'ERROR', 'LBL'=> "ID IS NOT NUMERIC");
	
	$sql_verif_group="select id from hardware where id=%s and DEVICEID='_SYSTEMGROUP_' or DEVICEID='_DOWNLOADGROUP_'";
	$arg_verif_group=$id_supp;
	$res_verif_group = mysql2_query_secure( $sql_verif_group, $_SESSION['OCS']["readServer"],$arg_verif_group);
	if( $val_verif_group = mysqli_fetch_array( $res_verif_group ) ){	
		 deleteDid($arg_verif_group);
		addLog("DELETE GROUPE",$id_supp);
		return array('RESULT'=>'OK', 'LBL'=> '');
	}else
	return array('RESULT'=>'ERROR', 'LBL'=> $l->g(623));
	
	
}

function group_4_all($id_group){
	if ($id_group == "")
	return array('RESULT'=>'ERROR', 'LBL'=> "ID IS NULL");
	if (!is_numeric($id_group))
	return array('RESULT'=>'ERROR', 'LBL'=> "ID IS NOT NUMERIC");
	
	$sql_verif="select WORKGROUP from hardware where id=%s";
	$arg_verif=$id_group;
	$res = mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"],$arg_verif);
	$item = mysqli_fetch_object($res);
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
