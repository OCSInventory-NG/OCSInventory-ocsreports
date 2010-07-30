<?php

if (!isset($debut))
die('FORBIDDEN');
@session_start();
require_once('fichierConf.class.php');
/*****************************************************LOGOUT*********************************************/
if (isset($_POST['LOGOUT']) and $_POST['LOGOUT'] == 'ON'){
	unset($_SESSION['OCS']);
	unset($_GET);
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
/***********************************************************gestion des logs*************************************************************************/
if (!isset($_SESSION['OCS']['LOG_GUI'])){
	$values=look_config_default_values(array('LOG_GUI','LOG_DIR','LOG_SCRIPT'));
	$_SESSION['OCS']['LOG_GUI']=$values['ivalue']['LOG_GUI'];
	$_SESSION['OCS']['LOG_DIR']=$values['tvalue']['LOG_DIR'];
	$_SESSION['OCS']['LOG_SCRIPT'] = $values['tvalue']['LOG_SCRIPT'];
}
/****************END GESTION LOGS***************/



/******************************************Checking sql update*********************************************/
if (!isset($_SESSION['OCS']['SQL_BASE_VERS'])){
	$values=look_config_default_values('GUI_VERSION');
	$_SESSION['OCS']['SQL_BASE_VERS']=$values['tvalue']['GUI_VERSION'];
}
if (GUI_VER	!= $_SESSION['OCS']['SQL_BASE_VERS']){
	unset($_SESSION['OCS']['SQL_BASE_VERS']);
	$fromAuto = true;
	require('install.php');
	die();	
}

if (!defined("SERVER_READ")){
	$fromdbconfig_out = true;
	require('install.php');
	die();	
}

//SECURITY

	$protectedPost=$_POST;
	$protectedGet=$_GET;
	
//print_r($GLOBALS);
@set_time_limit(0);

//Don't take care of error identify 
//For the fuser, $no_error  = 'YES'
if (!isset($no_error))
$no_error='NO';
/**************************************mise en place des r�pertoires de plugins et d'auhentification************************************/
if (!isset($_SESSION['OCS']['plugins_dir']) or !isset($_SESSION['OCS']['CONF_MYSQL'])){

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
}else
$pages_refs=$_SESSION['OCS']['URL'];
/**********************************************************GESTION DES COLONNES DES TABLEAUX PAR COOKIES***********************************/
require_once('require/function_cookies.php');

//Delete all cookies if GUI_VER change
if (!isset($_COOKIE["VERS"]) or $_COOKIE["VERS"] != GUI_VER){
	if( isset( $_COOKIE) ) {	
		foreach( $_COOKIE as $key=>$val ) {
			cookies_reset($key);		
		}
		unset( $_COOKIE );
	}
	cookies_add("VERS", GUI_VER);
}


if (isset($protectedPost['SUP_COL']) and $protectedPost['SUP_COL'] != "" and isset($_SESSION['OCS']['col_tab'][$protectedPost['TABLE_NAME']])){
	unset($_SESSION['OCS']['col_tab'][$tab_name][$protectedPost['SUP_COL']]);
	cookies_add($protectedPost['TABLE_NAME'],implode('///',$_SESSION['OCS']['col_tab'][$protectedPost['TABLE_NAME']]));
}
if (isset($protectedPost['RAZ']) and $protectedPost['RAZ'] != ""){
	cookies_reset($protectedPost['TABLE_NAME']);
}
if (isset($protectedPost['TABLE_NAME']) and 
	isset($protectedPost['restCol'.$protectedPost['TABLE_NAME']]) 
	and $protectedPost['restCol'.$protectedPost['TABLE_NAME']] != ''){
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
		
		/*$file_name=$_SESSION['OCS']['plugins_dir']."language/".$language."/".$_SESSION['OCS']['LANGUAGE'].".txt";
		
		
		$file=fopen($file_name."_old","x+");
		foreach ($_SESSION['OCS']['LANGUAGE_FILE'] as $key=>$value){
				fwrite($file,$key." ".$value."/r/n");			
		}
		fclose($file);*/
		
	/*	$sql="update languages set json_value = '%s'
				where name= '%s'"; 
		$arg=array(json_encode($_SESSION['OCS']['LANGUAGE_FILE']->tableauMots),$_SESSION['OCS']['LANGUAGE']);
		mysql2_query_secure( $sql, $_SESSION['OCS']["writeServer"],$arg);*/
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
if (!isset($_SESSION['OCS']["ipdiscover"])){
	require_once($_SESSION['OCS']['backend'].'/ipdiscover/ipdiscover.php');
}


/*********************************************************gestion de la suppression automatique des machines trop vieilles*************************/
//require_once('plugins/options_config/del_old_computers.php');



/********************GESTION CACHE******************/
if (!isset($_SESSION['OCS']["usecache"])){
	$values=look_config_default_values(array('INVENTORY_CACHE_ENABLED'));
	$_SESSION['OCS']['usecache']=$values['ivalue']['INVENTORY_CACHE_ENABLED'];
	if (!isset($_SESSION['OCS']["usecache"]))
		$_SESSION['OCS']["usecache"]=1;
}

/********************END GESTION CACHE******************/


/*********************************************GESTION OF LBL_TAG*************************************/

if (!isset($_SESSION['OCS']['TAG_LBL'])){
	require_once('require/function_admininfo.php');
	$all_tag_lbl=witch_field_more();
	foreach ($all_tag_lbl['LIST_NAME'] as $key=>$value){
		$_SESSION['OCS']['TAG_LBL'][$value]=$all_tag_lbl['LIST_FIELDS'][$key];
		$_SESSION['OCS']['TAG_ID'][$key]=$value;
	}
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
}
else{
 	if ($_SESSION['OCS']['PAGE_PROFIL']['ms_console'])	
	require ($_SESSION['OCS']['main_sections_dir']."ms_console/ms_console.php");	
	else{
		echo  "<img src='image/fond.png'>";
		
	}
		
}



?>
