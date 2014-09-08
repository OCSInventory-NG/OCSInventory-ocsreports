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

echo '<div class="headfoot navbar navbar-default">';
//on affiche l'entete de la page
if (!isset($protectedGet["popup"])) {
	//si unlock de l'interface
	if (isset($protectedPost['LOCK']) and $protectedPost['LOCK'] == 'RESET'){
		if (isset($_SESSION['OCS']["TRUE_mesmachines"]) and $_SESSION['OCS']["TRUE_mesmachines"] != array())
			$_SESSION['OCS']["mesmachines"]=$_SESSION['OCS']["TRUE_mesmachines"];
		else
			unset($_SESSION['OCS']["mesmachines"]);
		unset($_SESSION['OCS']["TRUE_mesmachines"]);
	}


	
}
echo '<div class="header-logo">';
echo '<a href="index.php?first"><img src="image/logo OCS-ng-96.png"></a>';
echo '</div>';

if ($_SESSION['OCS']['profile']) {
	echo '<div class="main-menu-container">';
	if (!isset($protectedGet["popup"])) {
		show_menu();
	}
	
	
	
	if (isset($_SESSION['OCS']["loggeduser"]) && !isset($protectedGet["popup"])) {
		echo '<ul class="nav navbar-nav" style="float: right">
				<li class="dropdown"><a href="#" data-toggle="dropdown" >
					<span class="glyphicon glyphicon-cog" id="menu_settings"></span></a>
					<ul class="dropdown-menu dropdown-menu-right">';
			
		if (isset($_SESSION['OCS']["TRUE_mesmachines"])){
			echo "<li><a onclick='return pag(\"RESET\",\"LOCK\",\"log_out\")'><img src='image/settings.png' alt='settings'>".$l->g(891)."</a></li>";
		}
		// DEBUG = 1011
		echo "<li><a href='index.php?".PAG_INDEX."=".$pages_refs['ms_config_account']."&head=1'>".$l->g(1361)."</a></li>";// TODO translate
		
		
		
		//pass in debug mode if plugin debug exist
		if (isset($pages_refs['ms_debug'])){
			echo "<li>";
			if ((isset($_SESSION['OCS']['DEBUG']) and $_SESSION['OCS']['DEBUG']=='ON')
					or (isset($_SESSION['OCS']['MODE_LANGUAGE']) and $_SESSION['OCS']['MODE_LANGUAGE']=="ON")){
				echo"<b>".GUI_VER."/".DB_NAME."</b>";
				echo "<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_debug']."&head=1'><img src=image/red.png>".$l->g(1011)."</a>";
		
				if ($_SESSION['OCS']['DEBUG']=='ON') {
					echo "<b>CACHE:&nbsp;<font color='".($_SESSION['OCS']["usecache"]?"green'>ON":"red'>OFF")."</font></b><span id='tps'>wait...</span>";
				}
			} else if( !isset($_SESSION['OCS']['DEBUG'])){
				if (($_SESSION['OCS']['profile'] && $_SESSION['OCS']['profile']->hasPage('ms_debug')) || (is_array($_SESSION['OCS']['TRUE_PAGES']) && array_search('ms_debug', $_SESSION['OCS']['TRUE_PAGES']))){
					echo "<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_debug']."&head=1'><img src=image/green.png>".$l->g(1011)."</a>";
				}
			}
			echo "</li>";
		}
		echo "<li><a>About</a></li>";
		//  PAGE ABOUT
		//	echo '<span class="version">V <b>' . GUI_VER_SHOW . '</b></span>';
		
		
		if (!isset($_SERVER['PHP_AUTH_USER']) and !isset($_SERVER['HTTP_AUTH_USER'])){
			echo "<li><a onclick='return pag(\"ON\",\"LOGOUT\",\"log_out\")'>".$l->g(251)."</a></li>";
		}
		echo '</li></ul></ul></div>';
		echo open_form('log_out','index.php');
		echo "<input type='hidden' name='LOGOUT' id='LOGOUT' value=''>";
		echo "<input type='hidden' name='LOCK' id='LOCK' value=''>";
		echo close_form();
	
	}
	
	
	

}

echo '</div>';


if (isset($_SESSION['OCS']["loggeduser"]) && $_SESSION['OCS']['profile']->getConfigValue('ALERTE_MSG')=='YES'){
/**************************************************   ALERT MESSAGES ********************************************************/
	$msg_header_error=array();
	$msg_header_error_sol=array();
	//install.php already exist ?
	if(is_readable("install.php")){
		$msg_header_error[]= $l->g(2020);
		$msg_header_error_sol[] = $l->g(2023);
	}
	//defaut user already exist on databases?
	try{
		$link_read=mysqli_connect(SERVER_READ,DFT_DB_CMPT,DFT_DB_PSWD);
		$link_write=mysqli_connect(SERVER_WRITE,DFT_DB_CMPT,DFT_DB_PSWD);
		mysqli_select_db($link_read,DB_NAME);
		mysqli_select_db($link_write,DB_NAME);
		$msg_header_error[]= $l->g(2024).' '.DB_NAME;
		$msg_header_error_sol[] = $l->g(2025);
	} catch (Exception $e){} ;
	
	
	//admin user already exist on data base with defaut password?
	$reqOp="SELECT id,user_group FROM operators WHERE id='%s' and passwd ='%s'";
	$arg_reqOp=array(DFT_GUI_CMPT,md5(DFT_GUI_PSWD));	
	$resOp=mysql2_query_secure($reqOp,$_SESSION['OCS']["readServer"],$arg_reqOp);
	$rowOp=mysqli_fetch_object($resOp);
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
	
	if(version_compare(phpversion(), '5.3.7', '<')){
		$msg_header_warning[]=$l->g(2113)." ".phpversion()." ) ";
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

if (isset($_SESSION['OCS']['TRUE_USER'])) {
	msg_info($_SESSION['OCS']['TRUE_USER']." ".$l->g(889)." ".$_SESSION['OCS']["loggeduser"]);
}

if (isset($_SESSION['OCS']["TRUE_mesmachines"])) {
	msg_info($l->g(890));
}

echo "</td></tr></table></td></tr>";
if (!isset($_SESSION['OCS']["loggeduser"])){
	echo "<tr><td colspan=20 align=right>";
 require_once('plugins/language/language.php');
 	echo "</td></tr>";
}
echo "</table>";		
echo "<div class='fond'>";

if ($_SESSION['OCS']["mesmachines"] == "NOTAG" 
	and !(array_search('ms_debug', $_SESSION['OCS']['TRUE_PAGES']['ms_debug']) and $protectedGet[PAG_INDEX] == $pages_refs['ms_debug']) ){
	if (isset($LIST_ERROR))
		$msg_error=$LIST_ERROR;
	else
		$msg_error=$l->g(893);
	msg_error($msg_error);
	require_once(FOOTER_HTML);
	die();

}

?>
