<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Erwan GOALOU 2010
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
// Magic Quotes :
// This feature has been deprecated as of PHP 5.3 and deleted as of PHP 5.4.
if (get_magic_quotes_gpc()) {
    function magicQuotes_awStripslashes(&$value, $key) {$value = stripslashes($value);}
    $gpc = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    array_walk_recursive($gpc, 'magicQuotes_awStripslashes');
}

require_once ('require/function_index.php');
$sleep=1;
$debut = getmicrotime();

define('AJAX', false);

require ('require/header.php');
addLog('PAGE',$protectedGet[PAG_INDEX]);

if( !isset($protectedGet["popup"] )&& !isset($protectedGet["no_footer"] ))
	require (FOOTER_HTML);

?>
