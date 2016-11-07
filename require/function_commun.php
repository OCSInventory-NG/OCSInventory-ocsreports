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

function dbconnect($server, $compte_base, $pswd_base, $db = DB_NAME) {
    error_reporting(E_ALL & ~E_NOTICE);
    mysqli_report(MYSQLI_REPORT_STRICT);
    //$link is ok?
    try {
        $link = mysqli_connect($server, $compte_base, $pswd_base);
    } catch (Exception $e) {
        if (mysqli_connect_errno()) {
            return "ERROR: MySql connection problem " . $e->getCode() . "<br>" . $e->getMessage();
        }
    }
    //database is ok?
    if (!mysqli_select_db($link, $db)) {
        return "NO_DATABASE";
    }
    //force UTF-8
    mysqli_query($link, "SET NAMES 'utf8'");
    //sql_mode => not strict
    mysqli_query($link, "SET sql_mode='NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");

    return $link;
}

/* * *********************************END SQL FUNCTION***************************************** */

function addLog($type, $value = "", $lbl_sql = '') {
    if ($_SESSION['OCS']['LOG_GUI'] == 1) {
        if (is_writable(LOG_FILE)) {
            $logHandler = fopen(LOG_FILE, "a");
            $dte = getDate();
            $date = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $dte["mday"], $dte["mon"], $dte["year"], $dte["hours"], $dte["minutes"], $dte["seconds"]);
            if ($lbl_sql != '') {
                $value = $lbl_sql . ' => ' . $value;
            }
            $towite = $_SESSION['OCS']["loggeduser"] . ";" . $date . ";" . DB_NAME . ";" . $type . ";" . $value . ";" . $_SERVER['REMOTE_ADDR'] . ";\n";
            fwrite($logHandler, $towite);
            fclose($logHandler);
        }
    }
}

function dateTimeFromMysql($v) {
    global $l;

    $sql = "SELECT date_format('%s', '%s %%H:%%i:%%S') as dt";
    $arg = array($v, $l->g(269));
    $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
    $ret = mysqli_fetch_array($result);
    return $ret['dt'];
}

