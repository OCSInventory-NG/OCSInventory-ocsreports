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
$data_on['CONNEXION'] = $l->g(1255);
$data_on['BAD CONNEXION'] = $l->g(1256);
if (!isset($protectedPost['onglet']))
    $protectedPost['onglet'] = 'CONNEXION';

if ($protectedPost['onglet'] == 'CONNEXION' || $protectedPost['onglet'] == 'BAD CONNEXION') {

    $ms_cfg_file = $_SESSION['OCS']['LOG_DIR'] . "log.csv";

    if (!is_readable($ms_cfg_file)) {
        return "NO_FILES";
    }
    $fd = fopen($ms_cfg_file, "r");
    $max = 0;
    $array_profil[7] = $l->g(1259);
    $array_profil[30] = $l->g(1260);
    $array_profil['ALL'] = $l->g(215);
    if (!isset($protectedPost['REST']))
        $protectedPost['REST'] = 7;
    $stats .= $l->g(1251) . ": " . show_modif($array_profil, "REST", 2, $form_name) . "<br>";

    if (isset($protectedPost['REST']) && $protectedPost['REST'] != 'ALL')
        $lastWeek = time() - ($protectedPost['REST'] * 24 * 60 * 60);
    //msg_error(time()."=>".$lastWeek);
    //echo date('d/m/Y', $lastWeek) ;    
    while (!feof($fd)) {
        $line = trim(fgets($fd, 256));
        $trait = explode(';', $line);
        if ($trait[3] == $protectedPost['onglet']) {
            $h = explode(' ', $trait[1]);
            $time = explode('/', $h[0]);
            //echo mktime(0, 0, 0, $time[1], $time[0], $time[2])." => ".$lastWeek."<br>" ; 
            if (mktime(0, 0, 0, $time[1], $time[0], $time[2]) >= $lastWeek) {
                $find_connexion[$h[0]] = $find_connexion[$h[0]] + 1;
                if ($find_connexion[$h[0]] > $max)
                    $max = $find_connexion[$h[0]];
            }
        }
    }

    fclose($fd);
    if (isset($find_connexion)) {
        $stats .= '<CENTER><div id="chart" style="width: 700px; height: 500px"></div></CENTER>';
        $stats .= '<script type="text/javascript">
	$(function() {
	  $("#chart").chart({
	  template: "line_speed_stat",
	  tooltips: {
	    serie1: ["' . implode('","', array_keys($find_connexion)) . '"],
	  },
	  values: {
	    serie1: [' . implode(',', $find_connexion) . '],
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
    } else
        msg_warning($l->g(766));
}
?>