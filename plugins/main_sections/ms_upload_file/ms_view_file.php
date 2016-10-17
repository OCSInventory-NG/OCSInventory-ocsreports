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

//origin = workflow teledeploy
if ($protectedGet['prov'] == "dde_wk") {
    $sql = "select FILE,FILE_NAME,FILE_TYPE,FILE_SIZE
			 FROM temp_files
			 where id = '%s'";
    $arg = array($protectedGet["value"]);
}

if ($protectedGet['prov'] == "agent") {
    $sql = "select %s as FILE,name as FILE_NAME from deploy where name = '%s'";
    $arg = array('content', $protectedGet["value"]);
}

if ($protectedGet['prov'] == "ssl") {
    $sql = "select FILE,FILE_NAME from ssl_store where id = '%s'";
    $arg = array($protectedGet["value"]);
}

if (isset($sql) && $sql != '') {
    $res_document_root = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
    $val_document_root = mysqli_fetch_array($res_document_root);
    if (!isset($val_document_root['FILE_TYPE']) || $val_document_root['FILE_TYPE'] != '') {
        $val_document_root['FILE_TYPE'] = "application/force-download";
    }
    if (!isset($val_document_root['FILE_SIZE']) || $val_document_root['FILE_SIZE'] != '') {
        $val_document_root['FILE_SIZE'] = strlen($val_document_root['FILE']);
    }
}

if (isset($val_document_root['FILE_NAME'])) {
    // iexplorer problem
    if (ini_get("zlib.output-compression")) {
        ini_set("zlib.output-compression", "Off");
    }

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-control: private", false);
    header("Content-type: " . $val_document_root['FILE_TYPE']);
    header("Content-Disposition: attachment; filename=\"" . $val_document_root['FILE_NAME'] . "\"");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . $val_document_root['FILE_SIZE']);
    echo $val_document_root['FILE'];
    die();
}
?>