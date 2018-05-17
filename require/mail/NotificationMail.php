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

 // Import PHPMailer classes into the global namespace
 // These must be at the top of your script, not inside a function
 use PHPMailer\PHPMailer\PHPMailer;
 use PHPMailer\PHPMailer\Exception;

 //Load libraries
 require PHPMAILER.'/Exception.php';
 require PHPMAILER.'/PHPMailer.php';
 require PHPMAILER.'/SMTP.php';
 require 'require/mail/Templates.php';

 /**
  * Class for the notification mail
  */
 class NotificationMail
 {
      private $notif_follow = null;
      private $mail_admin = null;
      private $name_admin = null;
      private $mail_reply = null;
      private $name_reply = null;
      private $send_mode = null;
      private $host_SMTP = null;
      private $port_SMTP = null;
      private $user_SMTP = null;
      private $psswd_SMTP = null;
      private $notif;

      /**
       * retrieve values notification config
       * @return call send_notification()
       */
      public function get_info_smtp(){
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

          $this->notif_follow = $values['ivalue']['NOTIF_FOLLOW'];
          $this->mail_admin = $values['tvalue']['NOTIF_MAIL_ADMIN'];
          $this->name_admin = $values['tvalue']['NOTIF_NAME_ADMIN'];

          if($values['tvalue']['NOTIF_MAIL_REPLY'] != null){
              $this->mail_reply = $values['tvalue']['NOTIF_MAIL_REPLY'];
              $this->name_reply = $values['tvalue']['NOTIF_NAME_REPLY'];
          }

          $this->send_mode = $values['tvalue']['NOTIF_SEND_MODE'];
          $this->host_SMTP = $values['tvalue']['NOTIF_SMTP_HOST'];
          $this->port_SMTP = $values['ivalue']['NOTIF_PORT_SMTP'];

          if($values['tvalue']['NOTIF_USER_SMTP'] != null){
              $this->user_SMTP = $values['tvalue']['NOTIF_USER_SMTP'];
              $this->psswd_SMTP = $values['tvalue']['NOTIF_PASSWD_SMTP'];
          }

      }

      /**
       * configuration of phpmailer object
       * @return phpMailer $this->notif
       */
      public function config_mailer(){
          // Passing `true` enables exceptions
          $this->notif = new PHPMailer(true);
          // Server settings
          // Set mailer to use SMTP
          $this->notif->isSMTP();
          // Specify main and backup SMTP servers
          $this->notif->Host = $this->host_SMTP;

          if($this->user_SMTP != null && $this->psswd_SMTP != null){
              // Enable SMTP authentication
              $this->notif->SMTPAuth = true;
              // SMTP username
              $this->notif->Username = $this->user_SMTP;
              // SMTP password
              $this->notif->Password = $this->psswd_SMTP;
          }else{
              $this->notif->SMTPAuth = false;
          }
          $this->notif->SMTPSecure = $this->send_mode;
          $this->notif->Port = $this->port_SMTP;

          // Recipients
          $this->notif->setFrom($this->mail_admin, 'OCSInventory');
          $this->notif->addAddress($this->mail_admin, $this->name_admin);

          if($this->mail_reply != null && $this->name_reply != null){
             $this->notif->addReplyTo($this->mail_reply, $this->name_reply);
          }

      }


      /**
       * send notification with phpMailer
       * @return void
       */
     public function send_notification($subject, $body, $altBody, $isHtml = false){
          try{
             // Content
             $this->notif->isHTML(false);
             $this->notif->Subject = 'Notification OCSInventory';
             $this->notif->Body    = file_get_contents('require/mail/Templates/template.php', true);;
             $this->notif->AltBody = 'This is the body in plain text for non-HTML mail clients';

             $this->notif->send();
             error_log('Message has been sent');
         } catch (Exception $e) {
             $msg = 'Message could not be sent. Mailer Error: '. $mail->ErrorInfo;
             error_log($msg);
         }
      }
 }
