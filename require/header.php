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

unset($_SESSION['OCS']['SQL_DEBUG']);

// Before session_start to allow objects to be unserialized from session
require_once('var.php');
require_once(COMPOSER_AUTOLOAD);
require_once('require/extensions/include.php');
require_once('require/menu/include.php');
require_once('require/config/include.php');

@session_start();
error_reporting(E_ALL & ~E_NOTICE);
/* * ******************************************FIND SERVER URL*************************************** */
$addr_server = explode('/', $_SERVER['HTTP_REFERER']);
array_pop($addr_server);
define("OCSREPORT_URL", implode('/', $addr_server));

if ($_SESSION['OCS']['LOG_GUI'] == 1) {
    define("LOG_FILE", $_SESSION['OCS']['LOG_DIR'] . "log.csv");
}

require_once('require/fichierConf.class.php');
require_once('require/function_commun.php');
require_once('require/aide_developpement.php');
require_once('require/function_table_html.php');
require_once('require/views/forms.php');
require_once('require/plugin/include.php');
require_once('require/history/History.php');
require_once('require/layouts/Layout.php');

if (isset($_SESSION['OCS']['CONF_RESET'])) {
    unset($_SESSION['OCS']['LOG_GUI']);
    unset($_SESSION['OCS']['CONF_DIRECTORY']);
    unset($_SESSION['OCS']['URL']); 
    unset($_SESSION['OCS']["usecache"]);
    unset($_SESSION['OCS']['CONF_RESET']);
}

//If you have to reload conf
if ($_POST['RELOAD_CONF'] == 'RELOAD') {
    $_SESSION['OCS']['CONF_RESET'] = true;
}



/* * ***************************************************LOGOUT******************************************** */
if (isset($_POST['LOGOUT']) && $_POST['LOGOUT'] == 'ON') {
    unset($_SESSION['OCS']);
    unset($_GET);
}
    

/* * *************************************************** First installation checking ******************************************************** */
if ((!is_readable(CONF_MYSQL)) || (!function_exists('session_start')) || (!function_exists('mysqli_real_connect'))) {
    require('install.php');
    die();
} else {
    require_once(CONF_MYSQL);
}

if (!defined("SERVER_READ") || !defined("DB_NAME") || !defined("SERVER_WRITE") || !defined("COMPTE_BASE") || !defined("PSWD_BASE")) {
    $fromdbconfig_out = true;
    require('install.php');
    die();
}

//connect to databases
$link_write = dbconnect(SERVER_WRITE, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);
$link_read = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);
//p($link_write);

if (is_object($link_write) && is_object($link_read)) {
    $_SESSION['OCS']["writeServer"] = $link_write;
    $_SESSION['OCS']["readServer"] = $link_read;
} else {
    if ($link_write == "NO_DATABASE" || $link_read == "NO_DATABASE") {
        require('install.php');
        die();
    }
    $msg = '';
    if (!is_object($link_write)) {
        $msg .= $link_write . "<br>";
    }
    if (!is_object($link_read)) {
        $msg .= $link_read;
    }
    html_header(true);
    msg_error($msg);
    require_once(FOOTER_HTML);
    die();
}


/* * *********************************************************LOGS ADMIN************************************************************************ */
if (!isset($_SESSION['OCS']['LOG_GUI'])) {
    $values = look_config_default_values(array('LOG_GUI', 'LOG_DIR', 'LOG_SCRIPT'));
    $_SESSION['OCS']['LOG_DIR'] = $values['tvalue']['LOG_DIR'];
    if ($_SESSION['OCS']['LOG_DIR']) {
        $_SESSION['OCS']['LOG_DIR'] .= '/logs/';
    } else {
        $_SESSION['OCS']['OLD_CONF_DIR'] = VARLOG_DIR . '/logs/';
    }
    $_SESSION['OCS']['LOG_GUI'] = $values['ivalue']['LOG_GUI'];
    if ($_SESSION['OCS']['LOG_SCRIPT']) {
        $_SESSION['OCS']['LOG_SCRIPT'] .= "/scripts/";
    } else {
        $_SESSION['OCS']['OLD_CONF_DIR'] = VARLOG_DIR . '/scripts/';
    }
}
/* * **************END LOGS************** */

