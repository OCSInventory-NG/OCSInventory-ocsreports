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
	echo "><tr><td >
			<table BORDER='0' ALIGN = 'left' CELLPADDING='0'";
			if ($ban_head=='no') echo " style='display:none;'";
		echo "><tr>";
	//show FIRST TAB of icons 
	show_icon_block($_SESSION['OCS']['ORDER_FIRST_TABLE']);
	$align="right";
	echo "			</tr></table>
				</td><td>
				<table BORDER='0' ALIGN = '".$align."' CELLPADDING='0'";
	if ($ban_head=='no') echo " style='display:none;'";
		echo "><tr>";
	//show SECOND TAB of icons
	show_icon_block($_SESSION['OCS']['ORDER_SECOND_TABLE']);
	echo "		</tr></table>
				</td></tr></table>";
}

//Hidding menus to have a better display 
echo "<script language='javascript'>show_menu('nomenu','".$_SESSION['OCS']['all_menus']."');</script>";
echo "<br><center><span id='wait' class='warn'><font color=red>".$l->g(332)."</font></span></center><br>";
		flush();
$span_wait=1;



?>