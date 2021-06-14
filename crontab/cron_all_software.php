#!/usr/bin/php
<?php
require_once('../var.php');
require_once(CONF_MYSQL);
require_once('../require/function_commun.php');
require_once('../require/softwares/AllSoftware.php');
require_once('../require/config/include.php');
require_once('../require/fichierConf.class.php');

$_SESSION['OCS']["writeServer"] = dbconnect(SERVER_WRITE, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);
$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);

$software = new AllSoftware();

error_log(print_r("Please wait, software processing is in progress. It could take a few minutes ...", true));
$insert = $software->software_link_treatment();