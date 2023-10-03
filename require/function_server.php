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

function desactive_mach_serv($list_id, $packid) {
    global $l;
    $id_pack = found_id_pack_serv($packid);
    $sql_desactive = "delete from devices where hardware_id in ";
    $arg_desactive = mysql2_prepare($sql_desactive, array(), $list_id);
    $arg_desactive = mysql2_prepare($arg_desactive['SQL'] . " and name='DOWNLOAD' and IVALUE in ", $arg_desactive['ARG'], $id_pack);
    mysql2_query_secure($arg_desactive['SQL'], $_SESSION['OCS']["writeServer"], $arg_desactive['ARG']);
    addLog($l->g(512), $l->g(876) . $packid . " => " . $list_id);
}

function found_id_pack_serv($packid) {
    $sql_id_pack = "select ID from download_enable where fileid=%s and ( group_id != '' and group_id is not null)";
    $arg = $packid;
    $result = mysql2_query_secure($sql_id_pack, $_SESSION['OCS']["readServer"], $arg);
    while ($id_pack = mysqli_fetch_array($result)) {
        $id_paquets[] = $id_pack['ID'];
    }
    return $id_paquets;
}

//fonction qui permet de savoir
//le nombre de serveur déjà dans un groupe
//et dans lesquels ils se trouvent
function exist_server($list_id) {
    global $l;

    $sql = "SELECT count(hardware_id) c,group_id,name
		  FROM download_servers,hardware
			where hardware.id=download_servers.group_id
			and hardware_id in ";
    $arg = mysql2_prepare($sql, array(), $list_id);
    $res = mysql2_query_secure($arg['SQL'] . " group by group_id ", $_SESSION['OCS']["readServer"], $arg['ARG']);
    $msg = "";
    while ($val = mysqli_fetch_array($res)) {
        $msg .= $val['c'] . " " . $l->g(1135) . " " . $val['name'] . "<br>";
    }
    if ($msg != "") {
        msg_error($l->g(877) . " <br>" . $msg . " " . $l->g(878));
    }
}

//function for add machine in server's group
function add_mach($id_group, $list_mach) {
    $default_values = look_config_default_values(array('DOWNLOAD_SERVER_URI', 'DOWNLOAD_SERVER_DOCROOT'));
    if (is_array($list_mach)) {
        foreach ($list_mach as $value) {
            $reqCache = "INSERT IGNORE INTO download_servers(hardware_id, url, add_rep,GROUP_ID)
						VALUES (%s,'%s','%s',%s)";
            $argcache = array($value, $default_values['tvalue']['DOWNLOAD_SERVER_URI'], $default_values['tvalue']['DOWNLOAD_SERVER_DOCROOT'], $id_group);
            $cachedRes = mysql2_query_secure($reqCache, $_SESSION['OCS']["writeServer"], $argcache);
        }
    } else {
        $reqCache = "INSERT IGNORE INTO download_servers(hardware_id, url, add_rep,GROUP_ID)
						select id,'%s','%s',%s
					    from hardware where id in ";
        $argCache = array($default_values['tvalue']['DOWNLOAD_SERVER_URI'], $default_values['tvalue']['DOWNLOAD_SERVER_DOCROOT'], $id_group);
        $arg = mysql2_prepare($reqCache, $argCache, $list_mach);
        $cachedRes = mysql2_query_secure($arg['SQL'], $_SESSION['OCS']["writeServer"], $arg['ARG']);
    }
    return mysqli_affected_rows($_SESSION['OCS']["writeServer"]);
}

