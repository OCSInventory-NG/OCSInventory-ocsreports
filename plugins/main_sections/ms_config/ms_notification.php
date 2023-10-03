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

$mail = new NotificationMail($_SESSION['OCS']['LANGUAGE']);
$mail->html_div();

$def_onglets['NOTIF_CONFIG'] = $l->g(8011); //Notification config.
$def_onglets['NOTIF_PERSO'] = $l->g(8012); //Notification personnalisation

//default => first onglet
if (empty($protectedPost['onglet'])) {
    $protectedPost['onglet'] = "NOTIF_CONFIG";
}

$week = array('MON' => $l->g(540),
              'TUE' => $l->g(541),
              'WED' => $l->g(542),
              'THURS' => $l->g(543),
              'FRI' => $l->g(544),
              'SAT' => $l->g(545),
              'SUN' => $l->g(539));

printEnTete($l->g(8000));

$form_name = 'notification_config';

echo open_form($form_name, '', 'enctype="multipart/form-data"', 'form-horizontal');

show_tabs($def_onglets,$form_name,"onglet",true);

echo '<div class="col col-md-10" >';

/*******************************************NOTIFICATION CONFIG*****************************************************/

if($protectedPost['onglet'] == 'NOTIF_CONFIG'){

    if (isset($protectedPost['Valid']) && $protectedPost['Valid'] == $l->g(103)) {
        $mail->insert_info_smtp($protectedPost);
    }

    if (is_defined($MAJ)) {
        msg_success($MAJ);
    }

    $mail->get_info_smtp();
    $send_mode = array('nonsecure' => 'SMTP'/*, 'PHP' => 'PHP'*/,'ssl' => 'SMTP+SSL', 'tls'=>'SMTP+TLS');

    echo $mail->div['NOTIF_FOLLOW'];
    if(isset($mail->info['NOTIF_FOLLOW']) && $mail->info['NOTIF_FOLLOW'] == 'ON'){
      echo "<input type='radio' id='NOTIF_FOLLOW_ON' name='NOTIF_FOLLOW' value='ON' onclick='checkrequire(\"ON\");' checked/>ON</br>";
      echo "<input type='radio' id='NOTIF_FOLLOW_OFF' name='NOTIF_FOLLOW' value='OFF' onclick='checkrequire(\"OFF\");'/>OFF";
      $required = "required";
    }else{
      echo "<input type='radio' id='NOTIF_FOLLOW_ON' name='NOTIF_FOLLOW' value='ON' onclick='checkrequire(\"ON\");'/>ON</br>";
      echo "<input type='radio' id='NOTIF_FOLLOW_OFF' name='NOTIF_FOLLOW' value='OFF' onclick='checkrequire(\"OFF\");' checked/>OFF";
      $required = "";
    }
    echo "</div></div></div>";
    echo $mail->div['NOTIF_MAIL_ADMIN'];
    echo "<input type='text' id='NOTIF_MAIL_ADMIN' name='NOTIF_MAIL_ADMIN' class='form-control input-sm' value='".($mail->info['NOTIF_MAIL_ADMIN'] ?? '')."' maxlength='254' ".$required."/>";
    echo "</div></div></div>";
    echo $mail->div['NOTIF_NAME_ADMIN'];
    echo "<input type='text' id='NOTIF_NAME_ADMIN' name='NOTIF_NAME_ADMIN' class='form-control input-sm' value='".($mail->info['NOTIF_NAME_ADMIN'] ?? '')."' maxlength='254' ".$required."/>";
    echo "</div></div></div>";
    echo $mail->div['NOTIF_MAIL_REPLY'];
    echo "<input type='text' id='NOTIF_MAIL_REPLY' name='NOTIF_MAIL_REPLY' class='form-control input-sm' value='".($mail->info['NOTIF_MAIL_REPLY'] ?? '')."' maxlength='254'/>";
    echo "<p class='help-block'>" . $l->g(8010) . "</p>";
    echo "</div></div></div>";
    echo $mail->div['NOTIF_NAME_REPLY'];
    echo "<input type='text' id='NOTIF_NAME_REPLY' name='NOTIF_NAME_REPLY' class='form-control input-sm' value='".($mail->info['NOTIF_NAME_REPLY'] ?? '')."' maxlength='254'/>";
    echo "<p class='help-block'>" . $l->g(8010) . "</p>";
    echo "</div></div></div>";
    echo $mail->div['NOTIF_SEND_MODE'];
    echo "<select id='NOTIF_SEND_MODE' name='NOTIF_SEND_MODE' class='form-control' value='' maxlength='254'>";
    foreach($send_mode as $key => $value){
      if(isset($mail->info['NOTIF_SEND_MODE']) && $key == $mail->info['NOTIF_SEND_MODE']){
        echo "<option value='".$key."' selected>".$value."</option>";
      }else{
        echo "<option value='".$key."'>".$value."</option>";
      }
    }
    echo "</select>";
    echo "</div></div></div>";
    echo $mail->div['NOTIF_SMTP_HOST'];
    echo "<input type='text' id='NOTIF_SMTP_HOST' name='NOTIF_SMTP_HOST' class='form-control input-sm' value='".($mail->info['NOTIF_SMTP_HOST'] ?? '')."' maxlength='254' ".$required."/>";
    echo "</div></div></div>";
    echo $mail->div['NOTIF_PORT_SMTP'];
    echo "<input type='number' id='NOTIF_PORT_SMTP' name='NOTIF_PORT_SMTP' class='form-control input-sm' value='".($mail->info['NOTIF_PORT_SMTP'] ?? '')."' maxlength='254' ".$required."/>";
    echo "</div></div></div>";
    echo $mail->div['NOTIF_USER_SMTP'];
    echo "<input type='text' id='NOTIF_USER_SMTP' name='NOTIF_USER_SMTP' class='form-control input-sm' value='".($mail->info['NOTIF_USER_SMTP'] ?? '')."' maxlength='254'/>";
    echo "<p class='help-block'>" . $l->g(8010) . "</p>";
    echo "</div></div></div>";
    echo $mail->div['NOTIF_PASSWD_SMTP'];
    echo "<input type='password' id='NOTIF_PASSWD_SMTP' name='NOTIF_PASSWD_SMTP' class='form-control input-sm' value='".($mail->info['NOTIF_PASSWD_SMTP'] ?? '')."' maxlength='254'/>";
    echo "<p class='help-block'>" . $l->g(8010) . "</p>";
    echo "</div></div></div>";
    echo $mail->div['NOTIF_PROG_TIME'];
    echo "<input type='time' id='NOTIF_PROG_TIME' class='form-control input-sm' name='NOTIF_PROG_TIME' value='".($mail->info['NOTIF_PROG_TIME'] ?? '')."' ".$required."/>";
    echo "</div></div></div>";
    echo $mail->div['NOTIF_PROG_DAY'];

    foreach($week as $day => $trad){
      if(array_key_exists($day, $mail->info)){
        echo $trad."<input type='checkbox' id='".$day."' class='' name='".$day."' checked/>";
      }else{
        echo $trad."<input type='checkbox' id='".$day."' class='' name='".$day."'/>";
      }
    }

    echo "</div></div></div></br>";

    ?>

    <input type='hidden' id='RELOAD_CONF' name='RELOAD_CONF' value=''>
    <input type="submit" name="Valid" value="<?php echo $l->g(103) ?>" class="btn btn-success">
    <input type="submit" name="Reset" value="<?php echo $l->g(1364) ?>" class="btn btn-danger">

    <?php
}

