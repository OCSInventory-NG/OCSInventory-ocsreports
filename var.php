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
define('DOCUMENT_REAL_ROOT', dirname(__FILE__));
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

//====================================================================================
// Default configuration dir (Logs / Data / Document root / Sql config file)
//====================================================================================
define('ETC_DIR', DOCUMENT_REAL_ROOT);  // Ocsreports root folder ( /usr/share/ocsinventory-reports/ocsreports by default)
define('VARLIB_DIR', "/var/lib/ocsinventory-reports"); // Default lib dir
define('VARLOG_DIR', "/var/lib/ocsinventory-reports"); // Défault log dir
define('CONF_MYSQL', ETC_DIR . '/dbconfig.inc.php'); // Database configuration infos
//====================================================================================
// Librairies / Backend / Mac address file
//====================================================================================
define("MAC_FILE", __DIR__ . '/files/oui.txt');  /// Mac address file
define('BACKEND', __DIR__ . '/backend/');    // Backend folder (AUTH / CAS / etc )
define('PHPCAS', __DIR__ . '/libraries/phpcas/CAS.php');  // Lib
define('TC_LIB_BARCODE', __DIR__ . '/libraries/tclib/Barcode/autoload.php'); // Lib
define('PASSWORD_COMPAT', __DIR__ . '/libraries/password_compat/password.php'); // Lib
//====================================================================================
// GUI Options
//====================================================================================
define("GUI_VER", "7010"); // ocs' mysql database version
define("GUI_VER_SHOW", "2.2.2");    // GUI Version
define("DEFAULT_LANGUAGE", "english");   // Default GUI language
define("PAG_INDEX", "function");
define("UPDATE_JSON_URI", "http://check-version.ocsinventory-ng.org");

//====================================================================================
// Default OCS DIR
//====================================================================================
define('CONFIG_DIR', __DIR__ . '/config/');  // Configuration dir
define('CD_CONFIG_DIR', CONFIG_DIR . "computer/");  //Computer detail configuration dir
define('PLUGINS_DIR', __DIR__ . '/plugins/'); // Plugins dir
define('PLUGINS_GUI_DIR', '/tmp/');
define('HEADER_HTML', __DIR__ . '/require/html_header.php'); // HEADER for ocsreports
define('FOOTER_HTML', __DIR__ . '/require/footer.php');  // FOOTER for ocsreports
define('MAIN_SECTIONS_DIR', "plugins/main_sections/"); // Main section dir
define('DEV_OPTION', false);

//====================================================================================
// Plugins Configuration
//====================================================================================
define('PLUGINS_DL_DIR', __DIR__ . '/download/');  // Dir where you put plugin sources
define('PLUGINS_SRV_SIDE', __DIR__ . '/upload/'); // Don't touch this dir used by plugin engine
define('PLUGIN_WS_URL', '/ocsplugins'); // Plugins engine ws url, don't touch if you don't know what you are doing
//====================================================================================
// Misc Options
//====================================================================================
define('PC4PAGE', 20);
define('CSRF', 1000);

//====================================================================================
// Demo mode config and defaults accounts logins
//====================================================================================
define("DEMO", false);
define("DEMO_LOGIN", 'demo');
define("DEMO_PASSWD", 'demo');
define("DFT_DB_CMPT", 'ocs');
define("DFT_DB_PSWD", 'ocs');
define("DFT_GUI_CMPT", 'admin');
define("DFT_GUI_PSWD", 'admin');
?>