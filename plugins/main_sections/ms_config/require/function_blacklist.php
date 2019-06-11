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
$javascript_mac = "onKeyPress='return scanTouche(event,/[0-9 a-f A-F]/)'
		  onkeydown='convertToUpper(this)'
		  onkeyup='convertToUpper(this)'
		  onblur='convertToUpper(this)'
		  onclick='convertToUpper(this)'";

$MACnb_field = 6;
$MACnb_value_by_field = 2;
$MACsize = 3;
$MACfield_name = 'ADD_MAC_';
$MACseparat = ":";
$MACtable = "blacklist_macaddresses";
$MACfield = "MACADDRESS";

$SERIALnb_field = 1;
$SERIALnb_value_by_field = 100;
$SERIALsize = 30;
$SERIALfield_name = 'ADD_SERIAL_';
$SERIALseparat = "";
$SERIALtable = "blacklist_serials";
$SERIALfield = "SERIAL";

$SUBnb_field = 4;
$SUBnb_value_by_field = 3;
$SUBsize = 3;
$SUBfield_name = 'ADD_SUBNET_';
$SUBseparat = ".";
$SUBtable = "blacklist_subnet";
$SUBfield = "SUBNET";

$MASKnb_field = 4;
$MASKnb_value_by_field = 3;
$MASKsize = 3;
$MASKfield_name = 'ADD_MASK_';
$MASKseparat = ".";
$MASKtable = "blacklist_subnet";
$MASKfield = "MASK";

/* Function to add a MAC address to the blacklist */
function add_mac_add($mac_value) {
    global $l, $MACnb_field, $MACfield_name, $MACseparat, $MACtable, $MACfield, $MACnb_field;

    $field_value = generate_value($mac_value, $MACfield_name, $MACseparat, $MACnb_field);
    if (!$field_value) {
        return $l->g(1144);
    }
    insert_blacklist_table($MACtable, $MACfield, $field_value);
}

/* Function to add a serial number to the blacklist */
function add_serial_add($serial_value) {
    global $SERIALnb_field, $SERIALfield_name, $SERIALseparat, $SERIALtable, $SERIALfield, $SERIALnb_field;

    $field_value = generate_value($serial_value, $SERIALfield_name, $SERIALseparat, $SERIALnb_field);
    if (!isset($field_value)) {
        $field_value = '';
    }

    insert_blacklist_table($SERIALtable, $SERIALfield, $field_value);
}

/* Function to add a subnet to the blacklist */
function add_subnet_add($subnet_value) {
    global $l, $SUBnb_field, $SUBfield_name, $SUBseparat, $SUBtable, $SUBfield, $SUBnb_field,
    $MASKnb_field, $MASKfield_name, $MASKseparat, $MASKfield, $MASKnb_field;

    $field_value_SUB = generate_value($subnet_value, $SUBfield_name, $SUBseparat, $SUBnb_field, array('DOWN' => 0, 'UP' => 255));
    if (!$field_value_SUB) {
        return $l->g(299);
    }
    if (is_array($field_value_SUB)) {
        return $l->g(1145) . ' ' . implode(',', $field_value_SUB);
    }

    $field_value_MASK = generate_value($subnet_value, $MASKfield_name, $MASKseparat, $MASKnb_field, array('DOWN' => 0, 'UP' => 255));
    if (!$field_value_MASK) {
        return $l->g(300);
    }
    if (is_array($field_value_MASK)) {
        return $l->g(1145) . ' ' . implode(',', $field_value_MASK);
    }
    insert_blacklist_table($SUBtable, array($SUBfield, $MASKfield), array($field_value_SUB, $field_value_MASK));
}

function show_blacklist_fields($nb_field, $default_values, $field_name, $nb_value_by_field, $size, $separat, $javascript = '') {
    global $aff;
    $i = 1;
    while ($i <= $nb_field) {
        if ($i != 1) {
            $aff .= $separat;
        }
        $aff .= show_modif($default_values[$field_name . $i], $field_name . $i, 0, '', array('MAXLENGTH' => $nb_value_by_field, 'SIZE' => $size, 'JAVASCRIPT' => $javascript));
        $i++;
    }
    $aff .= "</td></tr><tr><td>";
    return $aff;
}

/* 
 * Returns a single string (or 'false' in case of error).
 * The string returned is the concatenation of several fields' value 
*/
function generate_value($values, $field_name, $separat, $nb_field, $limit = array()) {
    $field_value = '';
    $i = 1;
    while ($i <= $nb_field) {
        if ($i != 1) {
            $field_value .= $separat;
        }
        if ($values[$field_name . $i] != '') {
            if ((isset($limit['DOWN']) && $values[$field_name . $i] < $limit['DOWN']) || (isset($limit['UP']) && $values[$field_name . $i] > $limit['UP'])) {
                return false;
            }
            $field_value .= $values[$field_name . $i];
        } else {
            return false;
        }
        $i++;
    }
    return $field_value;
}

function insert_blacklist_table($table, $field, $field_value) {
    global $l;
    $sql = "insert into %s ";
    $arg = array($table);
    $sql = mysql2_prepare($sql, $arg, $field, true);
    $sql['SQL'] .= " value ";
    $sql = mysql2_prepare($sql['SQL'], $sql['ARG'], $field_value);
    //no error
    mysql2_query_secure($sql['SQL'], $_SESSION['OCS']["writeServer"], $sql['ARG']);
    msg_success($l->g(655));
}

?>
