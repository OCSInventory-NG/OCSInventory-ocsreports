#!/usr/bin/php
<?php
require_once('../var.php');
require_once(CONF_MYSQL);
require_once('../require/function_commun.php');
require_once('../require/config/include.php');
require_once('../require/fichierConf.class.php');
require_once('../require/function_table_html.php');
require_once('../require/groupReports/GroupReport.php');

$_SESSION['OCS']["writeServer"] = dbconnect(SERVER_WRITE, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);
$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);


$report = new GroupReport();
$scheduled = $report->getScheduledReports();




