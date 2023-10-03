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

function find_info_subnet($netid) {
    $sql = "select NETID,NAME,ID,MASK from subnet where netid='%s'";
    $arg = $netid;
    $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
    return mysqli_fetch_object($res);
}

function find_info_type($name = '', $id = '', $update = '') {
    if ($name != '') {
        $sql = "select ID,NAME from devicetype where NAME = '%s'";
        $arg = array($name);
    } elseif ($id != '') {
        $sql = "select ID,NAME from devicetype where ID = '%s'";
        $arg = array($id);
    }
    if ($update != '') {
        $sql .= " AND ID != '%s'";
        $arg[] = $update;
    }
    $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
    return mysqli_fetch_object($res);
}

function form_add_subnet($title = '', $default_value, $form) {
    global $l, $pages_refs;

    $name_field = array("RSX_NAME", "ID_NAME", "ADD_IP", "ADD_SX_RSX");
    if (isset($_SESSION['OCS']["ipdiscover_id"])) {
        $lbl_id = $_SESSION['OCS']["ipdiscover_id"];
    } else {
        $lbl_id = $l->g(305) . ":";
    }

    $tab_name = array($l->g(304) . ": ", $lbl_id . ":", $l->g(34) . ": ", $l->g(208) . ": ");
    if ($title == $l->g(931)) {
        $type_field = array(0, 2, 13, 0);
    } else {
        $type_field = array(0, 2, 0, 0);
    }

    $value_field = array($default_value['RSX_NAME'], $default_value['ID_NAME'], $default_value['ADD_IP'], $default_value['ADD_SX_RSX']);

    $tab_typ_champ = show_field($name_field, $type_field, $value_field);
    foreach ($tab_typ_champ as $id => $values) {
        $tab_typ_champ[$id]['CONFIG']['SIZE'] = 30;
    }
    $tab_typ_champ[1]['COMMENT_AFTER'] = "<a href=\"index.php?" . PAG_INDEX . "=" . $pages_refs['ms_adminvalues'] . "&head=1&tag=ID_IPDISCOVER&form=" . $form . "\"><img src=image/plus.png></a>";

    $tab_typ_champ[1]["CONFIG"]['DEFAULT'] = "NO";

    modif_values($tab_name, $tab_typ_champ, array(), array(
        'title' => $title,
        'show_frame' => false
    ));
}

function verif_base_methode($base) {
    global $l;
    if (isset($_SESSION['OCS']['ipdiscover_methode'])
            and $_SESSION['OCS']['ipdiscover_methode'] != $base) {
        return $l->g(929) . "<br>" . $l->g(930);
    } else {
        return false;
    }
}

function add_subnet($add_ip, $sub_name, $id_name, $add_sub) {
    global $l;

    if (trim($add_ip) == '') {
        return $l->g(932);
    }
    if (trim($sub_name) == '') {
        return $l->g(933);
    }
    if (trim($id_name) == '' || $id_name == '0') {
        return $l->g(934);
    }
    if (trim($add_sub) == '') {
        return $l->g(935);
    }
    $row_verif = find_info_subnet($add_ip);
    if (isset($row_verif->NETID)) {
        $sql = "update subnet set name='%s', id='%s', MASK='%s'
				where netid = '%s'";
        $arg = array($sub_name, $id_name, $add_sub, $add_ip);
    } else {
        $sql = "insert into subnet (netid,name,id,mask) VALUES ('%s','%s',
					'%s','%s')";
        $arg = array($add_ip, $sub_name, $id_name, $add_sub);
    }
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
    //@TODO : always false ?
    return false;
}

function add_type($name, $update = '') {
    global $l;

    if (trim($name) == '') {
        return $l->g(936);
    } else {
        $row = find_info_type($name, '', $update);
        if (isset($row->ID)) {
            return $l->g(937);
        }
    }
    if ($update != '') {
        $sql = "update devicetype set NAME = '%s' where ID = '%s' ";
        $arg = array($name, $update);
    } else {
        $sql = "insert into devicetype (NAME) VALUES ('%s')";
        $arg = $name;
    }
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
    //@TODO : always false ?
    return false;
}

function delete_type($id_type) {
    $sql = "delete from devicetype where id='%s'";
    $arg = $id_type;
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
}

function delete_subnet($netid) {
    $sql = "delete from subnet where netid='%s'";
    $arg = $netid;
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
}

/**
 * Loads the whole mac file in memory
 */
function loadMac() {
    if (is_readable(MAC_FILE)) {
        $file = fopen(MAC_FILE, "r");
        while (!feof($file)) {
            $line = fgets($file, 4096);
            if (preg_match("/((?:[a-fA-F0-9]{2}-){2}[a-fA-F0-9]{2})\s+\([^)]*\)\s+(.+)/", $line, $result)) {
                $_SESSION['OCS']["mac"][mb_strtoupper(str_replace("-", ":", $result[1]))] = $result[2];
            }
        }
        fclose($file);
    }
}

