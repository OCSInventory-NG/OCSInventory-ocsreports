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
if ((array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')){
	parse_str($protectedPost['ocs']['0'], $params);
	$protectedPost+=$params;
	ob_start();
	$ajax = true;
}
else{
	$ajax=false;
}
require('require/function_stats.php');

$form_name="plugins";
$table_name=$form_name;	
printEnTete($l->g(6000));
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
$plugin=false;
$showit=false;
if ($ajax){
	ob_end_clean();
}
foreach ($_SESSION['OCS']['URL'] as $name=>$lbl){
	if (substr($name,0,11) == 'ms_plugins_' and $_SESSION['OCS']['DIRECTORY'][$name] == 'ms_plugins'){
		$plugin=true;
		$list_plugin[]=$name.".php";
		require_once($name.".php");
	}	
}
if ($ajax){
	ob_start();
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
echo "</form>";
if ($ajax){
	ob_end_clean();
}
?>