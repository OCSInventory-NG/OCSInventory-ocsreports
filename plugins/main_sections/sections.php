<?php
//Select config file depending on user profile
switch( $_SESSION["lvluser"]) {		
	case	SADMIN: $ms_cfg_file="sadmin_config.txt" ; break;
	case	ADMIN: $ms_cfg_file="admin_config.txt" ; break;
	case	LADMIN: $ms_cfg_file="ladmin_config.txt" ; break;
}
//show only true sections
if (file_exists($_SESSION['main_sections_dir'].$ms_cfg_file)) {
      $fd = fopen ($_SESSION['main_sections_dir'].$ms_cfg_file, "r");
      $capture='';
      while( !feof($fd) ) {

         $line = trim( fgets( $fd, 256 ) );
		
		 if (substr($line,0,2) == "</")
            $capture='';

         if ($capture == 'OK_ORDER_FIRST_TABLE')
            $_SESSION['list_plugins_first'][]=$line;
            
         if ($capture == 'OK_ORDER_SECOND_TABLE')
            $_SESSION['list_plugins_second'][]=$line;
         
         if ($line{0} == "<"){ 	//Getting tag type for the next launch of the loop
            $capture = 'OK_'.substr(substr($line,1),0,-1);
         }        
      }
   fclose( $fd );
}

//Config for all user
$ms_cfg_file="4all_config.txt";
if (file_exists($_SESSION['main_sections_dir'].$ms_cfg_file)) {
      $fd = fopen ($_SESSION['main_sections_dir'].$ms_cfg_file, "r");
      $capture='';
      while( !feof($fd) ) {

         $line = trim( fgets( $fd, 256 ) );
		
		 if (substr($line,0,2) == "</")
            $capture='';
    	 
    	 if ($capture == 'OK_LBL'){
            $tab_lbl=explode(":", $line);
            $_SESSION['list_lbl'][$tab_lbl[0]]=$tab_lbl[1];
         }
         
         if ($capture == 'OK_MENU'){
            $tab_menu=explode(":", $line);
            $_SESSION['list_menu'][$tab_menu[1]][]=$tab_menu[0];
          //  $list_menu_V2[$tab_menu[0]]=$tab_menu[1];
         }
         
         if ($capture == 'OK_MENU_TITLE'){
            $tab_lbl_menu=explode(":", $line);
            $_SESSION['lbl_menu'][$tab_lbl_menu[0]]=$tab_lbl_menu[1];
         }
         
         if ($capture == 'OK_MENU_NAME'){
            $tab_name_menu=explode(":", $line);
            $_SESSION['name_menu'][$tab_name_menu[0]]=$tab_name_menu[1];
         }
         
         if ($capture == 'OK_URL'){
            $tab_url=explode(":", $line);
            $_SESSION['list_url'][$tab_url[0]]=$tab_url[1];
          //  $pages_refs[$tab_url[0]]=$tab_url[1];
         }
         if ($capture == 'OK_DIRECTORY'){
            $tab_dir=explode(":", $line);
            $_SESSION['list_dir'][$tab_dir[0]]=$tab_dir[1];
         }
         
         if ($line{0} == "<"){ 	//Getting tag type for the next launch of the loop
            $capture = 'OK_'.substr(substr($line,1),0,-1);
         }                  
      }
   fclose( $fd );
}

//Splitting name_menu array for use with the "show_menu" javascript function
$_SESSION['all_menus']=implode("|", $_SESSION['name_menu']);
?>
