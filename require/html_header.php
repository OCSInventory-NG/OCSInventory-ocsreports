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

header("Pragma: no-cache");
header("Expires: -1");
header("Cache-control: must-revalidate, post-check=0, pre-check=0");
header("Cache-control: private", false);
header("Content-type: text/html; charset=utf-8");
?>
<html>
<head>
<TITLE>OCS Inventory</TITLE>

<link rel="shortcut icon" href="favicon.ico" />
<LINK REL='StyleSheet' TYPE='text/css' HREF='css/ocsreports.css'>
<?php incPicker(); 
echo "<script language='javascript' type='text/javascript' src='js/function.js'></script>";
if (isset($_SESSION['OCS']['JAVASCRIPT'])){
	foreach ($_SESSION['OCS']['JAVASCRIPT'] as $file => $rep){
		echo "<script language='javascript' type='text/javascript' src='".$_SESSION['OCS']['main_sections_dir'].$rep.$file."'></script>";
	}
}
echo "</head>"; 
echo "<body bottommargin='0' leftmargin='0' topmargin='0' rightmargin='0' marginheight='0' marginwidth='0'>";
//on affiche l'entete de la page
if( !isset($protectedGet["popup"] )) {
	//si unlock de l'interface
	if (isset($protectedPost['LOCK']) and $protectedPost['LOCK'] == 'RESET'){
		 $_SESSION['OCS']["mesmachines"]=$_SESSION['OCS']["TRUE_mesmachines"];
		unset($_SESSION['OCS']["TRUE_mesmachines"]);
	}


echo "<table  border='0' class='headfoot' ";
if ($ban_head=='no') echo "style='display:none;'";
echo "><tr><td width= 10%><table width= 50% align=center border='0'><tr>
 	<Td align='left'><a href='index.php?first'><img src='image/logo OCS-ng-48.png'></a></Td></tr></table></td><td width= 100%>";
 	
if (isset($_SESSION['OCS']["loggeduser"]) && $_SESSION['OCS']['CONFIGURATION']['ALERTE_MSG']=='YES'){
	//echo "<table width= 100% align=center border='0'><tr><Td align='center' bgcolor='#f2f2f2' BORDERCOLOR='#f2f2f2' width:80%>";
	$msg_header='';
	if($fconf=@fopen("install.php","r")){
		$msg_header = $l->g(2006) . "<br>" . $l->g(2020) . "<br>";
	}
	if ($_SESSION['OCS']['LOG_GUI'] == 1){
		//check if the GUI logs directory is writable
		$rep_ok=is_writable($_SESSION['OCS']['LOG_DIR']);
		if (!$rep_ok){
			$msg_header.=$l->g(2021);
		}
	}
	if ($msg_header != '')
	msg_warning($msg_header);
	
}

if( isset($_SESSION['OCS']['TRUE_USER']))
		msg_info($_SESSION['OCS']['TRUE_USER']." ".$l->g(889)." ".$_SESSION['OCS']["loggeduser"]);

if (isset($_SESSION['OCS']["TRUE_mesmachines"])){
			msg_info($l->g(890));
}

echo "</td><td width= 10%><table width= 100% align=center border='0'><tr><Td align='center'>
	<b>Ver. " . GUI_VER . " &nbsp&nbsp&nbsp;</b>";
	//pass in debug mode if plugin debug exist
	if (isset($pages_refs['ms_debug'])){
		$javascript="OnClick='window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_debug']."&head=1\",\"debug\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=350\")'";
		if((isset($_SESSION['OCS']['DEBUG']) and $_SESSION['OCS']['DEBUG']=='ON') 
			or (isset($_SESSION['OCS']['MODE_LANGUAGE']) and $_SESSION['OCS']['MODE_LANGUAGE']=="ON")){
			echo "<br><a ".$javascript."><img src=image/red.png></a><br>";
			if ($_SESSION['OCS']['DEBUG']=='ON')
			echo "<font color='black'><b>CACHE:&nbsp;<font color='".($_SESSION['OCS']["usecache"]?"green'><b>ON</b>":"red'><b>OFF</b>")."</font><div id='tps'>wait...</div>";
		}elseif ((($_SESSION['OCS']['PAGE_PROFIL']['ms_debug']) or $_SESSION['OCS']['TRUE_PAGES']['ms_debug']) and !isset($_SESSION['OCS']['DEBUG'])){
			echo "<br><a ".$javascript."><img src=image/green.png></a><br>";
		}
	}
}

if(isset($_SESSION['OCS']["loggeduser"])&&!isset($protectedGet["popup"] )) {
		echo "<a onclick='return pag(\"ON\",\"LOGOUT\",\"log_out\")'>";
		echo "<img src='image/deconnexion.png' title='".$l->g(251)."' alt='".$l->g(251)."'>";
		echo "</a>";
		if (isset($_SESSION['OCS']["TRUE_mesmachines"])){
			echo "<a onclick='return pag(\"RESET\",\"LOCK\",\"log_out\")'>";
			echo "<img src='image/cadena_op.png' title='".$l->g(891)."' alt='".$l->g(891)."' >";
			echo "</a>";
		}
		$javascript="OnClick='window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_config_account']."&head=1\",\"debug\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=650,height=650\")'";
		echo "<a ".$javascript."><img src=".$_SESSION['OCS']['plugins_dir']."/main_sections/img/ms_pass.png></a>";
		echo "<form name='log_out' id='log_out' action='' method='post'>";
		echo "<input type='hidden' name='LOGOUT' id='LOGOUT' value=''>";
		echo "<input type='hidden' name='LOCK' id='LOCK' value=''>";
		echo "</form>";			
}

echo "</Td></tr></table></td></tr>";
if (!isset($_SESSION['OCS']["loggeduser"])){
	echo "<tr><td colspan=20 align=right>";
 require_once('plugins/language/language.php');
 	echo "</td></tr>";
}
echo "</table>";		
//echo "<form name='reload_fuser' id='reload_fuser' action='' method='post'></form>";
echo "<div class='fond'>";

if ($_SESSION['OCS']["mesmachines"] == "NOTAG" 
	and !(isset($_SESSION['OCS']['TRUE_PAGES']['ms_debug']) and $protectedGet[PAG_INDEX] == $pages_refs['ms_debug']) ){
		msg_error($l->g(893));
	require_once($_SESSION['OCS']['FOOTER_HTML']);
	die();

}


//if you don't want to see the icons
if ($icon_head!='NO'){
require_once($_SESSION['OCS']['plugins_dir']."main_sections/section_html.php");
echo "<form action='' name='ACTION_CLIC' id='ACTION_CLIC' method='POST'>";
	echo "<input type='hidden' name='RESET' id='RESET' value=''>";
	echo "</form>";
}


?>
