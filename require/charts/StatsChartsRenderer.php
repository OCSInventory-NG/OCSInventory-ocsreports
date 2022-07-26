<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

/**
 * Renders the stats charts
 */
class StatsChartsRenderer {

    public $colorsList = array(
        "#1941A5", //Dark Blue
        "#AFD8F8",
        "#F6BD0F",
        "#8BBA00",
        "#A66EDD",
        "#F984A1",
        "#CCCC00", //Chrome Yellow+Green
        "#999999", //Grey
        "#0099CC", //Blue Shade
        "#FF0000", //Bright Red
        "#006F00", //Dark Green
        "#0099FF",//Blue (Light)
        "#FF66CC", //Dark Pink
        "#669966", //Dirty green
        "#7C7CB4", //Violet shade of blue
        "#FF9933", //Orange
        "#9900FF", //Violet
        "#99FFCC", //Blue+Green Light
        "#CCCCFF", //Light violet
        "#669900", //Shade of green
    );

    /**
     * @param type $name : name of the canvas
     * @param type $legend : show legend or not ?
     */
    public function createChartCanvas($name, $legend = true, $offset = true){

        foreach($name as $key => $value){

            if($legend && $key != 'SEEN' && $key != "MANUFAC" && $key != "TYPE"){
                $mainClass = "col-md-4";
            }elseif($key == 'SEEN'){
                $mainClass = "col-md-12";
            }else{
                $mainClass = "col-md-6 col-sm-6";
            }

            $offset = "";
            ?>
            <div>
            <?php if($legend && ($key == 'NB_AGENT' || $key == 'NB_OS' || $key == 'teledeploy_stats')){ ?>
                <div class='<?php echo $mainClass ?>'>
                    <canvas id="<?php echo $key?>" height="150"/>
                </div>
                <div class='col-md-2 text-left'>
                    <div id="<?php echo $key ?>legend" class="span-charts">&nbsp;</div>
                </div>
            <?php }elseif($key == 'SEEN'){ ?>
                <div class='<?php echo $mainClass ?>' style='margin-top: 5%;'>
                    <canvas id="<?php echo $key?>" width="400" height="100"/>
                </div>
            <?php }elseif($key == 'MANUFAC' || $key == "TYPE"){ ?>
                <div class="<?php echo $mainClass ?>" style='margin-top: 5%;'>
				    <canvas id="<?php echo $key?>" height="160"></canvas>
			    </div>
            <?php }elseif($key == 'teledeploy_speed'){  ?>
                <div class='col-md-2'></div>
                <div class='col-md-8'>
                    <canvas id="<?php echo $key?>" height="150"/>
                </div>
            <?php } ?>
            </div>
            <?php
        }
    }

