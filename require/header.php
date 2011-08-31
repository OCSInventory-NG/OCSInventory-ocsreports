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

if (!isset($debut))
die('FORBIDDEN');

unset($_SESSION['OCS']['SQL_DEBUG']);
@session_start();
error_reporting(E_ALL & ~E_NOTICE);
/*************************************************WHAT OS USE************************************/
$real_dir=explode('/',$_SERVER['SCRIPT_FILENAME']);
array_pop($real_dir);	
define("DOCUMENT_REAL_ROOT",implode('/',$real_dir)."/");

if(substr($_SERVER['DOCUMENT_ROOT'],-1) != '/'){
	define("DOCUMENT_ROOT",$_SERVER['DOCUMENT_ROOT']."/");	
}else{
	define("DOCUMENT_ROOT",$_SERVER['DOCUMENT_ROOT']);
}
//echo DOCUMENT_ROOT."<br>".DOCUMENT_REAL_ROOT;
//print_r($_SERVER);

if ($_SESSION['OCS']['LOG_GUI'] == 1){	
		define("LOG_FILE",$_SESSION['OCS']['LOG_DIR']."log.csv");
	}

require_once('var.php');
require_once('require/fichierConf.class.php');
require_once('require/function_commun.php');
require_once('require/aide_developpement.php');
require_once('require/function_table_html.php');
require_once('require/function_ssl.php');

if (isset($_SESSION['OCS']['CONF_RESET'])){
	unset($_SESSION['OCS']['LOG_GUI']);
	unset($_SESSION['OCS']['CONF_DIRECTORY']);
	unset($_SESSION['OCS']['URL']);
	unset($_SESSION['OCS']['SUPPORT']);
	unset($_SESSION['OCS']["usecache"]);
	unset($_SESSION['OCS']["use_redistribution"]);
	unset($_SESSION['OCS']['CONF_RESET']);
}

//If you have to reload conf
if ($_POST['RELOAD_CONF'] == 'RELOAD'){
	$_SESSION['OCS']['CONF_RESET']=true;
}



/*****************************************************LOGOUT*********************************************/
if (isset($_POST['LOGOUT']) and $_POST['LOGOUT'] == 'ON'){
	unset($_SESSION['OCS']);
	unset($_GET);
}

/***************************************************** First installation checking *********************************************************/
if( (!$fconf=@fopen("dbconfig.inc.php","r")) 
		|| (!function_exists('session_start')) 
		|| (!function_exists('mysql_connect')) ) {
	require('install.php');	
	die();
}
else{	
	require_once('dbconfig.inc.php');
	fclose($fconf);
}

if (!defined("SERVER_READ") 
		|| !defined("DB_NAME") 
		|| !defined("SERVER_WRITE") 
		|| !defined("COMPTE_BASE")
		|| !defined("PSWD_BASE")){
	$fromdbconfig_out = true;
	require('install.php');
	die();	
}


//connect to databases
$link_write=dbconnect(SERVER_WRITE,COMPTE_BASE,PSWD_BASE);
$link_read=dbconnect(SERVER_READ,COMPTE_BASE,PSWD_BASE);

if (is_resource($link_write) and is_resource($link_read)) {
	$_SESSION['OCS']["writeServer"] = $link_write;	
	$_SESSION['OCS']["readServer"] = $link_read;
}else{
	if ($link_write == "NO_DATABASE" or $link_read == "NO_DATABASE"){
		require('install.php');
		die();
	}
	$msg='';
	if (!is_resource($link_write))
		$msg.=$link_write."<br>";
	if (!is_resource($link_read))
		$msg.=$link_read;
	html_header(true);
	msg_error($msg);
	require_once(FOOTER_HTML);
	die();
}


