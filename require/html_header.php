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

if (isset($protectedGet['head']) and $protectedGet['head'] == 1)
$ban_head='no';
/*******************************************************AFFICHAGE HTML DU HEADER*******************************************/
html_header();
//on affiche l'entete de la page
if( !isset($protectedGet["popup"] )) {
	//si unlock de l'interface
	if (isset($protectedPost['LOCK']) and $protectedPost['LOCK'] == 'RESET'){
		 $_SESSION['OCS']["mesmachines"]=$_SESSION['OCS']["TRUE_mesmachines"];
		unset($_SESSION['OCS']["TRUE_mesmachines"]);
	}


echo "<table  border='0' class='headfoot' ";
if ($ban_head=='no') echo "style='display:none;'";
echo "><tr><td align=left><a onclick='clic(\"index.php?first\",\"FIRST\");'>";

if (!isset($_SESSION['OCS']['SUPPORT']) or $_SESSION['OCS']['SUPPORT'] == 1 or !isset($_SESSION['OCS']["loggeduser"])){
	echo "<img src='image/logo OCS-ng-96.png'></a>";
}else
	echo "<img src='image/logo OCS-ng-96_registry.png'></a>";
echo "</td><td width= 70%>";
 	
if (isset($_SESSION['OCS']["loggeduser"]) && $_SESSION['OCS']['CONFIGURATION']['ALERTE_MSG']=='YES'){
/**************************************************   ALERT MESSAGES ********************************************************/
	$msg_header_error=array();
	$msg_header_error_sol=array();
	//install.php already exist ?
	if($fconf=@fopen("install.php","r")){
		$msg_header_error[]= $l->g(2020);
		$msg_header_error_sol[] = $l->g(2023);
	}
	//defaut user already exist on databases?
	$link_read=@mysql_connect(SERVER_READ,DFT_DB_CMPT,DFT_DB_PSWD);
	$link_write=@mysql_connect(SERVER_WRITE,DFT_DB_CMPT,DFT_DB_PSWD);
	if (@mysql_select_db(DB_NAME,$link_read) or @mysql_select_db(DB_NAME,$link_write)){
		$msg_header_error[]= $l->g(2024).' '.DB_NAME;	
		$msg_header_error_sol[] = $l->g(2025);	
	}
	
	//admin user already exist on data base with defaut password?
	$reqOp="SELECT id,user_group FROM operators WHERE id='%s' and passwd ='%s'";
	$arg_reqOp=array(DFT_GUI_CMPT,md5(DFT_GUI_PSWD));	
	$resOp=mysql2_query_secure($reqOp,$_SESSION['OCS']["readServer"],$arg_reqOp);
	$rowOp=mysql_fetch_object($resOp);
	if (isset($rowOp->id)){
		$msg_header_error[]= $l->g(2026);
		$msg_header_error_sol[] = $l->g(2027);
	}
/***************************************************** WARNING MESSAGES *****************************************************/
	$msg_header_warning=array();
	//Demo mode activate?
	if (DEMO) {
		$msg_header_warning[]= $l->g(2104)." ".GUI_VER_SHOW."<br>";
	} 
	
	
	if ($_SESSION['OCS']['LOG_GUI'] == 1){
		//check if the GUI logs directory is writable
		$rep_ok=is_writable($_SESSION['OCS']['LOG_DIR']);
		if (!$rep_ok){
			$msg_header_warning[]=$l->g(2021);
		}
	}
	
	//Error are detected
	if ($msg_header_error != array()){
			js_tooltip();
			$msg_tooltip='';
			foreach ($msg_header_error as $poub=>$values){
				if (isset($msg_header_error_sol[$poub])){
					$tooltip=tooltip($msg_header_error_sol[$poub]);
					$msg_tooltip .= "<div ".$tooltip.">".$values."</div>";
				}
			}
			
		msg_error("<big>".$l->g(1263)."</big><br>".$msg_tooltip);
		
	}
	//warning are detected
	if ($msg_header_warning != array())
		msg_warning(implode('<br>',$msg_header_warning));
	
}

if( isset($_SESSION['OCS']['TRUE_USER']))
		msg_info($_SESSION['OCS']['TRUE_USER']." ".$l->g(889)." ".$_SESSION['OCS']["loggeduser"]);

if (isset($_SESSION['OCS']["TRUE_mesmachines"])){
			msg_info($l->g(890));
}

echo "</td><td width= 10% align=center>
	Ver. <b>" . GUI_VER_SHOW . "</b><br>";
	//pass in debug mode if plugin debug exist
	if (isset($pages_refs['ms_debug'])){
		$javascript="OnClick='window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_debug']."&head=1\",\"debug\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=350\")'";
		if((isset($_SESSION['OCS']['DEBUG']) and $_SESSION['OCS']['DEBUG']=='ON') 
			or (isset($_SESSION['OCS']['MODE_LANGUAGE']) and $_SESSION['OCS']['MODE_LANGUAGE']=="ON")){
				echo"<b>".GUI_VER."/".DB_NAME."</b><br>";
			echo "<a ".$javascript."><img src=image/red.png></a><br>";
			if ($_SESSION['OCS']['DEBUG']=='ON')
			echo "<font color='black'><b>CACHE:&nbsp;<font color='".($_SESSION['OCS']["usecache"]?"green'><b>ON</b>":"red'><b>OFF</b>")."</font><div id='tps'>wait...</div>";
		}elseif ((($_SESSION['OCS']['PAGE_PROFIL']['ms_debug']) or $_SESSION['OCS']['TRUE_PAGES']['ms_debug']) and !isset($_SESSION['OCS']['DEBUG'])){
			echo "<a ".$javascript."><img src=image/green.png></a><br>";
		}
	}
}

