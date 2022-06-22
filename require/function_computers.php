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
 * Hardware locking function. Prevents the hardware to be altered by either the server or another administrator using the GUI
 * @param id Hardware identifier to be locked
 */
function lock($id) {
    $reqClean = "DELETE FROM locks WHERE since<(date_sub(NOW(), interval 3600 second))";
    mysql2_query_secure($reqClean, $_SESSION['OCS']["writeServer"]);

    $reqLock = "INSERT INTO locks(hardware_id) VALUES ('%s')";
    $argLock = $id;
    if (mysql2_query_secure($reqLock, $_SESSION['OCS']["writeServer"], $argLock)) {
        return( mysqli_affected_rows($_SESSION['OCS']["writeServer"]) == 1 );
    } else {
        return false;
    }
}
/**
 * Hardware unlocking function
 * @param id Hardware identifier to be unlocked
 */
function unlock($id) {
    $reqLock = "DELETE FROM locks WHERE hardware_id='%s'";
    $argLock = $id;
    mysql2_query_secure($reqLock, $_SESSION['OCS']["writeServer"], $argLock);
    return( mysqli_affected_rows($_SESSION['OCS']["writeServer"]) == 1 );
}
/**
 * Show an error message if the locking failed
 */
function errlock() {
    global $l;
    msg_error($l->g(376));
}
function computer_list_by_tag($tag = "", $format = 'LIST') {
    $arg_sql = array();
    if ($tag == "") {
        $sql_mycomputers['SQL'] = "select hardware_id from accountinfo a where " . $_SESSION['OCS']["mesmachines"];
    } elseif (is_array($tag)) {
        $sql_mycomputers = "select hardware_id from accountinfo a where a.tag in ";
        $sql_mycomputers = mysql2_prepare($sql_mycomputers, $arg_sql, $tag);
    } else {
        $sql_mycomputers = "select hardware_id from accountinfo a where a.tag in ";
        $sql_mycomputers = mysql2_prepare($sql_mycomputers, $arg_sql, $tag);
    }
    $res_mycomputers = mysql2_query_secure($sql_mycomputers['SQL'], $_SESSION['OCS']["readServer"], $sql_mycomputers['ARG'] ?? []);
    $mycomputers = "(";
    while ($item_mycomputers = mysqli_fetch_object($res_mycomputers)) {
        $mycomputers .= $item_mycomputers->hardware_id . ",";
        $array_mycomputers[] = $item_mycomputers->hardware_id;
    }
    $mycomputers = substr($mycomputers, 0, -1) . ")";
    if ($mycomputers == "()" || !isset($array_mycomputers)) {
        $mycomputers = "ERROR";
    }
    if ($format == 'LIST') {
        return $mycomputers;
    } else {
        return $array_mycomputers;
    }
}
/**
 * Deleting function
 * @param id Hardware identifier to be deleted
 * @param checkLock Tells wether or not the locking system must be used (default true)
 * @param traceDel Tells wether or not the deleted entities must be inserted in deleted_equiv for tracking purpose (default true)
 */
