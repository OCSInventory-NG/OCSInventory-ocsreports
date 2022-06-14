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
echo "<div class='container-fluid'>";
function printEnTeteInstall($ent) {
    echo "<h3>$ent</h3>";
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

// Check requirements ( function_commun.php )
check_requirements();

//post the first form
if (isset($_POST["name"])) {

    $link = dbconnect($_POST["host"], $_POST["name"], $_POST["pass"], $_POST["database"], $_POST["sslkey"], $_POST["sslcert"], $_POST["cacert"], $_POST["port"], $_POST["sslmode"], $_POST["enablessl"] );
    if (mysqli_connect_errno()) {
        $firstAttempt = false;
        msg_error($l->g(2001) . " " . $l->g(249) .
                " (" . $l->g(2010) . "=" . $_POST["host"] .
                " " . $l->g(2011) . "=" . $_POST["name"] .
                " " . $l->g(2014) . "=" . $_POST["pass"] .
                " " . $l->g(2014) . "=" . $_POST["port"] .
                " " . $l->g(2014) . "=" . $_POST["enablessl"] .
                " " . $l->g(2014) . "=" . $_POST["sslmode"] .
                " " . $l->g(2014) . "=" . $_POST["sslkey"] .
                " " . $l->g(2014) . "=" . $_POST["sslcert"] .
                " " . $l->g(2014) . "=" . $_POST["cacert"] .
                ")<br>" . $link);
    } else {
        //if database not exist
        if ($link == "NO_DATABASE") {
            $dbc = mysqli_init();
            if($_POST["enablessl"] == 1) {
                $dbc->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
                $dbc->ssl_set($_POST["sslkey"], $_POST["sslcert"], $_POST["cacert"], NULL, NULL);
                if($_POST["sslmode"] == "MYSQLI_CLIENT_SSL") {
                    $connect = MYSQLI_CLIENT_SSL;
                } elseif($_POST["sslmode"] == "MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT") {
                    $connect = MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
                }
            } else {
                $connect = NULL;
            }
            $dbc->options(MYSQLI_INIT_COMMAND, "SET NAMES 'utf8'");
            $dbc->options(MYSQLI_INIT_COMMAND, "SET sql_mode='NO_ENGINE_SUBSTITUTION'");

            $link = mysqli_real_connect($dbc, $_POST["host"], $_POST["name"], $_POST["pass"], NULL, $_POST["port"], NULL, $connect);

            if($link) {
                $link = $dbc;
            }
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
            
            $item = $res ? mysqli_fetch_object($res) : null;
            if (!isset($item) || $item->tvalue < 7006) {
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
                fwrite($ch, "define(\"SERVER_PORT\",\"" . $_POST["port"] . "\");\n");
                fwrite($ch, "define(\"COMPTE_BASE\",\"" . $_POST["name"] . "\");\n");
                fwrite($ch, "define(\"PSWD_BASE\",\"" . $_POST["pass"] . "\");\n");
                fwrite($ch, "define(\"ENABLE_SSL\",\"" . $_POST["enablessl"] . "\");\n");
                fwrite($ch, "define(\"SSL_MODE\",\"" . $_POST["sslmode"] . "\");\n");
                fwrite($ch, "define(\"SSL_KEY\",\"" . $_POST["sslkey"] . "\");\n");
                fwrite($ch, "define(\"SSL_CERT\",\"" . $_POST["sslcert"] . "\");\n");
                fwrite($ch, "define(\"CA_CERT\",\"" . $_POST["cacert"] . "\");\n");
                fwrite($ch, "?>");
                fclose($ch);
                msg_success("<b><a href='index.php'>" . $l->g(2051) . "</a></b>");
                unset($_SESSION['OCS']['SQL_BASE_VERS']);
                die();
            }
        }

        if (!isset($error)) {
            ob_flush();
            flush();
            msg_info($l->g(2030));
            exec_fichier_sql($db_file, $link);
            $ch = @fopen(CONF_MYSQL, "w");
            fwrite($ch, "<?php\n");
            fwrite($ch, "define(\"DB_NAME\", \"" . $_POST['database'] . "\");\n");
            fwrite($ch, "define(\"SERVER_READ\",\"" . $_POST["host"] . "\");\n");
            fwrite($ch, "define(\"SERVER_WRITE\",\"" . $_POST["host"] . "\");\n");
            fwrite($ch, "define(\"SERVER_PORT\",\"" . $_POST["port"] . "\");\n");
            fwrite($ch, "define(\"COMPTE_BASE\",\"" . $name_connect . "\");\n");
            fwrite($ch, "define(\"PSWD_BASE\",\"" . $pass_connect . "\");\n");
            fwrite($ch, "define(\"ENABLE_SSL\",\"" . $_POST["enablessl"] . "\");\n");
            fwrite($ch, "define(\"SSL_MODE\",\"" . $_POST["sslmode"] . "\");\n");
            fwrite($ch, "define(\"SSL_KEY\",\"" . $_POST["sslkey"] . "\");\n");
            fwrite($ch, "define(\"SSL_CERT\",\"" . $_POST["sslcert"] . "\");\n");
            fwrite($ch, "define(\"CA_CERT\",\"" . $_POST["cacert"] . "\");\n");
            fwrite($ch, "?>");
            fclose($ch);

            $dbc = mysqli_init();
            if($_POST["enablessl"] == 1) {
                $dbc->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
                $dbc->ssl_set($_POST["sslkey"], $_POST["sslcert"], $_POST["cacert"], NULL, NULL);
                if($_POST["sslmode"] == "MYSQLI_CLIENT_SSL") {
                    $connect = MYSQLI_CLIENT_SSL;
                } elseif($_POST["sslmode"] == "MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT") {
                    $connect = MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
                }
            } else {
                $connect = NULL;
            }
            $dbc->options(MYSQLI_INIT_COMMAND, "SET NAMES 'utf8'");
            $dbc->options(MYSQLI_INIT_COMMAND, "SET sql_mode='NO_ENGINE_SUBSTITUTION'");

            $link = mysqli_real_connect($dbc, $_POST["host"], $_POST["name"], $_POST["pass"], NULL, $_POST["port"], NULL, $connect ?? NULL);

            if (!$link) {
                if (mysqli_connect_errno() == 0) {
                    echo "<br><center><font color=red><b>" . $l->g(2043) .
                    " " . $l->g(2044) .
                    "</b><br></font></center>";
                    die();
                } else {
                    echo "<br><center><font color=red><b>" . $l->g(2043) .
                    " (" . $l->g(2017) .
                    " " . $l->g(2010) .
                    "=" . $_POST["host"] .
                    " " . $l->g(2011) .
                    "=ocs " . $l->g(2014) .
                    "=ocs)"
                    . "</b><br></font></center>";
                }

                echo "<br><center><font color=red><b>" . $l->g(2065) . "</b></font></center>";
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
    if (defined('SERVER_PORT')) {
        $valPort = SERVER_PORT;
    } else {
        $valPort = 3306;
    }
    if (defined('DB_NAME')) {
        $valdatabase = DB_NAME;
    } else {
        $valdatabase = '';
    }
    if (defined('ENABLE_SSL')) {
        $valenablessl = [
            "1" => $l->g(455),
            "0" => $l->g(454)
        ];
    } else {
        $valenablessl = [
            "1" => $l->g(455),
            "0" => $l->g(454)
        ];
    }
    if (defined('SSL_MODE')) {
        $valsslmode = [
            "MYSQLI_CLIENT_SSL" => "MYSQLI_CLIENT_SSL",
            "MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT" => "MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT"
        ];
    } else {
        $valsslmode = [
            "MYSQLI_CLIENT_SSL" => "MYSQLI_CLIENT_SSL",
            "MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT" => "MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT"
        ];
    }
    if (defined('SSL_KEY')) {
        $valsslkey = SSL_KEY;
    } else {
        $valsslkey = '';
    }
    if (defined('SSL_CERT')) {
        $valsslcert = SSL_CERT;
    } else {
        $valsslcert = '';
    }
    if (defined('CA_CERT')) {
        $valcacert = CA_CERT;
    } else {
        $valcacert = '';
    }
}
//show first form
$form_name = 'fsub';
$name_field = array("name", "pass", "database", "host", "port", "enablessl", "sslmode", "sslkey", "sslcert", "cacert");
$tab_name = array($l->g(247) . ": ", $l->g(248) . ": ", $l->g(1233) . ":", $l->g(250) . ":", $l->g(9105) . " :", $l->g(9102) . ":", $l->g(9103) . ":", $l->g(9100) . ":", $l->g(9101) . ":", $l->g(9104) . ":");
$type_field = array(0, 4, 0, 0, 15, 2, 2, 0, 0, 0);
if (isset($_POST["name"], $_POST["pass"], $_POST["database"], $_POST["host"])) {
    $value_field = array($_POST["name"], $_POST["pass"], $_POST["database"], $_POST["host"], $_POST["port"], $valenablessl, $valsslmode, $_POST["sslkey"], $_POST["sslcert"], $_POST["cacert"]);
} else {
    $value_field = array($valNme, $valPass, $valdatabase, $valServ, $valPort, $valenablessl, $valsslmode, $valsslkey, $valsslcert, $valcacert);
}
$tab_typ_champ = show_field($name_field, $type_field, $value_field);
modif_values($tab_name, $tab_typ_champ, $tab_hidden ?? "", array(
    'button_name' => 'INSTALL',
    'show_button' => 'BUTTON',
    'form_name' => $form_name
));

echo "</div>";
die();
?>