if(isset($_SESSION['OCS']["loggeduser"])&&!isset($protectedGet["popup"] )) {
	if (!isset($_SERVER['PHP_AUTH_USER']) and !isset($_SERVER['HTTP_AUTH_USER'])){
		echo "<a onclick='return pag(\"ON\",\"LOGOUT\",\"log_out\")'>";
		echo "<img src='image/deconnexion.png' title='".$l->g(251)."' alt='".$l->g(251)."'>";
		echo "</a>";
	}
		if (isset($_SESSION['OCS']["TRUE_mesmachines"])){
			echo "<a onclick='return pag(\"RESET\",\"LOCK\",\"log_out\")'>";
			echo "<img src='image/cadena_op.png' title='".$l->g(891)."' alt='".$l->g(891)."' >";
			echo "</a>";
		}
		$javascript="OnClick='window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_config_account']."&head=1\",\"debug\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=650,height=650\")'";
		echo "<a ".$javascript."><img src=".PLUGINS_DIR."/main_sections/img/ms_pass.png></a>";
		echo "<form name='log_out' id='log_out' action='index.php' method='post'>";
		echo "<input type='hidden' name='LOGOUT' id='LOGOUT' value=''>";
		echo "<input type='hidden' name='LOCK' id='LOCK' value=''>";
		echo "</form>";			
}

echo "</td></tr>";
if (!isset($_SESSION['OCS']["loggeduser"])){
	echo "<tr><td colspan=20 align=right>";
 require_once('plugins/language/language.php');
 	echo "</td></tr>";
}
if ($_SESSION['OCS']['RESTRICTION']['SUPPORT']=='NO' and $_SESSION['OCS']['SUPPORT'] == 1){
	echo "<tr><td colspan=3 align=left>";
	$support=support();
	if ($support)
		echo "<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_help']."&TAB=4' ><img src='image/supported.png'></a>";
	else
		echo "<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_help']."&TAB=5' ><img src='image/not_supported.png'></a>";
	echo "</td></tr>";
}
echo "</table>";		
//echo "<form name='reload_fuser' id='reload_fuser' action='' method='post'></form>";
echo "<div class='fond'>";

if ($_SESSION['OCS']["mesmachines"] == "NOTAG" 
	and !(isset($_SESSION['OCS']['TRUE_PAGES']['ms_debug']) and $protectedGet[PAG_INDEX] == $pages_refs['ms_debug']) ){
	if (isset($LIST_ERROR))
		$msg_error=$LIST_ERROR;
	else
		$msg_error=$l->g(893);
	msg_error($msg_error);
	require_once(FOOTER_HTML);
	die();

}


//if you don't want to see the icons
if ($icon_head!='NO'){
require_once(PLUGINS_DIR."main_sections/section_html.php");

}


?>
