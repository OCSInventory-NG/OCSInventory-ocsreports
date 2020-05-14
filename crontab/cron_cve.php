#!/usr/bin/php
<?php
require_once('../dbconfig.inc.php');
require_once('../var.php');
require_once('../require/function_commun.php');
require_once('../require/cve/Cve.php');
require_once('../require/config/include.php');
require_once('../require/fichierConf.class.php');

$_SESSION['OCS']["writeServer"] = dbconnect(SERVER_WRITE, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);
$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);

$cve = new Cve();
$date = null;
$clean = false;

//Check if CVE is activate
if($cve->CVE_ACTIVE == 1) {

    if($cve->CVE_EXPIRE_TIME != null && $cve->CVE_EXPIRE_TIME != "") {
        $date = date('Y/m/d H:i:s', time() - (3600 * $cve->CVE_EXPIRE_TIME));
        $clean = true;
    }

    $curl = curl_init($cve->CVE_SEARCH_URL);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_exec($curl);

    // Check if any error occured on cve-search server
    if(curl_errno($curl)) {
        $info = curl_getinfo($curl);
        $cve->verbose($cve->CVE_VERBOSE, 1);
        curl_close($curl);
        exit();
    } else {
        curl_close($curl);
        $cve->getSoftwareInformations($date, $clean);
        //$cve->insertFlag();
        $cve->verbose($cve->CVE_VERBOSE, 2);
    }
} else {
    $cve->verbose($cve->CVE_VERBOSE, 3);
    exit();
}?>
