#!/usr/bin/php
<?php
require_once(__DIR__.'/../var.php');
require_once(CONF_MYSQL);
require_once(ETC_DIR.'/require/function_commun.php');
require_once(ETC_DIR.'/require/config/include.php');
require_once(ETC_DIR.'/require/fichierConf.class.php');

$_SESSION['OCS']["writeServer"] = dbconnect(SERVER_WRITE, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);
$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);

print("[".date("Y-m-d H:i:s"). "] Start to purge removed packages from download history\n");

$reqDelHistory = "DELETE FROM `download_history` WHERE PKG_ID NOT IN (SELECT FILEID FROM `download_available`);";
mysql2_query_secure($reqDelHistory, $_SESSION['OCS']["writeServer"]);

print("[".date("Y-m-d H:i:s"). "] Download history has been purged of removed packages\n");
?>
