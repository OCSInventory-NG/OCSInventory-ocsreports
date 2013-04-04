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
require('require/function_stats.php');

$form_name="plugins";
$table_name=$form_name;	
printEnTete($l->g(6000));
echo open_form($form_name);
$plugin=false;
$showit=false;
foreach ($_SESSION['OCS']['URL'] as $name=>$lbl){
	if (substr($name,0,11) == 'ms_plugins_' and $_SESSION['OCS']['DIRECTORY'][$name] == 'ms_plugins'){
		$plugin=true;
		$list_plugin[]=$name.".php";
		require_once($name.".php");
	}	
}

if ($plugin){
	//Create the chart - Column 3D Chart with data from strXML variable using dataXML method
	onglet($data_on,$form_name,"onglet",4);
	echo '<div class="mlt_bordure" >';
	$showit=true;	
	foreach ($list_plugin as $key=>$name){
		require($name);		
	}
	echo "</div>";
}else
	msg_warning($l->g(1262));
echo close_form();

?>