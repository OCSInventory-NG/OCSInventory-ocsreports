<?php
/*
 * Copyright 2005-2023 OCSInventory-NG/OCSInventory-ocsreports contributors.
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
require_once('require/snmp/Snmp.php');

########### EXPORTING SNMP TYPES CONFIGURATION FOR LOCAL SNMP SCAN ###########
# will be exporting the snmp configuration for the local snmp scan
# this exports 2 files:
# 1. localsnmp_types_conf.xml - the types configuration
# 2. localsnmp_communities_conf.xml - the communities configuration
# 3. localsnmp_scans_conf.xml - the scans configuration
# 4. localsnmp_subnets_conf.xml - list of subnets to scan
##############################################################################

// snmp conf to xml general function
function SnmpConfToXml($conf_choice) {
    $plural = $conf_choice[0];
    $singular = $conf_choice[1];

    if ($plural == "TYPES") {
        $sql = "SELECT t.TYPE_NAME, tc.CONDITION_OID, tc.CONDITION_VALUE, t.TABLE_TYPE_NAME, l.LABEL_NAME, c.OID FROM snmp_types t LEFT JOIN snmp_configs c ON t.ID = c.TYPE_ID LEFT JOIN snmp_labels l ON l.ID = c.LABEL_ID LEFT JOIN snmp_types_conditions tc ON tc.TYPE_ID = t.ID";
    } else if ($plural == "COMMUNITIES") {
        $sql = "SELECT VERSION,NAME,USERNAME,AUTHPASSWD,LEVEL,AUTHPROTO,PRIVPASSWD,PRIVPROTO FROM snmp_communities";
    } else if ($plural == "CONFS" && isset($_GET['id']) && $_GET['id'] != "") {
        // special treatment if we are retrieving the scan configuration for a specific device or group
        // if the value of conf has been customized, we retrieve it but if not, we use the default value
        $sql = "SELECT NAME, IVALUE, TVALUE FROM devices WHERE NAME LIKE 'SCAN_%' AND HARDWARE_ID=".$_GET['id'];

        $sql_default = "SELECT NAME, IVALUE, TVALUE FROM config WHERE NAME LIKE 'SCAN_%'";
        
    } else if ($plural == "CONFS") { 
        $sql = "SELECT NAME, IVALUE, TVALUE FROM config WHERE NAME LIKE 'SCAN_%'";
    } else if ($plural == "SUBNETS") {
        $sql = "SELECT TVALUE FROM devices WHERE HARDWARE_ID=".$_GET['id']." AND NAME='SNMP_NETWORK'";
    }

    if (isset($sql) && $sql != "" && !isset($sql_default)) {
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
        $xml .= "<".$plural.">\n";
        while ($row = mysqli_fetch_array($result)) {
            

            // the subnets are stored in a single field separated by a comma so we need to split them into different subnet tags
            if ($plural == "SUBNETS") {
                $subnets = explode(",", $row['TVALUE']);
                foreach ($subnets as $subnet) {
                    $xml .= "<".$singular." ";
                    $xml .= "TVALUE=\"".$subnet."\" ";
                    $xml .= "TYPE=\"".$singular."\" />\n";
                }
            } else {
                $xml .= "<".$singular." ";
                foreach ($row as $key => $value) {
                    if (!is_numeric($key)) {
                        $xml .= $key."=\"".$value."\" ";
                    }
                }
                $xml .= "TYPE=\"".$singular."\" />\n";
            }

            $xml .= "</".$plural.">\n";

            return $xml;
        }
    // handling conf options 
    } else if (isset($sql_default) && $sql_default != '') {
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
        $result_default = mysql2_query_secure($sql_default, $_SESSION['OCS']["readServer"]);

        $result = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $result_default = mysqli_fetch_all($result_default, MYSQLI_ASSOC);

        // compare result array and result_default array to generate a conf file with default and customized values if any
        foreach ($result_default as $default_entry) {
            $found = false;
            foreach ($result as $entry) {
                if ($entry['NAME'] === $default_entry['NAME']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $result[] = $default_entry;
            }
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
        $xml .= "<".$plural.">\n";
        foreach ($result as $row) {
            $xml .= "<".$singular." ";
            foreach ($row as $key => $value) {
                if (!is_numeric($key)) {
                    $xml .= $key."=\"".$value."\" ";
                }
            }
            $xml .= "TYPE=\"".$singular."\" />\n";
        }
        $xml .= "</".$plural.">\n";

        return $xml;
        
    }
}



// 1. localsnmp_types_conf.xml - the types configuration
if (isset($_GET['conf']) && $_GET['conf'] == "type") {
    $xml = SnmpConfToXml(array("TYPES", "TYPE"));
    $xml_filename = "localsnmp_types_conf.xml";
// 2. localsnmp_communities_conf.xml - the communities configuration
} else if (isset($_GET['conf']) && $_GET['conf'] == "comm") {
    $xml = SnmpConfToXml(array("COMMUNITIES", "COMMUNITY"));
    $xml_filename = "localsnmp_communities_conf.xml";
// 3. localsnmp_scans_conf.xml - the scans configuration
} else if (isset($_GET['conf']) && $_GET['conf'] == "scan") {
    $xml = SnmpConfToXml(array("CONFS", "CONF"));
    $xml_filename = "localsnmp_scans_conf.xml";
// 4. localsnmp_subnets_conf.xml - list of subnets to scan
} else if (isset($_GET['conf']) && $_GET['conf'] == "net") {
    $xml = SnmpConfToXml(array("SUBNETS", "SUBNET"));
    $xml_filename = "localsnmp_subnets_conf.xml";
}

// send the xml file
if (isset($xml) && $xml != "") {
    // iexplorer problem
    if (ini_get("zlib.output-compression")) {
        ini_set("zlib.output-compression", "Off");
    }

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-control: private", false);
    header("content-type: text/xml ");
    header("Content-Disposition: attachment; filename=\"$xml_filename\"");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . strlen($xml));
    echo $xml,
    die();
} else {
    require_once (HEADER_HTML);
    msg_error($l->g(920));
    require_once(FOOTER_HTML);
    die();
}
?>