/* * *********************************************************CONF DIRECTORY************************************************************************ */
if (!isset($_SESSION['OCS']['CONF_PROFILS_DIR'])) {
    $values = look_config_default_values(array('CONF_PROFILS_DIR', 'OLD_CONF_DIR'));
    $_SESSION['OCS']['OLD_CONF_DIR'] = $values['tvalue']['OLD_CONF_DIR'];
    if ($_SESSION['OCS']['OLD_CONF_DIR']) {
        $_SESSION['OCS']['OLD_CONF_DIR'] .= '/old_conf/';
    } else {
        $_SESSION['OCS']['CONF_PROFILS_DIR'] = ETC_DIR . '/' . MAIN_SECTIONS_DIR . 'old_conf/';
    }

    $_SESSION['OCS']['CONF_PROFILS_DIR'] = $values['tvalue']['CONF_PROFILS_DIR'];
    if ($_SESSION['OCS']['CONF_PROFILS_DIR']) {
        $_SESSION['OCS']['CONF_PROFILS_DIR'] .= '/conf/';
    } else {
        $_SESSION['OCS']['CONF_PROFILS_DIR'] = ETC_DIR . '/' . MAIN_SECTIONS_DIR . 'conf/';
    }
}
/* * **************END LOGS************** */


/* * ****************************************Checking sql update******************************************** */
if (!isset($_SESSION['OCS']['SQL_BASE_VERS'])) {
    $values = look_config_default_values('GUI_VERSION');
    $_SESSION['OCS']['SQL_BASE_VERS'] = $values['tvalue']['GUI_VERSION'];
}
if (GUI_VER != $_SESSION['OCS']['SQL_BASE_VERS']) {
    $fromAuto = true;
    if ($_SESSION['OCS']['SQL_BASE_VERS'] < 7006) {
        unset($_SESSION['OCS']['SQL_BASE_VERS']);
        require('install.php');
    } else {
        require('update.php');
    }
    die();
}

if (!defined("SERVER_READ")) {
    $fromdbconfig_out = true;
    require('install.php');
    die();
}

//SECURITY
$protectedPost = strip_tags_array($_POST);
$protectedGet = strip_tags_array($_GET);

@set_time_limit(0);

//Don't take care of error identify
//For the fuser, $no_error  = 'YES'
if (!isset($no_error)) {
    $no_error = 'NO';
}

/* * **************************************************SQL TABLE & FIELDS********************************************** */
if (!isset($_SESSION['OCS']['SQL_TABLE'])) {
    $sql = "show tables from `%s`";
    $arg = DB_NAME;
    $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
    while ($item = mysqli_fetch_row($res)) {
        $sql = "SHOW COLUMNS FROM `%s`";
        $arg = $item[0];
        $res_column = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
        while ($item_column = mysqli_fetch_row($res_column)) {

            if ($item_column[0] == "HARDWARE_ID" && !isset($_SESSION['OCS']['SQL_TABLE_HARDWARE_ID'][$item[0]])) {
                $_SESSION['OCS']['SQL_TABLE_HARDWARE_ID'][$item[0]] = $item[0];
            }

            $_SESSION['OCS']['SQL_TABLE'][$item[0]][$item_column[0]] = $item_column[0];
        }
    }
}

/* * ***************************************************GESTION DU NOM DES PAGES*************************************** */
//Config for all user
if (!isset($_SESSION['OCS']['url_service'])) {
    if (!file_exists(CONFIG_DIR.'urls.xml')) {
        migrate_config_2_2();
    }

    $url_serializer = new XMLUrlsSerializer();
    $urls = $url_serializer->unserialize(file_get_contents(CONFIG_DIR.'urls.xml'));
    $_SESSION['OCS']['url_service'] = $urls;

    // Backwards compatibility
    $pages_refs = array();
    foreach ($urls->getUrls() as $key => $url) {
        $pages_refs[$key] = $url['value'];
    }

    $_SESSION['OCS']['URL'] = $pages_refs;
} else {
    $urls = $_SESSION['OCS']['url_service'];
    $pages_refs = $_SESSION['OCS']['URL'];
}


/* * ***************************************************GESTION DES FICHIERS JS*************************************** */
if (!isset($_SESSION['OCS']['JAVASCRIPT'])) {
    $js_serializer = new XMLJsSerializer();
    $_SESSION['OCS']['JAVASCRIPT'] = $js_serializer->unserialize(file_get_contents(CONFIG_DIR.'js.xml'));
}


/* * ********************************************************GESTION DES COLONNES DES TABLEAUX PAR COOKIES********************************** */
require_once('require/function_cookies.php');

