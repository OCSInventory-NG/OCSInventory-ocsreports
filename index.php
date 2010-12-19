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
@session_start();

require_once ('require/function_index.php');

$sleep=1;
$debut = getmicrotime();
require ('require/header.php');
addLog('PAGE',$protectedGet[PAG_INDEX]);
if( !isset($protectedGet["popup"] ))
	require ($_SESSION['OCS']['FOOTER_HTML']);
	


?>
