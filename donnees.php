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
//Modified on $Date: 2007/02/08 15:53:24 $$Author: plemmet $($Revision: 1.7 $)

$user=$_SESSION['OCS']["loggeduser"];
if( isset( $protectedGet["fuser"] ) ) {
	unset($_SESSION['OCS']["mesmachines"]);
}
if($_SESSION['OCS']['RESTRICTION']== "NO") {
	$mesMachines="";
	$_SESSION['OCS']["mesmachines"] = "";
}
else
 if( ! isset($_SESSION['OCS']["mesmachines"] )) {
	$mesMachines = "a.".TAG_NAME." IN ('".@implode("','",$list_Supportcu)."') ";	
	$_SESSION['OCS']["mesmachines"] = $mesMachines;
}
else {
	$mesMachines = $_SESSION['OCS']["mesmachines"];
}

?>