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


if(AJAX){
	parse_str($protectedPost['ocs']['0'], $params);
	$protectedPost+=$params;
	ob_start();
	$ajax = true;
}
else{
	$ajax=false;
}
require('require/function_stats.php');

printEnTete($l->g(6000));



if ($ajax){
	ob_end_clean();
}
?>