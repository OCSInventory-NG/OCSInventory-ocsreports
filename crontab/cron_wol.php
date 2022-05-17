#!/usr/bin/php
<?php
require_once('../var.php');
require_once(CONF_MYSQL);
require_once('../require/function_commun.php');
require_once('../require/wol/WakeOnLan.php');
require_once('../require/config/include.php');
require_once('../require/fichierConf.class.php');

$wol_class = new Wol();

$today = date('Y-m-d H:i');

$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);

$sql = 'SELECT * FROM schedule_WOL';
$result_wol = mysqli_query($_SESSION['OCS']["readServer"], $sql);

$wol = [];

while ($list_wol = mysqli_fetch_array($result_wol)) {
    $wol[] = ['WOL_ID' => $list_wol['MACHINE_ID'],
              'WOL_DATE' => $list_wol['WOL_DATE']
            ];
}

if (!empty($wol)) {
  for($i = 0; $wol[$i] != null; $i++){
    $date_wol = date('Y-m-d H:i', strtotime($wol[$i]['WOL_DATE']));
    $id = explode(',', $wol[$i]['WOL_ID']);
  
    if($date_wol == $today){
      foreach($id as $value){
        $sql_computer = "SELECT MACADDR,IPADDRESS FROM networks WHERE (hardware_id=%s) AND status='Up'";
        $sql_arg = array($value);
        $resultDetails = mysql2_query_secure($sql_computer, $_SESSION['OCS']["readServer"], $sql_arg);
  
        while ($wol_item = mysqli_fetch_object($resultDetails)) {
            $wol_class->look_config_wol($wol_item->IPADDRESS, $wol_item->MACADDR);
        }
      }
    }
  }
}

