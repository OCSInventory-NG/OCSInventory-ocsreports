<?php
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Erwan GOALOU 2010 (erwan(at)ocsinventory-ng(pt)org)
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================

@session_start();
define('DOCUMENT_REAL_ROOT', dirname(__FILE__));
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

define("GUI_VER", "7009");												// Version of the database
define("GUI_VER_SHOW","2.2RC");        								    // Version of the GUI
define("MAC_FILE", "files/oui.txt");									// File containing MAC database
define("DEFAULT_LANGUAGE","english");    								// Default language
define("PAG_INDEX","function");         								// define name in url (like multi=32)
define("DEMO",false);			        								// Define if we use demo version or not (for OCS TEAM, other=> DO NOT USE IT)
define("DEMO_LOGIN",'demo');											// Define demo login for connexion
define("DEMO_PASSWD",'demo');											// Define demo password for connexion
define("DFT_DB_CMPT",'ocs');  											// Define default login to connect to database
define("DFT_DB_PSWD",'ocs');											// Define default password to connect to database
define("DFT_GUI_CMPT",'admin');											// Define default login to connect to GUI
define("DFT_GUI_PSWD",'admin');											// Define default password to connect to GUI
define('BACKEND',"backend/");									    // Define backend Directory
define('PHPCAS',BACKEND.'require/lib/phpcas/CAS.php');					// Path to CAS (change to use system provided library)
define('PLUGINS_DL_DIR', 'download/');
define('PLUGINS_SRV_SIDE', 'upload/');
define('CONFIG_DIR',"config/");
define('CD_CONFIG_DIR', CONFIG_DIR."computer/" );
define('PLUGINS_DIR',"plugins/");										// Define plugins Directory
define('PLUGINS_GUI_DIR','/tmp/');	
define('HEADER_HTML',"require/html_header.php");						// Define html_header file				
define('FOOTER_HTML',"require/footer.php");								// Define footer file		
define('MAIN_SECTIONS_DIR',PLUGINS_DIR."main_sections/");				//
define('DEV_OPTION',false);												// Define DEV Options DO NOT USE
define('PC4PAGE',20);													// Define result by page MUST in (5,10,15,20,50,100,200,1000000);
define('CSRF',1000);														// max number of csrf session 
define('ETC_DIR', DOCUMENT_REAL_ROOT);									// Configuration dir
define('VARLIB_DIR', DOCUMENT_ROOT);									// Data default dir
define('VARLOG_DIR', DOCUMENT_ROOT);									// Log defaut dir
define('CONF_MYSQL', ETC_DIR.'/dbconfig.inc.php');						// Define dbconf file


?>
