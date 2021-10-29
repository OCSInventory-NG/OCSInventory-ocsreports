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
@session_start();

//looking for default value of ocs config
//default_values => replace with your data if config data is null or empty
//default_values => array(array())// ex: array('LOCAL_SERVER'=>array('TVALUE'=>'http:\\localhost'))
function look_config_default_values($field_name, $like = '', $default_values = '') {
    if ($like == '') {
        $sql = "select NAME,IVALUE,TVALUE,COMMENTS from config where NAME in ";
        $arg_sql = array();
        $arg = mysql2_prepare($sql, $arg_sql, $field_name);
    } else {
        $arg['SQL'] = "select NAME,IVALUE,TVALUE,COMMENTS from config where NAME like '%s'";
        $arg['ARG'] = $field_name;
    }
    $resdefaultvalues = mysql2_query_secure($arg['SQL'], $_SESSION['OCS']["readServer"], $arg['ARG']);
    while ($item = mysqli_fetch_object($resdefaultvalues)) {
        $result['name'][$item->NAME] = $item->NAME;
        $result['ivalue'][$item->NAME] = $item->IVALUE;
        $result['tvalue'][$item->NAME] = $item->TVALUE;
        $result['comments'][$item->NAME] = $item->COMMENTS;
    }

    if (is_array($default_values)) {
        foreach ($default_values as $key => $value) {
            $key = strtolower($key);
            if (is_array($value)) {
                foreach ($value as $name => $val) {
                    if (!is_defined($result[$key][$name])) {
                        $result[$key][$name] = $val;
                    }
                }
            }
        }
    }

    return $result;
}

/* * ****************************************************SQL FUNCTION*************************************************** */

function generate_secure_sql($sql, $arg = '') {

    if (is_array($arg)) {
        foreach ($arg as $value) {
            $arg_array_escape_string[] = mysqli_real_escape_string($_SESSION['OCS']["readServer"], $value);
        }
        $arg_escape_string = $arg_array_escape_string;
    } elseif ($arg != '') {
        $arg_escape_string = mysqli_real_escape_string($_SESSION['OCS']["readServer"], $arg);
    }
    if (isset($arg_escape_string)) {
        if (is_array($arg_escape_string)) {
            $sql = vsprintf($sql, $arg_escape_string);
        } else {
            $sql = sprintf($sql, $arg_escape_string);
        }
    }
    return $sql;
}

function mysql2_query_secure($sql, $link, $arg = '', $log = false) {
    global $l, $lbl_log;
    $query = generate_secure_sql($sql, $arg);
    if ($log) {
        addLog($log, $query, $lbl_log);
    }

    if ($_SESSION['OCS']['DEBUG'] == 'ON') {
        $_SESSION['OCS']['SQL_DEBUG'][] = html_entity_decode($query, ENT_QUOTES);
    }

    if (DEMO) {
        $rest = mb_strtoupper(substr($query, 0, 6));
        if ($rest == 'UPDATE' || $rest == 'INSERT' || $rest == 'DELETE') {
            if (DEMO_MSG != 'show') {
                msg_info($l->g(2103));
                define('DEMO_MSG', 'show');
            }
            return false;
        }
    }
    $result = mysqli_query($link, $query);
    if ($_SESSION['OCS']['DEBUG'] == 'ON' && !$result) {
        msg_error(mysqli_error($link));
    }
    return $result;
}

/*
 * use this function before mysql2_query_secure
 * $sql= requeste
 * $arg_sql = arguments for mysql2_query_secure
 * $arg_tab = arguments to implode
 *
 */

function mysql2_prepare($sql, $arg_sql, $arg_tab = '', $nocot = false) {
    if ($arg_sql == '') {
        $arg_sql = array();
    }

    if (!is_array($arg_tab)) {
        $arg_tab = explode(',', $arg_tab);
    }

    $sql .= " ( ";
    foreach ($arg_tab as $value) {
        if (!$nocot) {
            $sql .= " '%s', ";
        } else {
            $sql .= " %s, ";
        }
        array_push($arg_sql, $value);
    }
    $sql = substr($sql, 0, -2) . " ) ";
    return array('SQL' => $sql, 'ARG' => $arg_sql);
}

