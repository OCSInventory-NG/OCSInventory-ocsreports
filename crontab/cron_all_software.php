#!/usr/bin/php
<?php
require_once(__DIR__.'/../var.php');
require_once(CONF_MYSQL);
require_once(ETC_DIR.'/require/function_commun.php');
require_once(ETC_DIR.'/require/softwares/AllSoftware.php');
require_once(ETC_DIR.'/require/config/include.php');
require_once(ETC_DIR.'/require/fichierConf.class.php');

$_SESSION['OCS']["writeServer"] = dbconnect(SERVER_WRITE, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);
$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);

$shortOptions = "c:h::";
$longOptions = array("chunk:","help::");

$options = getopt($shortOptions, $longOptions);

$chunk = 1000;

if(array_key_exists("h", $options) || array_key_exists("help", $options)) {
    echo "Usage: php cron_all_software.php [--] [args...]\n\n";
    echo "  -c, --chunk <number>    Process software by pool of <number>, default 5000\n\n";
    exit();
}

if(array_key_exists("c", $options) || array_key_exists("chunk", $options)) {
    $chunk = $options["c"] ?? $options["chunk"];
}

$software = new AllSoftware();

print("[".date("Y-m-d H:i:s"). "] Start to clean orphan software\n");
$cleanup = $software->software_cleanup();
print("[".date("Y-m-d H:i:s"). "] End of clean\n");

print("[".date("Y-m-d H:i:s"). "] Start to process software by pool of ".$chunk."\n");
$insert = $software->software_link_treatment($chunk);
print("[".date("Y-m-d H:i:s"). "] End of process\n");