/***********************************************************LOGS ADMIN*************************************************************************/
if (!isset($_SESSION['OCS']['LOG_GUI'])){
	$values=look_config_default_values(array('LOG_GUI','LOG_DIR','LOG_SCRIPT'));
	$_SESSION['OCS']['LOG_GUI']=$values['ivalue']['LOG_GUI'];
	$_SESSION['OCS']['LOG_DIR']=$values['tvalue']['LOG_DIR'];
	$_SESSION['OCS']['LOG_SCRIPT'] = $values['tvalue']['LOG_SCRIPT'];
	
	if ($_SESSION['OCS']['LOG_DIR'] == '')
		$_SESSION['OCS']['LOG_DIR'] =DOCUMENT_ROOT.'logs/';
	else
		$_SESSION['OCS']['LOG_DIR'] .='/logs/';
	
	if ($_SESSION['OCS']['LOG_SCRIPT'] == '')
		$_SESSION['OCS']['LOG_SCRIPT'] =DOCUMENT_ROOT.'scripts/';		
	else
		$_SESSION['OCS']['LOG_SCRIPT'] .="/scripts/";
}
/****************END LOGS***************/

/***********************************************************CONF DIRECTORY*************************************************************************/
if (!isset($_SESSION['OCS']['CONF_PROFILS_DIR'])){
	$values=look_config_default_values(array('CONF_PROFILS_DIR','OLD_CONF_DIR'));
	$_SESSION['OCS']['OLD_CONF_DIR']=$values['tvalue']['OLD_CONF_DIR'];
	$_SESSION['OCS']['CONF_PROFILS_DIR']=$values['tvalue']['CONF_PROFILS_DIR'];
	if ($_SESSION['OCS']['OLD_CONF_DIR'] == '')
		$_SESSION['OCS']['OLD_CONF_DIR'] =DOCUMENT_REAL_ROOT.'plugins/main_sections/conf/old_conf/';
	else
		$_SESSION['OCS']['OLD_CONF_DIR'] .='/old_conf/';
		
	if ($_SESSION['OCS']['CONF_PROFILS_DIR'] == '')
		$_SESSION['OCS']['CONF_PROFILS_DIR'] =DOCUMENT_REAL_ROOT.'plugins/main_sections/conf/';
	else
		$_SESSION['OCS']['CONF_PROFILS_DIR'] .='/conf/';
}
/****************END LOGS***************/





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

/****************************************************SQL TABLE & FIELDS***********************************************/

if (!isset($_SESSION['OCS']['SQL_TABLE'])){
	$sql="show tables from %s";
	$arg=DB_NAME;
	$res=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$arg);
	while($item = mysql_fetch_row($res)){
		$sql="SHOW COLUMNS FROM %s";
		$arg=$item[0];
		$res_column=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$arg);
		while ($item_column = mysql_fetch_row($res_column)){
			
			if ($item_column[0] == "HARDWARE_ID" 
				and !isset($_SESSION['OCS']['SQL_TABLE_HARDWARE_ID'][$item[0]]))
				$_SESSION['OCS']['SQL_TABLE_HARDWARE_ID'][$item[0]]=$item[0];
				
			$_SESSION['OCS']['SQL_TABLE'][$item[0]][$item_column[0]]=$item_column[0];
			
		}
	}
}

