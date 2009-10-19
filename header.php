<?php
@session_start();
//print_r($_SESSION['LANGUAGE_FILE']);
require_once('fichierConf.class.php');

/***************************************************** First installation checking *********************************************************/
if( (!$fconf=@fopen("dbconfig.inc.php","r")) 
		|| (!function_exists('session_start')) 
		|| (!function_exists('mysql_connect'))) {
	require('install.php');	
	die();
}
else
	fclose($fconf);
require_once("preferences.php");
/******************************************Checking sql update*********************************************/
if (!isset($_SESSION['SQL_BASE_VERS'])){
	$sql_log="select TVALUE from config where name='GUI_VERSION'";
	$result_log = mysql_query($sql_log, $_SESSION["readServer"]) or die(mysql_error($_SESSION["readServer"]));
	while($value=mysql_fetch_array($result_log))
		$_SESSION['SQL_BASE_VERS'] = $value['TVALUE'];	
}
if (GUI_VER	> $_SESSION['SQL_BASE_VERS']){
	unset($_SESSION['SQL_BASE_VERS']);
	require('install.php');
	die();	
}
//SECURITY
$protectedPost=escape_string($_POST);
$protectedGet=escape_string($_GET);
//print_r($GLOBALS);
@set_time_limit(0);
//global $protectedPost,$protectedGet;

//pour ne pas tenir compte des erreurs renvoy�s par l'identification
//pour le fuser, la variable $no_error est = 'YES'
if (!isset($no_error))
$no_error='NO';
/**************************************mise en place des r�pertoires de plugins et d'auhentification************************************/
if (!isset($_SESSION['plugins_dir']) or !isset($_SESSION['CONF_MYSQL'])){
//	$rep=explode("/", $_SERVER["DOCUMENT_ROOT"].$_SERVER["PHP_SELF"]);
//	array_pop($rep);
	$_SESSION['backend']="backend/";
	$_SESSION['plugins_dir']="plugins/";
	$_SESSION['CONF_MYSQL']="dbconfig.inc.php";
	$_SESSION['HEADER_HTML']="require/html_header.php";
	$_SESSION['FOOTER_HTML']="footer.php";
}

/*****************************************************GESTION DU NOM DES PAGES****************************************/
//Config for all user
$ms_cfg_file=$_SESSION['plugins_dir']."main_sections/4all_config.txt";
if (file_exists($ms_cfg_file)) {
      $fd = fopen ($ms_cfg_file, "r");
      $capture='';
      while( !feof($fd) ) {
         $line = trim( fgets( $fd, 256 ) );
         		
		 if (substr($line,0,2) == "</")
            $capture='';
            
         if ($capture == 'OK_URL'){
            $tab_url=explode(":", $line);
         //   $list_url[$tab_url[0]]=$tab_url[1];
            $pages_refs[$tab_url[0]]=$tab_url[1];
         }
         
         if ($line{0} == "<"){ 	//Getting tag type for the next launch of the loop
            $capture = 'OK_'.substr(substr($line,1),0,-1);
         }                  
      }
   fclose( $fd );
}


/*****************************************************GESTION DU LOGOUT*********************************************/
if ($protectedPost['LOGOUT'] == 'ON'){
	unset($_SESSION["loggeduser"],
		  $_SESSION["lvluser"],
		  $_SERVER['PHP_AUTH_USER'],
		  $_SESSION["mesmachines"],
		  $_SESSION['TRUE_USER'],
		  $_SESSION['TRUE_LVL'],
		  $_SESSION['DEBUG'],
		  $_SESSION["ipdiscover"],
		  $_SESSION["mytag"],
		  $_SESSION["LANGUAGE_FILE"],
		  $_SESSION['LOG_GUI']);
}
/**********************************************************GESTION DES COLONNES DES TABLEAUX PAR COOKIES***********************************/
require_once('require/function_cookies.php');
if (isset($protectedPost['SUP_COL']) and $protectedPost['SUP_COL'] != "" and isset($_SESSION['col_tab'][$protectedPost['TABLE_NAME']])){
	unset($_SESSION['col_tab'][$tab_name][$protectedPost['SUP_COL']]);
	cookies_add($protectedPost['TABLE_NAME'],implode('///',$_SESSION['col_tab'][$protectedPost['TABLE_NAME']]));
}
if (isset($protectedPost['RAZ']) and $protectedPost['RAZ'] != ""){
	cookies_reset($protectedPost['TABLE_NAME']);
}
if (isset($protectedPost['restCol'.$protectedPost['TABLE_NAME']]) and $protectedPost['restCol'.$protectedPost['TABLE_NAME']] != ''){
	$_SESSION['col_tab'][$tab_name][$protectedPost['restCol'.$tab_name]]=$protectedPost['restCol'.$tab_name];
	cookies_add($protectedPost['TABLE_NAME'],implode('///',$_SESSION['col_tab'][$protectedPost['TABLE_NAME']]));
}