function prepare_sql_tab($list_fields, $explu = array(), $distinct = false) {
    $begin_arg = array();
    $begin_sql = "SELECT ";
    if ($distinct) {
        $begin_sql .= " distinct ";
    }
    foreach ($list_fields as $key => $value) {
        if (!in_array($key, $explu)) {
            $begin_sql .= '%s, ';
            array_push($begin_arg, $value);
        }
    }
    return array('SQL' => substr($begin_sql, 0, -2) . " ", 'ARG' => $begin_arg);
}

function dbconnect($server, $compte_base, $pswd_base, $db = DB_NAME, $sslkey = SSL_KEY, $sslcert = SSL_CERT, $cacert = CA_CERT, $port = 3306, $sslmode = SSL_MODE, $enablessl = ENABLE_SSL) {
    error_reporting(E_ALL & ~E_NOTICE);
    mysqli_report(MYSQLI_REPORT_STRICT);
    //$link is ok?
    try {
        $dbc = mysqli_init();
        if($enablessl == "1") {
            $dbc->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
            $dbc->ssl_set($sslkey, $sslcert, $cacert, NULL, NULL);
            if($sslmode == "MYSQLI_CLIENT_SSL") {
                $connect = MYSQLI_CLIENT_SSL;
            } elseif($sslmode == "MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT") {
                $connect = MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
            }
        } else {
            $connect = NULL;
        }

        $dbc->options(MYSQLI_INIT_COMMAND, "SET NAMES 'utf8'");
        $dbc->options(MYSQLI_INIT_COMMAND, "SET sql_mode='NO_ENGINE_SUBSTITUTION'");

        $link = mysqli_real_connect($dbc, $server, $compte_base, $pswd_base, NULL, $port, NULL, $connect);

        if($link) {
            $link = $dbc;
        }
    } catch (Exception $e) {
        if (mysqli_connect_errno()) {
            return "ERROR: MySql connection problem " . $e->getCode() . "<br>" . $e->getMessage();
        }
    }
    //database is ok?
    if (!$link->select_db($db)) {
        return "NO_DATABASE";
    }

    return $link;
}

// Function to retrieve the columns that are full-text indexed within a table
// Arguments:
//   $tableName : The name of the SQL table to query
//   $tableAlias: The alias of the SQL table in the query
function dbGetFTIndex($tableName, $tableAlias) {

     $ft_idx = [];
     $sql_ft='show index from ' . $tableName . ';';
     $resultDetails = mysql2_query_secure($sql_ft, $_SESSION['OCS']["readServer"]);
     while($row = mysqli_fetch_object($resultDetails)){
           if ( $row->Index_type == 'FULLTEXT') {
                $ft_idx[ $row->Column_name ] = "$tableAlias.$row->Column_name";
           }
     }

     return $ft_idx;
}

/* * *********************************END SQL FUNCTION***************************************** */

function addLog($type, $value = "", $lbl_sql = '') {
    if ($_SESSION['OCS']['LOG_GUI'] == 1) {
        //if (is_writable(LOG_FILE)) {
            $logHandler = fopen(LOG_FILE, "a");
            $dte = getDate();
            $date = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $dte["mday"], $dte["mon"], $dte["year"], $dte["hours"], $dte["minutes"], $dte["seconds"]);
            if ($lbl_sql != '') {
                $value = $lbl_sql . ' => ' . $value;
            }
            $towite = $_SESSION['OCS']["loggeduser"] . ";" . $date . ";" . DB_NAME . ";" . $type . ";" . $value . ";" . $_SERVER['REMOTE_ADDR'] . ";\n";
            fwrite($logHandler, $towite);
            fclose($logHandler);
        //}
    }
}


function dateTimeFromMysql($v) {
    global $l;
    $d = DateTime::createFromFormat('Y-m-d H:i:s', $v);
    return $d? $d->format($l->g(1242)) : '';
}

function reloadform_closeme($form = '', $close = false) {
    echo "<script>";
    if ($form != '') {
        echo "window.opener.document.forms['" . $form . "'].submit();";
    }
    if ($close) {
        echo "self.close();";
    }
    echo "</script>";
}

function change_window($url){
    echo "<script>";
    if ($url != '') {
        echo "window.location.href = '".$url."';";
    }
    echo "</script>";
}

