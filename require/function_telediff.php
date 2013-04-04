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


echo "<script language='javascript'>
	function active(id, sens) {
		var mstyle = document.getElementById(id).style.display	= (sens!=0?\"block\" :\"none\");
	}</script>";

function javascript_pack(){
	global $protectedPost;
 echo "<script language='javascript'>
	function time_deploy(name,name_value,other_name,other_value){
		var tps_cycle=".$_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_CYCLE_LATENCY']*$_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_PERIOD_LENGTH'].";
		var nb_frag_by_cycle=".($protectedPost['PRIORITY'] != 0 ? floor($_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_PERIOD_LENGTH']/$protectedPost['PRIORITY']): $_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_PERIOD_LENGTH']).";
		if (name == 'tailleFrag'){
			var taille=name_value;
			var nb_frag=other_value;
		}
		else{
			var taille=other_value;
			var nb_frag=name_value;
		}
		var nb_cycle_for_download=nb_frag/nb_frag_by_cycle;
		var tps_cycle_for_download = nb_cycle_for_download*tps_cycle;
		var tps_frag_latency=nb_frag_by_cycle*".$_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_FRAG_LATENCY']."*nb_cycle_for_download;
		var tps_period_latency=".$_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_PERIOD_LATENCY']."*nb_cycle_for_download;
		var download_speed=25000;
		var tps_download_speed=taille/download_speed;
		var tps_total=tps_cycle_for_download+tps_frag_latency+tps_period_latency+tps_download_speed;
		var heure=Math.floor(tps_total/3600);
		tps_total=tps_total-heure*3600;
		var minutes=Math.floor(tps_total/60);
		tps_total=Math.floor(tps_total-minutes*60);
		var affich=heure+'h'+minutes+'m'+tps_total+'s';
		document.getElementById('TPS').value = affich;
	}

	function maj(name,other_field,siz){
		if (document.getElementById(name).value != '' &&  document.getElementById(name).value != 0){
			if ( Math.ceil(document.getElementById(name).value*1024) < siz)							
			document.getElementById(other_field).value = Math.ceil( siz / (Math.ceil(document.getElementById(name).value*1024)) );	
			else{
			document.getElementById(other_field).value = 1;
			document.getElementById(name).value=Math.ceil(siz/1024)
			}
		}else
		document.getElementById(other_field).value = '';
		time_deploy(name,document.getElementById(name).value,other_field,document.getElementById(other_field).value);
	}
	</script>";
}
function looking4config(){
	if (!isset($_SESSION['OCS']['CONFIG_DOWNLOAD'])){
		$values=look_config_default_values(array('DOWNLOAD_CYCLE_LATENCY','DOWNLOAD_PERIOD_LENGTH',
							'DOWNLOAD_FRAG_LATENCY','DOWNLOAD_PERIOD_LATENCY'));
		$_SESSION['OCS']['CONFIG_DOWNLOAD']=$values['ivalue'];
	}
}
	
	
	
function champ_select_block($name,$input_name,$input_cache)
{
		global $protectedPost;
		$champs="<select name='".$input_name."' id='".$input_name."'";
		$champs.=" onChange='";
		if ($input_name == "ACTION"){
			$i=0;
			while ($input_cache[$i]){
			$champs.="active(\"".$input_cache[$i]."\", false);";
			$i++;
			}
			$champs.="active(this.value + \"_div\", true);";
		}else{
			foreach ($input_cache as $key=>$value)
				$champs.="active(\"".$key."_div\", this.value==\"".$value."\");";
		}

		$champs.="'><option value=''></option>";
		foreach ($name as $key=>$value){
			$champs.= "<option value=\"".$key."\"";
			if ($protectedPost[$input_name] == $key )
			$champs.= " selected";
			$champs.= ">".$value."</option>";
		}
		$champs.="</select>";
		return $champs;
	
}
 
 function time_deploy(){
 	
 	$champ="<input id='TPS' name='TPS' size='10' readonly style='color:black; background-color:#e1e1e2;'> ";
 	return $champ;
 }
 
 
 
 function input_pack_taille($name,$other_field,$size,$input_size,$input_value){
	javascript_pack();
 	$champ.= "<input id='".$name."' name='".$name."' size='".$input_size."'";
	if( $size > 1024 ) { 
		$champ.= "	onKeyPress='maj(\"".$name."\",\"".$other_field."\",\"".$size."\");'
		 				onkeydown='maj(\"".$name."\",\"".$other_field."\",\"".$size."\");' onkeyup='maj(\"".$name."\",\"".$other_field."\",\"".$size."\");' value='".$input_value."'
		  				onblur='maj(\"".$name."\",\"".$other_field."\",\"".$size."\");'  onclick='maj(\"".$name."\",\"".$other_field."\",\"".$size."\");'> ";
	}else
	 	$champ.= " value=1 readonly style='color:black; background-color:#e1e1e2;'> ";
	 return $champ;
 	
 }
 
 function desactive_option($name,$list_id,$packid){
 	global $l;
 	$sql_desactive="delete from devices where name='%s' and ivalue=%s";
 	$arg_desactive=array($name,$packid);
 	if ($list_id != ''){
 		$sql_desactive.=" and hardware_id in ";
 		$sql=mysql2_prepare($sql_desactive,$arg_desactive,$list_id);
 		$res_desactive=mysql2_query_secure($sql['SQL'],$_SESSION['OCS']["writeServer"],$sql['ARG'],$l->g(512));
 	}else
 		$res_desactive=mysql2_query_secure($sql_desactive,$_SESSION['OCS']["writeServer"],$arg_desactive,$l->g(512));
 	return( mysql_affected_rows ( $_SESSION['OCS']["writeServer"] ) );
 }
 
 function active_option($name,$list_id,$packid,$tvalue=''){
 	global $l;
 	desactive_option($name,$list_id,$packid);
 	$sql_active="insert into devices (HARDWARE_ID, NAME, IVALUE,TVALUE) select ID,'%s','%s',";
	if ($tvalue == ''){
		$sql_active.="null from hardware where id in ";
		$arg_active=array($name,$packid);
	}else{
		$sql_active.="'%s' from hardware where id in ";
		$arg_active=array($name,$packid,$tvalue);
	}
	//$lbl_log=$l->g(601)." ".$id_pack." => ".$list_id;
	$sql=mysql2_prepare($sql_active,$arg_active,$list_id);
	$res_active=mysql2_query_secure($sql['SQL'],$_SESSION['OCS']["writeServer"],$sql['ARG'],$l->g(512));
	return( mysql_affected_rows ( $_SESSION['OCS']["writeServer"] ) );
 }
 
 function desactive_download_option($list_id,$packid){
 	desactive_option('DOWNLOAD_FORCE',$list_id,$packid);
 	desactive_option('DOWNLOAD_SCHEDULE',$list_id,$packid);
 	desactive_option('DOWNLOAD_POSTCMD',$list_id,$packid); 	
 }
 
 function desactive_packet($list_id,$packid){
 	desactive_download_option($list_id,$packid);
 	$nb_line=desactive_option('DOWNLOAD',$list_id,$packid); 
 	return $nb_line;	 	
 }
 
 
 function found_id_pack($packid){
 	$sql_id_pack="select ID from download_enable where fileid=%s and ( group_id = '' or group_id is null)";
 	$arg_id_pack=$packid;
 	$result = mysql2_query_secure($sql_id_pack,$_SESSION['OCS']["readServer"],$arg_id_pack);
	$id_pack = mysql_fetch_array( $result );
	return $id_pack['ID']; 	
 }
 
 function active_serv($list_id,$packid,$id_rule){
 	global $l;
 	require_once('function_server.php');
 	//get all condition of this rule
	$sql="select PRIORITY,CFIELD,OP,COMPTO,SERV_VALUE from download_affect_rules where rule=%s order by PRIORITY";
	$arg=$id_rule;
	$res_rules = mysql2_query_secure( $sql, $_SESSION['OCS']["readServer"],$arg );
	while( $val_rules = mysql_fetch_array($res_rules)) {
		$cfield[$val_rules['PRIORITY']]=$val_rules['CFIELD'];
		$op[$val_rules['PRIORITY']]=$val_rules['OP'];
		$compto[$val_rules['PRIORITY']]=$val_rules['COMPTO'];
	}
	$nb_insert=0;
	foreach ($cfield as $key=>$value)
	{
		$rule_detail=array('cfield'=>$cfield[$key],'op'=>$op[$key],'compto'=>$compto[$key]);
		$result=insert_with_rules($list_id,$rule_detail,$packid);
		$nb_insert+=$result['nb_insert'];
		$m=0;
		while ($result['exist'][$m]){
			$exist[]=$result['exist'][$m];
			$m++;
		}
		$nb_exist += $result['nb_exist'];
	
		if ($result['not_match'] == "")
		break;
		else{
			unset($list_id);
			$list_id=$result['not_match'];
		}
	}

	if (isset($result['not_match']))
	{
		tab_list_error($result['not_match'],$result['nb_not_match']." ".$l->g(658)." ".$l->g(887)."<br>");
	}
	
	if (isset($exist))
	{
			tab_list_error($exist,$nb_exist." ".$l->g(659)." ".$l->g(482));
	}
 	return $nb_insert;
 }
 
 function loadInfo( $serv, $tstamp ) {
	
	$fname = $serv."/".$tstamp."/info";
	$info =@file_get_contents( $fname );
	if( ! $info )
		return false;
		
	@preg_match_all( "/((?:\d|\w)+)=\"((?:\d|\w)+)\"/", $info, $resul );
	if( ! $resul )
		return false;
	$noms = array_flip( $resul[1] );
	foreach( $noms as $nom=>$int ) {
		$noms[ $nom ] = $resul[2][$int];
	}
	return( $noms );
}
 
function activ_pack($fileid,$https_server,$file_serv){
	global $l;
//checking if corresponding available exists
		$reqVerif = "SELECT * FROM download_available WHERE fileid=%s";
		$argVerif = $fileid;
		if( ! mysql_num_rows( mysql2_query_secure( $reqVerif, $_SESSION['OCS']["readServer"],$argVerif) )) {
			
			$infoTab = loadInfo( $https_server, $file_serv );
			if ($infoTab == ''){
				$infoTab= array("PRI"=>'10',"FRAGS"=>'0');
				
			}
			$req1 = "INSERT INTO download_available(FILEID, NAME, PRIORITY, FRAGMENTS, OSNAME ) VALUES
			( '%s', 'Manual_%s',%s,%s, 'N/A' )";
			$arg1=array($fileid,$fileid,$infoTab["PRI"],$infoTab["FRAGS"]);
			mysql2_query_secure( $req1, $_SESSION['OCS']["writeServer"],$arg1);
		}
		
		$req = "INSERT INTO download_enable(FILEID, INFO_LOC, PACK_LOC, CERT_FILE, CERT_PATH ) VALUES
		( '%s', '%s', '%s', 'INSTALL_PATH/cacert.pem','INSTALL_PATH')";
		$arg=array($fileid,$https_server,$file_serv);
		$lbl_log= $l->g(514)." ".$fileid;	
		mysql2_query_secure( $req, $_SESSION['OCS']["writeServer"],$arg,$l->g(512));
		
} 
 
function activ_pack_server($fileid,$https_server,$id_server_group){
	global $protectedPost;
		//search all computers have this package
		$sqlDoub="select SERVER_ID,INFO_LOC from download_enable where FILEID= %s";
		$argDoub = $fileid;
		$resDoub = mysql2_query_secure( $sqlDoub, $_SESSION['OCS']["readServer"], $argDoub );	
		
		//exclu them
		while ($valDoub = mysql_fetch_array( $resDoub )){
			if ($valDoub['SERVER_ID'] != "")
			$listDoub[]=$valDoub['SERVER_ID'];
	
			//Update https server location if different from mysql database
			if ($valDoub['INFO_LOC'] != $https_server) {
				$sql_update_https= "UPDATE download_enable SET download_enable.INFO_LOC='%s' WHERE SERVER_ID=%s";
				$arg_update_https=array($https_server,$valDoub['SERVER_ID']);
				mysql2_query_secure( $sql_update_https, $_SESSION['OCS']["readServer"], $arg_update_https );
			}

		}
		//If this list is not null, we create the end of sql request
		if (isset($listDoub)){
			$listDoub = " AND HARDWARE_ID not in (".implode(',',$listDoub).")";
		}
		//on insert l'activation du paquet pour les serveurs du groupe
		$sql="insert into download_enable (FILEID,INFO_LOC,PACK_LOC,CERT_PATH,CERT_FILE,SERVER_ID,GROUP_ID)
				select %s,'%s',url,'INSTALL_PATH','INSTALL_PATH/cacert.pem',
				 HARDWARE_ID, GROUP_ID from download_servers where GROUP_ID=%s".$listDoub;
		$arg=array($fileid,$https_server,$id_server_group);
		
		mysql2_query_secure( $sql, $_SESSION['OCS']["writeServer"],$arg);
		
		$query="UPDATE download_available set COMMENT = '%s' WHERE FILEID = %s";
		$arg_query=array($protectedPost['id_server_add'],$fileid);
		mysql2_query_secure( $query, $_SESSION['OCS']["writeServer"], $arg_query );
}

function del_pack($fileid){
	global $l;
	//find all activate package
	$reqEnable = "SELECT id FROM download_enable WHERE FILEID='%s'";
	$argEnable = $fileid;
	$resEnable = mysql2_query_secure($reqEnable, $_SESSION['OCS']["readServer"],$argEnable);
	while($valEnable = mysql_fetch_array( $resEnable ) ) {
		$list_id[]=$valEnable["id"];
	}
	//delete packet in DEVICES table
	if ($list_id != ""){
		foreach ($list_id as $k=>$v){
			desactive_packet('',$v);
		}
	}
	//delete activation of this pack
	$reqDelEnable = "DELETE FROM download_enable WHERE FILEID='%s'";
	$argDelEnable = $fileid;
	mysql2_query_secure($reqDelEnable, $_SESSION['OCS']["writeServer"],$argDelEnable);

	//delete info of this pack
	$reqDelAvailable = "DELETE FROM download_available WHERE FILEID='%s'";
	$argDelAvailable = $fileid;	
	mysql2_query_secure($reqDelAvailable, $_SESSION['OCS']["writeServer"],$argDelAvailable);
	//what is the directory of this package?
	$info=look_config_default_values('DOWNLOAD_PACK_DIR');
	$document_root=$info['tvalue']['DOWNLOAD_PACK_DIR'];
	//if no directory in base, take $_SERVER["DOCUMENT_ROOT"]
	if (!isset($document_root))
	$document_root = $_SERVER["DOCUMENT_ROOT"];
	if (@opendir($document_root."/download/".$fileid)){
		//delete all files from this package
		if( ! @recursive_remove_directory( $document_root."/download/".$fileid ))  {
			msg_error($l->g(472)." ".$document_root."/download/".$fileid);
		}
	}
	addLog($l->g(512), $l->g(888)." ".$fileid );
}

function recursive_remove_directory($directory, $empty=FALSE) {
     if(substr($directory,-1) == '/')
         $directory = substr($directory,0,-1);
     
     if(!file_exists($directory) || !is_dir($directory))
         return FALSE;
     elseif(is_readable($directory)) {     
         $handle = opendir($directory);
         while (FALSE !== ($item = readdir($handle))) {
             if($item != '.' && $item != '..') {
                 $path = $directory.'/'.$item;
                 if(is_dir($path))
				 	recursive_remove_directory($path);
                 else
                 	unlink($path);               
             }
         }
         closedir($handle);
         if($empty == FALSE) {
             if(!rmdir($directory))
                 return FALSE;
         }
     }
     return TRUE;
}

function create_pack($sql_details,$info_details){
	global $l;
	$info_details=xml_escape_string($info_details);
	//get temp file
	$fname = $sql_details['document_root'].$sql_details['timestamp']."/tmp";
	//cut this package
	if( $size = @filesize( $fname )) {
			$handle = fopen ( $fname, "rb");			
			$read = 0;
			for( $i=1; $i<$sql_details['nbfrags']; $i++ ) {
				$contents = fread ($handle, $size / $sql_details['nbfrags'] );
				$read += strlen( $contents );
				$handfrag = fopen( $sql_details['document_root'].$sql_details['timestamp']."/".$sql_details['timestamp']."-".$i, "w+b" );
				fwrite( $handfrag, $contents );
				fclose( $handfrag );
			}	
			
			$contents = fread ($handle, $size - $read);
			$read += strlen( $contents );
			$handfrag = fopen( $sql_details['document_root'].$sql_details['timestamp']."/".$sql_details['timestamp']."-".$i, "w+b" );
			fwrite( $handfrag, $contents );
			fclose( $handfrag );
			fclose ($handle);
	
			unlink( $sql_details['document_root'].$sql_details['timestamp']."/tmp" );
		}else{
			if (!file_exists( $sql_details['document_root'].$sql_details['timestamp']))			
				mkdir( $sql_details['document_root'].$sql_details['timestamp']);
		}
		//if $info_details['DIGEST'] is null =>  no file to deploy, only execute commande in info file
		// so nb_frag=0
		if (!isset($info_details['DIGEST']) or $info_details['DIGEST'] == "")
			$sql_details['nbfrags']=0;
		
		//create info
		$info = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; 
		$info .= "<DOWNLOAD ID=\"".$sql_details['timestamp']."\" ".
		"PRI=\"".$info_details['PRI']."\" ".
		"ACT=\"".$info_details['ACT']."\" ".
		"DIGEST=\"".$info_details['DIGEST']."\" ".		
		"PROTO=\"".	$info_details['PROTO']."\" ".
		"FRAGS=\"".$sql_details['nbfrags']."\" ".
		"DIGEST_ALGO=\"".$info_details['DIGEST_ALGO']."\" ".
		"DIGEST_ENCODE=\"".$info_details['DIGEST_ENCODE']."\" ";		
		if ($info_details['ACT'] == 'STORE')
		$info .="PATH=\"".$info_details['PATH']."\" ";
		if ($info_details['ACT'] == 'LAUNCH')
		$info .="NAME=\"".$info_details['NAME']."\" ";
		if ($info_details['ACT'] == 'EXECUTE')
		$info .="COMMAND=\"".$info_details['COMMAND']."\" ";
		
		$info .="NOTIFY_USER=\"".$info_details['NOTIFY_USER']."\" ".
		"NOTIFY_TEXT=\"".$info_details['NOTIFY_TEXT']."\" ".
		"NOTIFY_COUNTDOWN=\"".$info_details['NOTIFY_COUNTDOWN']."\" ".
		"NOTIFY_CAN_ABORT=\"".$info_details['NOTIFY_CAN_ABORT']."\" ".
		"NOTIFY_CAN_DELAY=\"".$info_details['NOTIFY_CAN_DELAY']."\" ".
		"NEED_DONE_ACTION=\"".$info_details['NEED_DONE_ACTION']."\" ".		
		"NEED_DONE_ACTION_TEXT=\"".$info_details['NEED_DONE_ACTION_TEXT']."\" ".		
		"GARDEFOU=\"".$info_details['GARDEFOU']."\" />\n";
		
		$handinfo = fopen( $sql_details['document_root'].$sql_details['timestamp']."/info", "w+" );
		fwrite( $handinfo, utf8_decode($info));
		fclose( $handinfo );
		
		//delete all package with the same id
		mysql2_query_secure( "DELETE FROM download_available WHERE FILEID='%s'", $_SESSION['OCS']["writeServer"],$sql_details['timestamp']);
		//insert new package
		$req = "INSERT INTO download_available(FILEID, NAME, PRIORITY, FRAGMENTS, SIZE, OSNAME, COMMENT,ID_WK) VALUES
		( '%s', '%s','%s', '%s','%s', '%s', '%s','%s' )";
		$arg = array($sql_details['timestamp'],$sql_details['name'],$info_details['PRI'],$sql_details['nbfrags'],
					 $sql_details['size'],$sql_details['os'],$sql_details['description'],$sql_details['id_wk']);
		mysql2_query_secure( $req, $_SESSION['OCS']["writeServer"], $arg);
		addLog($l->g(512), $l->g(617)." ".$sql_details['timestamp'] );
		//info message
		msg_success($l->g(437)." ".$sql_details['document_root'].$sql_details['timestamp']);
		//delete cache for activation
		unset($_SESSION['OCS']['DATA_CACHE']['LIST_PACK']);
		unset($_SESSION['OCS']['NUM_ROW']['LIST_PACK']);	
}

function crypt_file($dir_FILES,$digest_algo,$digest_encod){
	//crypt this file
	if( $digest_algo == "SHA1" )
		$digest = sha1_file($dir_FILES,true);
	else
		$digest = md5_file($dir_FILES);
	
	if( $digest_encod == "Base64" )
		$digest = base64_encode( $digest );
	return $digest;		
}

function creat_temp_file($directory,$dir_FILES){
	if (!file_exists ($directory."/tmp")){
		if (! @mkdir( $directory) 
		or !copy( $dir_FILES, $directory."/tmp" )
			)
			msg_error("ERROR: can't create or write in ".$directory." folder, please refresh when fixed.<br>(or try disabling php safe mode)");
	}
}
//$val_details['priority'],$val_details['fragments'],$val_details['size']
function tps_estimated($val_details)
{
	global $l;
	if ($val_details == "")
	return;
	/*********************************DETAIL SUR LE TEMPS APPROXIMATIF DE TELEDEPLOIEMENT*****************************************/
//	$sql_config="select name,ivalue from config where name in ('DOWNLOAD_CYCLE_LATENCY',
//					    'DOWNLOAD_PERIOD_LENGTH',
//					    'DOWNLOAD_FRAG_LATENCY',
//	    				'DOWNLOAD_PERIOD_LATENCY')";
//	$res_config = mysql_query( $sql_config, $_SESSION['OCS']["readServer"] );
//	while ($val_config = mysql_fetch_array( $res_config ))
//	$config[$val_config['name']]=$val_config['ivalue'];
	looking4config();
	if ($val_details['priority'] == 0)
	$val_details['priority']=1;
	//dur�e compl�te d'un cycle en seconde
	$tps_cycle=$_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_CYCLE_LATENCY']*$_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_PERIOD_LENGTH'];
	//nbre de t�l�chargement de fragment par cycle
	$nb_frag_by_cycle=floor($_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_PERIOD_LENGTH']/$val_details['priority']);
	//nombre de cycles necessaires pour le t�l�chargement complet
	$nb_cycle_for_download=$val_details['fragments']/$nb_frag_by_cycle;
	//temps dans le cycle
	$tps_cycle_for_download=$nb_cycle_for_download*$tps_cycle;
	//temps entre chaque fragment pour tous les cycles
	$tps_frag_latency=($nb_frag_by_cycle*$_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_FRAG_LATENCY'])*$nb_cycle_for_download;
	//temps entre chaque p�riode
	$tps_period_latency=$_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_PERIOD_LATENCY']*$nb_cycle_for_download;
	//ajout de la vitesse de t�l�chargement
	$download_speed=25000;
	$tps_download_speed=$val_details['size']/$download_speed;
	
	//temps total de t�l�chargement:
	$tps_total=$tps_cycle_for_download
				+$tps_frag_latency
				+$tps_period_latency
				+$tps_download_speed
				;
	$heure= floor($tps_total/3600);
	$tps_total-=$heure*3600;
	$minutes=floor($tps_total/60);
	$tps_total-=$minutes*60;
	$tps= $heure."h ".$minutes."min ";
	if ($heure == 0 and $minutes == 0)
	$tps.=floor($tps_total)." ".$l->g(511);
	return $tps;
	
}


function found_info_pack($id){
	global $l;
	if (!is_numeric($id))
		return array('ERROR'=>$l->g(1129));
	
	$sql="select NAME,PRIORITY,FRAGMENTS,SIZE,OSNAME,COMMENT from download_available where fileid=%s";
	$res = mysql2_query_secure( $sql, $_SESSION['OCS']["readServer"],$id);
	$val = mysql_fetch_array( $res );
	return $val;
	
}

?>
