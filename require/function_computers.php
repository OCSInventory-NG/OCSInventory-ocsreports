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



/**
  * Hardware locking function. Prevents the hardware to be altered by either the server or another administrator using the GUI
  * @param id Hardware identifier to be locked
  */
function lock($id) {
	$reqClean = "DELETE FROM locks WHERE unix_timestamp(since)<(unix_timestamp(NOW())-3600)";
	$resClean = mysql2_query_secure($reqClean, $_SESSION['OCS']["writeServer"]);
	
	$reqLock = "INSERT INTO locks(hardware_id) VALUES ('%s')";
	$argLock=$id;
	if( $resLock = mysql2_query_secure($reqLock, $_SESSION['OCS']["writeServer"],$argLock))
		return( mysql_affected_rows ( $_SESSION['OCS']["writeServer"] ) == 1 );
	else return false;
}

/**
  * Hardware unlocking function
  * @param id Hardware identifier to be unlocked
  */
function unlock($id) {
	$reqLock = "DELETE FROM locks WHERE hardware_id='%s'";
	$argLock=$id;
	$resLock = mysql2_query_secure($reqLock, $_SESSION['OCS']["writeServer"],$argLock);
	return( mysql_affected_rows ( $_SESSION['OCS']["writeServer"] ) == 1 );
}

/**
  * Show an error message if the locking failed
  */
function errlock() {
	global $l;
	msg_error($l->g(376));
}



function computer_list_by_tag($tag="",$format='LIST'){
	$arg_sql=array();
	if ($tag == ""){
		$sql_mycomputers['SQL']="select hardware_id from accountinfo a where ".$_SESSION['OCS']["mesmachines"];
	}elseif (is_array($tag)){		
		$sql_mycomputers="select hardware_id from accountinfo a where a.tag in ";
		$sql_mycomputers=mysql2_prepare($sql_mycomputers,$arg_sql,$tag);
	}else{
		$sql_mycomputers="select hardware_id from accountinfo a where a.tag in ";
		$sql_mycomputers=mysql2_prepare($sql_mycomputers,$arg_sql,$tag);
	}
	$res_mycomputers = mysql2_query_secure($sql_mycomputers['SQL'], $_SESSION['OCS']["readServer"],$sql_mycomputers['ARG']);
	$mycomputers="(";
	while ($item_mycomputers = mysql_fetch_object($res_mycomputers)){
		$mycomputers.= $item_mycomputers->hardware_id.",";	
		$array_mycomputers[]=$item_mycomputers->hardware_id;
	}
	$mycomputers=substr($mycomputers,0,-1).")";	
	if ($mycomputers == "()" or !isset($array_mycomputers))
		$mycomputers = "ERROR";
	if ($format == 'LIST'){
		return $mycomputers;
	}else{
		return $array_mycomputers;
		
	}
}


/**
  * Deleting function
  * @param id Hardware identifier to be deleted
  * @param checkLock Tells wether or not the locking system must be used (default true)
  * @param traceDel Tells wether or not the deleted entities must be inserted in deleted_equiv for tracking purpose (default true)
  */
