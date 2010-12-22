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
require_once($_SESSION['OCS']['FCharts']."/Code/PHP/Includes/FusionCharts.php");
//a file having a list of colors to be applied to each column (using getFCColor() function)
require_once($_SESSION['OCS']['FCharts']."/Code/PHP/Includes/FC_Colors.php");

$form_name="stats";
$table_name=$form_name;	
printEnTete($l->g(1251));
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
$data_on['CONNEXION']=$l->g(1255);
$data_on['BAD CONNEXION']=$l->g(1256);
//Create the chart - Column 3D Chart with data from strXML variable using dataXML method
onglet($data_on,$form_name,"onglet",4);
echo '<div class="mlt_bordure" >';
$ms_cfg_file=$_SESSION['OCS']['LOG_DIR']."log.csv";
$fd = @fopen ($ms_cfg_file, "r");
if (!$fd)
		return "NO_FILES";
$max=0;
while( !feof($fd) ) {
         $line = trim( fgets( $fd, 256 ) );
         $trait=explode (';',$line);
         if ($trait[3]==$protectedPost['onglet']){
         	$h=explode(' ',$trait[1]);
         	$time=explode(':',$h[1]);
         	$find_connexion[$h[0].' '.$time[0].'h']=$find_connexion[$h[0].' '.$time[0].'h']+1;     
         	if ($find_connexion[$h[0].' '.$time[0].'h']>$max)    
         		$max=$find_connexion[$h[0].' '.$time[0].'h'];
         }
}
fclose( $fd );
if (isset($find_connexion)){
	$strXML2="<graph  xAxisName='".$l->g(232)."'
	yAxisName='".$l->g(55)."' numberPrefix='' showValues='0' 
	numVDivLines='10' showAlternateVGridColor='1' AlternateVGridColor='e1f5ff' 
	divLineColor='e1f5ff' vdivLineColor='e1f5ff' yAxisMaxValue='".$max."'  yAxisMinValue='0'
	bgColor='E9E9E9' canvasBorderThickness='0' decimalPrecision='0' rotateNames='1'>
	<categories>";
	$setvalue='';
	foreach ($find_connexion as $name=>$value){
		$strXML2.="<category name='".$name."' />";
		$setvalue.="<set value='".$value."' />";
	}
	$strXML2.="</categories>
	<dataset seriesName='' color='B1D1DC'  areaAlpha='60' showAreaborder='1' areaBorderThickness='1' areaBorderColor='7B9D9D'>";
	$strXML2.=$setvalue;
	$strXML2.="</dataset>
	
	
	</graph> ";
	echo renderChartHTML($_SESSION['OCS']['FCharts']."/Charts/FCF_StackedArea2D.swf", "", $strXML2, "speedStat", 800, 500);
}else
	msg_warning($l->g(766));
echo "</div>";
echo "</form>";

?>