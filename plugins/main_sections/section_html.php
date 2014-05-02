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

// Main menu functions
require_once('require/function_section_html.php');

if (!isset($protectedGet["popup"] )) {
	echo "<table width='95%' border=0 align=center><tr><td>";
	// Show the menu
	if ($ban_head != 'no') show_menu();
	echo "</td></tr></table>";
}

$span_wait=1;

?>