function read_profil_file($name, $writable = '') {
    global $l;
    //Select config file depending on user profile
    $ms_cfg_file = $_SESSION['OCS']['CONF_PROFILS_DIR'] . $name . "_config.txt";
    $search = array('INFO' => 'MULTI', 'PAGE_PROFIL' => 'MULTI', 'RESTRICTION' => 'MULTI', 'ADMIN_BLACKLIST' => 'MULTI', 'CONFIGURATION' => 'MULTI');
    if (!is_writable($_SESSION['OCS']['OLD_CONF_DIR']) && $writable != '') {
        msg_error($l->g(297) . ":<br>" . $_SESSION['OCS']['OLD_CONF_DIR'] . "<br>" . $l->g(1148));
    }
    return read_files($search, $ms_cfg_file, $writable);
}

function read_config_file($writable = '') {
    //Select config file depending on user profile
    $ms_cfg_file = $_SESSION['OCS']['CONF_PROFILS_DIR'] . "4all_config.txt";
    $search = array('ORDER_FIRST_TABLE' => 'MULTI2',
        'ORDER_SECOND_TABLE' => 'MULTI2',
        'ORDER' => 'MULTI2',
        'LBL' => 'MULTI',
        'MENU' => 'MULTI',
        'MENU_TITLE' => 'MULTI',
        'MENU_NAME' => 'MULTI',
        'URL' => 'MULTI',
        'DIRECTORY' => 'MULTI',
        'JAVASCRIPT' => 'MULTI');
    return read_files($search, $ms_cfg_file, $writable);
}

function read_files($search, $ms_cfg_file, $writable = '') {
    global $l;
    if (!is_writable($ms_cfg_file) && $writable != '') {
        msg_error($ms_cfg_file . " " . $l->g(1006) . ". " . $l->g(1147));
        return false;
    }

    if (file_exists($ms_cfg_file)) {
        $profil_data = read_configuration($ms_cfg_file, $search);
        return $profil_data;
    } else {
        return false;
    }
}

function msg($txt, $css, $closeid = false) {
    global $protectedPost;

    if (is_defined($protectedPost['close_alert'])) {
        $_SESSION['OCS']['CLOSE_ALERT'][$protectedPost['close_alert']] = 1;
    }

    if (!$_SESSION['OCS']['CLOSE_ALERT'][$closeid]) {
        echo "<center><div id='my-alert-" . $closeid . "' class='alert alert-" . $css . " fade in' role='alert'>";
        if ($closeid != false) {
            echo "<button type='button' class='close' data-dismiss='alert'><span aria-hidden='true'>×</span><span class='sr-only'>Close</span></button>";
        }
        echo $txt . "</div></center>";
        if ($closeid != false) {
            echo "<script>$('#my-alert-" . $closeid . "').on('closed.bs.alert', function () {
			 pag('" . $closeid . "','close_alert','close_msg');
			})</script>";

            echo open_form('close_msg');
            echo "<input type='hidden' name='close_alert' id='close_alert' value=''>";
            echo close_form();
        }
        if ($css == 'error') {
            addLog('MSG_' . $css, $txt);
        }
    }
}

function msg_info($txt, $close = false) {
    msg($txt, 'info', $close);
}

function msg_success($txt, $close = false) {
    msg($txt, 'success', $close);
}

function msg_warning($txt, $close = false) {
    msg($txt, 'warning', $close);
}

function msg_error($txt, $close = false) {
    msg($txt, 'danger', $close);
    return true;
}

