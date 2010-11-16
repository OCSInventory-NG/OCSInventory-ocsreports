<?php
@session_start();
//looking for default value of ocs config
function look_config_default_values($field_name,$like=''){
	if ($like == ''){
		$sql="select NAME,IVALUE,TVALUE,COMMENTS from config where NAME in ";
		$arg_sql=array();
		$arg=mysql2_prepare($sql,$arg_sql,$field_name);
	}else{
		$arg['SQL']="select NAME,IVALUE,TVALUE,COMMENTS from config where NAME like '%s'";
		$arg['ARG']=$field_name;		
	}
	$resdefaultvalues=mysql2_query_secure($arg['SQL'],$_SESSION['OCS']["readServer"],$arg['ARG']);		
	while($item = mysql_fetch_object($resdefaultvalues)){
			$result['name'][$item ->NAME]=$item ->NAME;
			$result['ivalue'][$item ->NAME]=$item ->IVALUE;
			$result['tvalue'][$item ->NAME]=$item ->TVALUE;
			$result['comments'][$item ->NAME]=$item ->COMMENTS;
	}
	return $result;
}
/******************************************************SQL FUNCTION****************************************************/

function mysql2_query_secure($sql,$link,$arg='',$log=false){
	global $l,$lbl_log;
	if (is_array($arg)){
		foreach ($arg as $key=>$value){
			if (!get_magic_quotes_gpc()) {			
				$arg_array_escape_string[]=mysql_real_escape_string($value);
			}else
				$arg_array_escape_string[]=$value;
		}
		$arg_escape_string=$arg_array_escape_string;
	}elseif ($arg != ''){
		if (!get_magic_quotes_gpc()) {	
			$arg_escape_string=mysql_real_escape_string($arg);
		}else
			$arg_escape_string=$arg;
	}

	if (isset($arg_escape_string)){
		if (is_array($arg_escape_string)){
				$sql = vsprintf($sql,$arg_escape_string);
		}else
			$sql = sprintf($sql,$arg_escape_string);
	}
	$query = $sql;
	if ($log){
		addLog( $log, $query,$lbl_log);
	}
	
	if ($_SESSION['OCS']['DEBUG'] == 'ON'){
		$_SESSION['OCS']['SQL_DEBUG'][]=html_entity_decode($query,ENT_QUOTES);
		//echo "<br><small><small>".$l->g(5001)."<br>".html_entity_decode($query,ENT_QUOTES)."</small></small><br>";			
	}
	$result=mysql_query( $query, $link ) or mysql_error($link);
	return $result;
}

/*
 * use this function before mysql2_query_secure
 * $sql= requeste
 * $arg_sql = arguments for mysql2_query_secure
 * $arg_tab = arguments to implode 
 * 
 */

function mysql2_prepare($sql,$arg_sql,$arg_tab='',$nocot=false){

	if (!is_array($arg_tab)){
		$arg_tab=explode(',',$arg_tab);
	}

	$sql.=" ( ";
	foreach ($arg_tab as $key=>$value){
		if (!$nocot)
		$sql.=" '%s', ";
		else
		$sql.=" %s, ";
		array_push($arg_sql,$value);			
	}
	$sql = substr($sql,0,-2) . " ) ";
	return array('SQL'=>$sql,'ARG'=>$arg_sql); 	
}

function prepare_sql_tab($list_fields,$explu=array()){
 	$begin_arg=array();
 	$begin_sql="SELECT ";
 	foreach ($list_fields as $key=>$value){
 		if (!in_array($key,$explu)){
			$begin_sql .= '%s, ';
			array_push($begin_arg,$value);		
 		}
	} 
	return array('SQL'=>substr($begin_sql,0,-2),'ARG'=>$begin_arg); 	
 	
 }
 
function dbconnect() {
	//global SERVER_READ,COMPTE_BASE,PSWD_BASE,SERVER_WRITE;
	$db = DB_NAME;
	//echo $db;
	//echo $_SESSION['OCS']["SERVER_READ"];
	$link=@mysql_connect(SERVER_READ,COMPTE_BASE,PSWD_BASE);
	if(!$link) {
		echo "<br><center><font color=red><b>ERROR: MySql connection problem<br>".mysql_error()."</b></font></center>";
		die();
	}
	if( ! mysql_select_db($db,$link)) {
		require('install.php');
		die();
	}
		
	$link2=@mysql_connect(SERVER_WRITE,COMPTE_BASE,PSWD_BASE);
	if(!$link2) {
		echo "<br><center><font color=red><b>ERROR: MySql connection problem<br>".mysql_error($link2)."</b></font></center>";
		die();
	}

	if( ! @mysql_select_db($db,$link2)) {
		require('install.php');
		die();
	}
	//if (mb_detect_encoding($value, "UTF-8") == "UTF-8" )
	//	mysql_query("SET NAMES 'utf8'");
	$_SESSION['OCS']["writeServer"] = $link2;	
	$_SESSION['OCS']["readServer"] = $link;
	return $link2;
}


