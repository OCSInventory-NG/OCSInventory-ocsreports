<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Pierre LEMMET 2005
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================
//Modified on $Date: 2010 $$Author: Erwan Goalou

require('require/function_stats.php');
	//We've included ../Includes/FusionCharts.php, which contains functions
//to help us easily embed the charts.
require_once($_SESSION['OCS']['FCharts']."/Code/PHP/Includes/FusionCharts.php");
//a file having a list of colors to be applied to each column (using getFCColor() function)
require_once($_SESSION['OCS']['FCharts']."/Code/PHP/Includes/FC_Colors.php");

if($_SESSION['OCS']['CONFIGURATION']['TELEDIFF']=="YES"){

	if( isset($protectedPost["ACTION"]) and $protectedPost["ACTION"] == "VAL_SUCC") {	
		$sql="DELETE FROM devices WHERE name='DOWNLOAD' AND tvalue LIKE '%s' AND
				ivalue IN (SELECT id FROM download_enable WHERE fileid='%s') 
				AND hardware_id NOT IN (SELECT id FROM hardware WHERE deviceid='_SYSTEMGROUP_')";
		$arg=array('SUCCESS%',$protectedGet["stat"]);
		$resSupp = mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
	}
	if( isset($protectedPost["ACTION"]) and $protectedPost["ACTION"] == "DEL_ALL") {	
		$sql="DELETE FROM devices WHERE name='DOWNLOAD' AND tvalue IS NOT NULL AND
				ivalue IN (SELECT id FROM download_enable WHERE fileid='%s') 
				AND hardware_id NOT IN (SELECT id FROM hardware WHERE deviceid='_SYSTEMGROUP_')";
		$arg=$protectedGet["stat"];
		$resSupp = mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
	}
	if( isset($protectedPost["ACTION"]) and $protectedPost["ACTION"] == "DEL_NOT") {	
		$sql="DELETE FROM devices WHERE name='DOWNLOAD' AND tvalue IS NULL AND
				ivalue IN (SELECT id FROM download_enable WHERE fileid='%s') 
				AND hardware_id NOT IN (SELECT id FROM hardware WHERE deviceid='_SYSTEMGROUP_')";
		$arg=$protectedGet["stat"];
		$resSupp = mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
	}
}

$form_name="show_stats";
$table_name=$form_name;	
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";

$sql="SELECT name FROM download_available WHERE fileid='%s'";
$arg=$protectedGet["stat"];
$res =mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);		
$row=mysql_fetch_object($res);
printEnTete( $l->g(498)." <b>".$row -> name."</b> (".$l->g(296).": ".$protectedGet["stat"]." )");

$strXML  = "<graph caption='".$l->g(498).$row -> name."' subCaption='(".$l->g(296).": ".$protectedGet["stat"]." )' 
			showPercentValues='1' pieSliceDepth='25' showNames='1' decimalPrecision='0' >";
/*$funnelXML="<graph isSliced='1' slicingDistance='4' decimalPrecision='0'>
	<set name='Selected' value='41' color='99CC00' alpha='85'/>
	<set name='Tested' value='84' color='333333' alpha='85'/>
	<set name='Interviewed' value='126' color='99CC00'  alpha='85'/>
	<set name='Candidates Applied' value='180' color='333333' alpha='85'/>
</chart>";*/

$sqlStats="SELECT COUNT(id) as nb, tvalue as txt 
			FROM devices d, download_enable e 
			WHERE e.fileid='%s'
 				AND e.id=d.ivalue 
				AND name='DOWNLOAD' 
				AND hardware_id NOT IN (SELECT id FROM hardware WHERE deviceid='_SYSTEMGROUP_' or deviceid='_DOWNLOADGROUP_')";

