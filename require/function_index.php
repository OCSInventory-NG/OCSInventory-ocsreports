<?php


//Creating array for icons (this is not really a function, juste for code reading)

{
        $icons_list['ms_all_computers']=create_icon($l->g(2), $pages_refs['ms_all_computers']);
        $icons_list['ms_repart_tag']=create_icon($l->g(178), $pages_refs['ms_repart_tag']);
        $icons_list['ms_groups']=create_icon($l->g(583), $pages_refs['ms_groups']);
        $icons_list['ms_all_soft']=create_icon($l->g(765), $pages_refs['ms_all_soft']);
        $icons_list['ms_multi_search']=create_icon($l->g(9), $pages_refs['ms_multi_search']);
        $icons_list['ms_dict']=create_icon($l->g(380), $pages_refs['ms_dict']);
        $icons_list['ms_upload_file']=create_icon($l->g(17) , $pages_refs['ms_upload_file']);
        $icons_list['ms_regconfig']=create_icon($l->g(211), $pages_refs['ms_regconfig']);
        $icons_list['ms_logs']=create_icon($l->g(928), $pages_refs['ms_logs']);
        $icons_list['ms_admininfo']=create_icon($l->g(225), $pages_refs['ms_admininfo']);
        $icons_list['ms_ipdiscover']=create_icon($l->g(174), $pages_refs['ms_ipdiscover']);
        $icons_list['ms_doubles']=create_icon($l->g(175), $pages_refs['ms_doubles']);
        $icons_list['ms_label']=create_icon($l->g(263), $pages_refs['ms_label']);
        $icons_list['ms_users']=create_icon($l->g(243), $pages_refs['ms_users']);
        $icons_list['ms_local']=create_icon($l->g(287), $pages_refs['ms_local']);
        $icons_list['ms_help']=create_icon($l->g(570), $pages_refs['ms_help']);
}


function getmicrotime() {
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}


function create_icon( $label, $biere ) {
	
	global $pages_refs,$protectedGet;
        $llink = "?".PAG_INDEX."=$biere";

        switch($biere) {

                case $pages_refs['ms_codes']: $img = "codes"; break;
                case $pages_refs['ms_ipdiscover']: $img = "securite"; break;
                case $pages_refs['ms_config']: $img = "configuration"; break;
                case $pages_refs['ms_regconfig']: $img = "regconfig"; break;
                case $pages_refs['ms_doubles']: $img = "doublons"; break;
                case $pages_refs['ms_upload_file']: $img = "agent"; break;
                case $pages_refs['ms_admininfo']: $img = "administration"; break;
                case $pages_refs['ms_label']: $img = "label"; break;
                case $pages_refs['ms_local']: $img = "local"; break;
                case $pages_refs['ms_dict']: $img = "dictionnaire"; break;
                case $pages_refs['ms_help']: $img = "aide";$llink = "http://wiki.ocsinventory-ng.org"; break;
                case $pages_refs['ms_all_soft']: $img = "ttlogiciels"; break;
                case $pages_refs['ms_groups']: $img = "groups"; break;
                case $pages_refs['ms_logs']: $img = "log"; break;
                case $pages_refs['ms_multi_search']: $img = "recherche"; break;
                case $pages_refs['ms_stats']: $img = "statistiques"; break;
                case $pages_refs['ms_all_computers']: $img = "ttmachines"; break;
                case $pages_refs['ms_repart_tag']: $img = "repartition"; break;
                case $pages_refs['ms_users']: $img = "utilisateurs"; break;
        }
        if($protectedGet[PAG_INDEX] == $biere && $biere != "" ) {
                $img .= "_a";
        }
		//echo $img."<br>";
        //si on clic sur l'icone, on charge le formulaire
        //pour obliger le cache des tableaux a se vider
        return "<td onmouseover=\"javascript:montre();\"><a onclick='clic(\"".$llink."\");'><img title=\"".htmlspecialchars($label)."\" src='image/$img.png' id=$img></a></td>";
}


function menu_list($name_menu,$packAct,$nam_img,$title,$data_list)
{
        global $protectedGet;
		print_r($packAct);
        echo "<td onmouseover=\"javascript:montre('".$name_menu."');\">
        <dl id=\"menu\">
                <dt onmouseover=\"javascript:montre('".$name_menu."');\">
                <a href='javascript:void(0);'>
        <img src='image/".$nam_img;
        
	if( in_array($protectedGet[PAG_INDEX],$packAct) ) {
		//echo "'>toto<img src='image/".$nam_img;
		echo "_a"; 
	}

                echo ".png'></a></dt>
                        <dd id=\"".$name_menu."\" onmouseover=\"javascript:montre('".$name_menu."');\" onmouseout=\"javascript:montre();\">
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

?>
