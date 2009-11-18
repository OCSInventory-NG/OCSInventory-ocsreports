<?php
if ($protectedGet['head'] == 1)
$ban_head='no';
/*******************************************************AFFICHAGE HTML DU HEADER*******************************************/
//require("fichierConf.class.php");
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
<script language='javascript' type='text/javascript' src='js/function.js'></script>
<?php incPicker(); 
echo "</head>"; 
echo "<body bottommargin='0' leftmargin='0' topmargin='0' rightmargin='0' marginheight='0' marginwidth='0'>";
//on affiche l'entete de la page
if( !isset($protectedGet["popup"] )) {
	//si unlock de l'interface
	if ($protectedPost['LOCK'] == 'RESET'){
		 $_SESSION['OCS']["mesmachines"]=$_SESSION['OCS']["TRUE_mesmachines"];
		unset($_SESSION['OCS']["TRUE_mesmachines"]);
	}
//TODO: revoir �a!!! si la variable $ban_head est � 'no', on n'affiche pas l'entete... 
//echo "<font color=RED><B>ENVIRONNEMENT DE DEV</B></font>";

echo "<table  border='0' class='headfoot' ";
if ($ban_head=='no') echo "style='display:none;'";
echo "><tr><td width= 10%><table width= 50% align=center border='0'><tr>
 	<Td align='left'><a href='index.php?first'><img src='image/logo OCS-ng-48.png'></a></Td></tr></table></td><td width= 100%>";
 	
if (isset($_SESSION['OCS']["loggeduser"])){
	echo "<table width= 100% align=center border='0'><tr><Td align='center' bgcolor='#f2f2f2' BORDERCOLOR='#f2f2f2' width:80%>";
	echo "<font color=red><b>ATTENTION: USE THIS VERSION ONLY FOR TEST.<BR> THIS VERSION IN DEVELOPMENTAL STAGE</b></font><br>";
//si un fuser est en cours, on indique avec quel compte le super admin est connect�
	if( isset($_SESSION['OCS']['TRUE_USER']) )
		echo "<font color=red>".$_SESSION['OCS']['TRUE_USER']." ".$l->g(889)." ".$_SESSION['OCS']["loggeduser"]."</font>";
	if (isset($_SESSION['OCS']["TRUE_mesmachines"])){
			echo "<br><b><font color=red>".$l->g(890)."</font></b>";
		}
	echo "</Td></tr></table>";
}
echo "</td><td width= 10%><table width= 100% align=center border='0'><tr><Td align='center'>
	<b>Ver. 1.03A &nbsp&nbsp&nbsp;</b>";
	//pass in debug mode if plugin debug exist
	if (isset($pages_refs['ms_debug'])){
		$javascript="OnClick='window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_debug']."&head=1\",\"debug\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=350\")'";
		if($_SESSION['OCS']['DEBUG']=='ON' or $_SESSION['OCS']['MODE_LANGUAGE']=="ON"){
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
		//you can change your password only if you are identify on local mode
		if ($_SESSION['OCS']["cnx_origine"] == "LOCAL"){
			$javascript="OnClick='window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_passwd']."&head=1\",\"debug\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=350\")'";
			echo "<a ".$javascript."><img src=".$_SESSION['OCS']['plugins_dir']."/main_sections/img/ms_pass.png></a>";
		}
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

//if you don't want to see the icons
if ($icon_head!='NO'){
//getting existing plugins by using tags in config.txt file
//$Directory=$_SESSION['OCS']['plugins_dir']."main_sections/";
require_once($_SESSION['OCS']['plugins_dir']."main_sections/section_html.php");
//if ($ban_head!='no'){
echo "<form action='' name='ACTION_CLIC' id='ACTION_CLIC' method='POST'>";
	echo "<input type='hidden' name='RESET' id='RESET' value=''>";
	echo "</form>";
}


?>
