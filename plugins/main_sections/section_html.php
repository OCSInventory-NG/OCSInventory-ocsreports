<?php
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
while ($_SESSION['list_plugins_first'][$i]){
	show_icon($_SESSION['list_plugins_first'][$i],$_SESSION['list_lbl'][$_SESSION['list_plugins_first'][$i]]);	
	$i++;
	
}
echo "			</tr></table>
			</td><td>
			<table BORDER='0' ALIGN = 'right' CELLPADDING='0' BGCOLOR='#FFFFFF' BORDERCOLOR='#9894B5'";
			if ($ban_head=='no') echo " style='display:none;'";
			echo "><tr>";
$i=0;
while ($_SESSION['list_plugins_second'][$i]){
		show_icon($_SESSION['list_plugins_second'][$i],$_SESSION['list_lbl'][$_SESSION['list_plugins_second'][$i]]);
$i++;
	
}

echo "		</tr></table>
			</td></tr></table>";
}






function show_icon($index,$lbl_index){
	global $protectedGet,$l;

	if ($_SESSION['name_menu'][$index]){

			$name=$_SESSION['name_menu'][$index];
			$packAct = $_SESSION['list_menu'][$index];

			$nam_img=$index;
			$title=$l->g(substr(substr($_SESSION['lbl_menu'][$index],2),0,-1));
			$i=0;
			while ($_SESSION['list_menu'][$index][$i]){
				//echo $list_menu[$index]."<br>";
				$data_list_config[$_SESSION['list_url'][$_SESSION['list_menu'][$index][$i]]]=$l->g(substr(substr($_SESSION['list_lbl'][$_SESSION['list_menu'][$index][$i]],2),0,-1));
				$i++;
			}

			menu_list($name,$packAct,$nam_img,$title,$data_list_config);	
	}else{
	$img=$index;
	  $llink = "?".PAG_INDEX."=".$_SESSION['list_url'][$index];
	 // echo $protectedGet[PAG_INDEX]."=>".$list_url[$index]."<br>";
	  if($protectedGet[PAG_INDEX] == $_SESSION['list_url'][$index]) {
	  	
                $img .= "_a";
        }
        	if (substr($lbl_index,0,2) == 'g(')
		$lbl= $l->g(substr(substr($lbl_index,2),0,-1));

        //si on clic sur l'icone, on charge le formulaire
        //pour obliger le cache des tableaux a se vider
        echo "<td onmouseover=\"javascript:show_menu('nomenu','".$_SESSION['all_menus']."');\"><a onclick='clic(\"".$llink."\");'><img title=\"".$lbl."\" src='".$_SESSION['main_sections_dir']."/img/$img.png'></a></td>";
	}
	
	
}

function menu_list($name_menu,$packAct,$nam_img,$title,$data_list)
{
        global $protectedGet;

        $pag_name=array_flip($_SESSION['list_url']);
        echo "<td onmouseover=\"javascript:show_menu('".$name_menu."','".$_SESSION['all_menus']."');\">
        <dl id=\"menu\">
                <dt onmouseover=\"javascript:show_menu('".$name_menu."','".$_SESSION['all_menus']."');\">
                <a href='javascript:void(0);'>
        <img src='".$_SESSION['main_sections_dir']."/img/$nam_img";
       
	if( in_array($pag_name[$protectedGet[PAG_INDEX]],$packAct) ) {
		echo "_a"; 
	}

                echo ".png'></a></dt>
                        <dd id=\"".$name_menu."\" onmouseover=\"javascript:show_menu('".$name_menu."','".$_SESSION['all_menus']."');\" onmouseout=\"javascript:show_menu('nomenu','".$_SESSION['all_menus']."');\">
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
echo "<script language='javascript'>show_menu('nomenu','".$_SESSION['all_menus']."');</script>";
echo "<br><center><span id='wait' class='warn'><font color=red>".$l->g(332)."</font></span></center><br>";
		flush();



?>