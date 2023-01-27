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
/**
 * Ocsreports root folder ( /usr/share/ocsinventory-reports/ocsreports by default)
 */
define('ETC_DIR', DOCUMENT_REAL_ROOT);
/**
 * Default lib directory
 */
define('VARLIB_DIR', "/var/lib/ocsinventory-reports");
/**
 * Default log directory
 */
define('VARLOG_DIR', "/var/lib/ocsinventory-reports");
/**
 * Database configuration infos
 */
define('CONF_MYSQL_DIR', ETC_DIR);
define('CONF_MYSQL', CONF_MYSQL_DIR . '/dbconfig.inc.php');
//====================================================================================
// Librairies / Backend / Mac address file
//====================================================================================
/**
 * Mac address file
 */
define('MAC_FILE', __DIR__ . '/files/oui.txt');
/**
 * Backend folder (AUTH / CAS / etc )
 */
define('BACKEND', __DIR__ . '/backend/');
/**
 * PHP CAS folder (CAS auth)
 */
define('PHPCAS', __DIR__ . '/vendor/jasig/phpcas/CAS.php');
/**
 * Autoload composer
 */
define('COMPOSER_AUTOLOAD', __DIR__ . '/vendor/autoload.php');
/**
 * Template Mail Directory
 */
define('TEMPLATE', __DIR__.'/templates/');
//====================================================================================
// GUI Options
//====================================================================================
/**
 * OCS' MySQL database version
 */
define('GUI_VER', '7071');

/**
 * GUI Version
 */
define('GUI_VER_SHOW', '2.11.1');
/**
 * Default GUI language
 */
define('DEFAULT_LANGUAGE', 'en_GB');
define('PAG_INDEX', 'function');
define('UPDATE_JSON_URI', 'http://check-version.ocsinventory-ng.org');
define('URL_NEWS', 'http://check-version.ocsinventory-ng.org/newsfeed.json');

//====================================================================================
// Default OCS DIR
//====================================================================================
/**
 * Configuration directory
 */
define('CONFIG_DIR', __DIR__ . '/config/');
/**
 * Profiles directory
 */
define('PROFILES_DIR', CONFIG_DIR.'profiles/');
/**
 * Computer detail configuration directory
 */
define('CD_CONFIG_DIR', CONFIG_DIR . 'computer/');
/**
 * Plugins directory
 */
define('PLUGINS_DIR', __DIR__ . '/plugins/');
define('PLUGINS_GUI_DIR', '/tmp/');
/**
 * Language dir
 */
define('LANGUAGE_DIR', PLUGINS_DIR . "language/");
/**
 * HEADER for ocsreports
 */
define('HEADER_HTML', __DIR__ . '/require/html_header.php');
/**
 * FOOTER for ocsreports
 */
define('FOOTER_HTML', __DIR__ . '/require/footer.php');
/**
 * Main section directory
 */
define('MAIN_SECTIONS_DIR', PLUGINS_DIR . "main_sections/");
define('MAIN_SECTIONS_DIR_VISU', "plugins/main_sections/");

/**
 * Theme options
 */
define('THEMES_DIR', __DIR__ . '/themes/');
define('DEFAULT_THEME', "OCS");

/**
 * DEV Options
 */
define('DEV_OPTION', false);

//====================================================================================
// Authentication Configuration
//====================================================================================

/**
 * Define the auth type :
 * - 1 : Local Only
 * - 2 : Local and LDAP
 * - 3 : LDAP Only
 * - 4 : LDAP with SSO
 * - 5 : Always OK, won't ask for user and password
 * - 6 : CAS authentication
 * 
 * If LDAP / SSO Basic auth is configured, please configure the LDAP Authentication
 */
define('AUTH_TYPE', 1);

/**
* For LDAP with SSO auth_type, remove the domain of logins like login@domain 
*
*/

define('SSO_DEL_DOMAIN',false);

/**
 * Auth Insecure LDAP, skip SSL Cert (not recommended)
 * Note : OCS doesn't manage certificate for Ldaps, please edit "/etc/ldap/ldap.conf" to set proper certificate
 */
define('AUTH_LDAP_SKIP_CERT', false);

/**
 * Define password cryptograpgy
 * To see all available cryptography, read https://www.php.net/manual/fr/function.hash-algos.php
 */
define('PASSWORD_CRYPT', 'sha256');

//====================================================================================
// Plugins Configuration
//====================================================================================

/**
 * Directory where you put extensions sources
 */
define('EXT_DL_DIR', __DIR__ . '/extensions/');

//====================================================================================
// Misc Options
//====================================================================================
define('PC4PAGE', 20);
define('CSRF', 1000);

//====================================================================================
// Demo mode config and defaults accounts logins
//====================================================================================
define('DEMO', false);
define('DEMO_LOGIN', 'demo');
define('DEMO_PASSWD', 'demo');
define('DFT_DB_CMPT', 'ocs');
define('DFT_DB_PSWD', 'ocs');
define('DFT_GUI_CMPT', 'admin');
define('DFT_GUI_PSWD', 'admin');
?>
