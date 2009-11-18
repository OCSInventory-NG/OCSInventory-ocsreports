<?php
//Select config file depending on user profile
$ms_cfg_file= $_SESSION['OCS']['main_sections_dir'].$_SESSION['OCS']["lvluser"]."_config.txt";
	
//show only true sections
if (file_exists($ms_cfg_file)) {
	$search=array('PAGE_PROFIL'=>'MULTI','RESTRICTION'=>'SINGLE','ADMIN_BLACKLIST'=>'MULTI','CONFIGURATION'=>'MULTI');
	$profil_data=read_configuration($ms_cfg_file,$search);
	foreach ($profil_data as $key=>$value){
		$_SESSION['OCS'][$key]=$value;
	}
}

//Config for all user
$ms_cfg_file=$_SESSION['OCS']['main_sections_dir']."4all_config.txt";
if (file_exists($ms_cfg_file)) {
	$search=array('ORDER_FIRST_TABLE'=>'MULTI2',
				  'ORDER_SECOND_TABLE'=>'MULTI2',
				  'LBL'=>'MULTI',
				  'MENU'=>'MULTI',
				  'MENU_TITLE'=>'MULTI',
				  'MENU_NAME'=>'MULTI',
				  'URL'=>'MULTI',
				  'DIRECTORY'=>'MULTI');
	$profil_data=read_configuration($ms_cfg_file,$search);
	foreach ($profil_data as $key=>$value){
		$_SESSION['OCS'][$key]=$value;
	}
}

//Splitting name_menu array for use with the "show_menu" javascript function
$_SESSION['OCS']['all_menus']=implode("|", $_SESSION['OCS']['MENU_NAME']);
?>
