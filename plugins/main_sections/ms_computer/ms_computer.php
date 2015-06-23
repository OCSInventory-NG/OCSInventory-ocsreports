<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Pierre LEMMET 2005
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================
//Modified on $Date: 2010 $$Author: Erwan Goalou
@session_start();
if(AJAX){
	parse_str($protectedPost['ocs']['0'], $params);
	$protectedPost+=$params;
	ob_start();
	$ajax = true;
}
else{
	$ajax=false;
}
require('require/function_opt_param.php');
require('require/function_graphic.php');
require_once('require/function_machine.php');
require_once('require/function_files.php');
require_once('ms_computer_views.php');
//recherche des infos de la machine

$item=info($protectedGet,$protectedPost['systemid']);
if (!is_object($item)){
	msg_error($item);
	require_once(FOOTER_HTML);
	die();
}
//you can't view groups'detail by this way
if ( $item->DEVICEID == "_DOWNLOADGROUP_"
	or $item->DEVICEID == "_SYSTEMGROUP_"){
	die('FORBIDDEN');	
}

$systemid = $item->ID;

if (!isset($protectedGet['option']) and !isset($protectedGet['cat'])) {
	$protectedGet['cat'] = 'admin';
}

echo '<div class="left-menu">';
show_computer_menu($item->ID);
echo '</div>';

echo '<div class="right-content">';

show_computer_title($item);

if (isset($protectedGet['cat']) and $protectedGet['cat'] == 'admin') {
	show_computer_summary($item);
}

//Wake On Lan function
if (isset($protectedPost["WOL"]) and $protectedPost["WOL"] == 'WOL' and $_SESSION['OCS']['profile']->getRestriction('WOL', 'NO')=="NO"){
	require_once('require/function_wol.php');
	$wol = new Wol();
	$sql = "select MACADDR,IPADDRESS from networks WHERE (hardware_id=%s) and status='Up'";
	$arg = array($item->ID);
	$resultDetails = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
	$msg = "";

	while ($item = mysqli_fetch_object($resultDetails)){
		$wol->wake($item->MACADDR,$item->IPADDRESS);
			
		if ($wol->wol_send == $l->g(1282)) {
			msg_info($wol->wol_send."=>".$item->MACADDR."/".$item->IPADDRESS);
		} else {
			msg_error($wol->wol_send."=>".$item->MACADDR."/".$item->IPADDRESS);
		}
	}
}


if ($ajax) {
	ob_end_clean();
}

$plugins_serializer = new XMLPluginsSerializer();
$plugins = $plugins_serializer->unserialize(file_get_contents('config/computer/plugins.xml'));

if (isset($protectedGet['cat']) and in_array($protectedGet['cat'], array('software', 'hardware', 'devices', 'admin', 'config', 'other'))) {
	// If category
	foreach ($plugins as $plugin) {
		if ($plugin->getCategory() == $protectedGet['cat']) {
			$plugin_file = PLUGINS_DIR."computer_detail/".$plugin->getId()."/".$plugin->getId().".php";
			$protectedPost['computersectionrequest'] = $plugin->getId();
			if (file_exists($plugin_file)) {
				if ($plugin->getHideFrame()) {
					require $plugin_file;
				} else {
					echo '<div class="plugin-frame plugin-name-'.$plugin->getId().'">';
					require $plugin_file;
					echo '</div>';
				}
			}
		}
	}
} else if (isset($protectedGet['option']) and isset($plugins[$protectedGet['option']])) {
	// If specific plugin
	$plugin = $plugins[$protectedGet['option']];
	$plugin_file = PLUGINS_DIR."computer_detail/".$plugin->getId()."/".$plugin->getId().".php";
	
	if (file_exists($plugin_file)) {
		if (!$ajax) echo '<div class="plugin-frame plugin-name-'.$plugin->getId().'">';
		require $plugin_file;
		if (!$ajax) echo '</div>';
	}
} else {
	// Else error
	msg_error('Page not found');
}

echo '</div>';

if ($ajax) {
	ob_end_clean();
}


?>
