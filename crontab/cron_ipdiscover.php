#!/usr/bin/php
<?php
require_once(__DIR__.'/../var.php');
require_once(CONF_MYSQL);
require_once(ETC_DIR.'/require/function_commun.php');
require_once(ETC_DIR.'/require/config/include.php');
require_once(ETC_DIR.'/require/fichierConf.class.php');

$_SESSION['OCS']["writeServer"] = dbconnect(SERVER_WRITE, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);
$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);

$configToLookOut = [
    'IPDISCOVER_PURGE_OLD' => 'IPDISCOVER_PURGE_OLD',
    'IPDISCOVER_PURGE_VALIDITY_TIME' => 'IPDISCOVER_PURGE_VALIDITY_TIME'
];

$ipdiscoverPurgeConfig = look_config_default_values($configToLookOut);

if($ipdiscoverPurgeConfig["ivalue"]["IPDISCOVER_PURGE_OLD"] == "1"){
    $purgeValidityTime = $ipdiscoverPurgeConfig["ivalue"]["IPDISCOVER_PURGE_VALIDITY_TIME"];

    $dateformat = "Y-m-d H:i:s";
    $timedelta = sprintf("-%s days", $purgeValidityTime);

    $datetime = new Datetime("NOW");
    $datetime->modify($timedelta);
    $purgeDate = $datetime->format($dateformat);

    print("[".date("Y-m-d H:i:s"). "] Start to purge IpDiscover devices that have not been updated since ".$purgeDate."\n");

    $queryCount = "SELECT `MAC` FROM `netmap` WHERE `date` < '%s'";
    $resultCount = mysql2_query_secure($queryCount, $_SESSION['OCS']["readServer"], $purgeDate);

    $purgeQuery = "DELETE FROM `netmap` WHERE `date` < '%s'";
    mysql2_query_secure($purgeQuery, $_SESSION['OCS']["writeServer"], $purgeDate);

    print("[".date("Y-m-d H:i:s"). "] ".$resultCount->num_rows." devices have been removed\n");
}