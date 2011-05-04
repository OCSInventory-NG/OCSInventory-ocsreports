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

//Initiating icons
if( !isset($protectedGet["popup"] )) {
	echo "<table width='95%' border=0 align=center";
	echo "><tr><td >
			<table BORDER='0' ALIGN = 'left' CELLPADDING='0'";
			if ($ban_head=='no') echo " style='display:none;'";
		echo "><tr>";

//Using plugins sytem to show icons
$i=0;
while ($_SESSION['OCS']['ORDER_FIRST_TABLE'][$i]){
	show_icon($_SESSION['OCS']['ORDER_FIRST_TABLE'][$i],$_SESSION['OCS']['LBL'][$_SESSION['OCS']['ORDER_FIRST_TABLE'][$i]]);	
	$i++;
	
}
$align="right";
echo "			</tr></table>
			</td><td>
			<table BORDER='0' ALIGN = '".$align."' CELLPADDING='0'";
			if ($ban_head=='no') echo " style='display:none;'";
			echo "><tr>";
$i=0;
while ($_SESSION['OCS']['ORDER_SECOND_TABLE'][$i]){
		show_icon($_SESSION['OCS']['ORDER_SECOND_TABLE'][$i],$_SESSION['OCS']['LBL'][$_SESSION['OCS']['ORDER_SECOND_TABLE'][$i]]);
$i++;
	
}

echo "		</tr></table>
			</td></tr></table>";
}






function show_icon($index,$lbl_index){
	global $protectedGet,$l;
	
//if (isset($_SESSION['OCS']['list_page_profil'][$index])){
	if ($_SESSION['OCS']['MENU_NAME'][$index]){
			$name=$_SESSION['OCS']['MENU_NAME'][$index];
			foreach ($_SESSION['OCS']['MENU'] as $key=>$value){
				if ($value == $index)
					$packAct[]=$key;
			}		
			$nam_img=$index;
			$title=$l->g(substr(substr($_SESSION['OCS']['MENU_TITLE'][$index],2),0,-1));
			foreach ($_SESSION['OCS']['MENU'] as $name_page=>$name_menu){
				if (isset($_SESSION['OCS']['PAGE_PROFIL'][$name_page]) and $name_menu == $index)
				$data_list_config[$_SESSION['OCS']['URL'][$name_page]]=$l->g(substr(substr($_SESSION['OCS']['LBL'][$name_page],2),0,-1));
			}
			if (isset($data_list_config))
			menu_list($name,$packAct,$nam_img,$title,$data_list_config);	
	}elseif (isset($_SESSION['OCS']['PAGE_PROFIL'][$index])){
	$img=$index;
	  $llink = "?".PAG_INDEX."=".$_SESSION['OCS']['URL'][$index];
	 // echo $protectedGet[PAG_INDEX]."=>".$list_url[$index]."<br>";
	  if($protectedGet[PAG_INDEX] == $_SESSION['OCS']['URL'][$index]) {
	  	
                $img .= "_a";
        }
        	if (substr($lbl_index,0,2) == 'g(')
		$lbl= ucfirst($l->g(substr(substr($lbl_index,2),0,-1)));

        //si on clic sur l'icone, on charge le formulaire
        //pour obliger le cache des tableaux a se vider
        echo "<td onmouseover=\"javascript:show_menu('nomenu','".$_SESSION['OCS']['all_menus']."');\"><a onclick='clic(\"".$llink."\");' href='".$llink."'><img title=\"".$lbl."\" src='".MAIN_SECTIONS_DIR."/img/$img.png'></a></td>";
	}
	
//}
}

function menu_list($name_menu,$packAct,$nam_img,$title,$data_list)
{
        global $protectedGet;

        $pag_name=array_flip($_SESSION['OCS']['URL']);
      //	print_r($_SESSION['OCS']['all_menus']);
        echo "<td onmouseover=\"javascript:show_menu('".$name_menu."','".$_SESSION['OCS']['all_menus']."');\">
        <dl id=\"menu\">
                <dt onmouseover=\"javascript:show_menu('".$name_menu."','".$_SESSION['OCS']['all_menus']."');\">
                <a href='javascript:void(0);'>
        <img src='".MAIN_SECTIONS_DIR."/img/$nam_img";
       
	if( in_array($pag_name[$protectedGet[PAG_INDEX]],$packAct))  {
		echo "_a"; 
	}

                echo ".png'></a></dt>
                        <dd id=\"".$name_menu."\" onmouseover=\"javascript:show_menu('".$name_menu."','".$_SESSION['OCS']['all_menus']."');\" onmouseout=\"javascript:show_menu('nomenu','".$_SESSION['OCS']['all_menus']."');\">
                                <ul>
                                        <li><b>".ucfirst($title)."</b></li>";
                                        foreach ($data_list as $key=>$values){
                                        	if (isset($_SESSION['OCS']['PAGE_PROFIL'][$pag_name[$key]]))
                                                echo "<li><a href=\"index.php?".PAG_INDEX."=".$key."\">".ucfirst($values)."</a></li>";
                                        }
                echo "</ul>
                        </dd>
        </dl>
        </td> ";

}

//Hidding menus to have a better display 
echo "<script language='javascript'>show_menu('nomenu','".$_SESSION['OCS']['all_menus']."');</script>";
echo "<br><center><span id='wait' class='warn'><font color=red>".$l->g(332)."</font></span></center><br>";
		flush();
$span_wait=1;



?>