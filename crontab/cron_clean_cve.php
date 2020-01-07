#!/usr/bin/php
<?php
require_once('../dbconfig.inc.php');
require_once('../var.php');
require_once('../require/function_commun.php');
require_once('../require/cve/Cve.php');
require_once('../require/config/include.php');
require_once('../require/fichierConf.class.php');

$_SESSION['OCS']["writeServer"] = dbconnect(SERVER_WRITE, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT);
$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT);

$cve = new Cve();

//Check if CVE is activate
if($cve->CVE_ACTIVE == 1) {
    $curl = curl_init($cve->CVE_SEARCH_URL);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_exec($curl);

    // Check if any error occured on cve-search server
    if(curl_errno($curl)) {
        $info = curl_getinfo($curl);
        error_log(print_r($cve->CVE_SEARCH_URL." is not reachable.", true));
        curl_close($curl);
        exit();
    } else {
        curl_close($curl);
        $soft = $cve->clean_cve();
    }
} else {
    exit();
}?>