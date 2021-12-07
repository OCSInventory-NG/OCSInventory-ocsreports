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

// Check for mbstring before loading session and conf data
if (!extension_loaded('mbstring')) {
    die("Please install php mbstring extension");
}

require("require/fichierConf.class.php");

// Before session_start to allow objects to be unserialized from session
require_once('require/menu/include.php');
require_once('require/config/include.php');

@session_start();

$debut = microtime(true);

// Is it an AJAX call ? (ajax.php)
if (!defined('AJAX')) {
    define('AJAX', false);
}

require ('require/header.php');
if (isset($protectedGet[PAG_INDEX])) {
    addLog('PAGE', $protectedGet[PAG_INDEX]);
}

if (!AJAX && !isset($protectedGet["popup"]) && !isset($protectedGet["no_footer"])) {
    require (FOOTER_HTML);
}