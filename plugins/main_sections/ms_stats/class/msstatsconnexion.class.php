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

class MsStatsConnexion extends MsStats{
    
    public function getTabName(){
        global $l;
        
        return $l->g(1255);
    }
    
    public function showForm(){
        
        require('require/function_stats.php');
        
        global $l;
        global $protectedPost;

        $ms_cfg_file = $_SESSION['OCS']['LOG_DIR'] . "log.csv";

        if (!is_readable($ms_cfg_file)) {
            msg_info("No Files");
            return false;
        }
        
        $fd = fopen($ms_cfg_file, "r");
        $max = 0;
        $array_profil[7] = $l->g(1259);
        $array_profil[30] = $l->g(1260);
        $array_profil['ALL'] = $l->g(215);
        if (!isset($protectedPost['REST'])) {
            $protectedPost['REST'] = 7;
        }
        echo $l->g(1251) . ": " . show_modif($array_profil, "REST", 2, parent::getFormName()) . "<br>";

        if (isset($protectedPost['REST']) && $protectedPost['REST'] != 'ALL') {
            $lastWeek = time() - ($protectedPost['REST'] * 24 * 60 * 60);
        }
        
        while (!feof($fd)) {
            $line = trim(fgets($fd, 256));
            $trait = explode(';', $line);
            if ($trait[3] == "CONNEXION") {
                $h = explode(' ', $trait[1]);
                $time = explode('/', $h[0]);
                
                if (mktime(0, 0, 0, $time[1], $time[0], $time[2]) >= $lastWeek) {
                    $find_connexion[$h[0]] = $find_connexion[$h[0]] + 1;
                    if ($find_connexion[$h[0]] > $max) {
                        $max = $find_connexion[$h[0]];
                    }
                }
            }
        }
        
        fclose($fd);
        
        if (isset($find_connexion)) {
            $stats = new StatsChartsRenderer;
            $stats->createChartCanvas("connexion_stats", false, false);
            $stats->createPointChart("connexion_stats", array_keys($find_connexion), $find_connexion, $l->g(55));
            
        } else {
            echo "<div class='col-md-12'>";
            msg_info($l->g(766));
            echo "</div>";
        }
    }
    
}