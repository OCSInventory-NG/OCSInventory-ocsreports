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

function search_all_item() {
    $result_search_soft = mysqli_query($_SESSION['OCS']["readServer"], $_SESSION['OCS']['query_dico']);
    while ($item_search_soft = mysqli_fetch_object($result_search_soft)) {
        $list[] = $item_search_soft->ID;
    }
    return $list;
}

function del_soft($onglet, $list_soft) {
    /*if ($_SESSION['OCS']['usecache']) {
        $table = "softwares_name_cache";
    } else {
        $table = "software_name";
    }*/
    $table = "software_name";

    $sql_soft_name = "select distinct NAME from " . $table . " where ID in (" . implode(",", $list_soft) . ")";
    $result_soft_name = mysqli_query($_SESSION['OCS']["readServer"], $sql_soft_name);
    while ($item_soft_name = mysqli_fetch_object($result_soft_name)) {
        $list_soft_name[] = str_replace('"', '\"', $item_soft_name->NAME);
    }
    if ($onglet == "CAT" || $onglet == "UNCHANGED") {
        $sql_delete = "delete from dico_soft where extracted in (\"" . implode("\",\"", $list_soft_name) . "\")";
    }
    if ($onglet == "IGNORED") {
        $sql_delete = "delete from dico_ignored where extracted in (\"" . implode("\",\"", $list_soft_name) . "\")";
    }
    mysqli_query($_SESSION['OCS']["writeServer"], $sql_delete);
}

function trans($onglet, $list_soft, $affect_type, $new_cat, $exist_cat) {
    global $l;
    
    // If new cat and exist cat are empty return
    if($new_cat == '' and $exist_cat == ''){
        return ;
    }
    
    /*if ($_SESSION['OCS']['usecache']) {
        $table = "softwares_name_cache";
    } else {
        $table = "software_name";
    }*/
    $table = "software_name";
    
    //verif is this cat exist
    if ($new_cat != '') {
        $sql_verif = "select extracted from dico_soft where formatted ='" . mysqli_real_escape_string($_SESSION['OCS']["readServer"], $new_cat) . "'";
        $result_search_soft = mysqli_query($_SESSION['OCS']["readServer"], $sql_verif);
        $item_search_soft = mysqli_fetch_object($result_search_soft);
        if (isset($item_search_soft->extracted) || $new_cat == "IGNORED" || $new_cat == "UNCHANGED") {
            $already_exist = true;
        }
    }

    if ($onglet == "NEW") {
        $table = "software_name";
        $ok = true;
    } else {
        if (!isset($already_exist)) {
            del_soft($onglet, $list_soft);
        }
        $ok = true;
    }

    if ($ok == true) {
        if ($affect_type == "EXIST_CAT") {
            if ($exist_cat == "IGNORED") {
                $sql = "insert dico_ignored (extracted) select distinct NAME from " . $table . " where ID in (" . implode(",", $list_soft) . ")";
            } elseif ($exist_cat == "UNCHANGED") {
                $sql = "insert dico_soft (extracted,formatted) select distinct NAME,NAME from " . $table . " where ID in (" . implode(",", $list_soft) . ")";
            } else {
                $sql = "insert dico_soft (extracted,formatted) select distinct NAME,'" . mysqli_real_escape_string($_SESSION['OCS']["readServer"], $exist_cat) . "' from " . $table . " where ID in (" . implode(",", $list_soft) . ")";
            }
        } else {
            if (!isset($already_exist)) {
                $sql = "insert dico_soft (extracted,formatted) select distinct NAME,'" . mysqli_real_escape_string($_SESSION['OCS']["readServer"], $new_cat) . "' from " . $table . " where ID in (" . implode(",", $list_soft) . ")";
            } else {
                echo "<script>alert('" . $l->g(771) . "')</script>";
            }
        }
        if ($sql != '') {
            mysqli_query($_SESSION['OCS']["writeServer"], $sql);
        }
    }
}

?>