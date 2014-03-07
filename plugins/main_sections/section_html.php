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
//Initiating icons
require_once('require/function_section_html.php');
if( !isset($protectedGet["popup"] )) {
	echo "<table width='95%' border=0 align=center";
	echo "><tr><td>";
	// Show the menu
	show_menu($_SESSION['OCS']['ORDER']);
	echo "</td></tr></table>";
}

//Hidding menus to have a better display 
echo "<script language='javascript'>show_menu('nomenu','".$_SESSION['OCS']['all_menus']."');</script>";
echo "<center><span id='wait' class='warn'><font color=red>".$l->g(332)."</font></span></center><br>";
		flush();
$span_wait=1;



?>