//Delete all cookies if GUI_VER change
if (!isset($_COOKIE["VERS"]) || $_COOKIE["VERS"] != GUI_VER) {
    if (isset($_COOKIE)) {
        foreach ($_COOKIE as $key => $val) {
            cookies_reset($key);
        }
        unset($_COOKIE);
    }
    cookies_add("VERS", GUI_VER);
}

//del column
if (is_defined($protectedPost['SUP_COL']) && isset($_SESSION['OCS']['col_tab'][$protectedPost['TABLE_NAME']])) {
    unset($_SESSION['OCS']['col_tab'][$protectedPost['TABLE_NAME']][$protectedPost['SUP_COL']]);
    cookies_add($protectedPost['TABLE_NAME'], implode('///', $_SESSION['OCS']['col_tab'][$protectedPost['TABLE_NAME']]));
}

//default values
if (is_defined($protectedPost['RAZ'])) {
    cookies_reset($protectedPost['TABLE_NAME']);
}

//add column
if (isset($protectedPost['TABLE_NAME']) && is_defined($protectedPost['restCol' . $protectedPost['TABLE_NAME']])) {
    $_SESSION['OCS']['col_tab'][$tab_name][$protectedPost['restCol' . $tab_name]] = $protectedPost['restCol' . $tab_name];
    if (is_array($_SESSION['OCS']['col_tab'][$protectedPost['TABLE_NAME']])) {
        cookies_add($protectedPost['TABLE_NAME'], implode('///', $_SESSION['OCS']['col_tab'][$protectedPost['TABLE_NAME']]));
    }
}

/* * ******************************************************GESTION DE LA LANGUE PAR COOKIES********************************************* */
/* * ***************************************************Gestion des fichiers de langues  TEST************************************ */
if (isset($protectedPost['Valid_EDITION'])) {
    if ($protectedPost['ID_WORD'] != '') {
        if ($protectedPost['ACTION'] == "DEL") {
            unset($_SESSION['OCS']['LANGUAGE_FILE']->tableauMots[$protectedPost['ID_WORD']]);
        } else {
            $_SESSION['OCS']['LANGUAGE_FILE']->tableauMots[$protectedPost['ID_WORD']] = $protectedPost['UPDATE'];
        }
    }
}
unset($_SESSION['OCS']['EDIT_LANGUAGE']);


if (is_defined($protectedPost['LANG'])) {
    unset($_SESSION['OCS']['LANGUAGE']);
    cookies_add('LANG', $protectedPost['LANG']);
    $_SESSION['OCS']['LANGUAGE'] = $protectedPost['LANG'];
    $_SESSION['OCS']["LANGUAGE_FILE"] = new language($_SESSION['OCS']['LANGUAGE']);
}
//unset($_SESSION['OCS']['LANGUAGE']);
//si la langue par défaut n'existe pas, on récupèrer le cookie
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
/* * *******************************************************gestion de l'authentification*************************************************** */

