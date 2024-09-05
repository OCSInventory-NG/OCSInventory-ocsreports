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
require_once('require/function_computers.php');
$systemId = preg_replace('/[^0-9]/', '', $protectedGet['systemid']); 
$seeit = is_mine_computer($systemId);
if (!$seeit) {
    require_once (HEADER_HTML);
    msg_error($l->g(837));
    require_once(FOOTER_HTML);
    die();
}
$sql = "select * from hardware where id=%s";
$arg = $systemId;
$res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
$item_hardware = mysqli_fetch_object($res);
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
$table_not_use = array('accountinfo', 'groups_cache', 'download_history', 'devices');
$xml .= "<REQUEST>\n";
$xml .= "\t<DEVICEID>" . $item_hardware->DEVICEID . "</DEVICEID>\n";
$xml .= "\t<CONTENT>\n";
foreach ($_SESSION['OCS']['SQL_TABLE_HARDWARE_ID'] as $tablename) {
    if (!in_array($tablename, $table_not_use)) {
        $sql = "select * from %s where hardware_id=%s";
        $arg = array($tablename, $systemId);

        $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
        if($res) {
            while ($item = mysqli_fetch_object($res)) {
                $xml .= "\t\t<" . mb_strtoupper($tablename) . ">\n";
                foreach ($_SESSION['OCS']['SQL_TABLE'][$tablename] as $field_name => $field_type) {
                    if ($field_name != 'HARDWARE_ID') {
                        if (replace_entity_xml($item->$field_name) != '') {
                            $xml .= "\t\t\t<" . $field_name . ">";
                            $xml .= replace_entity_xml($item->$field_name);
                            $xml .= "</" . $field_name . ">\n";
                        } else {
                            $xml .= "\t\t\t<" . $field_name . " />\n";
                        }
                    }
                }
                $xml .= "\t\t</" . mb_strtoupper($tablename) . ">\n";
            }
        }
    }
}
//HARDWARE INFO
$xml .= "\t\t<HARDWARE>\n";
foreach ($_SESSION['OCS']['SQL_TABLE']['hardware'] as $field_name => $field_type) {
    if ($field_name != 'ID' && $field_name != 'DEVICEID') {
        if (replace_entity_xml($item_hardware->$field_name) != '') {
            $xml .= "\t\t\t<" . $field_name . ">";
            $xml .= replace_entity_xml($item_hardware->$field_name);
            $xml .= "</" . $field_name . ">\n";
        } else {
            $xml .= "\t\t\t<" . $field_name . " />\n";
        }
    }
}
$xml .= "\t\t</HARDWARE>\n";

//ACCOUNTINFO VALUES
$sql = "select * from accountinfo where hardware_id=%s";
$arg = $systemId;
$res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
$item_accountinfo = mysqli_fetch_object($res);

foreach ($_SESSION['OCS']['SQL_TABLE']['accountinfo'] as $field_name => $field_type) {
    if ($field_name != 'HARDWARE_ID') {
        $xml .= "\t\t<ACCOUNTINFO>\n";
        $xml .= "\t\t\t<KEYNAME>" . $field_name . "</KEYNAME>\n";
        if (replace_entity_xml($item_accountinfo->$field_name) != '') {
            $xml .= "\t\t\t<KEYVALUE>" . replace_entity_xml($item_accountinfo->$field_name) . "</KEYVALUE>\n";
        } else {
            $xml .= "\t\t\t<KEYVALUE />\n";
        }
        $xml .= "\t\t</ACCOUNTINFO>\n";
    }
}

$xml .= "\t</CONTENT>\n";
$xml .= "\t<QUERY>INVENTORY</QUERY>\n";
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
    header("Content-Disposition: attachment; filename=\"" . $item_hardware->DEVICEID . ".xml\"");
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