function deleteDid($id, $checkLock = true, $traceDel = true, $silent = false
) {
    global $l;
    if ($_SESSION['OCS']['profile']->getConfigValue('DELETE_COMPUTERS') == "NO") {
        msg_error($l->g(1273));
        return false;
    }
    //If lock is not user OR it is used and available
    if (!$checkLock || lock($id)) {
        $sql = "SELECT deviceid,name,IPADDR,OSNAME FROM hardware WHERE id='%s'";
        $resId = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $id);
        $valId = mysqli_fetch_array($resId);
        $idHard = $id;
        $did = $valId["deviceid"] ?? '';
        if ($did) {
            //Deleting a network device
            if (!strpos($did, "NETWORK_DEVICE-")) {
                $sql = "SELECT macaddr FROM networks WHERE hardware_id='%s'";
                $resNetm = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $idHard);
                while ($valNetm = mysqli_fetch_array($resNetm)) {
                    $sql = "DELETE FROM netmap WHERE mac='%s'";
                    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $valNetm["macaddr"]);
                }
            }
            //deleting a regular computer
            if ($did != "_SYSTEMGROUP_" && $did != '_DOWNLOADGROUP_') {
                $tables = $_SESSION['OCS']['SQL_TABLE_HARDWARE_ID'];
            } elseif ($did == "_SYSTEMGROUP_" || $did == '_DOWNLOADGROUP_') {//Deleting a group
                $tables = Array("devices");
                //del messages on this group
                $sql_group_msg = "DELETE FROM config WHERE name like '%s' and ivalue='%s'";
                mysql2_query_secure($sql_group_msg, $_SESSION['OCS']["writeServer"], array('GUI_REPORT_MSG%', $idHard));
                $sql_group = "DELETE FROM `groups` WHERE hardware_id='%s'";
                mysql2_query_secure($sql_group, $_SESSION['OCS']["writeServer"], $idHard);
                $sql_group_cache = "DELETE FROM groups_cache WHERE group_id='%s'";
                $resDelete = mysql2_query_secure($sql_group_cache, $_SESSION['OCS']["writeServer"], $idHard);
                $affectedComputers = mysqli_affected_rows($_SESSION['OCS']["writeServer"]);
            }

            if (!$silent) {
                msg_success($valId["name"] . " " . $l->g(220));
            }

            if (isset($tables) && is_array($tables)) {
                foreach ($tables as $table) {
                    $sql = "DELETE FROM `%s` WHERE hardware_id='%s'";
                    $arg = array($table, $idHard);
                    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
                }
            }
            $sql = "delete from download_enable where SERVER_ID='%s'";
            mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $idHard);
            $sql = "DELETE FROM hardware WHERE id='%s'";
            mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $idHard);
            //Deleted computers tracking
            if ($traceDel && mysqli_num_rows(mysql2_query_secure("SELECT IVALUE FROM config WHERE IVALUE>0 AND NAME='TRACE_DELETED'", $_SESSION['OCS']["readServer"]))) {
                $sql = "insert into deleted_equiv(DELETED,EQUIVALENT) values('%s',%s)";
                $arg = array($idHard, 'NULL');
                mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
            }
        }
        //Using lock ? Unlock
        if ($checkLock) {
            unlock($id);
        }
        return $valId["name"] ?? '';
    } else {
        errlock();
    }
}
function fusionne($afus) {
    global $l;
    $i = 0;
    $maxStamp = 0;
    $minStamp = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")); //demain
    foreach ($afus as $a) {
        $d = $a["lastcome"];
        $param = array();
        $param[] = (int) $d[11] . $d[12];
        $param[] = (int) $d[14] . $d[15];
        $param[] = (int) $d[17] . $d[18];
        $param[] = (int) $d[5] . $d[6];
        $param[] = (int) $d[8] . $d[9];
        $param[] = (int) $d[0] . $d[1] . $d[2] . $d[3];
        $a["stamp"] = mktime($param[0], $param[1], $param[2], $param[3], $param[4], $param[5]);
        if ($maxStamp < $a["stamp"]) {
            $maxStamp = $a["stamp"];
            $maxInd = $i;
        }
        if ($minStamp > $a["stamp"]) {
            $minStamp = $a["stamp"];
            $minInd = $i;
        }
        $i++;
    }
    if ($afus[$minInd]["deviceid"] != "") {
        $okLock = true;
        foreach ($afus as $a) {
            if (!$okLock = ($okLock && lock($a["id"]))) {
                break;
            } else {
                $locked[] = $a["id"];
            }
        }

        if ($okLock) {
            //TRACE_DELETED
            if (mysqli_num_rows(mysql2_query_secure("SELECT * FROM config WHERE IVALUE>0 AND NAME='TRACE_DELETED'", $_SESSION['OCS']["readServer"]))) {
                foreach ($afus as $a) {
                    if ($afus[$maxInd]["deviceid"] == $a["deviceid"]) {
                        continue;
                    }
                    $sql = "insert into deleted_equiv(DELETED,EQUIVALENT) values('%s','%s')";
                    $arg = array($a["id"], $afus[$maxInd]["id"]);
                    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
                }
            }

            //KEEP OLD QUALITY,FIDELITY AND CHECKSUM
            $sql = "SELECT CHECKSUM,QUALITY,FIDELITY FROM hardware WHERE ID='%s'";
            $persistent_req = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $afus[$minInd]["id"]);

            msg_success($l->g(190) . " " . $afus[$maxInd]["deviceid"] . " " . $l->g(191));
            
            $accountid = null;
            $accountTable = [];

            // Check if accountinfo data exist and get ID of the more recent
            foreach($afus as $values) {
                $sqlverif = "SELECT * FROM accountinfo WHERE hardware_id = '%s' ORDER BY hardware_id ASC";
                $verif_req = mysql2_query_secure($sqlverif, $_SESSION['OCS']["readServer"], $values["id"]);
                while($row = mysqli_fetch_array($verif_req)){
                    if($row != null){
                        $accountTable[$row['HARDWARE_ID']] = $row;
                    }
                }
            }

            foreach($accountTable as $table) {
                foreach($table as $key => $value) {
                    if(strpos($key,"fields_") !== false) {
                        if($value != null && $value != "") {
                            $accountid = $table['HARDWARE_ID'];
                        }
                    }              
                }
            }

            if($afus[$maxInd]["id"] != $accountid) {
                $reqDelAccount = "DELETE FROM accountinfo WHERE hardware_id='%s'";
                mysql2_query_secure($reqDelAccount, $_SESSION['OCS']["writeServer"], $afus[$maxInd]["id"]);
            }

            $keep = array("devices", "groups_cache", "itmgmt_comments", "accountinfo");
            foreach ($keep as $tableToBeKept) {
                if(($tableToBeKept == "accountinfo" || $tableToBeKept == "itmgmt_comments") && $accountid != null){
                    $reqRecupAccount = "UPDATE %s SET hardware_id='%s' WHERE hardware_id='%s'";
                    $argRecupAccount = array($tableToBeKept, $afus[$maxInd]["id"], $accountid);
                } else {
                    $reqRecupAccount = "UPDATE %s SET hardware_id='%s' WHERE hardware_id='%s'";
                    $argRecupAccount = array($tableToBeKept, $afus[$maxInd]["id"], $afus[$minInd]["id"]);
                }
                mysql2_query_secure($reqRecupAccount, $_SESSION['OCS']["writeServer"], $argRecupAccount);
            }

            // Delete all old accountinfo
            foreach($afus as $values) {
                if($values["id"] != $afus[$maxInd]["id"]) {
                    $reqDelAccount = "DELETE FROM accountinfo WHERE hardware_id='%s'";
                    mysql2_query_secure($reqDelAccount, $_SESSION['OCS']["writeServer"], $values["id"]);
                }
            }
            
            msg_success($l->g(190) . " " . $afus[$minInd]["deviceid"] . " " . $l->g(206) . " " . $afus[$maxInd]["deviceid"]);
            $i = 0;
            $lesDel = '';
            foreach ($afus as $a) {
                if ($i != $maxInd) {
                    deleteDid($a["id"], false, false, true);
                    $lesDel .= $a["deviceid"] . "/";
                }
                $i++;
            }

            //RESTORE PERSISTENT VALUES
            $persistent_values = mysqli_fetch_row($persistent_req);
            $sql = "UPDATE hardware SET QUALITY=%s,FIDELITY=%s,CHECKSUM=CHECKSUM|%s WHERE id='%s'";
            $arg = array($persistent_values[1], $persistent_values[2], $persistent_values[0], $afus[$maxInd]["id"]);
            mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
        } else {
            errlock();
        }

        foreach ($locked as $a) {
            unlock($a);
        }
    }
    $lesDel .= " => " . $afus[$maxInd]["deviceid"];
    AddLog("FUSION", $lesDel);
}
function insert_manual_computer($values, $nb = 1) {
    global $i;
    if ($nb == 1) {
        $name = $values['COMPUTER_NAME_GENERIC'];
        $macaddr = $values['ADDR_MAC_GENERIC'];
        $serial = $values ['SERIAL_GENERIC'];
    } else {
        $name = $values['COMPUTER_NAME_GENERIC'] . $i;
        $macaddr = $values['ADDR_MAC_GENERIC']. $i;
        $serial = $values ['SERIAL_GENERIC']. $i;
    }

    $sql = "insert into hardware (deviceid,name) values ('%s','%s')";
    $arg = array('MANUEL', $name . '_M');
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
    $id_computer = mysqli_insert_id($_SESSION['OCS']["writeServer"]);

    $sql = "insert into bios (hardware_id,ssn) values ('%s','%s')";
    $arg = array($id_computer, $serial . '_M');
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);

    $sql = "insert into networks (hardware_id,macaddr) values ('%s','%s')";
    $arg = array($id_computer, $macaddr. '_M');
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);

    return $id_computer;
}
/*
 * function to verify if user can access
 * on computer
 */
function is_mine_computer($id) {
    if (isset($_SESSION['OCS']['TAGS']) && is_array($_SESSION['OCS']['TAGS'])) {
        $sql = "select hardware_id from accountinfo where hardware_id = %s and tag in ";
        $arg = array($id);
        $sql = mysql2_prepare($sql, $arg, $_SESSION['OCS']['TAGS']);
        $result = mysql2_query_secure($sql['SQL'], $_SESSION['OCS']["readServer"], $sql['ARG']);
        $item = mysqli_fetch_object($result);
        if (!isset($item->hardware_id)) {
            return false;
        }
    }
    return true;
}
function RandomMAC() {
    $word = "A,B,C,D,E,F,0,1,2,3,4,5,6,7,8,9";
    $mac = '';
    $array = explode(",", $word);
    $j = 0;
    while ($j < 8) {
        $i = 0;
        while ($i < 2) {
            shuffle($array);
            $mac .= $array[0];
            $i++;
        }
        $mac .= ":";
        $j++;
    }

    return substr($mac, 0, -1);
}