/********************************************************GESTION DE LA LANGUE PAR COOKIES**********************************************/
/*****************************************************Gestion des fichiers de langues  TEST*************************************/
if (isset($protectedPost['Valid_EDITION_x'])){
	if ($protectedPost['ID_WORD'] != ''){
		if ($protectedPost['ACTION'] == "DEL"){
			unset($_SESSION['LANGUAGE_FILE']->tableauMots[$protectedPost['ID_WORD']]);
		}else{
			$_SESSION['LANGUAGE_FILE']->tableauMots[$protectedPost['ID_WORD']]=$protectedPost['UPDATE'];
		}
		$sql="update languages set json_value = '".mysql_real_escape_string(json_encode($_SESSION['LANGUAGE_FILE']->tableauMots))."'
				where name= '".$_SESSION['LANGUAGE']."'"; 
		if( ! @mysql_query( $sql, $_SESSION["writeServer"] ))
				echo mysql_error($_SESSION["writeServer"]);
		}
}
unset($_SESSION['EDIT_LANGUAGE']);


if (isset($protectedPost['LANG'])){
	unset($_SESSION['LANGUAGE']);
	cookies_add('LANG',$protectedPost['LANG']);	
	$_SESSION['LANGUAGE']=$protectedPost['LANG'];
	$_SESSION["LANGUAGE_FILE"]=new language($_SESSION['LANGUAGE']);
}
//unset($_SESSION['LANGUAGE']);
//si la langue par d�faut n'existe pas, on r�cup�rer le cookie
if (!isset($_SESSION['LANGUAGE']) or !isset($_SESSION["LANGUAGE_FILE"])){
	if (isset($_COOKIE['LANG']))
	$_SESSION['LANGUAGE']=$_COOKIE['LANG'];
	if (!isset($_COOKIE['LANG']))
	$_SESSION['LANGUAGE']=DEFAULT_LANGUAGE;
	$_SESSION["LANGUAGE_FILE"]=new language($_SESSION['LANGUAGE']);
}
$l = $_SESSION["LANGUAGE_FILE"];

/*********************************************************gestion de l'authentification****************************************************/
if (!isset($_SESSION["loggeduser"]))
require_once('backend/AUTH/auth.php');

/**********************************************************gestion des droits sur les TAG****************************************************/
if (!isset($_SESSION["lvluser"]))
require_once('backend/identity/identity.php');



/**********************************************************gestion des droits sur l'ipdiscover****************************************************/
if (!isset($_SESSION["ipdiscover"]) and $protectedGet[PAG_INDEX] == $pages_refs['ms_ipdiscover'])
require_once('backend/ipdiscover/ipdiscover.php');
elseif($protectedGet[PAG_INDEX] != $pages_refs['ms_ipdiscover'])
unset($_SESSION['ipdiscover']);

/*********************************************************gestion de la suppression automatique des machines trop vieilles*************************/
//require_once('plugins/options_config/del_old_computors.php');

/***********************************************************gestion des logs*************************************************************************/
if (!isset($_SESSION['LOG_GUI'])){
	$sql_log="select name,ivalue,tvalue from config where name= 'LOG_GUI' or name='LOG_DIR' or name='LOG_SCRIPT'";
	$result_log = mysql_query($sql_log, $_SESSION["readServer"]) or die(mysql_error($_SESSION["readServer"]));
	while($value_log=mysql_fetch_array($result_log)) {
		if ($value_log["name"] == 'LOG_GUI')
			$_SESSION['LOG_GUI'] = $value_log['ivalue'];
		if ($value_log["name"] == 'LOG_DIR')
			$_SESSION['LOG_DIR'] = $value_log['tvalue'];
		if ($value_log["name"] == 'LOG_SCRIPT')
			$_SESSION['LOG_SCRIPT'] = $value_log['tvalue'];
	}
	if (!isset($_SESSION['LOG_GUI']))
		$_SESSION['LOG_GUI']=0;
	if (!isset($_SESSION['LOG_DIR']))
		$_SESSION['LOG_DIR']='';
	if (!isset($_SESSION['LOG_SCRIPT']))
		$_SESSION['LOG_SCRIPT']='';
}
/****************END GESTION LOGS***************/

/*********************************************GESTION OF LBL_TAG*************************************/
if (!isset($_SESSION['LBL_TAG'])){
	$sql_tag="select tvalue from config where name= 'LBL_TAG'";
	$result_tag = mysql_query($sql_tag, $_SESSION["readServer"]) or die(mysql_error($_SESSION["readServer"]));
	$value_tag=mysql_fetch_array($result_tag);
	if ($value_tag["tvalue"] != '')
		$_SESSION['TAG_LBL'] = $value_tag['tvalue'];
	else
		$_SESSION['TAG_LBL'] = "TAG";
}



if ((!isset($header_html) or $header_html != 'NO') and !isset($protectedGet['no_header'])){
	require_once ($_SESSION['HEADER_HTML']);
	//echo "toto";
}

if((!isset($_SESSION["loggeduser"]) or !isset($_SESSION["lvluser"]) or $_SESSION["lvluser"] == "") and $no_error != 'YES')
{		
	echo "<br><br><center><b><font color=red>".$LIST_ERROR."</font></b></center><br>";
	require_once($_SESSION['FOOTER_HTML']);
	die();
}


?>
