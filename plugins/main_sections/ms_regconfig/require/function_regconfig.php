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
$list_registry_key = array('HKEY_CLASSES_ROOT',
    'HKEY_CURRENT_USER',
    'HKEY_LOCAL_MACHINE',
    'HKEY_USERS',
    'HKEY_CURRENT_CONFIG',
    'HKEY_DYN_DATA (Windows 9X only)');

function add_update_key($form_values, $update = false) {
    global $l;

    foreach ($form_values as $value) {
        if (trim($value) == "") {
            msg_error($l->g(988));
            return false;
        }
    }

    if ($update) {
        $req = "UPDATE regconfig SET " .
                "NAME='%s'," .
                "REGTREE='%s'," .
                "REGKEY='%s'," .
                "REGVALUE='%s' " .
                "where ID='%s'";
        $arg_req = array($form_values["NAME"], $form_values["REGTREE"],
            $form_values["REGKEY"], $form_values["REGVALUE"],
            $update);
    } else {
        $sql_verif = "select ID from regconfig
						where REGTREE='%s'
							and REGKEY='%s'
							and REGVALUE='%s'";
        $arg_verif = array($form_values["REGTREE"], $form_values["REGKEY"], $form_values["REGVALUE"]);
        $res = mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"], $arg_verif);
        $row = mysqli_fetch_object($res);
        if (!isset($row->ID)) {
            $req = "INSERT INTO regconfig (NAME,REGTREE,REGKEY,REGVALUE)
					VALUES('%s','%s','%s','%s')";
            $arg_req = array($form_values["NAME"], $form_values["REGTREE"],
                $form_values["REGKEY"], $form_values["REGVALUE"]);
        } else {
            msg_error($l->g(987));
            return false;
        }
    }

    if (isset($req)) {
        mysql2_query_secure($req, $_SESSION['OCS']["writeServer"], $arg_req);
        if ($update)
            msg_success($l->g(1185));
        else
            msg_success($l->g(1184));
        return true;
    }
}

/*
 * function to delete a registry key
 * $id=> id of registry key
 */

function delkey($id) {
    //find the registry key
    $sql = "select name from regconfig where id =%s";
    $arg = $id;
    $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
    $row = mysqli_fetch_object($res);
    $name = $row->name ?? '';
    //delete key
    $sql_reg = "delete from regconfig where id =%s ";
    mysql2_query_secure($sql_reg, $_SESSION['OCS']["writeServer"], $arg);
    //delete cache
    $sql_reg = "delete from registry_name_cache where name ='%s' ";
    mysql2_query_secure($sql_reg, $_SESSION['OCS']["writeServer"], $name);
}

?>