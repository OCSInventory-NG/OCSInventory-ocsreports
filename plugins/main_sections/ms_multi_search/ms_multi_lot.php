<?php
$form_name='admin_search';

if ($list_id != ""){
	if (strpos($protectedGet['img'], "config_search.png"))
		include ("opt_param.php");
	elseif (strpos($protectedGet['img'], "groups_search.png"))
		 include ("opt_groups.php");
 	elseif (strpos($protectedGet['img'], "tele_search.png"))
 		 include ("opt_pack.php");
 	elseif (strpos($protectedGet['img'], "sup_search.png"))
 		 include ("opt_sup.php");
 	elseif (strpos($protectedGet['img'], "cadena_ferme.png"))
 		 include ("opt_lock.php");
 	elseif(strpos($protectedGet['img'], "mass_affect.png"))
 		 include ("opt_tag.php");
 	else
 		return false;
}else
	echo "<br><br><b><font color=red size=4>".$l->g(954)."</font></b>";

?>