//function for admin server
function admin_serveur($action, $name_server, $descr, $mach) {
    global $l;
    if ($action == "") {
        //intern problem
        return $l->g(663);
    }
    if (trim($name_server) == "") {
        //name of server is empty
        return $l->g(638);
    }
    if ($mach == "") {
        //no mach selected. group not creat
        return $l->g(665);
    }
    if (is_numeric($name_server)) {
        $idGroupServer = $name_server;
    } else {
        //verification group not have the same name
        $reqGetId = "SELECT id FROM hardware WHERE name='%s'";
        $arg = $name_server;
        $resGetId = mysql2_query_secure($reqGetId, $_SESSION['OCS']["readServer"], $arg);
        if ($valGetId = mysqli_fetch_array($resGetId)) {
            $idGroupServer = $valGetId['id'];
        }
    }
    //if we are in creat new server
    if ($action == 'new_serv') {
        //if the name not exist in the base
        if (!isset($idGroupServer)) {
            $deviceid = '_DOWNLOADGROUP_';
            $sql = "INSERT INTO hardware(deviceid,name,description,lastdate) VALUES( '%s' , '%s', '%s', NOW() )";
            $arg = array($deviceid, $name_server, $descr);
            mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
            //Getting hardware id
            $insertId = mysqli_insert_id($_SESSION['OCS']["writeServer"]);
            exist_server($mach);
            $nb_mach = add_mach($insertId, $mach);
            msg_success($l->g(880) . "<br>" . $nb_mach . " " . $l->g(881));
            return '';
        } else {
            //this name already exists
            return $l->g(621);
        }
    }//if the machines add to the group or the group is replace
    elseif ($action == 'add_serv' || $action == 'replace_serv') {
        if ($action == 'replace_serv') {
            $sql = "DELETE FROM download_servers WHERE GROUP_ID=%s";
            $arg = $idGroupServer;
            mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
        }
        exist_server($mach);
        $nb_mach = add_mach($idGroupServer, $mach);
        msg_success($l->g(879) . "<br>" . $nb_mach . " " . $l->g(881));
        return '';
    } elseif ($action == 'del_serv') {
        $nb_mach = remove_list_serv($idGroupServer, $mach);
        msg_success($nb_mach . " " . $l->g(882));
        return '';
    }
}