function deleteDid($id, $checkLock = true, $traceDel = true, $silent=false
) {
	global $l;
	if ($_SESSION['OCS']['CONFIGURATION']['DELETE_COMPUTERS'] == "NO"){
		msg_error($l->g(1273));
		return false;
	}
	//If lock is not user OR it is used and available
	if( ! $checkLock || lock($id) ) {	
		$sql="SELECT deviceid,name,IPADDR,OSNAME FROM hardware WHERE id='%s'";
		$resId = mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$id);
		$valId = mysql_fetch_array($resId);
		$idHard = $id;
		$did = $valId["deviceid"];
		if( $did ) {	
			//Deleting a network device
			if( strpos ( $did, "NETWORK_DEVICE-" ) === false ) {
				$sql="SELECT macaddr FROM networks WHERE hardware_id='%s'";
				$resNetm = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$idHard);
				while( $valNetm = mysql_fetch_array($resNetm)) {
					$sql="DELETE FROM netmap WHERE mac='%s'";
					mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$valNetm["macaddr"]);
				}		
			}
			//deleting a regular computer
			if( $did != "_SYSTEMGROUP_" and $did != '_DOWNLOADGROUP_') {
				$tables=Array("accesslog","accountinfo","bios","controllers","drives",
				"inputs","memories","modems","monitors","networks","ports","printers","registry",
				"slots","softwares","sounds","storages","videos","devices","download_history","download_servers","groups_cache");	
			}
			elseif($did == "_SYSTEMGROUP_"){//Deleting a group
				$tables=Array("devices");
				//del messages on this group
				$sql_group_msg="DELETE FROM config WHERE name like '%s' and ivalue='%s'";
				mysql2_query_secure($sql_group_msg, $_SESSION['OCS']["writeServer"],array('GUI_REPORT_MSG%',$idHard));
				$sql_group="DELETE FROM groups WHERE hardware_id='%s'";
				mysql2_query_secure($sql_group, $_SESSION['OCS']["writeServer"],$idHard);
				$sql_group_cache="DELETE FROM groups_cache WHERE group_id='%s'";
				$resDelete = mysql2_query_secure($sql_group_cache, $_SESSION['OCS']["writeServer"],$idHard);
				$affectedComputers = mysql_affected_rows( $_SESSION['OCS']["writeServer"] );
			}
			
			if( !$silent )
				msg_success($valId["name"]." ".$l->g(220));
			
			foreach ($tables as $table) {
				$sql="DELETE FROM %s WHERE hardware_id='%s'";
				$arg=array($table,$idHard);
				mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg);		
			}
			$sql="delete from download_enable where SERVER_ID='%s'";
			mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$idHard);
			$sql="DELETE FROM hardware WHERE id='%s'";
			mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$idHard);
			//Deleted computers tracking
			if($traceDel && mysql_num_rows(mysql2_query_secure("SELECT IVALUE FROM config WHERE IVALUE>0 AND NAME='TRACE_DELETED'", $_SESSION['OCS']["readServer"]))){
				$sql="insert into deleted_equiv(DELETED,EQUIVALENT) values('%s',%s)";
				$arg=array($did,'NULL');
				mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg);
			}
		}
		//Using lock ? Unlock
		if( $checkLock ) 
			unlock($id);
		return $valId["name"];
	}
	else
		errlock();
		
}

