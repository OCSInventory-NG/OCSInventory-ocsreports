<?php
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Arthur Jaouen 2014 (arthur(at)factorfx(dot)com)
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================

require("require/fichierConf.class.php");

// Before session_start to allow objects to be unserialized from session
require_once('require/menu/include.php');
require_once('require/config/include.php');

@session_start();
if (get_magic_quotes_gpc()) {
    function magicQuotes_awStripslashes(&$value, $key) {$value = stripslashes($value);}
    $gpc = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    array_walk_recursive($gpc, 'magicQuotes_awStripslashes');
}

require_once ('require/function_index.php');
$sleep=1;
$debut = microtime(true);

define('AJAX', true);

require ('require/header.php');

addLog('PAGE', $protectedGet[PAG_INDEX]);

?>