function reloadform_closeme($form = '', $close = false) {
    echo "<script type='text/javascript'>";
    if ($form != '') {
        echo "window.opener.document.forms['" . $form . "'].submit();";
    }
    if ($close) {
        echo "self.close();";
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
        echo "<div id='my-alert-" . $closeid . "' class='center-block alert alert-" . $css . " fade in' role='alert'>";
        if ($closeid != false) {
            echo "<button type='button' class='close' data-dismiss='alert'><span aria-hidden='true'>×</span><span class='sr-only'>Close</span></button>";
        }
        echo $txt . "</div>";
        if ($closeid != false) {
            echo "<script type='text/javascript'>$('#my-alert-" . $closeid . "').on('closed.bs.alert', function () {
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
    header("Pragma: no-cache");
    header("Expires: -1");
    header("Cache-control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-control: private", false);
    header("Content-type: text/html; charset=utf-8");
    echo '<!DOCTYPE html>
        <html>
			<head>
   				<meta charset="utf-8">
   				<meta http-equiv="X-UA-Compatible" content="IE=edge">
    			<meta name="viewport" content="width=device-width, initial-scale=1">

				<title>OCS Inventory</title>
				<link rel="shortcut icon" href="favicon.ico">
				<link rel="stylesheet" href="libraries/bootstrap/css/bootstrap.min.css">
				<link rel="stylesheet" href="libraries/bootstrap/css/bootstrap-theme.min.css">
				<link rel="stylesheet" href="css/bootstrap-custom.css">
				<link rel="stylesheet" href="css/dataTables-custom.css">
				<link rel="stylesheet" href="libraries/datatable/media/css/dataTables.bootstrap.min.css">
				<link rel="stylesheet" href="css/ocsreports.css">
				<link rel="stylesheet" href="css/header.css">
				<link rel="stylesheet" href="css/computer_details.css">
				<link rel="stylesheet" href="css/forms.css">';
    if (!$noJavascript) {
        incPicker();

        //js for graph
        echo '
        <script src="libraries/jquery/jquery.js" type="text/javascript"></script>
        <script src="libraries/jquery-migrate-1/jquery-migrate.min.js" type="text/javascript"></script>
        <script src="libraries/jquery-fileupload/jquery.ui.widget.min.js" type="text/javascript"></script>
        <script src="libraries/jquery-fileupload/jquery.iframe-transport.min.js" type="text/javascript"></script>
        <script src="libraries/jquery-fileupload/jquery.fileupload.min.js" type="text/javascript"></script>
        <script src="libraries/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="js/bootstrap-custom.js" type="text/javascript"></script>
        <script src="libraries/raphael/raphael.min.js" type="text/javascript"></script>
        <script src="libraries/elycharts/elycharts.min.js" type="text/javascript"></script>
        <!-- js for Datatables -->
        <script src="libraries/datatable/media/js/jquery.dataTables.min.js" type="text/javascript"></script>
        <script src="libraries/datatable/media/js/dataTables.bootstrap.js" type="text/javascript"></script>
        <script src="js/function.js" type="text/javascript"></script>';

        if (isset($_SESSION['OCS']['JAVASCRIPT'])) {
            foreach ($_SESSION['OCS']['JAVASCRIPT'] as $file) {
                echo "<script src='" . MAIN_SECTIONS_DIR . $file . "' type='text/javascript'></script>";
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
    return $value;
}

function open_form($form_name, $action = '', $more = '', $class = '') {
    if (!isset($_SESSION['OCS']['CSRFNUMBER']) || !is_numeric($_SESSION['OCS']['CSRFNUMBER']) || $_SESSION['OCS']['CSRFNUMBER'] >= CSRF) {
        $_SESSION['OCS']['CSRFNUMBER'] = 0;
    }
    $form = "<form name='" . $form_name . "' id='" . $form_name . "' method='POST'";
    if (!empty($class)) {
        $form .= " class='" . $class . "'";
    }
    if (!empty($action)) {
        $form .= " action='" . $action . "'";
    }
    $form .= " " . $more . " >";

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
    $stream = stream_context_create(array('http' =>
        array(
            'timeout' => 1, // Timeout after 1 seconds
        )
    ));

    $content = @file_get_contents(UPDATE_JSON_URI, false, $stream);
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

function formGroup($inputType, $inputName, $name, $size, $maxlength, $inputValue = "", $class = "", $optionsSelect = array(), $arrayDisplayValues = array(), $attrBalise = "", $groupAddon = "") {
    echo "<div class='form-group'>";
    echo "<label class='control-label col-sm-2' for='" . $inputName . "'>" . $name . "</label>";
    echo "<div class='col-sm-10'>";
    if ($inputType == "select") {
        echo "<div class='input-group'>";
        echo "<select name='" . $inputName . "' id='" . $inputName . "' class='form-control " . $class . "' " . $attrBalise . ">";
        foreach ($optionsSelect as $option => $value) {
            echo "<option value='" . $option . "' " . ($inputValue[$inputName] == $option ? 'selected' : '') . ">" . ($arrayDisplayValues[$option] ? $arrayDisplayValues[$option] : $option) . "</option>";
        }
        echo "</select>";
        if ($groupAddon != "") {
            echo "<span class='input-group-addon' id='" . $name . "-addon'>" . $groupAddon . "</span>";
        }
        echo "</div>";
    } else {
        echo "<div class='input-group'>";
        echo "<input type='" . $inputType . "' name='" . $inputName . "' id='" . $inputName . "' size='" . $size . "' maxlength='" . $maxlength . "' value='" . $inputValue . "' class='form-control " . $class . "' " . $attrBalise . ">";
        if ($groupAddon != "") {
            echo "<span class='input-group-addon' id='" . $name . "-addon'>" . $groupAddon . "</span>";
        }
        echo "</div>";
    }
    echo "</div>";
    echo "</div>";
}

//fonction qui permet d'utiliser un calendrier dans un champ
function calendars($NameInputField, $DateFormat) {
    return "<a href=\"javascript:NewCal('" . $NameInputField . "','" . $DateFormat . "',false,24,null);\"><span class=\"glyphicon glyphicon-calendar\"></span></a>";
}

function modif_values($field_labels, $fields, $hidden_fields, $options = array()) {
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
             * */
            switch ($field['INPUT_TYPE']) {
                case 0:
                case 1:
                case 6:
                case 10:
                    $inputType = 'text';
                    break;
                case 2:
                case 11:
                    $inputType = 'select';
                    break;
                case 3:
                    $inputType = 'hidden';
                    break;
                case 4:
                    $inputType = 'password';
                    break;
                case 5:
                    $inputType = 'checkbox';
                    break;
                case 8:
                    $inputType = 'button';
                    break;
                case 9:
                    $inputType = 'link';
                    break;
                case 12:
                    $inputType = 'qrcode';
                    break;
                case 13:
                    $inputType = 'disabled';
                    break;
                default:
                    $inputType = 'hidden';
                    break;
            }

            echo "<div class='form-group'>";
            echo "<label for='" . $field['INPUT_NAME'] . "' class='col-sm-2 control-label'>" . $label . "</label>";
            echo "<div class='col-sm-10'>";
            echo "<div class='input-group'>";

            if ($inputType == 'text') {
                echo "<input type='" . $inputType . "' name='" . $field['INPUT_NAME'] . "' id='" . $field['INPUT_NAME'] . "' value='" . $field['DEFAULT_VALUE'] . "' class='form-control' " . $field['CONFIG']['JAVASCRIPT'] . ">";
            } else if ($inputType == 'disabled') {
                echo "<input type='text' name='" . $field['INPUT_NAME'] . "' id='" . $field['INPUT_NAME'] . "' value='" . $field['DEFAULT_VALUE'] . "' class='form-control' " . $field['CONFIG']['JAVASCRIPT'] . " readonly>";
            } else if ($inputType == 'select') {
                echo "<select name='" . $field['INPUT_NAME'] . "' class='form-control' " . $field['CONFIG']['JAVASCRIPT'] . ">";
                foreach ($field['DEFAULT_VALUE'] as $key => $value) {
                    echo "<option value='" . $key . "'>" . $value . "</option>";
                }
                echo "</select>";
            } else if ($inputType == 'checkbox') {
                foreach ($field['DEFAULT_VALUE'] as $k => $v) {
                    echo "$label <input type='" . $inputType . "' name='" . $field['INPUT_NAME'] . "' id='" . $field['INPUT_NAME'] . "' class='form-control' " . $field['CONFIG']['JAVASCRIPT'] . ">";
                }
            } else if ($inputType == 'button' || $inputType == 'link') {
                echo "<a href='" . $field['DEFAULT_VALUE'] . "' class='" . ($inputType == 'button') ? 'btn' : '' . "' " . $field['CONFIG']['JAVASCRIPT'] . "></a>";
            } else if ($inputType == 'qrcode') {
                echo "<img src='" . $field['CONFIG']['DEFAULT'] . "' " . $field['CONFIG']['SIZE'] . " " . $field['CONFIG']['JAVASCRIPT'] . ">";
            } else {
                echo "<input type='" . $inputType . "' name='" . $field['INPUT_NAME'] . "' id='" . $field['INPUT_NAME'] . "' value='" . $field['DEFAULT_VALUE'] . "' class='form-control' " . $field['CONFIG']['JAVASCRIPT'] . ">";
            }

            if ($field['COMMENT_AFTER'] != "") {
                echo "<span class='input-group-addon' id='" . $field['INPUT_NAME'] . "-addon'>" . $field['COMMENT_AFTER'] . "</span>";
            }

            echo "</div>";
            echo "</div>";
            echo "</div>";
        }
    }

    if ($options['show_button'] === 'BUTTON') {
        echo '<div class="form-buttons">';
        echo '<input type="submit" name="Valid_' . $options['button_name'] . '" value="' . $l->g(13) . '"/>';
        echo '</div>';
    } else if ($options['show_button']) {
        echo '<div class="form-buttons">';
        echo '<input type="submit" name="Valid_' . $options['button_name'] . '" class="btn btn-success" value="' . $l->g(1363) . '"/>';
        echo '<input type="submit" name="Reset_' . $options['button_name'] . '" class="btn btn-danger" value="' . $l->g(1364) . '"/>';
        echo '</div>';
    }

    if ($hidden_fields) {
        foreach ($hidden_fields as $key => $value) {
            echo "<input type='hidden' name='" . $key . "' id='" . $key . "' value='" . htmlspecialchars($value, ENT_QUOTES) . "'>";
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
        if (!is_array($var)) {
            $maVar = trim($var);
        } else {
            $maVar = array_filter($var);
        }

        // Var contains something else than blank char ?
        if (!empty($maVar)) {
            $result = true;
        }
    }
    return $result;
}

?>