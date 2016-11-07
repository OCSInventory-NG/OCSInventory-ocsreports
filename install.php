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
set_time_limit(0); //Throw an error if Safe_mode is on, which was removed in 5.4
error_reporting(E_ALL & ~E_NOTICE);
require_once('require/fichierConf.class.php');
require_once('require/function_commun.php');
require_once('require/function_table_html.php');
require_once('var.php');
//show header
html_header(true);

function printEnTeteInstall($ent) {
    echo "<h3>$ent</h3>";
}

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val) - 1});
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

/**
 * execute sql file
 * @global type $l
 * @param type $fichier
 * @param type $link
 * @return boolean
 */
function exec_fichier_sql($fichier, $link) {
    global $l;
    $db_file = $fichier;
    $dbf_handle = @fopen($db_file, "r");
    echo "<br>";
    if (!$dbf_handle) {
        msg_error($l->g(2112) . " : " . $fichier);
        return true;
    } else {
        if (filesize($db_file) > 0) {
            $sql_query = fread($dbf_handle, filesize($db_file));
            fclose($dbf_handle);
            $data_sql = explode(";", $sql_query);
            foreach ($data_sql as $v) {
                if (is_defined($v)) {
                    echo ".";
                    ob_flush();
                    flush();
                    mysql2_query_secure($v, $link);
                }
            }
            return false;
        }
        return true;
    }
}

