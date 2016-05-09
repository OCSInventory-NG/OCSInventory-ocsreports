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
@session_start();
//looking for default value of ocs config
//default_values => replace with your data if config data is null or empty
//default_values => array(array())// ex: array('LOCAL_SERVER'=>array('TVALUE'=>'http:\\localhost'))
function look_config_default_values($field_name,$like='',$default_values=''){
	if ($like == ''){
		$sql="select NAME,IVALUE,TVALUE,COMMENTS from config where NAME in ";
		$arg_sql=array();
		$arg=mysql2_prepare($sql,$arg_sql,$field_name);
	}else{
		$arg['SQL']="select NAME,IVALUE,TVALUE,COMMENTS from config where NAME like '%s'";
		$arg['ARG']=$field_name;		
	}
	$resdefaultvalues=mysql2_query_secure($arg['SQL'],$_SESSION['OCS']["readServer"],$arg['ARG']);
	while($item = mysqli_fetch_object($resdefaultvalues)){
			$result['name'][$item ->NAME]=$item ->NAME;
			$result['ivalue'][$item ->NAME]=$item ->IVALUE;
			$result['tvalue'][$item ->NAME]=$item ->TVALUE;
			$result['comments'][$item ->NAME]=$item ->COMMENTS;
	}
	
	if (is_array($default_values)){
		foreach ($default_values as $key=>$value){
			$key=strtolower($key);
			if (is_array($value)){
				foreach ($value as $name=>$val){
					if (!isset($result[$key][$name]) or $result[$key][$name] == '')
						$result[$key][$name] = $val;
				}
				
			}
		}
		
	}
	
	return $result;
}
/******************************************************SQL FUNCTION****************************************************/

function generate_secure_sql($sql,$arg=''){
	if (is_array($arg)){
		foreach ($arg as $key=>$value){			
				$arg_array_escape_string[]=mysqli_real_escape_string($_SESSION['OCS']["readServer"],$value);
		}
		$arg_escape_string=$arg_array_escape_string;
	}elseif ($arg != ''){	
			$arg_escape_string=mysqli_real_escape_string($_SESSION['OCS']["readServer"],$arg);
	}
	if (isset($arg_escape_string)){
		if (is_array($arg_escape_string)){
			
				$sql = vsprintf($sql,$arg_escape_string);
		}else
			$sql = sprintf($sql,$arg_escape_string);
	}
	return $sql;
	
}