function form_add_community($title = '', $default_value, $form) {
    global $l, $protectedPost;

    $name_field = array("NAME", "VERSION");
    $tab_name = array($l->g(49) . ": ", $l->g(1199) . ": ");
    $type_field = array(0, 2);
    $value_field = array($default_value['NAME'], $default_value['VERSION']);

    if ($protectedPost['VERSION'] == '3') {
        array_push($name_field, "USERNAME", "AUTHKEY", "AUTHPASSWD");
        array_push($tab_name, "USERNAME : ", "AUTHKEY : ", "AUTHPASSWD :");
        array_push($type_field, 0, 0);
        array_push($value_field, $default_value['USERNAME'], $default_value['AUTHKEY'], $default_value['AUTHPASSWD']);
    }

    $tab_typ_champ = show_field($name_field, $type_field, $value_field);
    foreach ($tab_typ_champ as $id => $values) {
        $tab_typ_champ[$id]['CONFIG']['SIZE'] = 30;
    }

    $tab_typ_champ[1]['RELOAD'] = $form;
    if (is_numeric($protectedPost['MODIF'])) {
        $tab_hidden['MODIF'] = $protectedPost['MODIF'];
    }
    $tab_hidden['ADD_COMM'] = $protectedPost['ADD_COMM'];
    $tab_hidden['ID'] = $protectedPost['ID'];
    modif_values($tab_name, $tab_typ_champ, $tab_hidden, array(
        'title' => $title,
        'show_frame' => false
    ));
}

function add_community($ID, $NAME, $VERSION, $USERNAME, $AUTHKEY, $AUTHPASSWD) {
    global $l;

    if ($VERSION == -1) {
        $VERSION = '2c';
    }
    //this name of community still exist?
    $sql = "select name from snmp_communities where name='%s' and version='%s' ";
    $arg = array($NAME, $VERSION);

    if (isset($ID) && is_numeric($ID)) {
        $sql .= " and id != %s";
        array_push($arg, $ID);
    }
    $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
    $row = mysqli_fetch_object($res);
    //Exist
    if (isset($row->name)) {
        return array('ERROR' => $NAME . " " . $l->g(363));
    }

    if (isset($ID) && is_numeric($ID)) {
        del_community($ID);
        $SUCCESS = $l->g(1209);
    } else {
        $SUCCESS = $l->g(1208);
    }

    $sql = "insert into snmp_communities (ID,VERSION,NAME,USERNAME,AUTHKEY,AUTHPASSWD) VALUES ('%s','%s','%s','%s','%s','%s')";
    $arg = array($ID, $VERSION, $NAME, $USERNAME, $AUTHKEY, $AUTHPASSWD);
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
    return array('SUCCESS' => $SUCCESS);
}

function del_community($id_community) {
    $sql = "delete from snmp_communities where id='%s'";
    $arg = $id_community;
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
}

function find_community_info($id) {
    $sql = "select * from snmp_communities where id=%s";
    $arg = $id;
    $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
    return mysqli_fetch_object($res);
}

function runCommand($command = "", $fname) {
    $command = "perl ipdiscover-util.pl $command -xml -h=" . SERVER_READ . " -P=" . SERVER_PORT . " -u=" . COMPTE_BASE . " -p=" . PSWD_BASE . " -d=" . DB_NAME . " -path=" . $fname;
    exec($command);
}

function find_all_subnet($dpt_choise = '') {
    if ($dpt_choise != '') {
        return array_keys($_SESSION['OCS']["ipdiscover"][$dpt_choise]);
    }
    if (isset($_SESSION['OCS']["ipdiscover"])) {
        foreach ($_SESSION['OCS']["ipdiscover"] as $subnet) {
            foreach ($subnet as $sub => $poub) {
                $array_sub[] = $sub;
            }
        }
        return $array_sub;
    }
    return false;
}

function count_noinv_network_devices($dpt_choise = '') {
    $array_sub = find_all_subnet($dpt_choise);
    $arg_count = array();
    $sql_count = "SELECT COUNT(DISTINCT mac) as c
					FROM netmap n
					LEFT OUTER JOIN networks ns ON ns.macaddr = mac
					WHERE mac NOT IN (SELECT DISTINCT(macaddr) FROM network_devices)
						and ( ns.macaddr IS NULL OR ns.IPSUBNET <> n.netid)
						and netid in ";
    $detail_query = mysql2_prepare($sql_count, $arg_count, $array_sub);
    if (!isset($_SESSION['OCS']['COUNT_CONSOLE']['OCS_REPORT_NB_IPDISCOVER'])
            and $dpt_choise == '') {
        $res_count = mysql2_query_secure($detail_query['SQL'], $_SESSION['OCS']["readServer"], $detail_query['ARG']);
        $val_count = mysqli_fetch_array($res_count);
        return $val_count['c'];
    } else {
        return $_SESSION['OCS']['COUNT_CONSOLE']['OCS_REPORT_NB_IPDISCOVER'];
    }
}

// Check if mac address already exist in the inventoried devices
function check_if_inv_mac_already_exist($mac_address){
    
    $arg_count = array($mac_address);
    $sql_query = "SELECT * FROM `network_devices` WHERE MACADDR = '%s' ";
    
    $res_count = mysql2_query_secure($sql_query, $_SESSION['OCS']["readServer"], $arg_count);
    
    if (mysqli_num_rows($res_count) > 0){
        return true;
    }else{
        return false;
    }
    
}

?>
