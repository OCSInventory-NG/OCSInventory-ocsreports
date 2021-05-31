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

function get_download_root() {
    $document_root_conf = look_config_default_values(array('DOWNLOAD_PACK_DIR'));

    if (isset($document_root_conf["tvalue"]['DOWNLOAD_PACK_DIR'])) {
        return $document_root_conf["tvalue"]['DOWNLOAD_PACK_DIR'] . "/download/";
    } else {
        return DOCUMENT_ROOT . "download/";
    }
}

function get_redistrib_download_root() {
    $document_root_conf = look_config_default_values(array('DOWNLOAD_REP_CREAT'));

    if (isset($document_root_conf["tvalue"]['DOWNLOAD_REP_CREAT'])) {
        return $document_root_conf["tvalue"]['DOWNLOAD_REP_CREAT'];
    } else {
        return DOCUMENT_ROOT . "download/server/";
    }
}

function get_redistrib_distant_download_root() {
    $document_root_conf = look_config_default_values(array('DOWNLOAD_SERVER_DOCROOT'));
    return $document_root_conf["tvalue"]['DOWNLOAD_SERVER_DOCROOT'];
}

function package_exists($timestamp) {
    return file_exists(get_download_root() . $timestamp . '/info');
}

function redistrib_package_exists($timestamp) {
    return file_exists(get_download_root() . $timestamp . '/info');
}

function package_name_exists($name) {
    $query = "SELECT COUNT(*) FROM download_available WHERE NAME = '%s'";
    $res = mysql2_query_secure($query, $_SESSION['OCS']['readServer'], $name);
    $count = mysqli_fetch_row($res);
    return $count[0] > 0;
}

function get_package_info($timestamp) {
    $query = "SELECT FILEID, NAME, PRIORITY, FRAGMENTS, SIZE, OSNAME, COMMENT FROM download_available WHERE FILEID = %s";
    $res = mysql2_query_secure($query, $_SESSION['OCS']['readServer'], $timestamp);
    return mysqli_fetch_assoc($res);
}


?>