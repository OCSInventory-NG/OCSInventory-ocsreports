<?php
/*
 * Created on 26 mai 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once('require/function_files.php');
$Directory=PLUGINS_DIR.'language/';
$ms_cfg_file= $Directory."/lang_config.txt";	
	//show only true sections
if (file_exists($ms_cfg_file)) {
		$search=array('ORDER'=>'MULTI2','LBL'=>'MULTI');
		$language_data=read_configuration($ms_cfg_file,$search);
		$list_plugins=$language_data['ORDER'];
		$list_lbl=$language_data['LBL'];
}

$i=0;

while (isset($list_plugins[$i])){
	if (file_exists($Directory.$list_plugins[$i]."/".$list_plugins[$i].".png"))
	$show_lang.= "<img src='plugins/language/".$list_plugins[$i]."/".$list_plugins[$i].".png' width=\"20\" height=\"15\" OnClick='pag(\"".$list_plugins[$i]."\",\"LANG\",\"ACTION_CLIC\");'>&nbsp;";
	else
	$show_lang.= "<a href=# OnClick='pag(\"".$list_plugins[$i]."\",\"LANG\",\"ACTION_CLIC\");'>".$list_lbl[$list_plugins[$i]]."</a>&nbsp;";
	$i++;	
}

echo $show_lang;
?>