function mysql2_query_secure($sql,$link,$arg='',$log=false){
	global $l,$lbl_log;
	$query = generate_secure_sql($sql,$arg);
	if ($log){
		addLog( $log, $query,$lbl_log);
	}
	
	if ($_SESSION['OCS']['DEBUG'] == 'ON'){
		$_SESSION['OCS']['SQL_DEBUG'][]=html_entity_decode($query,ENT_QUOTES);			
	}
	

	
	if(DEMO){
		$rest = mb_strtoupper(substr($query, 0, 6));
		if ($rest == 'UPDATE' or $rest == 'INSERT' or $rest == 'DELETE'){
			if(DEMO_MSG != 'show'){
		 		msg_info($l->g(2103));
		 		define('DEMO_MSG','show');
			}
			return false;		
		 }
	}
	$result=mysqli_query( $link,$query );
	if ($_SESSION['OCS']['DEBUG'] == 'ON' and !$result)
		msg_error(mysqli_error($link));
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

	if ($arg_sql == '')
		$arg_sql = array();
		
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

function prepare_sql_tab($list_fields,$explu=array(),$distinct=false){
 	$begin_arg = array();
 	$begin_sql = "SELECT ";
 	if ($distinct)
 		$begin_sql .= " distinct ";
 	foreach ($list_fields as $key=>$value){
 		if (!in_array($key,$explu)){
			$begin_sql .= '%s, ';
			array_push($begin_arg,$value);		
 		}
	} 
	return array('SQL'=>substr($begin_sql,0,-2)." ",'ARG'=>$begin_arg); 	
 	
 }
 
function dbconnect($server,$compte_base,$pswd_base,$db = DB_NAME) {
	error_reporting(E_ALL & ~E_NOTICE);
	mysqli_report(MYSQLI_REPORT_STRICT);
	//$link is ok?
	try{
		$link = mysqli_connect($server,$compte_base,$pswd_base);
	} catch (Exception $e){
		if(mysqli_connect_errno()) {
			return  "ERROR: MySql connection problem ".$e->getCode()."<br>".$e->getMessage();
		}
	}
	//database is ok?
	if( ! mysqli_select_db($link,$db)) {
		return "NO_DATABASE";
	}
	//force UTF-8
	mysqli_query($link,"SET NAMES 'utf8'");
	//sql_mode => not strict
	mysqli_query($link,"SET sql_mode='NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");

	return $link;
}


/***********************************END SQL FUNCTION******************************************/

function addLog( $type, $value="",$lbl_sql='') {
	//global $logHandler;
	if ($_SESSION['OCS']['LOG_GUI'] == 1){
		if (is_writable(LOG_FILE)){
			$logHandler = fopen( LOG_FILE, "a");
			$dte = getDate();
			$date = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $dte["mday"], $dte["mon"], $dte["year"], $dte["hours"], $dte["minutes"], $dte["seconds"]); 
			if ($lbl_sql != ''){
				$value=$lbl_sql.' => '.$value;
			}
			$towite=$_SESSION['OCS']["loggeduser"].";".$date.";".DB_NAME.";".$type.";".$value.";".$_SERVER['REMOTE_ADDR'].";\n";
			fwrite($logHandler,$towite);
			fclose($logHandler);
		}
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
	$ms_cfg_file= $_SESSION['OCS']['CONF_PROFILS_DIR'].$name."_config.txt";
	$search=array('INFO'=>'MULTI','PAGE_PROFIL'=>'MULTI','RESTRICTION'=>'MULTI','ADMIN_BLACKLIST'=>'MULTI','CONFIGURATION'=>'MULTI');
	if (!is_writable($_SESSION['OCS']['OLD_CONF_DIR']) and $writable!='') {
		msg_error($l->g(297).":<br>".$_SESSION['OCS']['OLD_CONF_DIR']."<br>".$l->g(1148));
	}
	return read_files($search,$ms_cfg_file,$writable);
}

function read_config_file($writable=''){
	//Select config file depending on user profile
	$ms_cfg_file= $_SESSION['OCS']['CONF_PROFILS_DIR']."4all_config.txt";
	$search=array('ORDER_FIRST_TABLE'=>'MULTI2',
				  'ORDER_SECOND_TABLE'=>'MULTI2',
				  'ORDER'=>'MULTI2',
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

function msg($txt,$css,$closeid=false){
	global $protectedPost;
	
	if (isset($protectedPost['close_alert']) and $protectedPost['close_alert'] != '')
		$_SESSION['OCS']['CLOSE_ALERT'][$protectedPost['close_alert']]=1;
	
	if (!$_SESSION['OCS']['CLOSE_ALERT'][$closeid]){
		echo "<center><div id='my-alert-" . $closeid . "' class='alert alert-" . $css . " fade in' role='alert'>";
		if ($closeid != false)
		echo "<button type='button' class='close' data-dismiss='alert'><span aria-hidden='true'>×</span><span class='sr-only'>Close</span></button>";
		echo  $txt . "</div></center>";
		if ($closeid != false){
			echo "<script>$('#my-alert-" . $closeid . "').on('closed.bs.alert', function () {
			 pag('" . $closeid . "','close_alert','close_msg');
			})</script>";
			
			echo open_form('close_msg');
			echo "<input type='hidden' name='close_alert' id='close_alert' value=''>";
			echo close_form();
		}
		if ($css == 'error')
			addLog('MSG_'.$css, $txt);
	}
}
function msg_info($txt,$close=false){
	msg($txt,'info',$close);
}
function msg_success($txt,$close=false){
	msg($txt,'success',$close);
}
function msg_warning($txt,$close=false){
	msg($txt,'warning',$close);
}
function msg_error($txt,$close=false){
	msg($txt,'danger',$close);
	return true;
}


/*
 * 
 * Encode your data on UTF-8 
 * $data can be an array or a string
 * 
 */

function data_encode_utf8($data){
	return $data;
	/*if (is_array($data)){
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
		return $data;*/
}

function html_header($no_java=false){
	header("Pragma: no-cache");
	header("Expires: -1");
	header("Cache-control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-control: private", false);
	header("Content-type: text/html; charset=utf-8");
	echo "<html>
			<head>
				<TITLE>OCS Inventory</TITLE>
				<LINK REL='shortcut icon' HREF='favicon.ico' />
				<LINK REL='StyleSheet' TYPE='text/css' HREF='libraries/bootstrap/css/bootstrap.min.css'>
				<LINK REL='StyleSheet' TYPE='text/css' HREF='libraries/bootstrap/css/bootstrap-theme.min.css'>
				<LINK REL='StyleSheet' TYPE='text/css' HREF='css/bootstrap-custom.css'>
				<LINK REL='StyleSheet' TYPE='text/css' HREF='css/dataTables-custom.css'>
				<LINK REL='StyleSheet' TYPE='text/css' HREF='css/dataTables.bootstrap.css'>
				<LINK REL='StyleSheet' TYPE='text/css' HREF='css/ocsreports.css'>
				<LINK REL='StyleSheet' TYPE='text/css' HREF='css/header.css'>
				<LINK REL='StyleSheet' TYPE='text/css' HREF='css/computer_details.css'>
				<LINK REL='StyleSheet' TYPE='text/css' HREF='css/forms.css'>
				";
	if (!$no_java){
		incPicker(); 
		
		//js for graph
		echo "<script src='js/jquery-1.11.0.js' type='text/javascript'></script>";
		echo "<script src='js/jquery-migrate-1.2.1.min.js' type='text/javascript'></script>";
		echo "<script src='js/jquery.ui.widget.js'></script>";
		echo "<script src='js/jquery.iframe-transport.js'></script>";
		echo "<script src='js/jquery.fileupload.js'></script>";
		
		echo "<script src='libraries/bootstrap/js/bootstrap.min.js' type='text/javascript'></script>";
		echo "<script src='js/bootstrap-custom.js' type='text/javascript'></script>";
		
  		echo "<script src='js/graph/raphael.js' type='text/javascript'></script>";
  		echo "<script src='js/graph/elycharts.js' type='text/javascript'></script>";
  		
  		//js for Datatables 
  		echo "<script src='libraries/datatable/media/js/jquery.dataTables.js' type='text/javascript'></script>";
  		echo "<script src='js/dataTables.bootstrap.js' type='text/javascript'></script>";

  		echo "<script language='javascript' type='text/javascript' src='js/function.js'></script>";
  			
  		
		if (isset($_SESSION['OCS']['JAVASCRIPT'])){
			foreach ($_SESSION['OCS']['JAVASCRIPT'] as $file){
				echo "<script language='javascript' type='text/javascript' src='".MAIN_SECTIONS_DIR.$file."'></script>";
			}
		}
	}
	
	echo "</head>"; 

	echo "<body bottommargin='0' leftmargin='0' topmargin='0' rightmargin='0' marginheight='0' marginwidth='0'>";

}

function strip_tags_array($value='')
{
	
	if(is_object($value)){
		$value = get_class($value);
		$value = strip_tags($value,"<p><b><i><font><br><center>");
		$value = "Objet de la classe ".$value;
		return $value;
	}
	$value = is_array($value) ? array_map('strip_tags_array', $value) : strip_tags($value,"<p><b><i><font><br><center>");
	return $value;
}

function open_form($form_name,$action='',$more=''){
 	if (!isset($_SESSION['OCS']['CSRFNUMBER']) or !is_numeric($_SESSION['OCS']['CSRFNUMBER']) or $_SESSION['OCS']['CSRFNUMBER'] >= CSRF)
 		$_SESSION['OCS']['CSRFNUMBER'] = 0;
 	$form="<form name='".$form_name."' id='".$form_name."' method='POST' action='".$action."' ".$more." >";
 	$csrf_value = sha1(microtime());
 	$_SESSION['OCS']['CSRF'][$_SESSION['OCS']['CSRFNUMBER']] = $csrf_value;
 	$form.="<input type='hidden' name='CSRF_".$_SESSION['OCS']['CSRFNUMBER']."' id='CSRF_".$_SESSION['OCS']['CSRFNUMBER']."' value='".$csrf_value."'>";
 	$_SESSION['OCS']['CSRFNUMBER']++;
 	return $form;
}

function close_form(){
	return "</form>";	
}


?>
