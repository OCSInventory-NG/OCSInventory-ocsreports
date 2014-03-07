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
$item = mysqli_fetch_object($result);
$total_mach=$item->c;
if ($total_mach<=0){
	msg_error($l->g(837));
	require_once(FOOTER_HTML);
	die();
}
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
while($item = mysqli_fetch_object($result)){
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
$i=0;
foreach ($nb_4_hour as $key=>$value){
	$ancienne+=$value;
	$data[$i]=round((($ancienne*100) / $total_mach),2);
	$legende[$i]=date ( "d/m/Y H:00" ,$key)."<br>".$data[$i]."%";
	$i++;
}
if (isset($data) and count($data) != 1){
	echo '<br><div  class="mlt_bordure" >';
	echo '<CENTER><div id="chart" style="width: 700px; height: 500px"></div></CENTER>';
	echo '<script type="text/javascript">
$(function() {
  $("#chart").chart({
  template: "line_speed_stat",
  tooltips: {
    serie1: ["'.implode('","',$legende).'"],
  },
  values: {
    serie1: ['.implode(',',$data).'],
  },
  defaultSeries: {
    fill: true,
    stacked: false,
    highlight: {
      scale: 2
    },
    startAnimation: {
      active: true,
      type: "grow",
      easing: "bounce"
    }
  }
});

});

$.elycharts.templates[\'line_speed_stat\'] = {
  type: "line",
  margins: [10, 10, 20, 50],
  defaultSeries: {
    plotProps: {
      "stroke-width": 4
    },
    dot: true,
    dotProps: {
      stroke: "white",
      "stroke-width": 2
    }
  },
  series: {
    serie1: {
      color: "blue"
    },
  },
  defaultAxis: {
    labels: true
  },
  features: {
    grid: {
      draw: [true, false],
      props: {
        "stroke-dasharray": "-"
      }
    },
    legend: {
      horizontal: false,
      width: 80,
      height: 50,
      x: 220,
      y: 250,
      dotType: "circle",
      dotProps: {
        stroke: "white",
        "stroke-width": 2
      },
      borderProps: {
        opacity: 0.3,
        fill: "#c0c0c0",
        "stroke-width": 0
      }
    }
  }
};		</script>';	
echo "</div><br>";
	
		

}else
	msg_warning($l->g(989));
	
?>
