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
$data=$_SESSION['OCS']['STAT_TELEDEPLOY']['DATA'];
$lbl=$_SESSION['OCS']['STAT_TELEDEPLOY']['NAME'];

$graph = new PieGraph(600,500);
$graph->SetShadow();
$graph->title->SetFont(FF_FONT1,FS_BOLD);
 
$p1 = new PiePlot3D($data);
$p1->SetSize(0.5);
$p1->SetCenter(0.45);
//$p1->SetGuideLines(true,false);
$p1->SetLegends($lbl);
$p1->SetCenter(0.5,0.4);

 
$graph->Add($p1);
$graph->Stroke();
unset($_SESSION['OCS']['STAT_TELEDEPLOY']);

die();
?>