    /**
     * @param string $canvasName Name of the canvas
     * @param array $labels Labals array
     * @param array $data Data arrays
     */
    public function createChart($chart, $seen = null, $quants_seen = null, $man = null, $quants_man = null, $type = null, $quants_type = null){
        $i = 0;
        foreach($chart as $key => $value){
            if($key == 'NB_AGENT' || $key == 'NB_OS' || $key == 'teledeploy_stats'){
                ?>
                <script>
                var config<?php echo $i ?> = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [
                                <?php
                                foreach ($value['count'] as $data) {
                                    echo "$data ,";
                                }
                                ?>
                            ],
                            backgroundColor: [
                                <?php
                                self::generateColorList(count($value['name_value']));
                                ?>
                            ],
                            label: 'Stats'
                        }],
                        labels: [
                            <?php
                            foreach ($value['name_value'] as $label) {
                                echo "'$label' ,";
                            }
                            ?>
                        ]
                    },
                    options: {
                        responsive: true,
                        legend: {
                            display: false,
                        },
                        title: {
                            display: true,
                            text: "<?php echo $value['title'] ?? '' ?>"
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };
                </script>
                <?php
            }elseif($key == 'SEEN'){
                ?>
                <script>
                var config<?php echo $i ?> = {
                    type: 'line',
                    data: {
                        labels: <?php echo $seen; ?>,
                        datasets: [{
                            label: '',
                            data: <?php echo $quants_seen; ?>,
                                fill: true,
                                borderWidth: 6,
                                backgroundColor: 'rgba(150, 27, 126, 0.6)'
                        }]
                    },
                        options: {
                        responsive: true,
                        legend: {
                            display: false,
                        },
                        title: {
                            display: true,
                            text: "<?php echo $value['title'] ?>"
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };
                </script>
                <?php
            }elseif($key == 'TYPE' || $key == 'MANUFAC'){
                ?>
                <script>
                var config<?php echo $i ?> = {
                    type: 'horizontalBar',
                    data: {
                        labels: <?php if($key == 'MANUFAC'){ echo $man; }else{ echo $type; } ?>,
                        datasets: [{
                            label: '',
                            data: <?php if($key == 'MANUFAC'){ echo $quants_man; }else{ echo $quants_type; }  ?>,
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
                            text: "<?php echo $value['title'] ?>"
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        },
                        scales: {
                            xAxes: [{
                                ticks:{
                                    beginAtZero:true
                                }
                            }]
                        }
                    }
                }
                </script>
                <?php
            }

          $name[$i] = $value['name'][0] ?? "";
          $i++;
        }

        ?>
        <script>
        window.onload = function() {
          <?php for($p = 0; isset($name[$p]); $p++){ ?>
            var ctx<?php echo $p ?> = document.getElementById("<?php echo $name[$p] ?>").getContext("2d");
            window.myDoughnut = new Chart(ctx<?php echo $p ?>, config<?php echo $p ?>);
            <?php if($name[$p] == 'NB_AGENT' || $name[$p] == 'NB_OS' || $name[$p] == 'teledeploy_stats'){ ?>
                document.getElementById("<?php echo $name[$p] ?>legend").innerHTML = window.myDoughnut.generateLegend();
                <?php } ?>
          <?php } ?>
        };
        </script>
        <?php

    }

    public function createPointChart($canvasName, $labels, $datas, $dataLbl){

        ?>
        <script>
        var config = {
            type: 'line',
            data: {
                labels: [<?php
                    foreach ($labels as $label) {
                       echo "'$label' ,";
                    }
                ?>],
                datasets: [{
                    label: "<?php echo $dataLbl ?>",
                    backgroundColor: "#961b7e",
                    borderColor: "#961b7e",
                    data: [
                        <?php
                        foreach ($datas as $data) {
                            echo "$data ,";
                        }
                        ?>
                    ],
                    fill: false,
                }]
            },
            options: {
                responsive: true,
                title:{
                    display:false,
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Day'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        }
                    }]
                }
            }
        };

        window.onload = function() {
            var ctx = document.getElementById("<?php echo $canvasName ?>").getContext("2d");
            window.myLine = new Chart(ctx, config);
        };
        </script>
        <?php

    }

    /**
     * @param int $nb number of color to create in the list
     */
    static public function generateColorList($nb){
        $stats = new self();
        for ($i = 0; $i <= $nb; $i++) {
            echo "'".$stats->colorsList[$i]."' ,";
        }
    }


    public function createSNMPChartCanvas(){
        ?>
        <div>
            <div class='col-md-12' style='margin-top: 5%;'>
                <canvas id="snmp_type_stat" width="400" height="100"/>
            </div>
        </div>
        <?php
    }

    public function createSNMPChart($label, $quant, $nb, $title){
        ?>
        <script>
            var config = {
                type: 'bar',
                data: {
                    labels: <?php echo $label ?>,
                    datasets: [{
                        label: '',
                        data: <?php echo $quant ?>,
                        backgroundColor: [<?php self::generateColorList($nb); ?>],
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
                        text: "<?php echo $title ?>"
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    },
                    scales: {
                        yAxes: [{
                            ticks:{
                                beginAtZero:true
                            }
                        }]
                    }
                }
            }

            var ctx = document.getElementById("snmp_type_stat").getContext("2d");
            window.mySNMP = new Chart(ctx, config);

        </script>
        <?php
    }

}
