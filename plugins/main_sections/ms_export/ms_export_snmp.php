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
require_once('require/snmp/Snmp.php');

$snmp = New OCSSnmp();

// Initialize empty value
$getType    = null;
$getId      = null;

// Clean protectedGet
if(isset($protectedGet['type']))    $getType = preg_replace("/[^A-Za-z0-9\._]/", "", $protectedGet['type']);
if(isset($protectedGet['id']))      $getId = preg_replace("/[^0-9]/", "", $protectedGet['id']);

// Retrieve all equipment informations
$equipmentDetails   = $snmp->getDetails($getType, $getId);

$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
$xml .= "<REQUEST>\n";
$xml .= "\t<DEVICEID>".$getType."_".$equipmentDetails['ID']."</DEVICEID>\n";
$xml .= "\t<CONTENT>\n";

$xml .= "\t\t<DETAILS>\n";
foreach ($equipmentDetails as $field_name => $field_value) {
    if ($field_name != 'ID') {
        if (replace_entity_xml($field_value) != '') {
            $xml .= "\t\t\t<".$field_name.">";
            $xml .= replace_entity_xml($field_value);
            $xml .= "</".$field_name.">\n";
        } else {
            $xml .= "\t\t\t<".$field_name." />\n";
        }
    }
}
$xml .= "\t\t</DETAILS>\n";

$xml .= "\t</CONTENT>\n";
$xml .= "\t<QUERY>SNMP</QUERY>\n";
$xml .= "</REQUEST>\n";

if ($xml != "") {
    // iexplorer problem
    if (ini_get("zlib.output-compression")) {
        ini_set("zlib.output-compression", "Off");
    }

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-control: private", false);
    header("content-type: text/xml ");
    header("Content-Disposition: attachment; filename=\"".$getType."_".$equipmentDetails['ID'].".xml\"");
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