//function for insert machine with rules
function insert_with_rules($list_id, $rule_detail, $fileid) {

    if (is_array($list_id)) {
        $list_id_hardware = implode(',', $list_id);
    } else {
        $list_id_hardware = $list_id;
        // only one id selected ? is that a group ?
        $sql_is_group = "select HARDWARE_ID from %s where HARDWARE_ID = %s";
        $arg_is_group = array("groups", $list_id_hardware);
        $res_is_group = mysql2_query_secure($sql_is_group, $_SESSION['OCS']["readServer"], $arg_is_group);
        if(mysqli_num_rows($res_is_group) == 1){
          // then it's a group so get all machines in group
          $sql_group_mach = "select HARDWARE_ID, GROUP_ID from %s where GROUP_ID = %s";
          $arg_group_mach = array("groups_cache",$list_id_hardware);
          $res_group_mach = mysql2_query_secure($sql_group_mach, $_SESSION['OCS']["readServer"], $arg_group_mach);
          $list_id_array = array();
          while ($val_group_mach = mysqli_fetch_array($res_group_mach)){
            $list_id_array[] = $val_group_mach['HARDWARE_ID'];
          }
          $list_id_hardware = implode(',', $list_id_array);
        }
    }

    if ($list_id_hardware == "") {
        return;
    }
    //for servers
    //recherche de tous les hardware_id des servers et des id de download_enable correspondant
    $sql_infoServ = "select server_id,id from download_enable where group_id != ''
								and fileid=%s";
    $arg_infoServ = $fileid;
    $res_infoServ = mysql2_query_secure($sql_infoServ, $_SESSION['OCS']["readServer"], $arg_infoServ);
    //création de la liste des id_hardware des servers et d'un tableau de l'id de download_enable en fonction de l'hardware_id
    while ($val_infoServ = mysqli_fetch_array($res_infoServ)) {
        $list_serverId[$val_infoServ['server_id']] = $val_infoServ['server_id'];
    }

    if ($rule_detail['compto'] == "NAME" || $rule_detail['compto'] == "WORKGROUP" || $rule_detail['compto'] == "USERID") {
        $tablecompto = "hardware";
        $id_server = "ID";
    }

    if ($rule_detail['compto'] == "IPSUBNET" || $rule_detail['compto'] == "IPADDRESS") {
        $tablecompto = "networks";
        $id_server = "HARDWARE_ID";
    }


    $sql_servValues = "select a.%s,a.%s,d.id as id_download_enable from %s a,download_enable d
						 where a.%s in ";

    $arg_servValues = array($rule_detail['compto'], $id_server, $tablecompto, $id_server);
    $arg = mysql2_prepare($sql_servValues, $arg_servValues, $list_serverId);
    $arg['SQL'] .= " and d.server_id=a.%s  and fileid='%s'";
    array_push($arg['ARG'], $id_server);
    array_push($arg['ARG'], $fileid);
    $res_servValues = mysql2_query_secure($arg['SQL'], $_SESSION['OCS']["readServer"], $arg['ARG']);

    while ($val_servValues = mysqli_fetch_array($res_servValues)) {
        $tab_serValues[$val_servValues[$rule_detail['compto']]] = $val_servValues[$id_server];
        $correspond_servers[$val_servValues[$id_server]] = $val_servValues['id_download_enable'];
    }

    //for machines
    if ($rule_detail['cfield'] == "NAME" || $rule_detail['cfield'] == "WORKGROUP" || $rule_detail['cfield'] == "USERID") {
        $tablefield = "hardware";
        $id_mach = "ID";
    }
    if ($rule_detail['cfield'] == "IPSUBNET" || $rule_detail['cfield'] == "IPADDRESS") {
        $tablefield = "networks";
        $id_mach = "HARDWARE_ID";
    }

    $sql_machValue = "select %s,%s from %s where %s in ";
    $arg_machValue = array($rule_detail['cfield'], $id_mach, $tablefield, $id_mach);
    $arg = mysql2_prepare($sql_machValue, $arg_machValue, $list_id_hardware);
    $res_machValue = mysql2_query_secure($arg['SQL'], $_SESSION['OCS']["readServer"], $arg['ARG']);

    while ($val_machValue = mysqli_fetch_array($res_machValue)) {
        if ($rule_detail['op'] == "EGAL") {
            //cas of egal
            if (isset($tab_serValues[$val_machValue[$rule_detail['cfield']]])) {
                $tab_final[$val_machValue[$id_mach]] = $correspond_servers[$tab_serValues[$val_machValue[$rule_detail['cfield']]]];
                $verif_idMach[$val_machValue[$id_mach]] = $val_machValue[$id_mach];
            } else {
                $not_match[$val_machValue[$id_mach]] = $val_machValue[$id_mach];
            }
        } elseif ($rule_detail['op'] == "DIFF") {
            if (!isset($tab_serValues[$val_machValue[$rule_detail['cfield']]])) {
                $tab_final[$val_machValue[$id_mach]] = $correspond_servers[$tab_serValues[$val_machValue[$rule_detail['cfield']]]];
                $verif_idMach[$val_machValue[$id_mach]] = $val_machValue[$id_mach];
            } else {
                $not_match[$val_machValue[$id_mach]] = $val_machValue[$id_mach];
            }
        }
        // ipsubnet or ipaddress rule match for one machine network interface, drop not_match result from other interface of this machine
        if (isset($verif_idMach[$val_machValue[$id_mach]]) && isset($not_match[$val_machValue[$id_mach]])) {
            unset($not_match[$val_machValue[$id_mach]]);
        }
    }
    if (isset($verif_idMach)) {
        $sql_verif = "select d.hardware_id as hardware_id
			  from devices d,download_enable d_e
			  where d.ivalue=d_e.id and fileid=%s
				AND d.HARDWARE_ID in ";
        $arg_verif = array($fileid);
        $arg = mysql2_prepare($sql_verif, $arg_verif, $verif_idMach);
        $arg['SQL'] .= " and d.name='DOWNLOAD'";
        $res_verif = mysql2_query_secure($arg['SQL'], $_SESSION['OCS']["readServer"], $arg['ARG']);
        //recupération des machines en doublon
        while ($val_verif = mysqli_fetch_array($res_verif)) {
            //création du tableau de doublon
            $exist[$val_verif['hardware_id']] = $val_verif['hardware_id'];

            //suppression des doublons
            //unset($tab_final[$val_verif['hardware_id']]);
            //$nb_exist++;
        }
        //suppression des doublons pour remettre le statut a attente de notification
        if ($exist != '')
            desactive_mach_serv(implode(',', $exist), $fileid);
        //insertion en base
        $nb_insert = 0;
        foreach ($tab_final as $key => $value) {
            $query = "INSERT INTO devices(HARDWARE_ID, NAME, IVALUE) VALUES('%s', '%s','%s')";
            $arg = array($key, 'DOWNLOAD', $value);
            mysql2_query_secure($query, $_SESSION['OCS']["writeServer"], $arg);
            $insert[$key] = $value;
            $nb_insert++;
        }
    }

    $not_found = array();
    if (is_array($not_match) && count($not_match) > 0) {
        foreach ($not_match as $value) {
            $not_found[] = $value;
        }
    }

    $already_exist = array();
    if (is_array($exist)) {
        foreach ($exist as $key => $value) {
            if (!isset($insert[$key])) {
                $already_exist[] = $value;
            }
        }
    }

    //retour des erreurs
    $don['not_match'] = $not_found;
    $don['nb_not_match'] = count($not_found);
    $don['exist'] = $already_exist;
    $don['nb_exist'] = count($already_exist);
    $don['nb_insert'] = $nb_insert;
    return $don;
}

?>