//choose default language
if (!isset($_SESSION['OCS']['LANGUAGE']) || !isset($_SESSION['OCS']["LANGUAGE_FILE"])) {
    if (isset($_COOKIE['LANG'])) {
        $_SESSION['OCS']['LANGUAGE'] = $_COOKIE['LANG'];
    }
    if (!isset($_COOKIE['LANG'])) {
        $_SESSION['OCS']['LANGUAGE'] = DEFAULT_LANGUAGE;
    }
    $_SESSION['OCS']["LANGUAGE_FILE"] = new language($_SESSION['OCS']['LANGUAGE']);
}
$l = $_SESSION['OCS']["LANGUAGE_FILE"];
//OCS INSTALLATION
printEnTeteInstall($l->g(2030));
echo "<br>";
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
$msg_lbl['info'][] = $l->g(2040) . " " . $MaxAvail . $l->g(1240) . "<br>" . $l->g(2041) . "<br><br><span class=red>" . $l->g(2102) . "</span>";
//msg=no php-session function
if (!function_exists('session_start')) {
    $msg_lbl['error'][] = $l->g(2035);
}
//msg= no mysqli_connect function
if (!function_exists('mysqli_connect')) {
    $msg_lbl['error'][] = $l->g(2037);
}
if ((file_exists(CONF_MYSQL) && !is_writable(CONF_MYSQL)) || (!file_exists(CONF_MYSQL) && !is_writable(ETC_DIR))) {
    $msg_lbl['error'][] = "<br><div class='class='center-block alert alert-danger'><b>" . $l->g(2052) . "</b></div>";
}
//msg for phpversion
if (version_compare(phpversion(), '5.4.0', '<')) {
    $msg_lbl['warning'][] = $l->g(2113) . " " . phpversion() . " ) ";
}
if (!class_exists('SoapClient')) {
    $msg_lbl['warning'][] = $l->g(6006);
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
if (!is_writable(ETC_DIR)) {
    $msg_lbl['warning'][] = "Ocs reports' dir should be writable : " . ETC_DIR;
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
//post the first form
if (isset($_POST["name"])) {

    $link = dbconnect($_POST["host"], $_POST["name"], $_POST["pass"], $_POST["database"]);
    if (mysqli_connect_errno()) {
        $firstAttempt = false;
        msg_error($l->g(2001) . " " . $l->g(249) .
                " (" . $l->g(2010) . "=" . $_POST["host"] .
                " " . $l->g(2011) . "=" . $_POST["name"] .
                " " . $l->g(2014) . "=" . $_POST["pass"] .
                ")<br>" . $link);
    } else {
        //if database not exist
        if ($link == "NO_DATABASE") {
            $link = mysqli_connect($_POST["host"], $_POST["name"], $_POST["pass"]);
            //have to execute new install file
            $db_file = "files/ocsbase_new.sql";
            if (!mysqli_query($link, "CREATE DATABASE " . $_POST['database'] . " CHARACTER SET utf8 COLLATE utf8_bin;") || !mysqli_query($link, "USE " . $_POST['database']) || !mysqli_query($link, "GRANT ALL PRIVILEGES ON " . $_POST['database'] . ".* TO ocs IDENTIFIED BY 'ocs'") || !mysqli_query($link, "GRANT ALL PRIVILEGES ON " . $_POST['database'] . ".* TO ocs@localhost IDENTIFIED BY 'ocs'")) {
                $error = mysqli_errno($link);
            }
            $name_connect = "ocs";
            $pass_connect = 'ocs';
        } else {
            //update
            $res = mysql2_query_secure("select tvalue from config where name='GUI_VERSION'", $link);
            $item = mysqli_fetch_object($res);
            if ($item->tvalue < 7006) {
                $db_file = "files/ocsbase.sql";
                $name_connect = $_POST["name"];
                $pass_connect = $_POST["pass"];
            } else {
                msg_info($l->g(2105));
                $ch = @fopen(CONF_MYSQL, "w");
                fwrite($ch, "<?php\n");
                fwrite($ch, "define(\"DB_NAME\", \"" . $_POST['database'] . "\");\n");
                fwrite($ch, "define(\"SERVER_READ\",\"" . $_POST["host"] . "\");\n");
                fwrite($ch, "define(\"SERVER_WRITE\",\"" . $_POST["host"] . "\");\n");
                fwrite($ch, "define(\"COMPTE_BASE\",\"" . $_POST["name"] . "\");\n");
                fwrite($ch, "define(\"PSWD_BASE\",\"" . $_POST["pass"] . "\");\n");
                fwrite($ch, "?>");
                fclose($ch);
                msg_success("<b><a href='index.php'>" . $l->g(2051) . "</a></b>");
                unset($_SESSION['OCS']['SQL_BASE_VERS']);
                die();
            }
        }

        if (!$error) {
            ob_flush();
            flush();
            msg_info($l->g(2030));
            exec_fichier_sql($db_file, $link);
            $ch = @fopen(CONF_MYSQL, "w");
            fwrite($ch, "<?php\n");
            fwrite($ch, "define(\"DB_NAME\", \"" . $_POST['database'] . "\");\n");
            fwrite($ch, "define(\"SERVER_READ\",\"" . $_POST["host"] . "\");\n");
            fwrite($ch, "define(\"SERVER_WRITE\",\"" . $_POST["host"] . "\");\n");
            fwrite($ch, "define(\"COMPTE_BASE\",\"" . $name_connect . "\");\n");
            fwrite($ch, "define(\"PSWD_BASE\",\"" . $pass_connect . "\");\n");
            fwrite($ch, "?>");
            fclose($ch);
            if (!mysqli_connect($_POST["host"], $name_connect, $pass_connect)) {
                if (mysqli_connect_errno() == 0) {
                    echo "<br><div class='class='center-block alert alert-danger'><b>" . $l->g(2043) .
                    " " . $l->g(2044) .
                    "</b><br></div>";
                    die();
                } else {
                    echo "<br><div class='class='center-block alert alert-danger'><b>" . $l->g(2043) .
                    " (" . $l->g(2017) .
                    " " . $l->g(2010) .
                    "=" . $_POST["host"] .
                    " " . $l->g(2011) .
                    "=ocs " . $l->g(2014) .
                    "=ocs)"
                    . "</b><br></div>";
                }

                echo "<br><div class='class='center-block alert alert-danger'><b>" . $l->g(2065) . "</b></div>";
                unlink(CONF_MYSQL);
            } else {
                msg_success("<b>" . $l->g(2050) . "</b><br><br><b><a href='index.php'>" . $l->g(2051) . "</a></b>");
                unset($_SESSION['OCS']['SQL_BASE_VERS']);
            }
            die();
        } else {
            msg_error($l->g(2115));
        }
    }
    //die();
}
//use values in mysql config file
if (is_readable(CONF_MYSQL)) {
    require(CONF_MYSQL);
    if (defined('COMPTE_BASE')) {
        $valNme = COMPTE_BASE;
    } else {
        $valNme = '';
    }
    if (defined('PSWD_BASE')) {
        $valPass = PSWD_BASE;
    } else {
        $valPass = '';
    }
    if (defined('SERVER_WRITE')) {
        $valServ = SERVER_WRITE;
    } else {
        $valServ = '';
    }
    if (defined('DB_NAME')) {
        $valdatabase = DB_NAME;
    } else {
        $valdatabase = '';
    }
}
//show first form
$form_name = 'fsub';
$name_field = array("name", "pass", "database", "host");
$tab_name = array($l->g(247) . ": ", $l->g(248) . ": ", $l->g(1233) . ":", $l->g(250) . ":");
$type_field = array(0, 4, 0, 0);
if (isset($_POST["name"], $_POST["pass"], $_POST["database"], $_POST["host"])) {
    $value_field = array($_POST["name"], $_POST["pass"], $_POST["database"], $_POST["host"]);
} else {
    $value_field = array($valNme, $valPass, $valdatabase, $valServ);
}
$tab_typ_champ = show_field($name_field, $type_field, $value_field);
tab_modif_values($tab_name, $tab_typ_champ, $tab_hidden, array(
    'button_name' => 'INSTALL',
    'show_button' => 'BUTTON',
    'form_name' => $form_name
));
die();
?>