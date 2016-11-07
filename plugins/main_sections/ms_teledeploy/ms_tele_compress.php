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
if (!is_numeric($protectedGet["timestamp"])) {
	die();
}
header("content-type: application/zip");
header("Content-Disposition: attachment; filename=" . $protectedGet["timestamp"] . ".zip");
if (isset($protectedGet["timestamp"])) {
	$zipfile = new zipArchive();
	//looking for the directory for pack
	if ($protectedGet['type'] == "server") {
		$sql_document_root = "select tvalue from config where NAME='DOWNLOAD_REP_CREAT'";
	} else {
		$sql_document_root = "select tvalue from config where NAME='DOWNLOAD_PACK_DIR'";
	}

	$res_document_root = mysqli_query($_SESSION['OCS']["readServer"], $sql_document_root);
	while ($val_document_root = mysqli_fetch_array($res_document_root)) {
		$document_root = $val_document_root["tvalue"] . '/download/';
	}
	//echo $document_root;
	//if no directory in base, take $_SERVER["DOCUMENT_ROOT"]
	if (!isset($document_root)) {
		$document_root = VARLIB_DIR . '/download/';
		if ($protectedGet['type'] == "server") {
			$document_root .= "server/";
		}
	}

	$rep = $document_root . $protectedGet["timestamp"] . "/";
	$dir = opendir($rep);
	$tmpfile = tempnam("/tmp", ".zip");
	$zipfile->open($tmpfile, ZipArchive::CREATE);
	while ($f = readdir($dir)) {
		if (is_file($rep . $f)) {
			$zipfile->addFile($rep . $f, $f);
		}
	}
	$zipfile->close();
	closedir($dir);
	readfile($tmpfile);
	unlink($tmpfile);
	exit();
}
?>