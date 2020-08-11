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

//fonction pour avoir tous les groupes
//$group_type = STATIC,DYNAMIC,SERVER
//return tableau [id]=group_name
function all_groups($group_type) {
    //récupération des groupes demandés
    if ($group_type == "SERVER") {
        $reqGetId = "SELECT id,name FROM hardware
					     WHERE deviceid = '_DOWNLOADGROUP_'";
    } else {
        if ($group_type == "STATIC") {
            $reqGetId = "SELECT id,name FROM hardware,`groups`
					     WHERE `groups`.hardware_id=hardware.id
							and deviceid = '_SYSTEMGROUP_'
							and (request is null or trim(request) = '')
						    and (xmldef  is null or trim(xmldef) = '')";
            if (!($_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES")) {
                $reqGetId .= " and workgroup = 'GROUP_4_ALL'";
            }
        } else {
            $reqGetId = "SELECT id,name FROM hardware,`groups`
					     WHERE `groups`.hardware_id=hardware.id
							and deviceid = '_SYSTEMGROUP_'
							and ((request is not null and trim(request) != '')
								or (xmldef is not null and trim(xmldef) != ''))";
        }
    }
    $resGetId = mysql2_query_secure($reqGetId, $_SESSION['OCS']["readServer"]);
    while ($valGetId = mysqli_fetch_array($resGetId)) {
        $list_group[$valGetId['id']] = $valGetId['name'];
    }
    return $list_group;
}

//fonction pour sortir les machines d'un groupe
function remove_of_group($id_group, $list_id) {
    $sql_delcache = "DELETE FROM groups_cache WHERE group_id='%s' and hardware_id in ";
    $arg_delcache[] = $id_group;
    $delcache = mysql2_prepare($sql_delcache, $arg_delcache, $list_id);

    mysql2_query_secure($delcache['SQL'], $_SESSION['OCS']["writeServer"], $delcache['ARG']);
    $cached = mysqli_affected_rows($_SESSION['OCS']["writeServer"]);
    return $cached;
}

//fonction de remplacement d'un groupe
function replace_group($id_group, $list_id, $req, $group_type) {
    //static group?
    if ($group_type == 'STATIC') {
        $static = 1;
        $req = "";
    } else {
        $static = 0;
    }
    //delete cache
    $sql_delcache = "DELETE FROM groups_cache WHERE group_id='%s'";
    $arg_delcache = $id_group;
    mysql2_query_secure($sql_delcache, $_SESSION['OCS']["writeServer"], $arg_delcache);
    //update group
    $sql_updGroup = "UPDATE `groups` set request='', xmldef='%s' where hardware_id=%s";
    $arg_updGroup = array(generate_xml($req), $id_group);
    mysql2_query_secure($sql_updGroup, $_SESSION['OCS']["writeServer"], $arg_updGroup);
    $nb_computer = add_computers_cache($list_id, $id_group, $static);
    return $nb_computer;
}

//create group function
function creat_group($name, $descr, $list_id, $req, $group_type) {
    global $l;
    if (trim($name) == "") {
        return array('RESULT' => 'ERROR', 'LBL' => $l->g(638));
    }
    if (trim($descr) == "") {
        return array('RESULT' => 'ERROR', 'LBL' => $l->g(1234));
    }
    //static group?
    if ($group_type == 'STATIC') {
        $static = 1;
        $req = "";
    } else {
        $static = 0;
    }
    //does $name group already exists
    $reqGetId = "SELECT id FROM hardware WHERE name='%s' and deviceid = '_SYSTEMGROUP_'";
    $argGetId = $name;
    $resGetId = mysql2_query_secure($reqGetId, $_SESSION['OCS']["readServer"], $argGetId);
    if (mysqli_fetch_array($resGetId)) {
        return array('RESULT' => 'ERROR', 'LBL' => $l->g(621));
    }

    //insert new group
    $sql_insert = "INSERT INTO hardware(deviceid,name,description,lastdate) VALUES( '_SYSTEMGROUP_' , '%s', '%s', NOW())";
    $arg_insert = array($name, $descr);
    mysql2_query_secure($sql_insert, $_SESSION['OCS']["writeServer"], $arg_insert);
    //Getting hardware id
    $insertId = mysqli_insert_id($_SESSION['OCS']["writeServer"]);
    $xml = generate_xml($req);

    //Creating group
    $sql_group = "INSERT INTO `groups`(hardware_id, xmldef, create_time) VALUES ( %s, '%s', UNIX_TIMESTAMP() )";
    $arg_group = array($insertId, $xml);
    mysql2_query_secure($sql_group, $_SESSION['OCS']["writeServer"], $arg_group);
    addLog("CREATE GROUPE", $name);
    //Generating cache
    if ($list_id != '') {
        $nb_computer = add_computers_cache($list_id, $insertId, $static);
        return array('RESULT' => 'OK', 'LBL' => $nb_computer);
    }

    return array('RESULT' => 'OK', 'LBL' => $l->g(607) . " " . $l->g(608));
}

//function to add computer in groups_cache
function add_computers_cache($list_id, $groupid, $static) {
    require_once('function_computers.php');
    //Generating cache
    if (lock($groupid)) {
        $reqCache = "INSERT IGNORE INTO groups_cache(hardware_id, group_id, static)
						SELECT id, %s, %s from hardware where id in ";
        $argCache = array($groupid, $static);
        $cache = mysql2_prepare($reqCache, $argCache, $list_id);
        mysql2_query_secure($cache['SQL'], $_SESSION['OCS']["writeServer"], $cache['ARG']);
        $cached = mysqli_affected_rows($_SESSION['OCS']["writeServer"]);
        unlock($groupid);
        return $cached;
    }
}

//generation du xml en fonction des requetes
function generate_xml($req) {
    //si il exite une requete
    if (isset($req[0])) {
        //création du début du xml
        $xml = "<xmldef>";
        $i = 0;
        //concaténation des différentes requetes
        while (isset($req[$i])) {
            $xml .= "<REQUEST>" . clean($req[$i]) . "</REQUEST>";
            $i++;
        }
        $xml .= "</xmldef>";
    } else { //si aucune requete n'exite, on renvoie un xml vide
        $xml = "";
    }

    return $xml;
}

function clean($txt) {
    $cherche = array("&", "<", ">", "\"", "'");
    $replace = array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;");
    return str_replace($cherche, $replace, $txt);
}

function delete_group($id_supp) {
    global $l;
    if ($id_supp == "") {
        return array('RESULT' => 'ERROR', 'LBL' => "ID IS NULL");
    }
    if (!is_numeric($id_supp)) {
        return array('RESULT' => 'ERROR', 'LBL' => "ID IS NOT NUMERIC");
    }

    $sql_verif_group = "select id from hardware where id=%s and DEVICEID='_SYSTEMGROUP_' or DEVICEID='_DOWNLOADGROUP_'";
    $arg_verif_group = $id_supp;
    $res_verif_group = mysql2_query_secure($sql_verif_group, $_SESSION['OCS']["readServer"], $arg_verif_group);
    if (mysqli_fetch_array($res_verif_group)) {
        deleteDid($arg_verif_group);
        addLog("DELETE GROUPE", $id_supp);
        return array('RESULT' => 'OK', 'LBL' => '');
    } else {
        return array('RESULT' => 'ERROR', 'LBL' => $l->g(623));
    }
}

function group_4_all($id_group) {
    if ($id_group == "") {
        return array('RESULT' => 'ERROR', 'LBL' => "ID IS NULL");
    }
    if (!is_numeric($id_group)) {
        return array('RESULT' => 'ERROR', 'LBL' => "ID IS NOT NUMERIC");
    }

    $sql_verif = "select WORKGROUP from hardware where id=%s";
    $arg_verif = $id_group;
    $res = mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"], $arg_verif);
    $item = mysqli_fetch_object($res);
    if ($item->WORKGROUP != "GROUP_4_ALL") {
        $sql_update = "update hardware set workgroup= 'GROUP_4_ALL' where id=%s";
        $return_result['LBL'] = "Groupe visible pour tous";
    } else {
        $sql_update = "update hardware set workgroup= '' where id=%s";
        $return_result['LBL'] = "Groupe invisible";
    }
    mysql2_query_secure($sql_update, $_SESSION['OCS']["writeServer"], $arg_verif);
    $return_result['RESULT'] = "OK";
    addLog("ACTION VISIBILITY OF GROUPE", $id_group);
    return $return_result;
}

function show_redistrib_groups_packages($systemid){

    global $l;
    
    $query = "select da.NAME, da.PRIORITY,da.FRAGMENTS,da.SIZE,da.OSNAME,de.INFO_LOC,de.CERT_FILE,de.CERT_PATH,de.PACK_LOC
                from download_enable de,download_available da
                where de.GROUP_ID =%s
                and da.FILEID=de.FILEID
                group by de.fileid;";
    
    $arg_query = array($systemid);
    $resDeploy = mysql2_query_secure($query, $_SESSION['OCS']["readServer"], $arg_query);
    if (mysqli_num_rows($resDeploy) > 0) {
        ?>
        <div class='row'>
            <div class='col-md-12'>
                <p>
                    <table class='table table-striped'>
                      <thead>
                        <tr>
                          <th><?php echo $l->g(49) ?></th>
                          <th><?php echo $l->g(440) ?></th>
                          <th><?php echo $l->g(464) ?></th>
                          <th><?php echo $l->g(462) ?></th>
                          <th><?php echo $l->g(1387) ?></th>
                          <th>INFO_LOC</th>
                          <th>CERT_FILE</th>
                          <th>CERT_PATH</th>
                          <th>PACK_LOC</th>
                        </tr>
                      </thead>
                      <tbody>

                        <?php
                        while ($valDeploy = mysqli_fetch_array($resDeploy)) {
                            ?>
                          <tr>
                            <td><?php echo $valDeploy['NAME'] ?></td>
                            <td><?php echo $valDeploy['PRIORITY'] ?></td>
                            <td><?php echo $valDeploy['FRAGMENTS'] ?></td>
                            <td><?php echo $valDeploy['SIZE'] ?></td>
                            <td><?php echo $valDeploy['OSNAME'] ?></td>
                            <td><?php echo $valDeploy['INFO_LOC'] ?></td>
                            <td><?php echo $valDeploy['CERT_FILE'] ?></td>
                            <td><?php echo $valDeploy['CERT_PATH'] ?></td>
                            <td><?php echo $valDeploy['PACK_LOC'] ?></td>
                          </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </p>
            </div>
        </div>
        <?php
    }
}

?>