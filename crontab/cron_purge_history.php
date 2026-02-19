#!/usr/bin/php
<?php
require_once(__DIR__ . '/../var.php');
require_once(CONF_MYSQL);
require_once(ETC_DIR . '/require/function_commun.php');
require_once(ETC_DIR . '/require/config/include.php');
require_once(ETC_DIR . '/require/fichierConf.class.php');

$shrotopts = "a";
$longopts = array("activated");

$_SESSION['OCS']["writeServer"] = dbconnect(SERVER_WRITE, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);
$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);

print ("[" . date("Y-m-d H:i:s") . "] Start to purge removed packages from download history\n");

$reqDelHistory = "DELETE FROM `download_history` WHERE PKG_ID NOT IN (SELECT FILEID FROM `download_available`);";
mysql2_query_secure($reqDelHistory, $_SESSION['OCS']["writeServer"]);

$opts = getopt($shrotopts, $longopts);

if (isset($opts["a"])) {
    $opts["activated"] = $opts["a"];
}

if (isset($opts["activated"])) {
    $reqDelHistory = "DELETE FROM `devices` WHERE IVALUE NOT IN (SELECT ID FROM `download_enable`) AND NAME = 'DOWNLOAD';";
    mysql2_query_secure($reqDelHistory, $_SESSION['OCS']["writeServer"]);
    print ("[" . date("Y-m-d H:i:s") . "] Download activated packages has been purged of removed packages\n");
}

print ("[" . date("Y-m-d H:i:s") . "] Download history has been purged of removed packages\n");

session_destroy();
?>