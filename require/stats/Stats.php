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

class Stats{

    public $form_name = "stats";

    //We also initiate a counter variable to help us cyclically rotate through
    //the array of colors.
    public $FC_ColorCounter = 0;

    public $arr_FCColors = [0 => "1941A5",
    1 => "AFD8F8",
    2 => "F6BD0F",
    3 => "8BBA00",
    4 => "A66EDD",
    5 => "F984A1",
    6 => "CCCC00",//Chrome Yellow+Green
    7 => "999999", //Grey
    8 => "0099CC", //Blue Shade
    9 => "FF0000", //Bright Red
    10 => "006F00", //Dark Green
    11 => "0099FF", //Blue (Light)
    12 => "FF66CC", //Dark Pink
    13 => "669966", //Dirty green
    14 => "7C7CB4", //Violet shade of blue
    15 => "FF9933", //Orange
    16 => "9900FF", //Violet
    17 => "99FFCC", //Blue+Green Light
    18 => "CCCCFF", //Light violet
    19 => "669900",
    ];
    //Shade of green
    //getFCColor method helps return a color from arr_FCColors array. It uses
    //cyclic iteration to return a color from a given index. The index value is
    //maintained in FC_ColorCounter

    public function showForm($form){

        global $l;
        global $protectedPost;

        $configToLookOut = [
            'INTERFACE_LAST_CONTACT' => 'INTERFACE_LAST_CONTACT',
            'EXCLUDE_ARCHIVE_COMPUTER' => 'EXCLUDE_ARCHIVE_COMPUTER'
        ];

        $configValues = look_config_default_values($configToLookOut);

        foreach($form as $key => $value){
            if ($key == 'NB_OS') {
                $sql = "select count(h.osname) c,h.osname as name from hardware h LEFT JOIN accountinfo a ON a.HARDWARE_ID = h.ID where h.osname != '' AND h.deviceid != '_SYSTEMGROUP_'";
                if (is_defined($_SESSION['OCS']["mesmachines"])) {
                    $sql .= " AND " . $_SESSION['OCS']["mesmachines"];
                }
                if (isset($configValues['ivalue']['EXCLUDE_ARCHIVE_COMPUTER']) && $configValues['ivalue']['EXCLUDE_ARCHIVE_COMPUTER'] == 1) {
                    $sql .= " AND h.archive IS NULL";
                }
                $sql .= " group by h.osname order by count(h.osname) DESC ";
                $height_legend = 300;
            } else {
                $sql = "select count(h.useragent) c, upper(h.useragent) as name from hardware h LEFT JOIN accountinfo a ON a.HARDWARE_ID = h.ID where h.useragent != '' AND h.deviceid != '_SYSTEMGROUP_'";
                if (is_defined($_SESSION['OCS']["mesmachines"])) {
                    $sql .= " AND " . $_SESSION['OCS']["mesmachines"];
                }
                if (isset($configValues['ivalue']['EXCLUDE_ARCHIVE_COMPUTER']) && $configValues['ivalue']['EXCLUDE_ARCHIVE_COMPUTER'] == 1) {
                    $sql .= " AND h.archive IS NULL";
                }
                $sql .= " group by h.useragent order by count(h.useragent) DESC ";
                $height_legend = 300;
            }

            $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
            $i = 0;
            while ($row = mysqli_fetch_object($res)) {
                if($i <= 10){
                    $chart[$key]['count'][$i] = $row->c;
                    $chart[$key]['name_value'][$i] = addslashes($row->name);
                    $chart[$key]['name'] = [$key];
                    if($key == 'NB_OS'){
                      $chart[$key]['title'] = $l->g(783);
                    }else{
                      $chart[$key]['title'] = $l->g(784);
                    }
                }
                $i++;
            }

            if($key == 'SEEN'){
                $result_seen_interval = $configValues['ivalue']['INTERFACE_LAST_CONTACT'] ?: 15;
                $date = date("Y-m-d",strtotime("-".$result_seen_interval." day"));
                $sql_seen = "SELECT DATE_FORMAT(h.lastcome, '%Y-%m') AS contact, count(h.lastcome) AS conta FROM `hardware` h LEFT JOIN accountinfo a ON a.HARDWARE_ID = h.ID WHERE h.LASTCOME < '".$date."' AND h.deviceid != '_SYSTEMGROUP_'";
                if (is_defined($_SESSION['OCS']["mesmachines"])) {
                    $sql_seen .= " AND " . $_SESSION['OCS']["mesmachines"];
                }
                if (isset($configValues['ivalue']['EXCLUDE_ARCHIVE_COMPUTER']) && $configValues['ivalue']['EXCLUDE_ARCHIVE_COMPUTER'] == 1) {
                    $sql_seen .= " AND h.archive IS NULL";
                }
                $sql_seen .= " GROUP BY contact ORDER BY contact ASC";

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
                $chart[$key]['title'] = $l->g(820).' > '.htmlentities($result_seen_interval, ENT_QUOTES).' '.$l->g(496);
            }

            if($key == 'MANUFAC'){
                $sql_man = "SELECT b.SMANUFACTURER AS man, count(b.SMANUFACTURER) AS c_man FROM `bios` b LEFT JOIN accountinfo a ON a.HARDWARE_ID = b.HARDWARE_ID";
                if (isset($configValues['ivalue']['EXCLUDE_ARCHIVE_COMPUTER']) && $configValues['ivalue']['EXCLUDE_ARCHIVE_COMPUTER'] == 1) {
                    $sql_man .= " LEFT JOIN hardware h ON h.ID = b.HARDWARE_ID WHERE h.ARCHIVE IS NULL";
                }
                if (is_defined($_SESSION['OCS']["mesmachines"]) && $configValues['ivalue']['EXCLUDE_ARCHIVE_COMPUTER'] != 1) {
                    $sql_man .= " WHERE " . $_SESSION['OCS']["mesmachines"];
                } elseif (is_defined($_SESSION['OCS']["mesmachines"]) && $configValues['ivalue']['EXCLUDE_ARCHIVE_COMPUTER'] == 1) {
                    $sql_man .= " AND " . $_SESSION['OCS']["mesmachines"];
                }
                $sql_man .= " group by b.SMANUFACTURER ORDER BY count(b.SMANUFACTURER)  DESC LIMIT 10";
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
                $chart[$key]['title'] = $l->g(851).' - Top 10';
            }

            if($key == 'TYPE'){
                $sql_type = "SELECT CASE WHEN TRIM(b.type) ='' THEN 'Unknow' ELSE b.type END as type, count(b.type) AS conta FROM `bios` b LEFT JOIN accountinfo a ON a.HARDWARE_ID = b.HARDWARE_ID";
                if (isset($configValues['ivalue']['EXCLUDE_ARCHIVE_COMPUTER']) && $configValues['ivalue']['EXCLUDE_ARCHIVE_COMPUTER'] == 1) {
                    $sql_type .= " LEFT JOIN hardware h ON h.ID = b.HARDWARE_ID WHERE h.ARCHIVE IS NULL";
                }
                if (is_defined($_SESSION['OCS']["mesmachines"]) && $configValues['ivalue']['EXCLUDE_ARCHIVE_COMPUTER'] != 1) {
                    $sql_man .= " WHERE " . $_SESSION['OCS']["mesmachines"];
                } elseif (is_defined($_SESSION['OCS']["mesmachines"]) && $configValues['ivalue']['EXCLUDE_ARCHIVE_COMPUTER'] == 1) {
                    $sql_man .= " AND " . $_SESSION['OCS']["mesmachines"];
                }
                $sql_type .= " GROUP BY type";
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
                $chart[$key]['title'] = $l->g(854);
            }
        }

        if (isset($chart)) {
            $stats = new StatsChartsRenderer;
            $stats->createChartCanvas($form);
            $stats->createChart($chart, $seen, $quants_seen, $man, $quants_man, $type, $quants_type);
            return true;
        } else {
          return false;
        }
    }

