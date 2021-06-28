<?php
/*
 * Copyright 2005-2019 OCSInventory-NG/OCSInventory-ocsreports contributors.
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
require_once('../var.php');
require_once(CONF_MYSQL);
require_once('../require/function_commun.php');
require_once('../require/config/include.php');
require_once('../require/fichierConf.class.php');
require_once('../require/teledeploy/Teledeploy.php');
require_once('../require/teledeploy/PackageBuilder.php');
require_once('../require/teledeploy/PackageBuilderForm.php');
require_once('../require/teledeploy/PackageBuilderFormOperatingSystem.php');
require_once('../require/teledeploy/PackageBuilderFormInteractions.php');
require_once('../require/teledeploy/PackageBuilderFormOptions.php');
require_once('../require/teledeploy/PackageBuilderParseXml.php');

$_SESSION['OCS']["writeServer"] = dbconnect(SERVER_WRITE, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);
$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);

$teledeploy = new Teledeploy();
$packageBuilderParseXml = new PackageBuilderParseXml();
$packageBuilderFormOperatingSystem = new PackageBuilderFormOperatingSystem();
$packageBuilderFormInteractions = new PackageBuilderFormInteractions($packageBuilderParseXml);
$packageBuilderFormOptions = new PackageBuilderFormOptions($packageBuilderParseXml);
$packageBuilderForm = new PackageBuilderForm($packageBuilderFormInteractions, $packageBuilderFormOperatingSystem, $packageBuilderParseXml, $packageBuilderFormOptions);
$packageBuilder = new PackageBuilder($packageBuilderForm, $packageBuilderParseXml);

if(isset($_GET['os']) && isset($_GET['linkedoptions'])) {
    $optionsForm = $packageBuilderForm->generateOptions($_GET['os'], $_GET['linkedoptions'], $_SESSION['OCS']["LANGUAGE"]);

    echo $optionsForm;
}

if(isset($_GET['name'])) {
    $fileid['file_exist'] = $packageBuilder->getPackageFileId($_GET['name'], $_SESSION['OCS']["readServer"]);
    $result = json_encode($fileid);
    echo $result;
}