/*******************************************NOTIFICATION CUSTOMIZE*****************************************************/
if($protectedPost['onglet'] == 'NOTIF_PERSO'){
    echo "</br></br>";

    if (isset($protectedPost['Send']) && $protectedPost['Send'] == $l->g(103)) {
        $mail->update_perso($protectedPost['notif_choice']);
        if($protectedPost['notif_choice'] == 'PERSO'){
            $result =$mail->upload_file($_FILES, $protectedPost['subject']);
        }
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
          <div id ="notif_perso" '.($style_perso ?? '').' align="center"></br></br>';
    msg_warning($l->g(8016));
    echo '<input type="file" id="template" name="template"/>
          </div>';

    echo "<hr><h4> Preview </h4>";
    echo '<div class="col-md-8 col-xs-offset-0 col-md-offset-2">';

    //Default
    echo "<div id=default_mail ".($style_default ?? '').">";
    echo "<div class='form-group'><label class='control-label col-sm-2' for='subject'>".$l->g(8018)."</label><div class='col-sm-8'>
          <input type='text' class='form-control' id='subject' name='subject' size='50' maxlength='255' value='".$l->g(8019)."' disabled/></div></div>";
    $output = $mail->replace_value('require/mail/Templates/OCS_template.html', 'DEFAULT');
    echo $output;
    echo "</div>";

    //Perso
    $info = $mail->get_all_information('PERSO');
    $output = $mail->replace_value($mail->get_template_perso(), 'PERSO');
    if(!$output){
        $output = $l->g(8020);
    }
    echo "<div id=perso_mail ".($style_perso ?? '').">";
    echo "<div class='form-group'><label class='control-label col-sm-2' for='subject'>".$l->g(8018)."</label><div class='col-sm-8'>
          <input type='text' class='form-control' id='subject' name='subject' size='50' maxlength='255' value='".$info['PERSO']['SUBJECT']."'/></div></div>";
    echo $output;
    echo "</div>";


    echo "</br></br>";

    ?>

    <input type='hidden' id='RELOAD_CONF' name='RELOAD_CONF' value=''>
    <input type="submit" name="Send" value="<?php echo $l->g(103) ?>" class="btn btn-success">
    <input type="submit" name="Reset" value="<?php echo $l->g(1364) ?>" class="btn btn-danger">

    <?php
    echo "</div>";
}

echo "</div>";
echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
} ?>
