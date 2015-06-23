<?php
$rep_ocs=explode('/',$_SERVER['SCRIPT_FILENAME']);
array_pop($rep_ocs);
$file_restriction_soft=implode($rep_ocs,'/')."/plugins/main_sections/ms_stats/ms_stats_top_soft.txt";
$data_on['TOP']=$l->g(800);
if ($protectedPost['onglet'] == 'TOP'){
	require_once('require/function_stats.php');
	$stats="";
	if (!isset($protectedPost['CHOICE_OP']) or $protectedPost['CHOICE_OP'] == "")
		$protectedPost['CHOICE_OP']='TOP_SOFT';

	$array_option=array('NB_OS'=>$l->g(783),'TOP_SOFT'=>'top soft','NB_AGENTS'=>$l->g(784));
	$stats.= $l->g(1251). ": " .show_modif($array_option,"CHOICE_OP",2,$form_name)."<br>";
	if ($protectedPost['CHOICE_OP'] == 'TOP_SOFT'){

		if (!isset($protectedPost['CHOICE_TOP']) or $protectedPost['CHOICE_TOP'] == "")
			$protectedPost['CHOICE_TOP']=10;
		// open file
		
		
		$tag=array('<LIKE>'=>'LIKE','<EXACTLY>'=>'=','<NOLIKE>'=>'NOT LIKE','<NOEXACTLY>'=>'!=');
		// read line
		if (is_readable($file_restriction_soft)){
			$fp = fopen($file_restriction_soft, "r");
			while ( $ln = fgets($fp, 1024)) {
				$ln=preg_replace('(\r\n|\n|\r|\t|)','',$ln);
				//foreach ($tag as $poub=>$key){
				if (array_key_exists($ln,$tag)){
					$index=$tag[$ln];
				}elseif(substr($ln,0,2) == '</'){
					unset($index);
				}elseif(trim($ln) != "" and isset($index)){
					$data[$index][]=$ln;
				}
			}
			fclose($fp);
		}else {
			msg_error("NO_FILES: ".$file_restriction_soft);
		}
		$array_top=array(5=>5,10=>10,20=>20);
		$stats.= $l->g(55). ": " .show_modif($array_top,"CHOICE_TOP",2,$form_name)."<br>";

		$sql="select count(id) c,name from softwares ";
		if (isset($data)){
			$sql .= " where (";
			$first=0;
			$j=0;
			foreach ($data as $k=>$v){
				$i=0;
				while ($v[$i]){

					$jonct='';
					if(($k == 'LIKE' or $k == '=') and $first != 0 ){
						$jonct=' OR ';
						$j++;
					}elseif($first != 0){
						if ($j!=0)
							$jonct=') AND (';
						else
							$jonct=' AND ';
						$j=0;
					}
					$sql .= $jonct." name ".$k." '%s'";
					$arg[]=$v[$i];
					$i++;
					$first++;
				}

			}

			$sql.= " ) ";
		}
		$sql.= " group by name order by count(id) DESC limit %s";
		$arg[]=$protectedPost['CHOICE_TOP'];
		$height_legend=12*$protectedPost['CHOICE_TOP'];
	}elseif($protectedPost['CHOICE_OP'] == 'NB_OS'){
		$sql="select count(osname) c,osname as name from hardware where osname != '' group by osname order by count(osname) DESC ";
		$height_legend=300;
	}elseif($protectedPost['CHOICE_OP'] == 'NB_AGENTS'){
		$sql="select count(useragent) c,useragent as name from hardware where useragent != '' group by useragent order by count(useragent) DESC ";
		$height_legend=300;
	}



	$res =mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
	$i=0;
	while ($row=mysqli_fetch_object($res)){
		$count_value[$i]=$row->c;
		$name_value[$i]=addslashes($row->name)."<br> (".$l->g(381).":".$row->c.")";
		$legend[$i]=addslashes($row->name);
		if (isset($arr_FCColors[$i]))
			$color[$i]=$arr_FCColors[$i];
		else
			$color[$i]=$arr_FCColors[$i-10];
		$color[$i]="plotProps: {fill: \"".$color[$i]."\"}";
		$i++;
	}

	if (isset($count_value)){
		$stats.= '<CENTER><div id="chart" style="width: 900px; height: 500px"></div></CENTER>';
		$stats.= '<script type="text/javascript">
		$(function() {
		$("#chart").chart({
		template: "pie_stat_teledeploy",
		values: {
		serie1: ['.implode(',',$count_value).']
	},
	labels: ["'.implode('","',$name_value).'"],
	legend: ["'.implode('","',$legend).'"],
	tooltips: {
	serie1: ["'.implode('","',$name_value).'"]
	},
	defaultSeries: {
	values: [{'.implode("}, {",$color).'
	}]
	}
	});
	});

	$.elycharts.templates[\'pie_stat_teledeploy\'] = {
	type: "pie",
	defaultSeries: {
	plotProps: {
	stroke: "white",
	"stroke-width": 2,
	opacity: 0.8
	},
	highlight: {
	move: 20
	},
	tooltip: {
	width: 200, height: 25,
	frameProps: {opacity: 0.5},
	contentStyle : { "font-family": "Arial", "font-size": "9px", "line-height": "8px", color: "black" }
	},
	startAnimation: {
	active: true,
	type: "grow"
	}
	},
	features: {
	legend: {
	horizontal: false,
	width: 240,
	height: '.$height_legend.',
	x: 655,
	y: 180,
	borderProps: {
	"fill-opacity": 0.3
	}
	}
	}
	};
	</script>';
		$stats.= "</div><br>";
	}

}
?>