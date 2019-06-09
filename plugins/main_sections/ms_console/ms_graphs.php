<?php

//get softwares
$sql = "SELECT SUBSTRING(bdate, 7,10) AS year, count(SUBSTRING(bdate, 7,10)) AS c_year FROM `bios` group by year  ";
$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);

$softs = array();
$softs_year = array();
$softs_quant = array();

while($item = mysqli_fetch_array($result)){
	$softs_year[] = $item['year'];
	$softs_quant[] = $item['c_year'];	
}	

$years = "['".implode("','",$softs_year)."']";
$quants = "['".implode("','",$softs_quant)."']";


//get manufacturers
$sql_man = "SELECT `SMANUFACTURER` AS man, count(`SMANUFACTURER`) AS c_man FROM `bios` group by `SMANUFACTURER` ORDER BY count(`SMANUFACTURER`)  DESC LIMIT 10";
$result_man = mysql2_query_secure($sql_man, $_SESSION['OCS']["readServer"]);

$man = array();
$man_name = array();
$man_quant = array();

while($item = mysqli_fetch_array($result_man)){
	$man_name[] = $item['man'];
	$man_quant[] = $item['c_man'];	
}	

$man = "['".implode("','",$man_name)."']";
$quants_man = "['".implode("','",$man_quant)."']";


//type
$sql_type = "SELECT type, count(type) AS conta FROM `bios` group by type";
$result_type = mysql2_query_secure($sql_type, $_SESSION['OCS']["readServer"]);

$type = array();
$type_name = array();
$type_quant = array();

while($item = mysqli_fetch_array($result_type)){
	$type_name[] = $item['type'];
	$type_quant[] = $item['conta'];	
}	

$type = "['".implode("','",$type_name)."']";
$quants_type = "['".implode("','",$type_quant)."']";


//memory
$sql_mem = "SELECT capac, count(capac) AS conta FROM
(
Select hardware_id ,       
SUM(capacity) as capac 
from memories 
GROUP BY hardware_id 
) as view
GROUP BY view.capac";

$result_mem = mysql2_query_secure($sql_mem, $_SESSION['OCS']["readServer"]);

$mem = array();
$mem_name = array();
$mem_quant = array();

while($item = mysqli_fetch_array($result_mem)){
	$mem_name[] = $item['capac'];
	$mem_quant[] = $item['conta'];	
}	

$mem = "['".implode("','",$mem_name)."']";
$quants_mem = "['".implode("','",$mem_quant)."']";



//last seen since

$date = date("y-m-d",strtotime("-15 day")); 

$sql_seen = "SELECT DATE_FORMAT(lastcome, '%Y-%m') AS contact, count(lastcome) AS conta 
FROM `hardware` 
WHERE LASTCOME < '".$date."'
group by contact 
ORDER BY `contact` ASC";

$result_seen = mysql2_query_secure($sql_seen, $_SESSION['OCS']["readServer"]);

$seen = array();
$seen_name = array();
$seen_quant = array();

while($item = mysqli_fetch_array($result_seen)){
	$seen_name[] = $item['contact'];
	$seen_quant[] = $item['conta'];	
}	

$seen = "['".implode("','",$seen_name)."']";
$quants_seen = "['".implode("','",$seen_quant)."']";

?>

<div class="col-md-12 col-sm-12" style="height: 160px; margin-top:30px; margin-bottom: 0px;">
	<canvas id="seen" width="400" height="140" class="col-md-6 col-sm-6"></canvas>
</div>

<div class="col-md-12 col-sm-12" style="height: 200px; margin-top:210px;">
	<div class="col-md-6 col-sm-6 row" style="margin-top: 30px; float: left;">
		<canvas id="manufac" height="180"></canvas>
	</div>
	<div class="col-md-6 col-sm-6 row" style="margin-top: 30px; float: right;">
		<canvas id="type" height="180"></canvas>
	</div>
</div>

<div class="col-md-12 col-sm-12" style="height: 160px; margin-top:160px;">
	<canvas id="memory" width="400" height="140" class=" col-md-12 col-sm-12"></canvas>
</div>

<div class="col-md-12 col-sm-12" style="height: 160px; margin-top:240px; margin-bottom: 170px;">
	<canvas id="ages" width="400" height="140" class="col-md-12 col-sm-12"></canvas>
</div>