function html_header($noJavascript = false) {
    if (!$_SESSION['OCS']['readServer']) {
        $value_theme['tvalue']['CUSTOM_THEME'] = DEFAULT_THEME;
        
    }
    if(is_null($value_theme)) {
        $value_theme = look_config_default_values('CUSTOM_THEME');
    }

    header("Pragma: no-cache");
    header("Expires: -1");
    header("Cache-control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-control: private", false);
    header("Content-type: text/html; charset=utf-8");
    echo '<!--DOCTYPE html-->
        <html>
			<head>
   				<meta charset="utf-8">
   				<meta http-equiv="X-UA-Compatible" content="IE=edge">
    			<meta name="viewport" content="width=device-width, initial-scale=1">

				<title>OCS Inventory</title>
				<link rel="shortcut icon" href="favicon.ico">
				<link rel="stylesheet" href="libraries/bootstrap/css/bootstrap.min.css">
				<link rel="stylesheet" href="libraries/bootstrap/css/bootstrap-theme.min.css">
				<link rel="stylesheet" href="libraries/select2/css/select2.min.css" />
				<link rel="stylesheet" href="css/dataTables-custom.css">
				<link rel="stylesheet" href="libraries/datatable/media/css/dataTables.bootstrap.css">
				<link rel="stylesheet" href="css/ocsreports.css">
        <link rel="stylesheet" href="css/bootstrap-datetimepicker.css">
				<link rel="stylesheet" href="css/header.css">
				<link rel="stylesheet" href="css/computer_details.css">
        <link rel="stylesheet" href="css/bootstrap-formhelpers.css">
				<link rel="stylesheet" href="css/forms.css">
                <link rel="stylesheet" href="themes/'.$value_theme['tvalue']['CUSTOM_THEME'].'/style.css">';

    if (!$noJavascript) {
        //js for graph
        echo '
        <script src="libraries/jquery/jquery.js" type="text/javascript"></script>
        <script src="libraries/jquery-migrate-1/jquery-migrate.min.js" type="text/javascript"></script>
        <script src="libraries/jquery-fileupload/jquery.ui.widget.min.js" type="text/javascript"></script>
        <script src="libraries/jquery-fileupload/jquery.iframe-transport.min.js" type="text/javascript"></script>
        <script src="libraries/jquery-fileupload/jquery.fileupload.min.js" type="text/javascript"></script>
        <script src="libraries/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="libraries/select2/js/select2.min.js" type="text/javascript"></script>
        <script src="js/bootstrap-custom.js" type="text/javascript"></script>
        <script src="js/bootstrap-datetimepicker.js" type="text/javascript"></script>
        <script src="js/bootstrap-datetimepicker-locale.js" type="text/javascript"></script>
        <script src="js/bootstrap-formhelpers.js" type="text/javascript"></script>
        <script src="libraries/charts.js/Chart.min.js" type="text/javascript"></script>
        <!-- js for Datatables -->
        <script src="libraries/datatable/media/js/jquery.dataTables.min.js" type="text/javascript"></script>
        <script src="libraries/datatable/media/js/dataTables.bootstrap.js" type="text/javascript"></script>
        <script src="js/function.js" type="text/javascript"></script>
        <script src="js/dataTables.conditionalPaging.js" type="text/javascript"></script>
        <script src="libraries/ace/js/ace.js" type="text/javascript"></script>';

        if (isset($_SESSION['OCS']['JAVASCRIPT'])) {
            foreach ($_SESSION['OCS']['JAVASCRIPT'] as $file) {
                echo "<script src='" . MAIN_SECTIONS_DIR_VISU . $file . "' type='text/javascript'></script>";
            }
        }
    }
    echo "</head>
        <body>";
}

function strip_tags_array($value = '') {
    if (is_object($value)) {
        $value = get_class($value);
        $value = strip_tags($value, "<p><b><i><font><br><center>");
        $value = "Objet de la classe " . $value;
        return $value;
    }

    $value = is_array($value) ? array_map('strip_tags_array', $value) : strip_tags($value, "<p><b><i><font><br><center>");

    if(!is_array($value)){
        // set double encode to false to avoid re encoding html entities
      $value = htmlspecialchars($value, ENT_QUOTES, $encoding = '', false);
    }

    return $value;
}

function open_form($form_name, $action = '', $more = '', $class = '') {
    if (!isset($_SESSION['OCS']['CSRFNUMBER']) || !is_numeric($_SESSION['OCS']['CSRFNUMBER']) || $_SESSION['OCS']['CSRFNUMBER'] >= CSRF) {
        $_SESSION['OCS']['CSRFNUMBER'] = 0;
    }
    $form = "<form class='" . $class . "' name='" . $form_name . "' id='" . $form_name . "' method='POST' action='" . $action . "' " . $more . " >";
    $csrf_value = sha1(microtime());
    $_SESSION['OCS']['CSRF'][$_SESSION['OCS']['CSRFNUMBER']] = $csrf_value;
    $form .= "<input type='hidden' name='CSRF_" . $_SESSION['OCS']['CSRFNUMBER'] . "' id='CSRF_" . $_SESSION['OCS']['CSRFNUMBER'] . "' value='" . $csrf_value . "'>";
    $_SESSION['OCS']['CSRFNUMBER'] ++;
    return $form;
}