/***********************************END SQL FUNCTION******************************************/

function addLog( $type, $value="",$lbl_sql='') {
	global $logHandler;
	if ($_SESSION['OCS']['LOG_GUI'] == 1){
		$dte = getDate();
		$date = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $dte["mday"], $dte["mon"], $dte["year"], $dte["hours"], $dte["minutes"], $dte["seconds"]); 
		if ($lbl_sql != ''){
			$value=$lbl_sql.' => '.$value;
		}
		@fwrite($logHandler, $_SESSION['OCS']["loggeduser"].";$date;".DB_NAME.";$type;$value;\n");
	}
}


function dateTimeFromMysql($v) {
	global $l;
	
	if( $l->g(269) == "%m/%d/%Y" )
		$ret = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $v[5].$v[6], $v[8].$v[9], $v, $v[11].$v[12],$v[14].$v[15],$v[17].$v[18]);
	else	
		$ret = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $v[8].$v[9], $v[5].$v[6], $v, $v[11].$v[12],$v[14].$v[15],$v[17].$v[18]);
	return $ret;
}


function dateToMysql($date_cible) {

	global $l;
	if(!isset($date_cible)) return "";
	
	$dateAr = explode("/", $date_cible);
	
	if( $l->g(269) == "%m/%d/%Y" ) {
		$jour  = $dateAr[1];
		$mois  = $dateAr[0];
	}
	else {
		$jour  = $dateAr[0];
		$mois  = $dateAr[1];
	}

	$annee = $dateAr[2];
	return sprintf("%04d-%02d-%02d", $annee, $mois, $jour);	
}

function reloadform_closeme($form='',$close=false){
	echo "<script>";
	if ($form != '')
		echo "window.opener.document.forms['".$form."'].submit();";
	if ($close)
		echo "self.close();";
	echo "</script>";	
}

function read_profil_file($name,$writable=''){	
	global $l;
	//Select config file depending on user profile
	$ms_cfg_file= $_SESSION['OCS']['main_sections_dir'].$name."_config.txt";
	$search=array('INFO'=>'MULTI','PAGE_PROFIL'=>'MULTI','RESTRICTION'=>'MULTI','ADMIN_BLACKLIST'=>'MULTI','CONFIGURATION'=>'MULTI');
	if (!is_writable($_SESSION['OCS']['main_sections_dir'].'old_config_files/') and $writable!='') {
		msg_error($l->g(297)." ".$_SESSION['OCS']['main_sections_dir']."old_config_files/
    				<br>".$l->g(1148));
	}
	return read_files($search,$ms_cfg_file,$writable);
}

function read_config_file($writable=''){
	//Select config file depending on user profile
	$ms_cfg_file= $_SESSION['OCS']['main_sections_dir']."4all_config.txt";
	$search=array('ORDER_FIRST_TABLE'=>'MULTI2',
				  'ORDER_SECOND_TABLE'=>'MULTI2',
				  'LBL'=>'MULTI',
				  'MENU'=>'MULTI',
				  'MENU_TITLE'=>'MULTI',
				  'MENU_NAME'=>'MULTI',
				  'URL'=>'MULTI',
				  'DIRECTORY'=>'MULTI',
				  'JAVASCRIPT'=>'MULTI');
	return read_files($search,$ms_cfg_file,$writable);
}

function read_files($search,$ms_cfg_file,$writable=''){
	global $l;
	if (!is_writable($ms_cfg_file) and $writable != '') {
		msg_error($ms_cfg_file." ".$l->g(1006).". ".$l->g(1147));
		return FALSE;
	}
	
	if (file_exists($ms_cfg_file)) {
		$profil_data=read_configuration($ms_cfg_file,$search);
		return $profil_data;
	}else
	return FALSE;		
}

function replace_language($info){
	global $l;
	if (substr($info,0,2) == 'g(')
			return	$l->g(substr(substr($info,2),0,-1));
			else	
			return $info;
}

function msg($txt,$css){
	echo "<center><div class='" . $css . "'>" . $txt . "</div></center>";	
}
function msg_info($txt){
	msg($txt,'info');
}
function msg_success($txt){
	msg($txt,'success');
}
function msg_warning($txt){
	msg($txt,'warning');
}
function msg_error($txt){
	msg($txt,'error');
}


/*
 * 
 * Encode your data on UTF-8 
 * $data can be an array or a string
 * 
 */

function data_encode_utf8($data){
	
	if (is_array($data)){
		$data_utf8=array();
		foreach ($data as $key=>$value){
			if (mb_detect_encoding($value) != "UTF-8" )
				$data_utf8[$key]=utf8_encode($value);
			else
				$data_utf8[$key]=$value;	
		}
		return $data_utf8;
	}
	//		echo $data."=>";
	//echo mb_detect_encoding("")."<br>";
	if (mb_detect_encoding($data) != "UTF-8" ){
		return utf8_encode($data);	
	}else
		return $data;
}

?>