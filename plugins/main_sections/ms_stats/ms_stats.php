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
$array_profil[7]=$l->g(1259);
$array_profil[30]=$l->g(1260);
$array_profil['ALL']=$l->g(215);
if (!isset($protectedPost['REST']))
	$protectedPost['REST']=7;
echo $l->g(1251). ": " .show_modif($array_profil,"REST",2,$form_name);

if (isset($protectedPost['REST']) and $protectedPost['REST'] != 'ALL')
$lastWeek = time() - ($protectedPost['REST'] * 24 * 60 * 60);
//msg_error(time()."=>".$lastWeek);
//echo date('d/m/Y', $lastWeek) ;    
while( !feof($fd) ) {
         $line = trim( fgets( $fd, 256 ) );
         $trait=explode (';',$line);
         if ($trait[3]==$protectedPost['onglet']){
         	$h=explode(' ',$trait[1]);
         	$time=explode('/',$h[0]);
       //  	p($time);
         	//echo mktime(0, 0, 0, $time[1], $time[0], $time[2])." => ".$lastWeek."<br>" ; 
         	if (mktime(0, 0, 0, $time[1], $time[0], $time[2])>= $lastWeek){
	         	$find_connexion[$h[0]]=$find_connexion[$h[0]]+1;     
	         	if ($find_connexion[$h[0]]>$max)    
	         		$max=$find_connexion[$h[0]];
        	}
         }
}

fclose( $fd );
if (isset($find_connexion)){
	if ($_SESSION['OCS']['useflash'] == 1){
		$strXML2="<graph  xAxisName='".$l->g(232)."'
		yAxisName='".$l->g(55)."' numberPrefix='' showValues='0' 
		numVDivLines='10' showAlternateVGridColor='1' AlternateVGridColor='e1f5ff' 
		divLineColor='e1f5ff' vdivLineColor='e1f5ff' yAxisMaxValue='".$max."'  yAxisMinValue='0'
		bgColor='E9E9E9' canvasBorderThickness='0' decimalPrecision='0' rotateNames='1'>
		<categories>";
	}
	$setvalue='';
	$data_value=array();
	foreach ($find_connexion as $name=>$value){
		if ($_SESSION['OCS']['useflash'] == 1){
			$strXML2.="<category name='".$name."' />";
			$setvalue.="<set value='".$value."' />";
		}
		//array_push($data_value,$value);
	}
	if ($_SESSION['OCS']['useflash'] == 1){
		$strXML2.="</categories>
		<dataset seriesName='' color='B1D1DC'  areaAlpha='60' showAreaborder='1' areaBorderThickness='1' areaBorderColor='7B9D9D'>";
		$strXML2.=$setvalue;
		$strXML2.="</dataset></graph> ";
		echo renderChartHTML($_SESSION['OCS']['FCharts']."/Charts/FCF_StackedArea2D.swf", "", $strXML2, "speedStat", 800, 500);
	}else
	{
		$_SESSION['OCS']['STAT_CNX']['DATA']=$find_connexion;
		echo "<img src='index.php?".PAG_INDEX."=".$pages_refs['jp_activity_stats']."&no_header=1' border=0> ";
 		
	}
}else
	msg_warning($l->g(766));
echo "</div>";
echo "</form>";

?>