function close_form() {
    return "</form>";
}

/*
 * Return a json from the website which help ocs determine if a new version is available
 */

function get_update_json() {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, UPDATE_JSON_URI);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $content = curl_exec($ch);
    curl_close($ch);

    if (!$content) {
        return false;
    }

    $json = json_decode($content);

    if ($json != null) {
        return $json;
    } else {
        return false;
    }
}

function formGroup($inputType, $inputName, $name, $size, $maxlength, $inputValue = "", $class = "", $optionsSelect = [], $arrayDisplayValues = [], $attrBalise = "", $groupAddon = ""){
	echo "<div class='form-group'>";
	echo "<label class='control-label col-sm-2' for='".$inputName."'>".$name."</label>";
	echo "<div class='col-sm-10'>";
  if($groupAddon != ""){
    echo "<div class='input-group'>";
  }

	if($inputType == "select"){
		echo "<select name='".$inputName."' id='".$inputName."' class='form-control ".$class."' ".$attrBalise.">";
		foreach ($optionsSelect as $option => $value){
			echo "<option value='".$option."' ".($inputValue == $option ? 'selected' : '').">".($arrayDisplayValues[$option] ? $arrayDisplayValues[$option] : $option)."</option>";
		}
		echo "</select>";
	} else {
        if($inputType == "checkbox") {
            echo "<input type='".$inputType."' name='".$inputName."' id='".$inputName."' size='".$size."' maxlength='".$maxlength."' value='".$inputValue."' class='".$class."' ".$attrBalise.">";
        } else {
            echo "<input type='".$inputType."' name='".$inputName."' id='".$inputName."' size='".$size."' maxlength='".$maxlength."' value='".$inputValue."' class='form-control ".$class."' ".$attrBalise.">";
        }
  }
  if($groupAddon != ""){
  	echo "<span class='input-group-addon' id='".$name."-addon'>".$groupAddon."</span>";
    echo "</div>";
  }
	echo "</div>";
	echo "</div>";
}

//fonction qui permet d'utiliser un calendrier dans un champ
function calendars($NameInputField,$DateFormat)
{
  $lang = $_SESSION['OCS']['LANGUAGE'];
  $calendar = "<i class=\"glyphicon glyphicon-calendar\"></i>";
  $calendar .= "<script type=\"text/javascript\">
      $(\".form_datetime\").datetimepicker({
          format: \"".$DateFormat."\",
          autoclose: true,
          todayBtn: true,
          language:\"".$lang."\",
          pickerPosition: \"bottom-left\"
      });
    </script>";
	return $calendar;
}