function fusionne($afus) {

	global $l;
	$i=0;
	$maxStamp = 0;
	$minStamp = mktime(0,0,0,date("m"),date("d") + 1,date("Y")); //demain
	foreach($afus as $a) {
		$d = $a["lastcome"];
		$a["stamp"] = mktime($d[11].$d[12],$d[14].$d[15],$d[17].$d[18],$d[5].$d[6],$d[8].$d[9],$d[0].$d[1].$d[2].$d[3]);
		//echo "stamp:".$a["stamp"]."== mktime($d[11]$d[12],$d[14]$d[15],$d[17]$d[18],$d[5]$d[6],$d[8]$d[9],$d[0]$d[1]$d[2]$d[3]);<br>";
		if($maxStamp<$a["stamp"]) {
			$maxStamp = $a["stamp"];
			$maxInd = $i;
		}
		if($minStamp>$a["stamp"]) {
			$minStamp = $a["stamp"];
			$minInd = $i;
		}		
		$i++;
	}
	if($afus[$minInd]["deviceid"]!="") {
		$okLock = true;
		foreach($afus as $a) {
			if( ! $okLock = ($okLock && lock($a["id"])) )
				break;
			else
				$locked[] = $a["id"];
		}
		
		if( $okLock ) {
			//TRACE_DELETED
			if(mysql_num_rows(mysql2_query_secure("SELECT * FROM config WHERE IVALUE>0 AND NAME='TRACE_DELETED'", $_SESSION['OCS']["readServer"]))){
				foreach($afus as $a) {	
					if($afus[$maxInd]["deviceid"]==$a["deviceid"]){continue;}
					$sql="insert into deleted_equiv(DELETED,EQUIVALENT) values('%s','%s')";
					$arg=array($a["deviceid"],$afus[$maxInd]["deviceid"]);
					mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg) ;
				}
			}
			
			//KEEP OLD QUALITY,FIDELITY AND CHECKSUM
			$sql="SELECT CHECKSUM,QUALITY,FIDELITY FROM hardware WHERE ID='%s'";
			$persistent_req = mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$afus[$minInd]["id"]);
					
			$reqDelAccount = "DELETE FROM accountinfo WHERE hardware_id='%s'";
			mysql2_query_secure($reqDelAccount, $_SESSION['OCS']["writeServer"],$afus[$maxInd]["id"]) ;
			msg_success($l->g(190)." ".$afus[$maxInd]["deviceid"]." ".$l->g(191));
			
			$keep = array( "accountinfo",  "devices", "groups_cache" );
			foreach( $keep as $tableToBeKept ) {
				$reqRecupAccount = "UPDATE %s SET hardware_id='%s' WHERE hardware_id='%s'";			
				$argRecupAccount=array($tableToBeKept,$afus[$maxInd]["id"],$afus[$minInd]["id"]);
				mysql2_query_secure($reqRecupAccount, $_SESSION['OCS']["writeServer"],$argRecupAccount) ;
			}						
			msg_success($l->g(190)." ".$afus[$minInd]["deviceid"]." ".$l->g(206)." ".$afus[$maxInd]["deviceid"]);
			$i=0;
			foreach($afus as $a) {
				if($i != $maxInd) {
					deleteDid($a["id"], false, false,false);
					$lesDel .= $a["deviceid"]."/";
				}			
				$i++;
			}
			
			//RESTORE PERSISTENT VALUES
			$persistent_values = mysql_fetch_row($persistent_req);
			$sql="UPDATE hardware SET QUALITY=%s,FIDELITY=%s,CHECKSUM=CHECKSUM|%s WHERE id='%s'";
			$arg=array($persistent_values[1],$persistent_values[2],$persistent_values[0],$afus[$maxInd]["id"]);
			mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg) ;
			
		}
		else
			errlock();
		
		foreach($locked as $a) {
			unlock($a);	
		}		
	}
	$lesDel .= " => ".$afus[$maxInd]["deviceid"];
	AddLog("FUSION", $lesDel);
}

function form_add_computer(){
	global $l;
	$name_field=array("NAME","OSNAME");
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

function insert_manual_computer($values,$nb=1,$generic=false){
	global $i;
		if ($nb == 1)
			$name=$values['COMPUTER_NAME_GENERIC'];
		else
			$name=$values['COMPUTER_NAME_GENERIC'].$i;
		
		if ($generic){
			if ($values['COMPUTER_NAME_GENERIC'] == "")
				$values['COMPUTER_NAME_GENERIC']='MANUEL_ENTRY';
			if ($values['SERIAL_GENERIC'] == "")
				$values['SERIAL_GENERIC']='MANUEL_ENTRY';
			if ($values['ADDR_MAC_GENERIC'] == "")
				$values['ADDR_MAC_GENERIC']='MANUEL_ENTRY';			
		}
		$sql="insert into hardware (deviceid,name) values ('%s','%s')";
		$arg=array('MANUEL',$name);
		mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
		$id_computer=mysql_insert_id($_SESSION['OCS']["writeServer"]);
		
		$sql="insert into bios (hardware_id,ssn) values ('%s','%s')";
		$arg=array($id_computer,$values['SERIAL_GENERIC']);
		mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
	
		$sql="insert into networks (hardware_id,macaddr) values ('%s','%s')";
		$arg=array($id_computer,$values['ADDR_MAC_GENERIC']);
		mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
		
		return $id_computer;
	
}


?>
