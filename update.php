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
if (!isset($debut)) {
    die('FORBIDDEN');
}
error_reporting(E_ALL & ~E_NOTICE);
if (isset($_GET['debug'])) {
    $_SESSION['OCS']['DEBUG'] = 'ON';
}
require_once('require/fichierConf.class.php');
require_once('require/function_commun.php');
require_once('require/function_files.php');
require_once('var.php');
html_header(true);

if (!isset($_SESSION['OCS']['LANGUAGE']) || !isset($_SESSION['OCS']["LANGUAGE_FILE"])) {
    if (isset($_COOKIE['LANG'])) {
        $_SESSION['OCS']['LANGUAGE'] = $_COOKIE['LANG'];
    }
    if (!isset($_COOKIE['LANG'])) {
        $_SESSION['OCS']['LANGUAGE'] = DEFAULT_LANGUAGE;
    }
    $_SESSION['OCS']["LANGUAGE_FILE"] = new language($_SESSION['OCS']['LANGUAGE']);
}

/**
 * Check for requierements
 */
//messages lbl
$msg_lbl = array();
$msg_lbl['info'] = array();
$msg_lbl['warning'] = array();
$msg_lbl['error'] = array();

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

$l = $_SESSION['OCS']["LANGUAGE_FILE"];
$version_database = $_SESSION['OCS']['SQL_BASE_VERS'];
$form_name = 'form_update';
$rep_maj = 'files/update/';

//search all sql files for update
$list_fichier = scanDirectory($rep_maj, "sql");
echo "<form name='" . $form_name . "' id='" . $form_name . "' method='POST'>";
$msg_info[] = $l->g(2057);

if (GUI_VER < $_SESSION['OCS']['SQL_BASE_VERS']) {
    msg_info(implode("<br />", $msg_info));
    msg_error($l->g(2107) . "<br>" . $l->g(2108) . "<br>" . $l->g(2109) . ":" . $version_database . "=>" . $l->g(2110) . ":" . GUI_VER);
    echo "</form>";
    require_once('require/footer.php');
    die();
}

$msg_info[] = $l->g(2109) . ":" . $version_database . "=>" . $l->g(2110) . ":" . GUI_VER;
msg_info(implode("<br />", $msg_info));

echo "<br><input type=submit name='update' value='" . $l->g(2111) . "' class='btn'>";

if (isset($_POST['update'])) {
    while ($version_database < GUI_VER) {
        $version_database++;
        if (in_array($version_database . ".sql", $list_fichier['name'])) {
            if ($_SESSION['OCS']['DEBUG'] == 'ON') {
                msg_success("Mise à jour effectuée: " . $version_database . ".sql");
            }
            exec_fichier_sql($rep_maj . '/' . $version_database . ".sql");
            $sql = "update config set tvalue='%s' where name='GUI_VERSION'";
            $arg = $version_database;
            $res_column = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
            $_SESSION['OCS']['SQL_BASE_VERS'] = $version_database;
        } else {
            msg_error($l->g(2114) . " " . $version_database);
            die();
        }
    }
    msg_success($l->g(1121));
    echo "<br><br><br><b><a href='index.php'>" . $l->g(2051) . "</a></b>";

    //Logout after update(s)
    //Contrib of FranciX (http://forums.ocsinventory-ng.org/viewtopic.php?pid=41923#p41923)
    if ($_SESSION['OCS']['cnx_origine'] == "CAS") {
        require_once(PHPCAS);
        require_once(BACKEND . 'require/cas.config.php');
        $cas = new phpCas();
        $cas->client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_uri);
        $cas->logout();
    }
    //end contrib
    unset($_SESSION['OCS']);
    unset($_GET);
}
echo "</form>";
if (isset($_GET['debug'])) {
    unset($_SESSION['OCS']['DEBUG']);
}
require_once('require/footer.php');

/**
 * execute sql file
 * @param type $fichier
 * @return boolean
 */
function exec_fichier_sql($fichier) {
    global $l;

    $db_file = $fichier;
    $dbf_handle = @fopen($db_file, "r");

    if (!$dbf_handle) {
        msg_error($l->g(2112) . " : " . $fichier);
        return true;
    } else {
        if (filesize($db_file) > 0) {
            $sql_query = fread($dbf_handle, filesize($db_file));
            fclose($dbf_handle);
            $data_sql = explode(";", $sql_query);
            foreach ($data_sql as $v) {
                if (trim($v) != "") {
                    mysql2_query_secure($v, $_SESSION['OCS']["writeServer"]);
                }
            }
            return false;
        }
        return true;
    }
}

?>