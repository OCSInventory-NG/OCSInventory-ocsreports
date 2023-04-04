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
##############################################################################


function SnmpTypesToXml() {
    // get the types configuration from db
    $sql = "SELECT t.TYPE_NAME, tc.CONDITION_OID, tc.CONDITION_VALUE, t.TABLE_TYPE_NAME, l.LABEL_NAME, c.OID FROM snmp_types t LEFT JOIN snmp_configs c ON t.ID = c.TYPE_ID LEFT JOIN snmp_labels l ON l.ID = c.LABEL_ID LEFT JOIN snmp_types_conditions tc ON tc.TYPE_ID = t.ID";
    $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);

    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    $xml .= "<TYPES>\n";
    while($row = mysqli_fetch_array($result)) {
        $xml .= "<TYPE ";
        foreach ($row as $key => $value) {
            if (!is_numeric($key)) {
                $xml .= $key."=\"".$value."\" ";
            }
        }
        $xml .= "TYPE=\"SNMP_TYPE\" />\n";
    }
    $xml .= "</TYPES>\n";

    return $xml;
}

function SnmpCommunitiesToXml() {
    // get the communities configuration from db
    $sql = "SELECT VERSION,NAME,USERNAME,AUTHPASSWD,LEVEL,AUTHPROTO,PRIVPASSWD,PRIVPROTO FROM snmp_communities";
    $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);

    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
    $xml .= "<COMMUNITIES>\n";
    while ($row = mysqli_fetch_array($result)) {
        $xml .= "<COMMUNITY ";
        foreach ($row as $key => $value) {
            if (!is_numeric($key)) {
                $xml .= $key."=\"".$value."\" ";
            }
        }
        $xml .= "TYPE=\"COMMUNITY\" />\n";
    }
    $xml .= "</COMMUNITIES>\n";

    return $xml;
}

// 1. localsnmp_types_conf.xml - the types configuration
if (isset($_GET['conf']) && $_GET['conf'] == "type") {
    $xml = SnmpTypesToXml();
    $xml_filename = "localsnmp_types_conf.xml";

// 2. localsnmp_communities_conf.xml - the communities configuration
} else if (isset($_GET['conf']) && $_GET['conf'] == "comm") {
    $xml = SnmpCommunitiesToXml();
    $xml_filename = "localsnmp_communities_conf.xml";
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