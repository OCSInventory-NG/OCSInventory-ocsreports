#!/usr/bin/php
<?php
require_once(__DIR__.'/../var.php');
require_once(CONF_MYSQL);
require_once(ETC_DIR.'/require/function_commun.php');
require_once(ETC_DIR.'/require/config/include.php');
require_once(ETC_DIR.'/require/fichierConf.class.php');

$_SESSION['OCS']["writeServer"] = dbconnect(SERVER_WRITE, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);
$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);

$query = "SELECT CONCAT('DELETE FROM `',TABLE_NAME,'` WHERE HARDWARE_ID  NOT IN (SELECT ID FROM hardware);') as deletequery, TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '%s' AND COLUMN_NAME='HARDWARE_ID';";
$args = array(DB_NAME);

$queryList = mysql2_query_secure($query, $_SESSION['OCS']["readServer"], $args);

print("[".date("Y-m-d H:i:s"). "] Start to clean orphan HARDWARE_ID\n");

if($queryList) {
    foreach($queryList as $key => $values) {
        print("[".date("Y-m-d H:i:s"). "] Clean orphan HARDWARE_ID from ".$values["TABLE_NAME"]."\n");
        $countDeletetionRowQuery = "SELECT DISTINCT HARDWARE_ID FROM `%s` WHERE HARDWARE_ID  NOT IN (SELECT ID FROM hardware)";
        $args = array($values["TABLE_NAME"]);
        $countDeletetionRow = mysql2_query_secure($countDeletetionRowQuery, $_SESSION['OCS']["readServer"], $args);

        $deleteResult = mysql2_query_secure($values["deletequery"], $_SESSION['OCS']["writeServer"]);

        if($deleteResult) print("[".date("Y-m-d H:i:s"). "] ".$countDeletetionRow->num_rows." orphan HARDWARE_ID have been deleted from ".$values["TABLE_NAME"]."\n");
        else print("[".date("Y-m-d H:i:s"). "] An error occurs when attempt to clean orphan HARDWARE_ID from ".$values["TABLE_NAME"]."\n");
    }
}

print("[".date("Y-m-d H:i:s"). "] End of process\n");

session_destroy();
