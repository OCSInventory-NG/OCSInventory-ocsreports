<?php
//Select config file depending on user profile
switch( $_SESSION["lvluser"]) {		
	case	SADMIN: $ms_cfg_file="sadmin_config.txt" ; break;
	case	ADMIN: $ms_cfg_file="admin_config.txt" ; break;
	case	LADMIN: $ms_cfg_file="ladmin_config.txt" ; break;
}

if (file_exists($Directory.$ms_cfg_file)) {
      $fd = fopen ($Directory.$ms_cfg_file, "r");
      $capture='';
      while( !feof($fd) ) {

         $line = trim( fgets( $fd, 256 ) );
		
		 if (substr($line,0,2) == "</")
            $capture='';

         if ($capture == 'OK_ORDER_FIRST_TABLE')
            $list_plugins_first[]=$line;
            
         if ($capture == 'OK_ORDER_SECOND_TABLE')
            $list_plugins_second[]=$line;
         
         if ($line{0} == "<"){ 	//Getting tag type for the next launch of the loop
            $capture = 'OK_'.substr(substr($line,1),0,-1);
         }     
             
		flush();
      }
   fclose( $fd );
}

//Config for all user
$ms_cfg_file="4all_config.txt";
if (file_exists($Directory.$ms_cfg_file)) {
      $fd = fopen ($Directory.$ms_cfg_file, "r");
      $capture='';
      while( !feof($fd) ) {

         $line = trim( fgets( $fd, 256 ) );
		
		 if (substr($line,0,2) == "</")
            $capture='';
    	 
    	 if ($capture == 'OK_LBL'){
            $tab_lbl=explode(":", $line);
            $list_lbl[$tab_lbl[0]]=$tab_lbl[1];
         }
         
         if ($capture == 'OK_MENU'){
            $tab_menu=explode(":", $line);
            $list_menu[$tab_menu[1]][]=$tab_menu[0];
          //  $list_menu_V2[$tab_menu[0]]=$tab_menu[1];
         }
         
         if ($capture == 'OK_MENU_TITLE'){
            $tab_lbl_menu=explode(":", $line);
            $lbl_menu[$tab_lbl_menu[0]]=$tab_lbl_menu[1];
         }
         
         if ($capture == 'OK_MENU_NAME'){
            $tab_name_menu=explode(":", $line);
            $name_menu[$tab_name_menu[0]]=$tab_name_menu[1];
         }
         
         if ($capture == 'OK_URL'){
            $tab_url=explode(":", $line);
            $list_url[$tab_url[0]]=$tab_url[1];
          //  $pages_refs[$tab_url[0]]=$tab_url[1];
         }
         if ($capture == 'OK_DIRECTORY'){
            $tab_dir=explode(":", $line);
            $list_dir[$tab_dir[0]]=$tab_dir[1];
         }
         
         if ($line{0} == "<"){ 	//Getting tag type for the next launch of the loop
            $capture = 'OK_'.substr(substr($line,1),0,-1);
         }                  
		flush();
      }
   fclose( $fd );
}

//Splitting name_menu array for use with the "show_menu" javascript function
$all_menus=implode("|", $name_menu);



//Initiating icons
if( !isset($protectedGet["popup"] )) {
	//si la variable RESET existe
	//c'est que l'on a clique sur un icone d'un menu 
	if (isset($protectedPost['RESET'])){
		if ($_SESSION['DEBUG'] == 'ON')
			echo  "<br><b><font color=red>".$l->g(5003)."</font></b><br>";
		unset($_SESSION['DATA_CACHE']);	
	}
	//formulaire pour detecter le clic sur un bouton du menu
	//permet de donner la fonctionnalite
	//de reset du cache des tableaux
	//si on reclic sur le meme icone
	
	//echo $ban_head;
	echo "<table width='100%' border=0";
	echo "><tr><td >
			<table BORDER='0' ALIGN = 'left' CELLPADDING='0' BGCOLOR='#FFFFFF' BORDERCOLOR='#9894B5'";
			if ($ban_head=='no') echo " style='display:none;'";
		echo "><tr>";

//Using plugins sytem to show icons
$i=0;
while ($list_plugins_first[$i]){
	show_icon($list_plugins_first[$i],$list_lbl[$list_plugins_first[$i]]);	
	$i++;
	
}
echo "			</tr></table>
			</td><td>
			<table BORDER='0' ALIGN = 'right' CELLPADDING='0' BGCOLOR='#FFFFFF' BORDERCOLOR='#9894B5'";
			if ($ban_head=='no') echo " style='display:none;'";
			echo "><tr>";
$i=0;
while ($list_plugins_second[$i]){
		show_icon($list_plugins_second[$i],$list_lbl[$list_plugins_second[$i]]);
$i++;
	
}

echo "		</tr></table>
			</td></tr></table>";
}