/*foreach ($_SESSION['OCS']['SQL_TABLE_HARDWARE_ID'] as $table_name=>$poub){
	$sql="select count(*) from hardware h right join %s a on a.hardware_id=h.ID WHERE h.id is null ";
	$arg=$table_name;
	$res_column=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$arg);
	$item_column = mysql_fetch_row($res_column);
	if ($item_column[0]>0)
		echo $table_name."<br>";
}*/
/*****************************************************GESTION DU NOM DES PAGES****************************************/
//Config for all user
if (!isset($_SESSION['OCS']['URL'])){
	require_once('require/function_files.php');
	$ms_cfg_file= $_SESSION['OCS']['CONF_PROFILS_DIR']."4all_config.txt";	
	//show only true sections
	if (file_exists($ms_cfg_file)) {
		$search=array('URL'=>'MULTI');
		$profil_data=read_configuration($ms_cfg_file,$search);
		$pages_refs=$profil_data['URL'];
		$_SESSION['OCS']['URL']=$pages_refs;
	}else{
		die("ERROR: CAN'T READ CONFIG FILE ".$_SESSION['OCS']['CONF_PROFILS_DIR']."4all_config.txt");
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
//nb result by page
if (isset($protectedPost["pcparpage"]) and isset($protectedPost["old_pcparpage"])
		and $protectedPost["pcparpage"] != $protectedPost["old_pcparpage"]){
	$_SESSION['OCS']['nb_tab'][$protectedPost['TABLE_NAME']]=$protectedPost["pcparpage"];
	cookies_add($protectedPost['TABLE_NAME'].'_nbpage',$protectedPost["pcparpage"]);
}elseif($_COOKIE[$protectedPost['TABLE_NAME'].'_nbpage'])
	$_SESSION['OCS']['nb_tab'][$protectedPost['TABLE_NAME']]=$_COOKIE[$protectedPost['TABLE_NAME'].'_nbpage'];
	
//del column
if (isset($protectedPost['SUP_COL']) and $protectedPost['SUP_COL'] != "" and isset($_SESSION['OCS']['col_tab'][$protectedPost['TABLE_NAME']])){
	unset($_SESSION['OCS']['col_tab'][$protectedPost['TABLE_NAME']][$protectedPost['SUP_COL']]);
	cookies_add($protectedPost['TABLE_NAME'],implode('///',$_SESSION['OCS']['col_tab'][$protectedPost['TABLE_NAME']]));
}

//default values
if (isset($protectedPost['RAZ']) and $protectedPost['RAZ'] != ""){
	cookies_reset($protectedPost['TABLE_NAME']);
}

//add column
if (isset($protectedPost['TABLE_NAME']) and 
	isset($protectedPost['restCol'.$protectedPost['TABLE_NAME']]) 
	and $protectedPost['restCol'.$protectedPost['TABLE_NAME']] != ''){
	$_SESSION['OCS']['col_tab'][$tab_name][$protectedPost['restCol'.$tab_name]]=$protectedPost['restCol'.$tab_name];
	if (is_array($_SESSION['OCS']['col_tab'][$protectedPost['TABLE_NAME']])){
		cookies_add($protectedPost['TABLE_NAME'],implode('///',$_SESSION['OCS']['col_tab'][$protectedPost['TABLE_NAME']]));
	}
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


if (isset($protectedPost['LANG']) and $protectedPost['LANG']!= ''){
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
require_once(BACKEND.'AUTH/auth.php');

/**********************************************************gestion des droits sur les TAG****************************************************/
if (!isset($_SESSION['OCS']["lvluser"]))
require_once(BACKEND.'identity/identity.php');



/**********************************************************gestion des droits sur l'ipdiscover****************************************************/
if (!isset($_SESSION['OCS']["ipdiscover"])){
	require_once(BACKEND.'ipdiscover/ipdiscover.php');
}


/*********************************************************gestion de la suppression automatique des machines trop vieilles*************************/
//require_once('plugins/options_config/del_old_computers.php');



/********************GESTION GUI CONF******************/
if (!isset($_SESSION['OCS']["usecache"]) or !isset($_SESSION['OCS']["tabcache"])){
	$conf_gui=array('usecache'=>'INVENTORY_CACHE_ENABLED',
					'tabcache'=>'TAB_CACHE',
					'SUPPORT'=>'SUPPORT',
					'USE_NEW_SOFT_TABLES'=>'USE_NEW_SOFT_TABLES');
	$default_value_conf=array('INVENTORY_CACHE_ENABLED'=>1,'TAB_CACHE'=>0,'SUPPORT'=>1,'USE_NEW_SOFT_TABLES' =>0);
	$values=look_config_default_values($conf_gui);
	foreach ($conf_gui as $k=>$v){
		if (isset($values['ivalue'][$v]))
			$_SESSION['OCS'][$k]=$values['ivalue'][$v];
		else
			$_SESSION['OCS'][$k]=$default_value_conf[$v];
		
	}
}

/********************END GESTION CACHE******************/


/********************MANAGE DOWNLOAD REDISTRIBUTION******************/
if (!isset($_SESSION['OCS']["use_redistribution"])){
	$values=look_config_default_values(array('DOWNLOAD_REDISTRIB'));
	$_SESSION['OCS']['use_redistribution']=$values['ivalue']['DOWNLOAD_REDISTRIB'];
	if (!isset($_SESSION['OCS']["use_redistribution"]))
		$_SESSION['OCS']["use_redistribution"]=1;
}

/********************END DOWNLOAD REDISTRIBUTION******************/


/*********************************************GESTION OF LBL_TAG*************************************/

if (!isset($_SESSION['OCS']['TAG_LBL'])){
	require_once('require/function_admininfo.php');
	$all_tag_lbl=witch_field_more('COMPUTERS');
	foreach ($all_tag_lbl['LIST_NAME'] as $key=>$value){
		$_SESSION['OCS']['TAG_LBL'][$value]=$all_tag_lbl['LIST_FIELDS'][$key];
		$_SESSION['OCS']['TAG_ID'][$key]=$value;
	}
}
/*******************************************GESTION OF PLUGINS (MAIN SECTIONS)****************************/

if (!isset($_SESSION['OCS']['all_menus'])){	
	require_once(MAIN_SECTIONS_DIR."sections.php");
}

$name=array_flip($_SESSION['OCS']['URL']);

if ((!isset($header_html) or $header_html != 'NO') and !isset($protectedGet['no_header'])){
	require_once (HEADER_HTML);
}

//VERIF ACCESS TO THIS PAGE
if (isset($protectedGet[PAG_INDEX]) 
	and !isset($_SESSION['OCS']['PAGE_PROFIL'][$name[$protectedGet[PAG_INDEX]]])
	and !isset($_SESSION['OCS']['TRUE_PAGES'][$name[$protectedGet[PAG_INDEX]]])
	//force access to profils witch have CONFIGURATION TELEDIFF  == 'YES' for ms_admin_ipdiscover page
	and !($_SESSION['OCS']['CONFIGURATION']['TELEDIFF'] == 'YES' 
			and $name[$protectedGet[PAG_INDEX]] == 'ms_admin_ipdiscover')){
		msg_error("ACCESS DENIED");
		require_once(FOOTER_HTML);
		die();	
}


if((!isset($_SESSION['OCS']["loggeduser"])
	 or !isset($_SESSION['OCS']["lvluser"]) 
	 or $_SESSION['OCS']["lvluser"] == "")
	 and !isset($_SESSION['OCS']['TRUE_USER'])
	 and $no_error != 'YES')
{		
	msg_error($LIST_ERROR);
	require_once(FOOTER_HTML);
	die();
}

if (isset($name[$protectedGet[PAG_INDEX]])){	
	if (isset($_SESSION['OCS']['DIRECTORY'][$name[$protectedGet[PAG_INDEX]]]))
	$rep=$_SESSION['OCS']['DIRECTORY'][$name[$protectedGet[PAG_INDEX]]];
	require (MAIN_SECTIONS_DIR.$rep."/".$name[$protectedGet[PAG_INDEX]].".php");
}
else{
	$default_first_page=MAIN_SECTIONS_DIR."ms_console/ms_console.php";
	if (isset($protectedGet['first'])){
		require (MAIN_SECTIONS_DIR."ms_console/ms_console.php");
	}elseif($_SESSION['OCS']['PAGE_PROFIL']['ms_console'])	
		require ($default_first_page);	
	else{
		echo  "<img src='image/fond.png'>";
	}
		
}


?>
