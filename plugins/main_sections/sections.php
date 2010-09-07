<?php
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
$_SESSION['OCS']['all_menus']=implode("|", $_SESSION['OCS']['MENU_NAME']);
?>
