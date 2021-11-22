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
 use PHPMailer\PHPMailer\SMTP;

 require __DIR__.'/../../vendor/phpmailer/phpmailer/src/Exception.php';
 require __DIR__.'/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
 require __DIR__.'/../../vendor/phpmailer/phpmailer/src/SMTP.php';

 //Load libraries
 require __DIR__.'/../softwares/SoftwareCategory.php';
 require __DIR__.'/../assets/AssetsCategory.php';

 /**
  * Class for the notification mail
  */
 class NotificationMail
 {
      public $info = [];
      public $notif;
      public $div = [];
      private $champs = array('NOTIF_FOLLOW'=>'NOTIF_FOLLOW','NOTIF_MAIL_ADMIN'=>'NOTIF_MAIL_ADMIN','NOTIF_NAME_ADMIN'=>'NOTIF_NAME_ADMIN','NOTIF_MAIL_REPLY'=>'NOTIF_MAIL_REPLY',
                          'NOTIF_NAME_REPLY'=>'NOTIF_NAME_REPLY','NOTIF_SEND_MODE'=>'NOTIF_SEND_MODE','NOTIF_SMTP_HOST'=>'NOTIF_SMTP_HOST',
                          'NOTIF_PORT_SMTP'=>'NOTIF_PORT_SMTP','NOTIF_USER_SMTP'=>'NOTIF_USER_SMTP','NOTIF_PASSWD_SMTP'=>'NOTIF_PASSWD_SMTP',
                          'NOTIF_PROG_TIME'=>'NOTIF_PROG_TIME','NOTIF_PROG_DAY'=>'NOTIF_PROG_DAY'
                        );
      private $week = array('MON' => 'MON', 'TUE' => 'TUE', 'WED' => 'WED', 'THURS' => 'THURS', 'FRI' => 'FRI', 'SAT' => 'SAT', 'SUN' => 'SUN');

      const HTML_EXT = 'html';

      public function __construct($language){
        global $l;
        $l = new language($language);
      }

      /**
       * Get the notification selected
       * @return [type] [description]
       */
      public function get_notif_selected(){
          $sql = "SELECT `FILE` FROM notification WHERE `TYPE`= 'SELECTED'";
          $result = mysqli_query($_SESSION['OCS']["readServer"], $sql);
          $item_notif = mysqli_fetch_array($result);

          return $item_notif['FILE'];
      }

      /**
       * Update the selected notification
       * @param  string $selected [description]
       */
      public function update_perso($selected){
          $sql = "UPDATE notification SET `FILE` = '%s' WHERE `TYPE` = 'SELECTED'";
          $arg_sql = array($selected);
          mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg_sql);
      }

      /**
       * Insert Info smtp in notification_config
       * @param  array $infos [description]
       * @return
       */
      public function insert_info_smtp($infos){
        $infos['NOTIF_PROG_DAY'] = $this->number_day($infos);
        $sql="SELECT * FROM notification_config";
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
        while($row = mysqli_fetch_array($result)){
           $verif[$row['NAME']]= $row['NAME'];
        }
        foreach($infos as $key => $value){
          if(array_key_exists($key, $this->champs) && !array_key_exists($key, $verif)){
            $sql = "INSERT INTO `notification_config`(`NAME`, `TVALUE`) VALUES('%s', '%s')";
            $arg = array($key, $value);
            mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
          }elseif(array_key_exists($key, $verif)){
            $sql_update = "UPDATE `notification_config` SET `NAME` = '%s', `TVALUE` = '%s' WHERE `NAME` = '%s' ";
            $arg_update = array($key, $value, $key);
            mysql2_query_secure($sql_update, $_SESSION['OCS']["writeServer"], $arg_update);
          }
        }
      }

      /**
       * Concatenation of day for bdd
       * @param  array $infos [description]
       * @return string        [description]
       */
      private function number_day($infos){
        foreach($infos as $key => $value){
          if(array_key_exists($key, $this->week)){
              $day .= $this->week[$key] . ",";
          }
        }
        return $day;
      }

      /**
       * retrieve info smtp
       * @return [type] [description]
       */
      public function get_info_smtp(){
          $sql = "SELECT * FROM notification_config";
          $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
          while($row = mysqli_fetch_array($result)){
              if($row['NAME'] == 'NOTIF_PROG_DAY'){
                $day[] = explode(",", $row['TVALUE']);
                foreach($day as $value){
                  foreach($value as $values){
                    if($values != ''){
                      $this->info[$values] = $values;
                    }
                  }
                }
              }else{
                $this->info[$row['NAME']]= $row['TVALUE'];
              }
          }
          return $this->info;
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
          $this->notif->Host = $this->info['NOTIF_SMTP_HOST'];

          if($this->info['NOTIF_USER_SMTP'] != '' && $this->info['NOTIF_PASSWD_SMTP'] != ''){
              // Enable SMTP authentication
              $this->notif->SMTPAuth = true;
              // SMTP username
              $this->notif->Username = $this->info['NOTIF_USER_SMTP'];
              // SMTP password
              $this->notif->Password = $this->info['NOTIF_PASSWD_SMTP'];
          }else{
              $this->notif->SMTPAuth = false;
          }
          $this->notif->SMTPSecure = $this->info['NOTIF_SEND_MODE'];
          $this->notif->Port = $this->info['NOTIF_PORT_SMTP'];

          // Recipients
          $this->notif->setFrom($this->info['NOTIF_MAIL_ADMIN'], 'OCSInventory');
          $this->notif->addAddress($this->info['NOTIF_MAIL_ADMIN'], $this->info['NOTIF_NAME_ADMIN']);

          if($this->info['NOTIF_MAIL_REPLY'] != '' && $this->info['NOTIF_NAME_REPLY'] != ''){
             $this->notif->addReplyTo($this->info['NOTIF_MAIL_REPLY'], $this->info['NOTIF_NAME_REPLY']);
          }

      }


      /**
       * send notification with phpMailer
       * @return void
       */
     public function send_notification($subject, $body, $selected, $altBody = '', $isHtml = false ){

            $body = $this->replace_value($body, $selected);

            if(!$body){
                error_log('Error reading custom template');
                return false;
            }

            try{
               // Content
               $this->notif->isHTML(false);
               $this->notif->Subject = $subject;
               $this->notif->Body    = $body;
               $this->notif->AltBody = $altBody;

               $this->notif->send();
               error_log('Message has been sent');
           } catch (Exception $e) {
               $msg = 'Message could not be sent. Mailer Error: '. $e->errorMessage();
               error_log($msg);
           }
      }

      /**
       * Replace value in template
       * @param  $_FILES $file [description]
       * @return [type]       [description]
       */
      public function replace_value($file, $selected){
          global $l;
          $soft = new SoftwareCategory();
          $asset = new AssetsCategory();

          $pattern[0] = "{{";
          $pattern[1] = "}}";
          $replacement[0] = "";
          $replacement[1] = "";

          if($selected == 'DEFAULT'){
            $template = file_get_contents(TEMPLATE.'OCS_template.html', true);
          }else{
            if(file_exists($file)){
                $template = file_get_contents($file, true);
            }else{
                return false;
            }
          }

          if(str_contains($template, "{{")){
            $explode1 = explode("{{", $template);
              foreach($explode1 as $value){
                if(str_contains($value, "}}")){
                    $explode2[] = explode("}}", $value);
                }
              }

              foreach ($explode2 as $values){
                foreach ($values as $trad){
                  if(!str_contains($trad, "<")){
                      $explode3[] = $trad;
                  }
                }
              }

              foreach ($explode3 as $replace){
                if(str_contains($replace, "g")){
                    $traduction = explode(".", $replace);
                    $pattern[] = $replace;
                    $replacement[] = $l->g($traduction[1]);
                }elseif($replace == 'Report.Asset'){
                    $pattern[] = $replace;
                    $asset->get_assets();
                    $replacement[] = $asset->html;
                }elseif($replace == 'Report.Software'){
                    $pattern[] = $replace;
                    $replacement[] = $soft->get_table_html_soft();
                }
              }

              return str_replace($pattern, $replacement, $template);
          }else{
              return $template;
          }
      }

      /**
       * Save file in Templates
       * @param  $_FILE $file [description]
       * @return [type]       [description]
       */
      public function upload_file($file, $subject){
          global $l;
          $uploadFile = TEMPLATE . basename($file['template']['name']);

          if(!$this->is_html_extension($uploadFile)){
              msg_error($l->g(8021));
              return false;
          }

          if($file['template']['type'] == 'text/html'){
            if(is_writable(TEMPLATE)){
              if (move_uploaded_file($_FILES['template']['tmp_name'], $uploadFile)) {
                $sql = "UPDATE `notification` SET FILE='%s', SUBJECT='%s' WHERE TYPE='PERSO'";
                $arg = array(TEMPLATE . basename($file['template']['name']), $subject);
                mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
                msg_success($l->g(8014));
                $this->get_template_perso();
              } else {
                msg_error($l->g(8015));
                return false;
              }
            }else{
              $msg = $l->g(8015). ", ". TEMPLATE . " " . $l->g(8029);
              msg_error($msg);
            }
          }else{
            msg_error($l->g(8017));
            return false;
          }
      }

      /**
       * Check if file respect naming convention
       * And have extension .html
       *
       * @param array $uploaded_file
       */
      private function is_html_extension($uploaded_file_name){
          $ext = end((explode(".", $uploaded_file_name)));
          if($ext == self::HTML_EXT){
              return true;
          }else{
              return false;
          }
      }

      /**
       * Get directory template perso
       * @return string [directory]
       */
      public function get_template_perso(){
          $sql = "SELECT FILE FROM `notification` WHERE TYPE='PERSO'";
          $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
          $row = mysqli_fetch_array($result);

          return $row['FILE'];
      }

      /**
       * [get_all_information description]
       * @param  string $value [description]
       * @return array        [description]
       */
      public function get_all_information($value){
        $sql = "SELECT * FROM `notification` WHERE TYPE='%s'";
        $arg = array($value);
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
        while ($notif = mysqli_fetch_array($result)) {
            $info[$notif['TYPE']]['SUBJECT'] = $notif['SUBJECT'];
            $info[$notif['TYPE']]['FILE'] = $notif['FILE'];
            $info[$notif['TYPE']]['ALTBODY'] = $notif['ALTBODY'];
        }
        return $info;
      }

      /**
       * html div for form
       * @return [type] [description]
       */
      public function html_div(){
        global $l;
        $champs = array('NOTIF_FOLLOW' => $l->g(8001),
            'NOTIF_MAIL_ADMIN' => $l->g(8002),
            'NOTIF_NAME_ADMIN' => $l->g(8003),
            'NOTIF_MAIL_REPLY' => $l->g(8004),
            'NOTIF_NAME_REPLY' => $l->g(8005),
            'NOTIF_SEND_MODE' => $l->g(8009),
            'NOTIF_SMTP_HOST' => $l->g(8006),
            'NOTIF_PORT_SMTP' => $l->g(279),
            'NOTIF_USER_SMTP' => $l->g(8007),
            'NOTIF_PASSWD_SMTP' => $l->g(8008),
            'NOTIF_PROG_TIME' => '',
            'NOTIF_PROG_DAY' => ''
        );

        foreach ($champs as $key => $value){
          $this->div[$key] = "<hr/>
                        <div class='row config-row'>
                        <div class='col-md-6'>
                        <label for='".$key."'>".$key."</label>
                        <p class='help-block'>".$value."</p>
                        </div>
                        <div class='col-md-4'>
                        <div class='form-group'>";
        }
      }
 }