    public function showSNMPForm() {
        global $l;

        $snmp = new OCSSnmp();
        $snmpTypes = $snmp->get_all_type();
        $snmpCountQuery = "SELECT COUNT(*) as nb FROM `%s`";

        $snmpTypeName = [];
        $snmpTypeCount = [];
        $snmpLabel = "";
        $snmpQuant = "";

        if(!empty($snmpTypes)) foreach($snmpTypes as $type) {
            $snmpTypeName[] = $type['TYPENAME'];
            $resultCount = mysql2_query_secure($snmpCountQuery, $_SESSION['OCS']["readServer"], $type['TABLENAME']);
            if($resultCount) foreach($resultCount as $count) {
                $snmpTypeCount[] = $count['nb'];
            }
        }

        $snmpLabel = "['".implode("','",$snmpTypeName)."']";
        $snmpQuant = "['".implode("','",$snmpTypeCount)."']";

        $stats = new StatsChartsRenderer;
        $stats->createSNMPChartCanvas();
        $stats->createSNMPChart($snmpLabel, $snmpQuant, count($snmpTypeName), $l->g(9038));

        return true;
    }

    public function showForm2($form){

        global $l;
        global $protectedPost;

        //last seen since
        $date = date("y-m-d",strtotime("-15 day")); 
        $sql_seen = "SELECT DATE_FORMAT(lastcome, '%Y-%m') AS contact, count(lastcome) AS conta 
        FROM `hardware` 
        WHERE LASTCOME < '".$date."'
        group by contact 
        ORDER BY `contact` ASC";
        $result_seen = mysql2_query_secure($sql_seen, $_SESSION['OCS']["readServer"]);
        $seen_name = array();
        $seen_quant = array();
        while($item = mysqli_fetch_array($result_seen)){
            $seen_name[] = $item['contact'];
            $seen_quant[] = $item['conta'];	
        }	
        $seen = "['".implode("','",$seen_name)."']";
        $quants_seen = "['".implode("','",$seen_quant)."']";

        if (!empty($seen)) {
            $stats = new StatsChartsRenderer;
            $stats->createChartCanvas2($form);
            $stats->createLineChart($seen, $quants_seen);
            return true;
        } else {
          return false;
        }
    }

    public function find_ivalues($packid) {
        $sql = "SELECT id FROM download_enable WHERE fileid='%s'";
        $arg = $packid;
        $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
        while ($row = mysqli_fetch_array($res)) {
            $result[] = $row['id'];
        }
        return $result;
    }

    public function find_device_line($status, $packid) {
        //get all ivalues
        $ivalues = $this->find_ivalues($packid);

        //get hardwareid foreach ivalue
        foreach ($ivalues as $value) {
            $sql = "select hardware_id,ivalue from devices where name='DOWNLOAD' and tvalue";
            if ($status == "NULL") {
                $sql .= " IS NULL ";
                $arg = $value;
            } elseif ($status == "NOTNULL") {
                $sql .= " IS NOT NULL ";
                $arg = $value;
            } else {
                $sql .= " LIKE '%s' ";
                $arg = array($status, $value);
            }
            $sql .= "AND ivalue='%s' " .
                    "AND hardware_id NOT IN (SELECT id FROM hardware WHERE deviceid='_SYSTEMGROUP_')";

            $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
            while ($row = mysqli_fetch_object($res)) {
                $result[$value][] = $row->hardware_id;
            }
        }
        return $result;
    }

}