function modif_values($field_labels, $fields, $hidden_fields, $options = array(), $field_name="form-group") {
	global $l;

	$options = array_merge(array(
		'title' => null,
		'comment' => null,
		'button_name' => 'modif',
		'show_button' => true,
		'form_name' => 'CHANGE',
		'top_action' => null,
		'show_frame' => true
	), $options);

	if ($options['form_name'] != 'NO_FORM') {
		echo open_form($options['form_name'], '', '', 'form-horizontal');
	}

	if (is_array($field_labels)) {
		foreach ($field_labels as $key => $label) {

                    $field = $fields[$key];

                    if (is_array($field_name)){
                        $name = $field_name[$key];
                    } else {
                        $name = $field_name;
                    }

                    /**
                     * 0 = text
                     * 1 = textarea
                     * 2 = select
                     * 3 = hidden
                     * 4 = password
                     * 5 = checkbox
                     * 6 = text multiple
                     * 7 = hidden
                     * 8 = button
                     * 9 = link
                     * 10 = ?
                     * 11 = Radio
                     * 12 = QRCode
                     * 13 = Disabled
                     * 14 = Date
                     * 15 = number
                     **/
                    if($field['INPUT_TYPE'] == 0 ||
                            $field['INPUT_TYPE'] == 1 ||
                            $field['INPUT_TYPE'] == 6 ||
                            $field['INPUT_TYPE'] == 10||
                            $field['INPUT_TYPE'] == 14
                    ){
                            $inputType = 'text';
                    } else if($field['INPUT_TYPE'] == 2){
                            $inputType = 'select';
                    } else if($field['INPUT_TYPE'] == 3){
                            $inputType = 'hidden';
                    } else if($field['INPUT_TYPE'] == 4){
                            $inputType = 'password';
                    } else if($field['INPUT_TYPE'] == 5){
                            $inputType = 'checkbox';
                    } else if($field['INPUT_TYPE'] == 8){
                            $inputType = 'button';
                    } else if($field['INPUT_TYPE'] == 9) {
                        $inputType = 'link';
                    } else if($field['INPUT_TYPE'] == 13){
                        $inputType = 'disabled';
                    } else if($field['INPUT_TYPE'] == 12){
                        $inputType = 'qrcode';
                    } elseif($field['INPUT_TYPE'] == 11){
                        $inputType = 'radio';
                    } elseif($field['INPUT_TYPE'] == 15){
                        $inputType = 'number';
                    } else {
                            $inputType = 'hidden';
                    }

                    echo "<div class='$name'>";
                        echo "<label for='".$field['INPUT_NAME']."' class='col-sm-2 control-label'>".$label."</label>";
                        echo "<div class='col-sm-10'>";

                                $field_checkbox = array();
                                if($inputType == 'text'){
                                    if($field['INPUT_TYPE'] == 14){
                                        echo "<div class='input-group date form_datetime'>";
                                    }else{
                                        echo "<div class='input-group'>";
                                    }
                                    echo "<input type='".$inputType."' name='".$field['INPUT_NAME']."' id='".$field['INPUT_NAME']."' value='".$field['DEFAULT_VALUE']."' class='form-control' ".$field['CONFIG']['JAVASCRIPT'].">";
                                    if($field['COMMENT_AFTER'] == ""){
                                      echo "</div>";
                                    }
                                }else if($inputType == 'number'){
                                    echo "<div class='input-group'>";
                                    echo "<input type='".$inputType."' name='".$field['INPUT_NAME']."' id='".$field['INPUT_NAME']."' value='".$field['DEFAULT_VALUE']."' min='1' class='form-control' ".$field['CONFIG']['JAVASCRIPT'].">";
                                    if($field['COMMENT_AFTER'] == ""){
                                      echo "</div>";
                                    }
                                }else if($inputType == 'disabled'){
                                    echo "<div class='input-group'>";
                                    echo "<input type='text' name='".$field['INPUT_NAME']."' id='".$field['INPUT_NAME']."' value='".$field['DEFAULT_VALUE']."' class='form-control' ".$field['CONFIG']['JAVASCRIPT']." readonly>";
                                    if($field['COMMENT_AFTER'] == ""){
                                      echo "</div>";
                                    }
                                }else if($inputType == 'select'){
                                    echo "<div class='input-group'>";
                                    echo "<select name='".$field['INPUT_NAME']."' class='form-control' ".$field['CONFIG']['JAVASCRIPT'].">";
                                    echo "<option value='' selected></option>";
                                    foreach ($field['DEFAULT_VALUE'] as $key => $value){
                                            if($key == $field['CONFIG']['SELECTED_VALUE']){
                                                echo "<option value='".$key."' selected>".$value."</option>";
                                            }else{
                                                echo "<option value='".$key."'>".$value."</option>";
                                            }
                                    }
                                    echo "</select>";
                                    if($field['COMMENT_AFTER'] == ""){
                                      echo "</div>";
                                    }
                                } else if($inputType == 'checkbox'){
                                  if($field["CONFIG"]["SELECTED_VALUE"] != ''){
                                      $field_check = explode("&&&", $field["CONFIG"]["SELECTED_VALUE"]);
                                      foreach($field_check as $keys => $values){
                                        if($values != ''){
                                          $field_checkbox[$values] = $values;
                                        }
                                      }
                                  }
                                  echo "<div>";
                                  foreach ($field['DEFAULT_VALUE'] as $key => $value){
                                      if(array_key_exists($value, $field_checkbox)){
                                          echo "<div><input style='display:initial;width:20px;height: 14px;'  type='".$inputType."' name='".$field['INPUT_NAME']."_".$value."' value='".$key."' id='".$field['INPUT_NAME']."_".$value."' class='form-control' ".$field['CONFIG']['JAVASCRIPT']." checked> $value </div> ";
                                      }else{
                                          echo "<div><input style='display:initial;width:20px;height: 14px;' type='".$inputType."' name='".$field['INPUT_NAME']."_".$value."' value='".$key."' id='".$field['INPUT_NAME']."_".$value."' class='form-control' ".$field['CONFIG']['JAVASCRIPT']."> $value </div>";
                                      }
                                  }
                                  if($field['COMMENT_AFTER'] == ""){
                                    echo "</div>";
                                  }
                                } else if($inputType == 'radio'){
                                  if($field["CONFIG"]["SELECTED_VALUE"] != ''){
                                      $field_radio = explode("&&&", $field["CONFIG"]["SELECTED_VALUE"]);
                                      foreach($field_radio as $keys => $values){
                                          if($values != ''){
                                            $field_radio[$values] = $values;
                                          }
                                      }
                                  }
                                  echo "<div>";
                                  foreach ($field['DEFAULT_VALUE'] as $key => $value){
                                      if(array_key_exists($key, $field_radio)){
                                          echo "<div><input style='display:initial;width:20px;height: 14px;'  type='".$inputType."' name='".$field['INPUT_NAME']."' value='".$key."' id='".$field['INPUT_NAME']."_".$value."' class='form-control' ".$field['CONFIG']['JAVASCRIPT']." checked> $value </div> ";
                                      }else{
                                          echo "<div><input style='display:initial;width:20px;height: 14px;' type='".$inputType."' name='".$field['INPUT_NAME']."' value='".$key."' id='".$field['INPUT_NAME']."_".$value."' class='form-control' ".$field['CONFIG']['JAVASCRIPT'].">$value </div>";
                                      }
                                  }
                                  if($field['COMMENT_AFTER'] == ""){
                                    echo "</div>";
                                  }
                                } else if( $inputType == 'button' || $inputType == 'link'){
                                    echo "<a href='".$field['DEFAULT_VALUE']."' class='".($inputType == 'button') ? 'btn' : ''."' ".$field['CONFIG']['JAVASCRIPT']."></a>";
                                } else if($inputType == 'qrcode'){
                                    echo "<img src='" . $field['CONFIG']['DEFAULT'] . "' ".$field['CONFIG']['SIZE']." ".$field['CONFIG']['JAVASCRIPT'].">";
                                } else{
                                    echo "<input type='".$inputType."' name='".$field['INPUT_NAME']."' id='".$field['INPUT_NAME']."' value='".$field['DEFAULT_VALUE']."' class='form-control' ".$field['CONFIG']['JAVASCRIPT'].">";
                                }

                                if($field['COMMENT_AFTER'] != ""){
                                    echo "<span class='input-group-addon' id='".$field['INPUT_NAME']."-addon'>".$field['COMMENT_AFTER']."</span>";
                                    echo "</div>";
                                }
                        echo "</div>";
                    echo "</div>";

		}
	}

	if ($options['show_button'] === 'BUTTON') {
		echo '<div class="form-buttons">';
		echo '<input type="submit" name="Valid_'.$options['button_name'].'" value="'.$l->g(13).'"/>';
		echo '</div>';
	} else if ($options['show_button']) {
		echo '<div class="form-buttons">';
		echo '<input type="submit" name="Valid_'.$options['button_name'].'" class="btn btn-success" value="'.$l->g(1363).'"/>';
		echo '<input type="submit" name="Reset_'.$options['button_name'].'" class="btn btn-danger" value="'.$l->g(1364).'"/>';
		echo '</div>';
	}

	if ($hidden_fields) {
		foreach ($hidden_fields as $key => $value) {
			echo "<input type='hidden' name='".$key."' id='".$key."' value='".htmlspecialchars($value, ENT_QUOTES)."'>";
		}
	}

	if ($options['form_name'] != 'NO_FORM') {
		echo close_form();
	}
}

