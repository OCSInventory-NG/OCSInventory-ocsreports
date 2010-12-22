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
$ydata = array();
 $xdata =array();
$xxdata=array();

foreach ($_SESSION['OCS']['STAT_SPEED_TELEDEPLOY']['DATA'] as $key=>$value){
	$ydata[]=$value;
	$xdata[]=$key;
	$xxdata[]=$_SESSION['OCS']['STAT_SPEED_TELEDEPLOY']['NAME'][$key];
	$i=$key;
}
$graph = new Graph(800,600);
$graph->SetScale('textlin');
$graph->yaxis->title->Set($l->g(1125));
$graph->legend->Pos(0.5,0.5, 'right', 'center');

$lineplot = new LinePLot($ydata, $xdata);
$graph->xaxis->SetLabelAngle(70);
$graph->xaxis->SetTickLabels($xxdata);
$graph->Add($lineplot);

$graph->Stroke();
unset($_SESSION['OCS']['STAT_SPEED_TELEDEPLOY']);


?>