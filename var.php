<?php
@session_start();
define("USE_CACHE", 1 );				//Do we use cache tables ?
define("UPDATE_CHECKSUM", 1 );			// do we need to update software checksum when using dictionnary ?
define("UTF8_DEGREE", 1 );				// 0 For non utf8 database, 1 for utf8
define("GUI_VER", "5011");				// Version of the GUI
define("MAC_FILE", "files/oui.txt");	// File containing MAC database
define("TAG_NAME", "TAG"); 				// do NOT change
define("DEFAULT_LANGUAGE","french");
define("PAG_INDEX","function");            // define name in url (like multi=32)
?>