if (!isset($_SESSION['OCS']["loggeduser"])) {
    if (!AJAX && !((array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'))) {

        $values = look_config_default_values('PASSWORD_VERSION');
        $_SESSION['OCS']['PASSWORD_VERSION'] = $values['ivalue']['PASSWORD_VERSION'];
        $_SESSION['OCS']['PASSWORD_ENCRYPTION'] = $values['tvalue']['PASSWORD_VERSION'];

        require_once(BACKEND . 'AUTH/auth.php');
    } else {
        header($_SERVER["SERVER_PROTOCOL"] . " 401 " . utf8_decode($l->g(1359)));
        die;
    }
}

/* * ********************************************************gestion des droits sur les TAG*************************************************** */
if (!isset($_SESSION['OCS']["lvluser"])) {
    require_once(BACKEND . 'identity/identity.php');
}

/* * ********************************************************gestion des droits sur l'ipdiscover*************************************************** */
if (!isset($_SESSION['OCS']["ipdiscover"])) {
    require_once(BACKEND . 'ipdiscover/ipdiscover.php');
}

/* * ********************************************************gestion des administrative data*************************************************** */
migrate_adminData_2_5();

/* * ******************GESTION GUI CONF***************** */
if (!isset($_SESSION['OCS']["usecache"]) || !isset($_SESSION['OCS']["tabcache"])) {
    $conf_gui = array('usecache' => 'INVENTORY_CACHE_ENABLED',
        'tabcache' => 'TAB_CACHE',
        'USE_NEW_SOFT_TABLES' => 'USE_NEW_SOFT_TABLES');
    $default_value_conf = array('INVENTORY_CACHE_ENABLED' => 1, 'TAB_CACHE' => 0, 'USE_NEW_SOFT_TABLES' => 0);
    $values = look_config_default_values($conf_gui);
    foreach ($conf_gui as $k => $v) {
        if (isset($values['ivalue'][$v]))
            $_SESSION['OCS'][$k] = $values['ivalue'][$v];
        else
            $_SESSION['OCS'][$k] = $default_value_conf[$v];
    }
}

/* * ******************END GESTION CACHE***************** */

/* * *******************************************GESTION OF LBL_TAG************************************ */
if (!isset($_SESSION['OCS']['TAG_LBL'])) {
    require_once('require/function_admininfo.php');
    $all_tag_lbl = witch_field_more('COMPUTERS');
    foreach ($all_tag_lbl['LIST_NAME'] as $key => $value) {
        $_SESSION['OCS']['TAG_LBL'][$value] = $all_tag_lbl['LIST_FIELDS'][$key];
        $_SESSION['OCS']['TAG_ID'][$key] = $value;
    }
}

/* * *****************************************GESTION OF PLUGINS (MAIN SECTIONS)*************************** */
if (!isset($_SESSION['OCS']['profile'])) {
    $profile_config = PROFILES_DIR . $_SESSION['OCS']["lvluser"] . '.xml';
    $profile_serializer = new XMLProfileSerializer();
    $profile = $profile_serializer->unserialize($_SESSION['OCS']["lvluser"], file_get_contents($profile_config));
    $_SESSION['OCS']['profile'] = $profile;
} else {
    $profile = $_SESSION['OCS']['profile'];
}

if (!AJAX and ( !isset($header_html) || $header_html != 'NO') && !isset($protectedGet['no_header'])) {
    require_once (HEADER_HTML);
}

$url_name = $urls->getUrlName($protectedGet[PAG_INDEX]);

//VERIF ACCESS TO THIS PAGE
if (isset($protectedGet[PAG_INDEX]) && !$profile->hasPage($url_name) && (!$_SESSION['OCS']['TRUE_PAGES'] || !array_search($url_name, $_SESSION['OCS']['TRUE_PAGES']))
        //force access to profils witch have CONFIGURATION TELEDIFF  == 'YES' for ms_admin_ipdiscover page
        && !($profile->getConfigValue('TELEDIFF') == 'YES' && $url_name == 'ms_admin_ipdiscover')) {
    msg_error("ACCESS DENIED");
    require_once(FOOTER_HTML);
    die();
}

if ((!isset($_SESSION['OCS']["loggeduser"]) || !is_defined($_SESSION['OCS']["lvluser"])) && !isset($_SESSION['OCS']['TRUE_USER']) && $no_error != 'YES') {
    msg_error('no loggeduser');
    require_once(FOOTER_HTML);
    die();
}

if ($url_name) {
    //CSRF security
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $csrf = true;
        if (isset($_SESSION['OCS']['CSRF'])) {
            foreach ($_SESSION['OCS']['CSRF'] as $k => $v) {
                if ($v == $protectedPost['CSRF_' . $k]) {
                    $csrf = false;
                }
            }
        }
        //Here we parse the form
        if ($csrf) {
            msg_error("<big>CSRF ATTACK!!!</big>");
            require_once(FOOTER_HTML);
            die();
        }

        //Do the rest of the processing here
    }

    if ($urls->getDirectory($url_name)) {
        $rep = $urls->getDirectory($url_name);
    }

    $test = $rep . "/" . $url_name . ".php";

    if(file_exists(MAIN_SECTIONS_DIR . $rep . "/" . $url_name . ".php")){
        require (MAIN_SECTIONS_DIR . $rep . "/" . $url_name . ".php");
    }elseif (file_exists($rep . "/" . $url_name . ".php")){
        require ($rep . "/" . $url_name . ".php");
    }else{
        die("page not found !!!!");
    }

} else {
    $default_first_page = MAIN_SECTIONS_DIR . "ms_console/ms_console.php";
    if (isset($protectedGet['first'])) {
        require (MAIN_SECTIONS_DIR . "ms_console/ms_console.php");
    } else if ($profile->hasPage('ms_console')) {
        require ($default_first_page);
    } else {
        echo "<img src='image/fond.png' class='background-pic'>";
    }
}
?>
