<?php

function show_menu($config) {
	if (!is_array($config)) {
		return false;
	}
	// Build menu
	$menu = new Menu();
	foreach ($config as $config_elem) {
		$url = "?".PAG_INDEX."=".$_SESSION['OCS']['URL'][$config_elem];
		
		if (isset($_SESSION['OCS']['MENU_NAME'][$config_elem])) {
			$lbl_index = $_SESSION['OCS']['MENU_TITLE'][$config_elem];
			
			if (is_null($lbl_index)) {
				$lbl = $config_elem;
			} else {
				$lbl = find_lbl($lbl_index);
			}
			
			$menu->addElem($config_elem, new MenuElem($lbl, $url));
			
			// Element has children
			foreach ($_SESSION['OCS']['MENU'] as $page_name => $menu_name) {
				if (isset($_SESSION['OCS']['PAGE_PROFIL'][$page_name]) and $menu_name == $config_elem) {
					$url = "?".PAG_INDEX."=".$_SESSION['OCS']['URL'][$page_name];
					$lbl = find_lbl($_SESSION['OCS']['LBL'][$page_name]);

					$menu->getElem($config_elem)->addElem($page_name, new MenuElem($lbl, $url));
				}
			}
		} else {
			// No children
			$lbl_index = $_SESSION['OCS']['LBL'][$config_elem];
			
			if (is_null($lbl_index)) {
				$lbl = $config_elem;
			} else {
				$lbl = find_lbl($lbl_index);
			}
			
			$menu->addElem($config_elem, new MenuElem($lbl, $url));
		}
	}
	
	$renderer = new BootstrapMenuRenderer();
	//$renderer->setParentElemClickable(true);
	echo $renderer->render($menu);
}

//function to show an icons group
function show_icon_block($order){	
	if (!is_array($order))
	return false;
	$i=0;
	while ($order[$i]){
		show_icon($order[$i],$_SESSION['OCS']['LBL'][$order[$i]]);	
		$i++;
	}
}

//Show only icons you have to see	
function show_icon($index,$lbl_index){
	global $protectedGet,$l;
	if (isset($_SESSION['OCS']['MENU_NAME'][$index])){
			$name=$_SESSION['OCS']['MENU_NAME'][$index];
			foreach ($_SESSION['OCS']['MENU'] as $key=>$value){
				if ($value == $index)
					$packAct[]=$key;
			}		
			$nam_img=$index;
			$title=find_lbl($_SESSION['OCS']['MENU_TITLE'][$index]);
			foreach ($_SESSION['OCS']['MENU'] as $name_page=>$name_menu){
				if (isset($_SESSION['OCS']['PAGE_PROFIL'][$name_page]) and $name_menu == $index)
				$data_list_config[$_SESSION['OCS']['URL'][$name_page]]= find_lbl($_SESSION['OCS']['LBL'][$name_page]);
			}
			if (isset($data_list_config))
				menu_list($name,$packAct,$nam_img,$title,$data_list_config,$lbl_index);	
	}elseif (isset($_SESSION['OCS']['PAGE_PROFIL'][$index])){
		show_icon_simple($index,$lbl_index,$index);
	}

}

//Show menu of icons
function menu_list($name_menu,$packAct,$nam_img,$title,$data_list,$lbl_index)
{
        global $protectedGet;
         $pag_name=array_flip($_SESSION['OCS']['URL']);
        if (count($data_list)<=1){
        	$info=each($data_list);
        	show_icon_simple($_SESSION['OCS']['PAGE_PROFIL'][$pag_name[$info[0]]],$info[1],$nam_img);
        }else{
	       
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

}

//Show only 1 icon
function show_icon_simple($index,$lbl_index,$img){
	global $protectedGet,$l;
	  $llink = "?".PAG_INDEX."=".$_SESSION['OCS']['URL'][$index];
	 // echo $protectedGet[PAG_INDEX]."=>".$list_url[$index]."<br>";
	  if($protectedGet[PAG_INDEX] == $_SESSION['OCS']['URL'][$index]) {	  	
                $img .= "_a";
      }
      $lbl=find_lbl($lbl_index);
 
        //si on clic sur l'icone, on charge le formulaire
        //pour obliger le cache des tableaux a se vider
        echo "<td onmouseover=\"javascript:show_menu('nomenu','".$_SESSION['OCS']['all_menus']."');\">
        		<a  onclick='clic(\"".$llink."\",1);'>
        			<img title=\"".$lbl."\" src='".MAIN_SECTIONS_DIR."/img/$img.png'>
        		</a>
        	</td>";
	
	
	
}

//Find the lbl of the icon
function find_lbl($id){
	global $l;
	if (substr($id,0,2) == 'g(')
		$lbl= ucfirst($l->g(substr(substr($id,2),0,-1)));
	else
		$lbl=$id;
	return strip_tags_array($lbl);
}


?>