<?php
@session_start();
//print_r($_SESSION['OCS']['LANGUAGE_FILE']);
require_once('fichierConf.class.php');
/*****************************************************GESTION DU LOGOUT*********************************************/
if ($_POST['LOGOUT'] == 'ON'){
	unset($_SESSION['OCS']);
}

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
if (!isset($_SESSION['OCS']['SQL_BASE_VERS'])){
	$sql_log="select TVALUE from config where name='GUI_VERSION'";
	$result_log = mysql_query($sql_log, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
	while($value=mysql_fetch_array($result_log))
		$_SESSION['OCS']['SQL_BASE_VERS'] = $value['TVALUE'];	
}
if (GUI_VER	> $_SESSION['OCS']['SQL_BASE_VERS']){
	unset($_SESSION['OCS']['SQL_BASE_VERS']);
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
if (!isset($_SESSION['OCS']['plugins_dir']) or !isset($_SESSION['OCS']['CONF_MYSQL'])){
//	$rep=explode("/", $_SERVER["DOCUMENT_ROOT"].$_SERVER["PHP_SELF"]);
//	array_pop($rep);
	$_SESSION['OCS']['backend']="backend/";
	$_SESSION['OCS']['plugins_dir']="plugins/";
	$_SESSION['OCS']['CONF_MYSQL']="dbconfig.inc.php";
	$_SESSION['OCS']['HEADER_HTML']="require/html_header.php";
	$_SESSION['OCS']['FOOTER_HTML']="footer.php";
	$_SESSION['OCS']['main_sections_dir']=$_SESSION['OCS']['plugins_dir']."main_sections/";
}

/*****************************************************GESTION DU NOM DES PAGES****************************************/
//Config for all user
if (!isset($_SESSION['OCS']['URL'])){
	require_once('require/function_files.php');
	$ms_cfg_file= $_SESSION['OCS']['plugins_dir']."main_sections/4all_config.txt";	
	//show only true sections
	if (file_exists($ms_cfg_file)) {
		$search=array('URL'=>'MULTI');
		$profil_data=read_configuration($ms_cfg_file,$search);
		$pages_refs=$profil_data['URL'];
	}
}
$pages_refs=$_SESSION['OCS']['URL'];
/**********************************************************GESTION DES COLONNES DES TABLEAUX PAR COOKIES***********************************/
require_once('require/function_cookies.php');
if (isset($protectedPost['SUP_COL']) and $protectedPost['SUP_COL'] != "" and isset($_SESSION['OCS']['col_tab'][$protectedPost['TABLE_NAME']])){
	unset($_SESSION['OCS']['col_tab'][$tab_name][$protectedPost['SUP_COL']]);
	cookies_add($protectedPost['TABLE_NAME'],implode('///',$_SESSION['OCS']['col_tab'][$protectedPost['TABLE_NAME']]));
}
if (isset($protectedPost['RAZ']) and $protectedPost['RAZ'] != ""){
	cookies_reset($protectedPost['TABLE_NAME']);
}
if (isset($protectedPost['restCol'.$protectedPost['TABLE_NAME']]) and $protectedPost['restCol'.$protectedPost['TABLE_NAME']] != ''){
	$_SESSION['OCS']['col_tab'][$tab_name][$protectedPost['restCol'.$tab_name]]=$protectedPost['restCol'.$tab_name];
	cookies_add($protectedPost['TABLE_NAME'],implode('///',$_SESSION['OCS']['col_tab'][$protectedPost['TABLE_NAME']]));
}

/********************************************************GESTION DE LA LANGUE PAR COOKIES**********************************************/
/*****************************************************Gestion des fichiers de langues  TEST*************************************/
if (isset($protectedPost['Valid_EDITION_x'])){
	if ($protectedPost['ID_WORD'] != ''){
		if ($protectedPost['ACTION'] == "DEL"){
			unset($_SESSION['OCS']['LANGUAGE_FILE']->tableauMots[$protectedPost['ID_WORD']]);
		}else{
			$_SESSION['OCS']['LANGUAGE_FILE']->tableauMots[$protectedPost['ID_WORD']]=$protectedPost['UPDATE'];
		}
		$sql="update languages set json_value = '".mysql_real_escape_string(json_encode($_SESSION['OCS']['LANGUAGE_FILE']->tableauMots))."'
				where name= '".$_SESSION['OCS']['LANGUAGE']."'"; 
		if( ! @mysql_query( $sql, $_SESSION['OCS']["writeServer"] ))
				echo mysql_error($_SESSION['OCS']["writeServer"]);
		}
}
unset($_SESSION['OCS']['EDIT_LANGUAGE']);


if (isset($protectedPost['LANG'])){
	unset($_SESSION['OCS']['LANGUAGE']);
	cookies_add('LANG',$protectedPost['LANG']);	
	$_SESSION['OCS']['LANGUAGE']=$protectedPost['LANG'];
	$_SESSION['OCS']["LANGUAGE_FILE"]=new language($_SESSION['OCS']['LANGUAGE']);
}
//unset($_SESSION['OCS']['LANGUAGE']);
//si la langue par d�faut n'existe pas, on r�cup�rer le cookie
if (!isset($_SESSION['OCS']['LANGUAGE']) or !isset($_SESSION['OCS']["LANGUAGE_FILE"])){
	if (isset($_COOKIE['LANG']))
	$_SESSION['OCS']['LANGUAGE']=$_COOKIE['LANG'];
	if (!isset($_COOKIE['LANG']))
	$_SESSION['OCS']['LANGUAGE']=DEFAULT_LANGUAGE;
	$_SESSION['OCS']["LANGUAGE_FILE"]=new language($_SESSION['OCS']['LANGUAGE']);
}
$l = $_SESSION['OCS']["LANGUAGE_FILE"];

/*********************************************************gestion de l'authentification****************************************************/
if (!isset($_SESSION['OCS']["loggeduser"]))
require_once('backend/AUTH/auth.php');

/**********************************************************gestion des droits sur les TAG****************************************************/
if (!isset($_SESSION['OCS']["lvluser"]))
require_once('backend/identity/identity.php');



/**********************************************************gestion des droits sur l'ipdiscover****************************************************/
if (!isset($_SESSION['OCS']["ipdiscover"]) and $protectedGet[PAG_INDEX] == $pages_refs['ms_ipdiscover'])
require_once('backend/ipdiscover/ipdiscover.php');
elseif($protectedGet[PAG_INDEX] != $pages_refs['ms_ipdiscover'])
unset($_SESSION['OCS']['ipdiscover']);

/*********************************************************gestion de la suppression automatique des machines trop vieilles*************************/
//require_once('plugins/options_config/del_old_computors.php');

/***********************************************************gestion des logs*************************************************************************/
if (!isset($_SESSION['OCS']['LOG_GUI'])){
	$sql_log="select name,ivalue,tvalue from config where name= 'LOG_GUI' or name='LOG_DIR' or name='LOG_SCRIPT'";
	$result_log = mysql_query($sql_log, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
	while($value_log=mysql_fetch_array($result_log)) {
		if ($value_log["name"] == 'LOG_GUI')
			$_SESSION['OCS']['LOG_GUI'] = $value_log['ivalue'];
		if ($value_log["name"] == 'LOG_DIR')
			$_SESSION['OCS']['LOG_DIR'] = $value_log['tvalue'];
		if ($value_log["name"] == 'LOG_SCRIPT')
			$_SESSION['OCS']['LOG_SCRIPT'] = $value_log['tvalue'];
	}
	if (!isset($_SESSION['OCS']['LOG_GUI']))
		$_SESSION['OCS']['LOG_GUI']=0;
	if (!isset($_SESSION['OCS']['LOG_DIR']))
		$_SESSION['OCS']['LOG_DIR']='';
	if (!isset($_SESSION['OCS']['LOG_SCRIPT']))
		$_SESSION['OCS']['LOG_SCRIPT']='';
}
/****************END GESTION LOGS***************/

/*********************************************GESTION OF LBL_TAG*************************************/
if (!isset($_SESSION['OCS']['LBL_TAG'])){
	$sql_tag="select tvalue from config where name= 'LBL_TAG'";
	$result_tag = mysql_query($sql_tag, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
	$value_tag=mysql_fetch_array($result_tag);
	if ($value_tag["tvalue"] != '')
		$_SESSION['OCS']['TAG_LBL'] = $value_tag['tvalue'];
	else
		$_SESSION['OCS']['TAG_LBL'] = "TAG";
}
/*******************************************GESTION OF PLUGINS (MAIN SECTIONS)****************************/
if (!isset($_SESSION['OCS']['all_menus'])){	
	require_once($_SESSION['OCS']['main_sections_dir']."sections.php");
}

$name=array_flip($_SESSION['OCS']['URL']);

if ((!isset($header_html) or $header_html != 'NO') and !isset($protectedGet['no_header'])){
	require_once ($_SESSION['OCS']['HEADER_HTML']);
	//echo "toto";
}

//VERIF ACCESS TO THIS PAGE
if (isset($protectedGet[PAG_INDEX]) 
	and !isset($_SESSION['OCS']['PAGE_PROFIL'][$name[$protectedGet[PAG_INDEX]]])
	and !isset($_SESSION['OCS']['TRUE_PAGES'][$name[$protectedGet[PAG_INDEX]]])){
	echo "<br><br><center><b><font color=red>ACCESS DENIED</font></b></center><br>";
	require_once($_SESSION['OCS']['FOOTER_HTML']);
	die();	
}


if((!isset($_SESSION['OCS']["loggeduser"])
	 or !isset($_SESSION['OCS']["lvluser"]) 
	 or $_SESSION['OCS']["lvluser"] == "")
	 and $no_error != 'YES')
{		
	echo "<br><br><center><b><font color=red>".$LIST_ERROR."</font></b></center><br>";
	require_once($_SESSION['OCS']['FOOTER_HTML']);
	die();
}

if (isset($name[$protectedGet[PAG_INDEX]])){	
	if (isset($_SESSION['OCS']['DIRECTORY'][$name[$protectedGet[PAG_INDEX]]]))
	$rep=$_SESSION['OCS']['DIRECTORY'][$name[$protectedGet[PAG_INDEX]]];
	require ($_SESSION['OCS']['main_sections_dir'].$rep."/".$name[$protectedGet[PAG_INDEX]].".php");
}else
require ($_SESSION['OCS']['main_sections_dir']."ms_console/ms_console.php");		




?>