/**
 * Test if a var is defined && contains something (not only blank char)
 * @param type $var var to test
 * @return boolean result
 */
function is_defined(&$var) {
    $result = false;

    // var is set ?
    if (isset($var)) {
        // PHP 5.3 hack : can't empty(trim($var))
        // Don't trim if it's an array
        if(!is_array($var)){
            $maVar = trim($var);
        }else{
            $maVar = array_filter($var);
        }

        // Var contains something else than blank char ?
        if (!empty($maVar)) {
            $result = true;
        }
    }
    return $result;
}

/**
 * Check for all php dependencies in a function
 * Called on install and update
 */
function check_requirements(){

    global $l;

    //messages lbl
    $msg_lbl = array();
    $msg_lbl['info'] = array();
    $msg_lbl['warning'] = array();
    $msg_lbl['error'] = array();
    //msg=you have to update database
    if (isset($fromAuto) && $fromAuto == true) {
        $msg_lbl['info'][] = $l->g(2031) . " " . $valUpd["tvalue"] . " " . $l->g(2032) . " (" . GUI_VER . "). " . $l->g(2033);
    }
    //msg=your config file doesn't exist
    if (isset($fromdbconfig_out) && $fromdbconfig_out == true) {
        $msg_lbl['info'][] = $l->g(2034);
    }
    //max to upload
    $pms = "post_max_size";
    $umf = "upload_max_filesize";
    $valTpms = ini_get($pms);
    $valTumf = ini_get($umf);
    $valBpms = return_bytes($valTpms);
    $valBumf = return_bytes($valTumf);
    if ($valBumf > $valBpms) {
        $MaxAvail = trim(mb_strtoupper($valTpms), "M");
    } else {
        $MaxAvail = trim(mb_strtoupper($valTumf), "M");
    }
    $msg_lbl['info'][] = $l->g(2040) . " " . $MaxAvail . $l->g(1240) . "<br>" . $l->g(2041) . "<br><br><font color=red>" . $l->g(2102) . "</font>";
    //msg=no php-session function
    if (!function_exists('session_start')) {
        $msg_lbl['error'][] = $l->g(2035);
    }
    //msg= no mysqli_connect function
    if (!function_exists('mysqli_real_connect')) {
        $msg_lbl['error'][] = $l->g(2037);
    }
    if ((file_exists(CONF_MYSQL) && !is_writable(CONF_MYSQL)) || (!file_exists(CONF_MYSQL) && !is_writable(CONF_MYSQL_DIR))) {
        $msg_lbl['error'][] = "<br><center><font color=red><b>" . $l->g(2052) . "</b></font></center>";
    }
    //msg for phpversion
    if (version_compare(phpversion(), '5.4', '<')) {
        $msg_lbl['warning'][] = $l->g(2113) . " " . phpversion() . " ) ";
    }
    if (!function_exists('xml_parser_create')) {
        $msg_lbl['warning'][] = $l->g(2036);
    }
    if (!function_exists('imagefontwidth')) {
        $msg_lbl['warning'][] = $l->g(2038);
    }
    if (!function_exists('openssl_open')) {
        $msg_lbl['warning'][] = $l->g(2039);
    }
    if (!function_exists('curl_version')) {
        $msg_lbl['warning'][] = $l->g(2125);
    }
    // Check if var lib directory is writable
    if (is_writable(VARLIB_DIR)) {
        if (!file_exists(VARLIB_DIR . "/download")) {
            mkdir(VARLIB_DIR . "/download");
        }
        if (!file_exists(VARLIB_DIR . "/logs")) {
            mkdir(VARLIB_DIR . "/logs");
        }
        if (!file_exists(VARLIB_DIR . "/scripts")) {
            mkdir(VARLIB_DIR . "/scripts");
        }
    } else {
        $msg_lbl['warning'][] = "Var lib dir should be writable : " . VARLIB_DIR;
    }
    // Check if ocsreports is writable
    if (!is_writable(CONF_MYSQL_DIR)) {
        $msg_lbl['warning'][] = "Ocs reports' dir should be writable : " . CONF_MYSQL_DIR;
    }
    //show messages
    foreach ($msg_lbl as $k => $v) {
        $show = implode("<br>", $v);
        if ($show != '') {
            call_user_func_array("msg_" . $k, array($show));
            //stop if error
            if ($k == "error") {
                die();
            }
        }
    }

}

/**
 * From a byte value return an int
 *
 * @param type $val
 * @return int
 */
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    switch ($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

?>