$sqlStats.= " GROUP BY tvalue";
$arg=$protectedGet["stat"];
$resStats =mysql2_query_secure($sqlStats." ORDER BY nb DESC", $_SESSION['OCS']["readServer"],$arg);		
$i=0;
while ($row=mysql_fetch_object($resStats)){
	if( $row->txt =="" )
		$name_value[$i] = $l->g(482);
	else
		$name_value[$i] = $row->txt;
	$count_value[$i]=$row->nb;
	 $strXML .= "<set name='".$name_value[$i]."' value='".$count_value[$i]."' color='".$arr_FCColors[$i]."' />";
	 $i++;
	
}
//Create an XML data document in a string variable

$strXML .= "</graph>";
$data_on[0]='Graph n°1';
$data_on[1]='Graph n°2';
//Create the chart - Column 3D Chart with data from strXML variable using dataXML method
onglet($data_on,$form_name,"onglet",4);
echo '<div class="mlt_bordure" >';
if ($protectedPost['onglet'] == 1){
echo renderChartHTML($_SESSION['OCS']['FCharts']."/Charts/FCF_Column3D.swf", "", $strXML, "myNext", 600, 300);
}elseif ($protectedPost['onglet'] == 0)
echo renderChartHTML($_SESSION['OCS']['FCharts']."/Charts/FCF_Pie3D.swf", "", $strXML, "myNext", 800, 400);
echo '</div><br>';


if($_SESSION['OCS']['CONFIGURATION']['TELEDIFF']=="YES"){
	echo "<table class='Fenetre' align='center' border='1' cellpadding='5' width='50%'><tr BGCOLOR='#C7D9F5'>";
	echo "<td width='33%' align='center'><a OnClick='pag(\"VAL_SUCC\",\"ACTION\",\"".$form_name."\");'><b>".$l->g(483)."</b></a></td>";	
	echo "<td width='33%' align='center'><a OnClick='pag(\"DEL_ALL\",\"ACTION\",\"".$form_name."\");'><b>".$l->g(571)."</b></a></td>";	
	echo "<td width='33%' align='center'><a OnClick='pag(\"DEL_NOT\",\"ACTION\",\"".$form_name."\");'><b>".$l->g(575)."</b></a></td>";
	echo "</tr></table><br><br>";
	echo "<input type='hidden' id='ACTION' name='ACTION' value=''>";
}
/*if ($protectedGet['group']){
echo "<form name='refresh' method=POST><div align=center>".$l->g(941)." <select name=selOpt OnChange='refresh.submit();'>
			<option value='ALL'";
if ($protectedPost['selOpt'] == "ALL")
echo " selected ";
echo ">".$l->g(940)."</option>
			<option value='GROUP'";
if ($protectedPost['selOpt'] == "GROUP")
echo " selected ";
echo ">".$l->g(939)."</option></select>
	</div></form>";
}*/
echo "<table class='Fenetre' align='center' border='1' cellpadding='5' width='50%'>
<tr BGCOLOR='#C7D9F5'><td width='30px'>&nbsp;</td><td align='center'><b>".$l->g(81)."</b></td><td align='center'><b>".$l->g(55)."</b></td></tr>";
$j=0;
while( $j<$i ) {
	$nb+=$count_value[$j];
	echo "<tr><td bgcolor='".$arr_FCColors[$j]."'>&nbsp;</td><td>".$name_value[$j]."</td><td>
			<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_multi_search']."&prov=stat&id_pack=".$protectedGet["stat"]."&stat=".urlencode($name_value[$j])."'>".$count_value[$j]."</a>";
	
	echo "<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_speed_stat']."&head=1&ta=".$name_value[$j]."&stat=".$protectedGet["stat"]."\",\"stats_speed\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=1000,height=900\")><img src='image/stat.png'></a>";		
	//echo "<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_speed_stat']."&ta=".$name_value[$j]."&stat=".$protectedGet["stat"]."'>&nbsp;stat</a>
	echo "	</td></tr>";
	$j++;
}
echo "<tr bgcolor='#C7D9F5'><td bgcolor='white'>&nbsp;</td><td><b>".$l->g(87)."</b></td><td><b>".$nb."</b></td></tr>";
echo "</table><br><br>";
      
  /* echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
$list_fields=array($l->g(81) => 'txt',
					$l->g(55) => 'nb',
				   );
$list_col_cant_del=$list_fields;
$default_fields= $list_fields;
$tab_options['ARG_SQL']=$arg;
//$queryDetails  = "SELECT * FROM modems WHERE (hardware_id=$systemid)";
tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sqlStats,$form_name,80,$tab_options);*/