function show_icon($index,$lbl_index){
	global $Directory,$list_url,$protectedGet,$l,$list_menu,$lbl_menu,$name_menu,$list_lbl,$all_menus;

	if ($name_menu[$index]){

			$name=$name_menu[$index];
			$packAct = $list_menu[$index];

			$nam_img=$index;
			$title=$l->g(substr(substr($lbl_menu[$index],2),0,-1));
			$i=0;
			while ($list_menu[$index][$i]){
				//echo $list_menu[$index]."<br>";
				$data_list_config[$list_url[$list_menu[$index][$i]]]=$l->g(substr(substr($list_lbl[$list_menu[$index][$i]],2),0,-1));
				$i++;
			}

			menu_list($name,$packAct,$nam_img,$title,$data_list_config);	
	}else{
	$img=$index;
	  $llink = "?".PAG_INDEX."=".$list_url[$index];
	 // echo $protectedGet[PAG_INDEX]."=>".$list_url[$index]."<br>";
	  if($protectedGet[PAG_INDEX] == $list_url[$index]) {
	  	
                $img .= "_a";
        }
        	if (substr($lbl_index,0,2) == 'g(')
		$lbl= $l->g(substr(substr($lbl_index,2),0,-1));

        //si on clic sur l'icone, on charge le formulaire
        //pour obliger le cache des tableaux a se vider
        echo "<td onmouseover=\"javascript:show_menu('nomenu','".$all_menus."');\"><a onclick='clic(\"".$llink."\");'><img title=\"".$lbl."\" src='$Directory/img/$img.png'></a></td>";
	}
	
	
}

function menu_list($name_menu,$packAct,$nam_img,$title,$data_list)
{
        global $protectedGet,$Directory,$list_url,$all_menus;

        $pag_name=array_flip($list_url);
        echo "<td onmouseover=\"javascript:show_menu('".$name_menu."','".$all_menus."');\">
        <dl id=\"menu\">
                <dt onmouseover=\"javascript:show_menu('".$name_menu."','".$all_menus."');\">
                <a href='javascript:void(0);'>
        <img src='$Directory/img/$nam_img";
       
	if( in_array($pag_name[$protectedGet[PAG_INDEX]],$packAct) ) {
		echo "_a"; 
	}

                echo ".png'></a></dt>
                        <dd id=\"".$name_menu."\" onmouseover=\"javascript:show_menu('".$name_menu."','".$all_menus."');\" onmouseout=\"javascript:show_menu('nomenu','".$all_menus."');\">
                                <ul>
                                        <li><b>".$title."</b></li>";
                                        foreach ($data_list as $key=>$values){
                                                echo "<li><a href=\"index.php?".PAG_INDEX."=".$key."\">".$values."</a></li>";
                                        }
                echo "</ul>
                        </dd>
        </dl>
        </td> ";

}

//Hidding menus to have a better display 
echo "<script language='javascript'>show_menu('nomenu','".$all_menus."');</script>";


if ($no_page != 'YES'){
	$name=array_flip($list_url);
	echo "<br><center><span id='wait' class='warn'><font color=red>".$l->g(332)."</font></span></center><br>";
		flush();
	if (isset($name[$protectedGet[PAG_INDEX]])){	
		if (isset($list_dir[$name[$protectedGet[PAG_INDEX]]]))
		$rep=$list_dir[$name[$protectedGet[PAG_INDEX]]];
		else
		$rep=$name[$protectedGet[PAG_INDEX]];
		require ($Directory.$rep."/".$name[$protectedGet[PAG_INDEX]].".php");
	}else
	require ("$Directory/ms_console/ms_console.php");		
}
?>