<script>
var ctxy = document.getElementById('ages').getContext('2d');
var myChart = new Chart(ctxy, {
    type: 'bar',
    data: {
        labels: <?php echo $years; ?>,
        datasets: [{
            label: '',
            data: <?php echo $quants; ?>,
				backgroundColor: ['#1941A5' ,'#AFD8F8' ,'#F6BD0F' ,'#8BBA00' ,'#A66EDD' ,'#F984A1' ,'#CCCC00' ,'#999999' ,'#0099CC' ,'#FF0000' ,'#006F00' ,'#0099FF', '#3e95cd', '#2a6bcf','#78867a','#e8c3b9','#c45850','#7eec72','#a36640','#c22a2c','#fad97b','#c40244' ],				
				//borderColor: 'rgba(75, 192, 192, 1)',
            //borderWidth: 1
        }]
    },
		options: {
         responsive: true,
         legend: {
             display: false,
         },
         title: {
             display: true,
             text: "<?php echo $l->g(210).' - '.$l->g(729); ?>"
         },
         animation: {
             animateScale: true,
             animateRotate: true
         }
       }
});

//manufac
var ctxm = document.getElementById('manufac').getContext('2d');
var myChart = new Chart(ctxm, {
    type: 'horizontalBar',
    data: {
        labels: <?php echo $man; ?>,
        datasets: [{
            label: '',
            data: <?php echo $quants_man; ?>,
				backgroundColor: ['#1941A5' ,'#AFD8F8' ,'#F6BD0F' ,'#8BBA00' ,'#A66EDD' ,'#F984A1' ,'#CCCC00' ,'#999999' ,'#0099CC' ,'#FF0000' ,'#006F00' ,'#0099FF', '#3e95cd', '#2a6bcf','#78867a','#e8c3b9','#c45850','#7eec72','#a36640','#c22a2c','#fad97b','#c40244' ],				

        }]
    },
		options: {
         responsive: true,
         legend: {
             display: false,
             position: 'bottom',
	      labels: {
	        fontColor: "#000080",
	      }
         },
         title: {
             display: true,
             text: "<?php echo $l->g(851).' - Top 10'; ?>"
         },
         animation: {
             animateScale: true,
             animateRotate: true
         }
       }
});


//types
var ctxm = document.getElementById('type').getContext('2d');
var myChart = new Chart(ctxm, {
    type: 'horizontalBar',
    data: {
        labels: <?php echo $type; ?>,
        datasets: [{
            label: '',
            data: <?php echo $quants_type; ?>,
				backgroundColor: ['#1941A5' ,'#AFD8F8' ,'#F6BD0F' ,'#8BBA00' ,'#A66EDD' ,'#F984A1' ,'#CCCC00' ,'#999999' ,'#0099CC' ,'#FF0000' ,'#006F00' ,'#0099FF', '#3e95cd', '#2a6bcf','#78867a','#e8c3b9','#c45850','#7eec72','#a36640','#c22a2c','#fad97b','#c40244' ],				

        }]
    },
		options: {
         responsive: true,
         legend: {
             display: false,
             position: 'bottom',
	      labels: {
	        fontColor: "#000080",
	      }
         },
         title: {
             display: true,
             text: "<?php echo $l->g(854); ?>"
         },
         animation: {
             animateScale: true,
             animateRotate: true
         }
       }
});



//memory
var ctx= document.getElementById('memory').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo $mem; ?>,
        datasets: [{
            label: '',
            data: <?php echo $quants_mem; ?>,
				backgroundColor: ['#1941A5' ,'#AFD8F8' ,'#F6BD0F' ,'#8BBA00' ,'#A66EDD' ,'#F984A1' ,'#CCCC00' ,'#999999' ,'#0099CC' ,'#FF0000' ,'#006F00' ,'#0099FF', '#3e95cd', '#2a6bcf','#78867a','#e8c3b9','#c45850','#7eec72','#a36640','#c22a2c','#fad97b','#c40244' ],				
        }]
    },
		options: {
         responsive: true,
         legend: {
             display: false,
         },
         title: {
             display: true,
             text: "<?php echo $l->g(26). ' - MB'; ?>"
         },
         animation: {
             animateScale: true,
             animateRotate: true
         }
       }
});


//last seen
var ctx= document.getElementById('seen').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo $seen; ?>,
        datasets: [{
            label: '',
            data: <?php echo $quants_seen; ?>,
				fill: true,
				borderWidth: 6,
				backgroundColor: 'rgba(75, 192, 192, 0.6)'							
        }]
    },
		options: {
         responsive: true,
         legend: {
             display: false,
         },
         title: {
             display: true,
             text: "<?php echo $l->g(820).' > 15 '.$l->g(496); ?>"
         },
         animation: {
             animateScale: true,
             animateRotate: true
         }
       }
});

</script>