/*


if ($protectedPost['selOpt'] == "GROUP" or $protectedGet['option']=="GROUP"){
$sql_group="select hardware_id from groups_cache where group_id=".$protectedGet['group'];
$res_group = mysql_query($sql_group, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
$machines_group="(";
	while ($item_group = mysql_fetch_object($res_group)){
		$machines_group.= $item_group->hardware_id.",";	
	}
	$machines_group=" IN ".substr($machines_group,0,-1).")";		
}
if ($_SESSION['OCS']["mesmachines"] != ""){
	$sql_mesMachines="select hardware_id from accountinfo a where ".$_SESSION['OCS']["mesmachines"];
	$res_mesMachines = mysql_query($sql_mesMachines, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
	$mesmachines="(";
	while ($item_mesMachines = mysql_fetch_object($res_mesMachines)){
		$mesmachines.= $item_mesMachines->hardware_id.",";	
	}
	$mesmachines=" IN ".substr($mesmachines,0,-1).")";	
	
}
$sqlStats="SELECT COUNT(id) as 'nb', tvalue as 'txt' 
			FROM devices d, download_enable e 
			WHERE e.fileid='".$protectedGet["stat"]."'
 				AND e.id=d.ivalue 
				AND name='DOWNLOAD' 
				AND hardware_id NOT IN (SELECT id FROM hardware WHERE deviceid='_SYSTEMGROUP_' or deviceid='_DOWNLOADGROUP_')";
if (isset($machines_group))
	$sqlStats.= " AND hardware_id".$machines_group;
if (isset($mesmachines))				
	$sqlStats.= " AND hardware_id".$mesmachines;	
$sqlStats.= " GROUP BY tvalue ORDER BY nb DESC";
$resStats = mysql_query($sqlStats, $_SESSION['OCS']["readServer"]);
 	$tot = 0;
	$quartiers = array();
	$coul = array( 0x0091C3, 0xFFCB03  ,0x33CCCC, 0xFF9900,  0x969696,  0x339966, 0xFF99CC, 0x99CC00);
	$coulHtml = array( "0091C3", "FFCB03"  ,"33CCCC", "FF9900",  "969696",  "339966", "FF99CC", "99CC00");
	$i = 0;
	while( $valStats = mysql_fetch_array( $resStats ) ) {
		$tot += $valStats["nb"];
		if( $valStats["txt"] =="" )
			$valStats["txt"] = $l->g(482);
		$quartiers[] = array( $valStats["nb"], $coul[ $i ], $valStats["txt"]." (".$valStats["nb"].")" );
		$legende[] = array( "color"=>$coulHtml[ $i ], "name"=>$valStats["txt"], "count"=>$valStats["nb"] );
		$i++;
		if( $i > sizeof( $coul ) )
			$i=0;
	}

	$sort = array();
	$index = 0;
	for( $count=0; $count < (sizeof( $quartiers )); $count++ ) {
		if( $count%2==0) {
			$sort[ $count ] = $quartiers[ $index ];
			//echo "sort[ $count ] = quartiers[ $index ];<br>";
			$index++;
		}
		else {
			$sort[ $count ] = $quartiers[ sizeof( $quartiers ) - $index ];			
		}		
	}

if( @mysql_num_rows( $resStats ) == 0 ) {
	echo "<center>".$l->g(526)."</center>";
	die();	
}


*/
 echo "</form>";
  
?>
