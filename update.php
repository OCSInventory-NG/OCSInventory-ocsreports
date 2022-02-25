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

$l = $_SESSION['OCS']["LANGUAGE_FILE"];
$version_database = $_SESSION['OCS']['SQL_BASE_VERS'];
$form_name = 'form_update';
$rep_maj = 'files/update/';

// Check requirements 
check_requirements();

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
        $sql_update = 'update';
        require(BACKEND . 'AUTH/methode/cas.php');
        $config = get_cas_config();
        $cas = new phpCas();
        $cas->client(CAS_VERSION_2_0, $config['CAS_HOST'], (int)$config['CAS_PORT'], $config['CAS_URI']);
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