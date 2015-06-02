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

//Select config file depending on user profile
$profil_data=read_profil_file($_SESSION['OCS']["lvluser"]);
if (is_array($profil_data)) {
	foreach ($profil_data as $key=>$value){
		$_SESSION['OCS'][$key]=$value;
	}
}

//Config for all user
$config_file=read_config_file();
if (is_array($config_file)) {
	foreach ($config_file as $key=>$value){
		$_SESSION['OCS'][$key]=$value;
	}
}
//Splitting name_menu array for use with the "show_menu" javascript function

if (isset($_SESSION['OCS']['MENU'])){
	foreach($_SESSION['OCS']['MENU'] as $key=>$value){
		if(isset($_SESSION['OCS']['PAGE_PROFIL'][$key]))
		$show_menu[$value]++;
	}
}

$_SESSION['OCS']['all_menus']=implode("|", $_SESSION['OCS']['MENU_NAME']);
?>
