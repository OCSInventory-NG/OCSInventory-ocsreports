<?php


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
	//If lock is not user OR it is used and available
	if( ! $checkLock || lock($id) ) {	
		$resId = mysql_query("SELECT deviceid,name,IPADDR,OSNAME FROM hardware WHERE id='$id'",$_SESSION['OCS']["readServer"]) or die(mysql_error());
		$valId = mysql_fetch_array($resId);
		$idHard = $id;
		$did = $valId["deviceid"];
		if( $did ) {	
			//Deleting a network device
			if( strpos ( $did, "NETWORK_DEVICE-" ) === false ) {
				$resNetm = @mysql_query("SELECT macaddr FROM networks WHERE hardware_id=$idHard", $_SESSION['OCS']["readServer"]) or die(mysql_error());
				while( $valNetm = mysql_fetch_array($resNetm)) {
					@mysql_query("DELETE FROM netmap WHERE mac='".$valNetm["macaddr"]."';", $_SESSION['OCS']["writeServer"]) or die(mysql_error());
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
				mysql_query("DELETE FROM groups WHERE hardware_id=$idHard", $_SESSION['OCS']["writeServer"]) or die(mysql_error());
				$resDelete = mysql_query("DELETE FROM groups_cache WHERE group_id=$idHard", $_SESSION['OCS']["writeServer"]) or die(mysql_error());
				$affectedComputers = mysql_affected_rows( $_SESSION['OCS']["writeServer"] );
			}
			
			if( !$silent )
				msg_error($valId["name"]." ".$l->g(220));
			
			foreach ($tables as $table) {
				mysql_query("DELETE FROM $table WHERE hardware_id=$idHard;", $_SESSION['OCS']["writeServer"]) or die(mysql_error());		
			}
			mysql_query("delete from download_enable where SERVER_ID=".$idHard, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));
			
			mysql_query("DELETE FROM hardware WHERE id=$idHard;", $_SESSION['OCS']["writeServer"]) or die(mysql_error());
			//Deleted computers tracking
			if($traceDel && mysql_num_rows(mysql_query("SELECT IVALUE FROM config WHERE IVALUE>0 AND NAME='TRACE_DELETED'", $_SESSION['OCS']["readServer"]))){
				mysql_query("insert into deleted_equiv(DELETED,EQUIVALENT) values('$did',NULL)", $_SESSION['OCS']["writeServer"]) or die(mysql_error());
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




function deleteListId($listid, $checkLock = true, $traceDel = true, $silent=false) {
	global $l;
	//If lock is not user OR it is used and available
	if( ! $checkLock || lock($id) ) {	
		$resId = mysql_query("SELECT deviceid,name,IPADDR,OSNAME FROM hardware WHERE id='$id'",$_SESSION['OCS']["readServer"]) or die(mysql_error());
		$valId = mysql_fetch_array($resId);
		$idHard = $id;
		$did = $valId["deviceid"];
		if( $did ) {
					
			//Deleting a network device
			if( strpos ( $did, "NETWORK_DEVICE-" ) === false ) {
				$resNetm = @mysql_query("SELECT macaddr FROM networks WHERE hardware_id=$idHard", $_SESSION['OCS']["writeServer"]) or die(mysql_error());
				while( $valNetm = mysql_fetch_array($resNetm)) {
					@mysql_query("DELETE FROM netmap WHERE mac='".$valNetm["macaddr"]."';", $_SESSION['OCS']["writeServer"]) or die(mysql_error());
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
				mysql_query("DELETE FROM groups WHERE hardware_id=$idHard", $_SESSION['OCS']["writeServer"]) or die(mysql_error());
				$resDelete = mysql_query("DELETE FROM groups_cache WHERE group_id=$idHard", $_SESSION['OCS']["writeServer"]) or die(mysql_error());
				$affectedComputers = mysql_affected_rows( $_SESSION['OCS']["writeServer"] );
			}
			
			if( !$silent )
				msg_error($valId["name"]." ".$l->g(220));
				
			foreach ($tables as $table) {
				mysql_query("DELETE FROM $table WHERE hardware_id=$idHard;", $_SESSION['OCS']["writeServer"]) or die(mysql_error());		
			}
			mysql_query("delete from download_enable where SERVER_ID=".$idHard, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));
			
			mysql_query("DELETE FROM hardware WHERE id=$idHard;", $_SESSION['OCS']["writeServer"]) or die(mysql_error());
			//Deleted computers tracking
			if($traceDel && mysql_num_rows(mysql_query("SELECT IVALUE FROM config WHERE IVALUE>0 AND NAME='TRACE_DELETED'", $_SESSION['OCS']["writeServer"]))){
				mysql_query("insert into deleted_equiv(DELETED,EQUIVALENT) values('$did',NULL)", $_SESSION['OCS']["writeServer"]) or die(mysql_error());
			}
		}
		//Using lock ? Unlock
		if( $checkLock ) 
			unlock($id);
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
			if(mysql_num_rows(mysql_query("SELECT * FROM config WHERE IVALUE>0 AND NAME='TRACE_DELETED'", $_SESSION['OCS']["readServer"]))){
				foreach($afus as $a) {	
					if($afus[$maxInd]["deviceid"]==$a["deviceid"]){continue;}
					mysql_query("insert into deleted_equiv(DELETED,EQUIVALENT) values('".$a["deviceid"]."','".$afus[$maxInd]["deviceid"]."')", $_SESSION['OCS']["writeServer"]) ;
				}
			}
			
			//KEEP OLD QUALITY,FIDELITY AND CHECKSUM
			$persistent_req = mysql_query("SELECT CHECKSUM,QUALITY,FIDELITY FROM hardware WHERE ID=".$afus[$minInd]["id"]) ;
					
			$reqDelAccount = "DELETE FROM accountinfo WHERE hardware_id=".$afus[$maxInd]["id"];
			mysql_query($reqDelAccount, $_SESSION['OCS']["writeServer"]) ;
			msg_success($l->g(190)." ".$afus[$maxInd]["deviceid"]." ".$l->g(191));
			
			$keep = array( "accountinfo",  "devices", "groups_cache" );
			foreach( $keep as $tableToBeKept ) {
				$reqRecupAccount = "UPDATE ".$tableToBeKept." SET hardware_id=".$afus[$maxInd]["id"]." WHERE hardware_id=".$afus[$minInd]["id"];			
				//echo $reqRecupAccount;
				mysql_query($reqRecupAccount, $_SESSION['OCS']["writeServer"]) ;
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
			mysql_query("UPDATE hardware SET QUALITY=".$persistent_values[1].",FIDELITY=".$persistent_values[2].",CHECKSUM=CHECKSUM|".$persistent_values[0]." WHERE id=".$afus[$maxInd]["id"]) ;
			
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
?>
