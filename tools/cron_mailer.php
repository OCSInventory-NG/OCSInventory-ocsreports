#!/usr/bin/php

<?php
require '../dbconfig.inc.php';
require '../require/function_commun.php';

$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME);

$champs = array('NOTIF_FOLLOW' => 'NOTIF_FOLLOW',
    'NOTIF_MAIL_ADMIN' => 'NOTIF_MAIL_ADMIN',
    'NOTIF_NAME_ADMIN' => 'NOTIF_NAME_ADMIN',
    'NOTIF_MAIL_REPLY' => 'NOTIF_MAIL_REPLY',
    'NOTIF_NAME_REPLY' => 'NOTIF_NAME_REPLY',
    'NOTIF_SEND_MODE' => 'NOTIF_SEND_MODE',
    'NOTIF_SMTP_HOST' => 'NOTIF_SMTP_HOST',
    'NOTIF_PORT_SMTP' => 'NOTIF_PORT_SMTP',
    'NOTIF_USER_SMTP' => 'NOTIF_USER_SMTP',
    'NOTIF_PASSWD_SMTP' => 'NOTIF_PASSWD_SMTP',
);
$values = look_config_default_values($champs);

$tab = array(
    'NOTIF_FOLLOW' => $values['ivalue']['NOTIF_FOLLOW'],
    'NOTIF_MAIL_ADMIN' => $values['tvalue']['NOTIF_MAIL_ADMIN'],
    'NOTIF_NAME_ADMIN' => $values['tvalue']['NOTIF_NAME_ADMIN'],
    'NOTIF_MAIL_REPLY' => $values['tvalue']['NOTIF_MAIL_REPLY'],
    'NOTIF_NAME_REPLY' => $values['tvalue']['NOTIF_NAME_REPLY'],
    'NOTIF_SEND_MODE' => $values['tvalue']['NOTIF_SEND_MODE'],
    'NOTIF_SMTP_HOST' => $values['tvalue']['NOTIF_SMTP_HOST'],
    'NOTIF_PORT_SMTP' => $values['ivalue']['NOTIF_PORT_SMTP'],
    'NOTIF_USER_SMTP' => $values['tvalue']['NOTIF_USER_SMTP'],
    'NOTIF_PASSWD_SMTP' => $values['tvalue']['NOTIF_PASSWD_SMTP']
);

foreach ($tab as $key => $value){
    echo $key." => ".$value."\n";
}
?>
