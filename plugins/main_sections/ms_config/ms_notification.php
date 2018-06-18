<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

 if (AJAX) {
     parse_str($protectedPost['ocs']['0'], $params);
     $protectedPost += $params;

     ob_start();
 }
 /*
  * Add value in config table
  */
require_once('require/function_config_generale.php');
require_once('require/mail/NotificationMail.php');

$mail = new NotificationMail();

$def_onglets['NOTIF_CONFIG'] = $l->g(8011); //Notification config.
$def_onglets['NOTIF_PERSO'] = $l->g(8012); //Notification personnalisation

//default => first onglet
if ($protectedPost['onglet'] == "") {
    $protectedPost['onglet'] = "NOTIF_CONFIG";
}

printEnTete($l->g(8000));

$form_name = 'notification_config';

echo open_form($form_name, '', '', 'form-horizontal');

show_tabs($def_onglets,$form_name,"onglet",true);

echo '<div class="col col-md-10" >';

/*******************************************NOTIFICATION CONFIG*****************************************************/

if($protectedPost['onglet'] == 'NOTIF_CONFIG'){

    if ($protectedPost['Valid'] == $l->g(103)) {
        $etat = verif_champ();
        if ($etat == null) {
            update_default_value($protectedPost); //function in function_config_generale.php
            $MAJ = $l->g(1121);
        } else {
            $msg = "";
            foreach ($etat as $name => $value) {
                if (!is_array($value)) {
                    $msg .= $name . " " . $l->g(759) . " " . $value . "<br>";
                } else {
                    if (isset($value['FILE_NOT_EXIST'])) {
                        if ($name == 'DOWNLOAD_REP_CREAT') {
                            $msg .= $name . ": " . $l->g(1004) . " (" . $value['FILE_NOT_EXIST'] . ")<br>";
                        } else {
                            $msg .= $name . ": " . $l->g(920) . " " . $value['FILE_NOT_EXIST'] . "<br>";
                        }
                    }
                }
            }
            msg_error($msg);
        }
    }

    if (is_defined($MAJ)) {
        msg_success($MAJ);
    }

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
    $send_mode = array('nonsecure' => 'SMTP'/*, 'PHP' => 'PHP'*/,'ssl' => 'SMTP+SSL', 'tls'=>'SMTP+TLS');

    ligne('NOTIF_FOLLOW', $l->g(8001), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['NOTIF_FOLLOW']), '', "");
    ligne('NOTIF_MAIL_ADMIN', $l->g(8002), 'input', array('VALUE' => $values['tvalue']['NOTIF_MAIL_ADMIN'], '', 'SIZE' => "30%", 'MAXLENGTH' => 254), '', "");
    ligne('NOTIF_NAME_ADMIN', $l->g(8003), 'input', array('VALUE' => $values['tvalue']['NOTIF_NAME_ADMIN'], 'SIZE' => "30%", 'MAXLENGTH' => 254), '', "");
    ligne('NOTIF_MAIL_REPLY', $l->g(8004), 'input', array('VALUE' => $values['tvalue']['NOTIF_MAIL_REPLY'], '', 'SIZE' => "30%", 'MAXLENGTH' => 254), '', "", $l->g(8010));
    ligne('NOTIF_NAME_REPLY', $l->g(8005), 'input', array('VALUE' => $values['tvalue']['NOTIF_NAME_REPLY'], 'SIZE' => "30%", 'MAXLENGTH' => 254), '', "", $l->g(8010));
    ligne('NOTIF_SEND_MODE', $l->g(8009), 'select', array('VALUE' => $values['tvalue']['NOTIF_SEND_MODE'], 'SELECT_VALUE' => $send_mode));
    ligne('NOTIF_SMTP_HOST', $l->g(8006), 'input', array('VALUE' => $values['tvalue']['NOTIF_SMTP_HOST'], 'SIZE' => "30%", 'MAXLENGTH' => 254), '', "");
    ligne('NOTIF_PORT_SMTP', $l->g(279), 'input', array('VALUE' => $values['ivalue']['NOTIF_PORT_SMTP'], '', 'SIZE' => "30%", 'MAXLENGTH' => 11), '', "");
    ligne('NOTIF_USER_SMTP', $l->g(8007), 'input', array('VALUE' => $values['tvalue']['NOTIF_USER_SMTP'], 'SIZE' => "30%", 'MAXLENGTH' => 254), '', "", $l->g(8010));
    ligne('NOTIF_PASSWD_SMTP', $l->g(8008), 'password', array('VALUE' => $values['tvalue']['NOTIF_PASSWD_SMTP'], 'SIZE' => "30%", 'MAXLENGTH' => 254), '', "", $l->g(8010));

    ?>

    <input type='hidden' id='RELOAD_CONF' name='RELOAD_CONF' value=''>
    <input type="submit" name="Valid" value="<?php echo $l->g(103) ?>" class="btn btn-success">
    <input type="submit" name="Reset" value="<?php echo $l->g(1364) ?>" class="btn btn-danger">

    <?php
}

/*******************************************NOTIFICATION PERSONNALISATION*****************************************************/
if($protectedPost['onglet'] == 'NOTIF_PERSO'){
    echo "</br></br>";
    if ($protectedPost['Send'] == $l->g(103)) {
        $mail->update_perso($protectedPost['notif_choice']);
    }

    $selected_radio = $mail->get_notif_selected();

    if($selected_radio == 'DEFAULT'){
        $default = 'checked';
        $style_perso = 'style="display:none"';
        $perso = '';
    }else{
        $default = '';
        $style_default = 'style="display:none"';
        $perso = 'checked';
    }

    echo '<input type="radio" name="notif_choice" id="notif_choice" value="DEFAULT" '.$default.' onclick="hide(\'notif_perso\', \'default_mail\', \'perso_mail\')" />'.$l->g(488).'</br>
          <input type="radio" name="notif_choice" id="notif_choice" value="PERSO" '.$perso.' onclick="show(\'notif_perso\', \'default_mail\', \'perso_mail\')"/>'.$l->g(8012).'
          <div id ="notif_perso" style="display:none" align="center"></br></br>
          <input type="file"/>
          </div>';

    echo "<hr><h4> Preview </h4>";


    echo "<div id=default_mail ".$style_default.">";
    require 'require/mail/Templates/OCS_template.php';
    echo "</div>";

    echo "<div id=perso_mail ".$style_perso.">";

    echo "</div>";


    echo "</br></br>";

    ?>

    <input type='hidden' id='RELOAD_CONF' name='RELOAD_CONF' value=''>
    <input type="submit" name="Send" value="<?php echo $l->g(103) ?>" class="btn btn-success">
    <input type="submit" name="Reset" value="<?php echo $l->g(1364) ?>" class="btn btn-danger">

    <?php
}

echo "</div>";
echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
} ?>
