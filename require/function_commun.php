<?php
@session_start();
//looking for default value of ocs config
function look_config_default_values($field_name,$like=''){
	if ($like == ''){
		$sql="select NAME,IVALUE,TVALUE from config where NAME in ";
		$arg_sql=array();
		$arg=mysql2_prepare($sql,$arg_sql,$field_name);
	}else{
		$arg['SQL']="select NAME,IVALUE,TVALUE from config where NAME like '%s'";
		$arg['ARG']=$field_name;		
	}
	$resdefaultvalues=mysql2_query_secure($arg['SQL'],$_SESSION['OCS']["readServer"],$arg['ARG']);		
	while($item = mysql_fetch_object($resdefaultvalues)){
			$result['name'][$item ->NAME]=$item ->NAME;
			$result['ivalue'][$item ->NAME]=$item ->IVALUE;
			$result['tvalue'][$item ->NAME]=$item ->TVALUE;
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
		echo "<br><b>".$l->g(5001)."<br>".html_entity_decode($query,ENT_QUOTES)."</b><br>";			
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

function mysql2_prepare($sql,$arg_sql,$arg_tab=''){

	if (!is_array($arg_tab)){
		$arg_tab=explode(',',$arg_tab);
	}

	$sql.=" ( ";
	foreach ($arg_tab as $key=>$value){
		$sql.=" '%s', ";
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
			$begin_sql .= '%s,';
			array_push($begin_arg,$value);		
 		}
	} 
	return array('SQL'=>substr($begin_sql,0,-1),'ARG'=>$begin_arg); 	
 	
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
		fwrite($logHandler, $_SESSION['OCS']["loggeduser"].";$date;".DB_NAME.";$type;$value;\n");
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

?>