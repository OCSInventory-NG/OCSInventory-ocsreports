<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Pierre LEMMET 2005
// Web: http://ocsinventory.sourceforge.net
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================
//Modified on $Date: 2007/02/08 16:05:52 $$Author: plemmet $($Revision: 1.13 $)

require("fichierConf.class.php");
@session_start();

require_once ('require/function_index.php');

$sleep=1;
$debut = getmicrotime();
require ('header.php');

if( !isset($protectedGet["popup"] ))
	require ($_SESSION['OCS']['FOOTER_HTML']);
	


?>
