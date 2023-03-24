#!/usr/bin/php
<?php
require_once(__DIR__.'/../var.php');
require_once(CONF_MYSQL);
require_once(ETC_DIR.'/require/function_commun.php');
require_once(ETC_DIR.'/require/mail/NotificationMail.php');
require_once(ETC_DIR.'/require/config/include.php');
require_once(ETC_DIR.'/require/fichierConf.class.php');


$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);
$mail = new NotificationMail(DEFAULT_LANGUAGE);

$week = array('MON' => 'Monday', 'TUE' => 'Tuesday', 'WED' => 'Wednesday', 'THURS' => 'Thursday', 'FRI' => 'Friday', 'SAT' => 'Saturday', 'SUN' => 'Sunday');
$values = $mail->get_info_smtp();

foreach ($values as $value){
  if(array_key_exists($value, $week)){
    $day[$week[$value]] = $week[$value];
  }
}

if($values['NOTIF_FOLLOW'] == 'ON' && $values['NOTIF_PROG_TIME'] == date('H:i') && array_key_exists(date('l'), $day)){
    $mail->config_mailer();
    $selected = $mail->get_notif_selected();
    $body_mail = $mail->get_all_information($selected);
    $mail->send_notification($body_mail[$selected]['SUBJECT'], $body_mail[$selected]['FILE'], $selected);
}

?>
