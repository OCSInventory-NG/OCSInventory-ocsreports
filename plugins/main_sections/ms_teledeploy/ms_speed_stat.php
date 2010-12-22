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
$year_mouth['Dec']=12;
$year_mouth['Nov']=11;
$year_mouth['Oct']=10;
$year_mouth['Sep']=9;
$year_mouth['Aug']=8;
$year_mouth['Jul']=7;
$year_mouth['Jun']=6;
$year_mouth['May']=5;
$year_mouth['Apr']=4;
$year_mouth['Mar']=3;
$year_mouth['Feb']=2;
$year_mouth['Jan']=1;

$sql="select count(*) c from devices d,
							download_enable d_e,download_available d_a
						where d.name='DOWNLOAD'
							and d_e.id=d.ivalue
							and d_a.fileid=d_e.fileid
							and d_e.fileid='%s'";
$arg=$protectedGet['stat'];
$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);	
$item = mysql_fetch_object($result);
$total_mach=$item->c;

$sql="select d.hardware_id as id,d.comments as date_valid 
					from devices d,download_enable d_e,download_available d_a
			where d.name='DOWNLOAD' 
				and tvalue='%s' 
				and comments is not null
				and d_e.id=d.ivalue
				and d_a.fileid=d_e.fileid
				and d_e.fileid='%s'";
$arg=array(urldecode($protectedGet['ta']),$protectedGet['stat']);
$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
$nb_4_hour=array();
//$total_mach=0;
while($item = mysql_fetch_object($result)){
	//echo $item->date_valid."<br>";
	unset($data_temp,$day,$year,$hour_temp,$hour);
	$data_temp=explode(' ',$item->date_valid);
	if ($data_temp[2] != '')
	$day=$data_temp[2];
	else
	$day=$data_temp[3];
	
	$mouth=$data_temp[1];
	if (isset($data_temp[5]))
	$year=$data_temp[5];
	else
	$year=$data_temp[4];
//	print_r($data_temp);
//	echo "=>".$year."<br>";
	$hour_temp=explode(':',$data_temp[3]);
	$hour=$hour_temp[0];
	if ($hour<12)
	$hour=12;
	else
	$hour=00;
	$timestamp=mktime ($hour,0,0,$year_mouth[$mouth],$day,$year);
	if (isset($nb_4_hour[$timestamp]))
	$nb_4_hour[$timestamp]++;
	else
	$nb_4_hour[$timestamp]=1;
	//$total_mach++;
}

ksort($nb_4_hour);
foreach ($nb_4_hour as $key=>$value){
	$ancienne+=$value;
	$legende[]=date ( "d/m/Y H:00" ,$key);
	$data[]=(($ancienne*100) / $total_mach);
	
}

if (isset($data) and count($data) != 1){
	if ($_SESSION['OCS']['useflash'] == 1){
		$strXML2="<graph caption='".$l->g(1250)." (".$protectedGet['stat'].") ".$l->g(81)." : ".$protectedGet['ta']."'  xAxisName='".$l->g(232)."'
		yAxisName='".$l->g(1125)."' numberPrefix='' showValues='0' 
		numVDivLines='10' showAlternateVGridColor='1' AlternateVGridColor='e1f5ff' 
		divLineColor='e1f5ff' vdivLineColor='e1f5ff' yAxisMaxValue='100'  yAxisMinValue='0'
		bgColor='E9E9E9' canvasBorderThickness='0' decimalPrecision='0' rotateNames='1'>
		<categories>";
	}
	foreach ($legende as $value){
		if ($_SESSION['OCS']['useflash'] == 1)
			$strXML2.="<category name='".$value."' />";	
	}
	if ($_SESSION['OCS']['useflash'] == 1){
		$strXML2.="</categories>
				<dataset seriesName='' color='B1D1DC'  areaAlpha='60' showAreaborder='1' areaBorderThickness='1' areaBorderColor='7B9D9D'>";
	}
	foreach ($data as $value){
		if ($_SESSION['OCS']['useflash'] == 1)
			$strXML2.="<set value='".$value."' />";	
	}
	if ($_SESSION['OCS']['useflash'] == 1){
		$strXML2.="</dataset></graph> ";
		echo renderChartHTML($_SESSION['OCS']['FCharts']."/Charts/FCF_StackedArea2D.swf", "", $strXML2, "speedStat", 800, 500);
	}else{
		
		$_SESSION['OCS']['STAT_SPEED_TELEDEPLOY']['DATA']=$data;
		$_SESSION['OCS']['STAT_SPEED_TELEDEPLOY']['NAME']=$legende;
		echo "<img src='index.php?".PAG_INDEX."=".$pages_refs['jp_teledeploy_speed_stats']."&no_header=1' border=0> ";	
		
	}
}else
	msg_warning($l->g(989));
	
?>
