<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */
@session_start();
if (AJAX) {
    parse_str($protectedPost['ocs']['0'], $params);
    $protectedPost += $params;
    ob_start();
}
require('require/function_opt_param.php');
require('require/function_graphic.php');
require_once('require/extensions/ExtensionManager.php');
require_once('require/extensions/ExtensionHook.php');
require_once('require/function_machine.php');
require_once('require/function_files.php');
require_once('ms_computer_views.php');
require_once('require/archive/ArchiveComputer.php');
//recherche des infos de la machine
$item = info($protectedGet, $protectedPost['systemid']);
if (!is_object($item)) {
    msg_error($item);
    require_once(FOOTER_HTML);
    die();
}
//you can't view groups'detail by this way
if ($item->DEVICEID == "_DOWNLOADGROUP_" || $item->DEVICEID == "_SYSTEMGROUP_") {
    die('FORBIDDEN');
}

$systemid = $item->ID;

if (!isset($protectedGet['option']) && !isset($protectedGet['cat'])) {
    $protectedGet['cat'] = 'admin';
}

show_computer_menu($item->ID);

echo '<div class="col col-md-10">';

//Wake On Lan function
if (isset($protectedPost["WOL"]) && $protectedPost["WOL"] == 'WOL' && $_SESSION['OCS']['profile']->getRestriction('WOL', 'NO') == "NO") {
    require_once('require/wol/WakeOnLan.php');
    $wol = new Wol();
    $sql = "select MACADDR,IPADDRESS,IPMASK from networks WHERE (hardware_id=%s) and status='Up'";
    $arg = array($item->ID);
    $resultDetails = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
    $msg = "";

    while ($wol_item = mysqli_fetch_object($resultDetails)) {
        $broadcast = long2ip(ip2long($wol_item->IPADDRESS) | ~ip2long($wol_item->IPMASK));
        $wol->look_config_wol($broadcast, $wol_item->MACADDR);

        if ($wol->wol_send == $l->g(1282)) {
            msg_info($wol->wol_send . "=>" . $wol_item->MACADDR . "/" . $wol_item->IPADDRESS);
        } else {
            msg_error($wol->wol_send . "=>" . $wol_item->MACADDR . "/" . $wol_item->IPADDRESS);
        }
    }
}

show_computer_title($item);

$archive = new ArchiveComputer();
if (isset($protectedPost["ARCHIVE"]) && $protectedPost['ARCHIVE'] == 'Archive') {
    $archive->archive($item->ID);
} elseif (isset($protectedPost["ARCHIVE"]) && $protectedPost['ARCHIVE'] == 'Restore') {
    $archive->restore($item->ID);
}

show_computer_actions($item);

if (isset($protectedGet['cat']) && $protectedGet['cat'] == 'admin') {
    show_computer_summary($item);
}

if (AJAX) {
    ob_end_clean();
}

$plugins_serializer = new XMLPluginsSerializer();
$plugins = $plugins_serializer->unserialize(file_get_contents( CD_CONFIG_DIR .'plugins.xml'));
$extMgr = new ExtensionManager();
$extHooks = new ExtensionHook($extMgr->installedExtensionsList);

if (isset($protectedGet['cat']) && in_array($protectedGet['cat'], array('software', 'hardware', 'network', 'devices', 'admin', 'config', 'teledeploy', 'other'))) {
    // If category
    foreach ($plugins as $plugin) {
        if ($plugin->getCategory() == $protectedGet['cat']) {
            $plugin_file = PLUGINS_DIR . "computer_detail/" . $plugin->getId() . "/" . $plugin->getId() . ".php";
            $protectedPost['computersectionrequest'] = $plugin->getId();
            if (file_exists($plugin_file)) {
                if ($plugin->getHideFrame()) {
                    require $plugin_file;
                } else {
                    echo '<div class="plugin-name-' . $plugin->getId() . ' ">';
                    require $plugin_file;
                    echo '</div>';
                }
            }
        }
    }
    // Load cd entries for extensions
    if($extHooks->getCdEntryByCategory($protectedGet['cat']) != null ){
        foreach ($extHooks->getCdEntryByCategory($protectedGet['cat']) as $extensionPlugins){
            $fileName = EXT_DL_DIR.$extensionPlugins[ExtensionHook::EXTENSION]."/".$extensionPlugins[ExtensionHook::IDENTIFIER]."/".$extensionPlugins[ExtensionHook::IDENTIFIER].".php";
            $protectedPost['computersectionrequest'] = $extensionPlugins[ExtensionHook::EXTENSION];
            if(file_exists($fileName)){
                echo '<div class="plugin-name-' . $extensionPlugins[ExtensionHook::IDENTIFIER] . ' ">';
                require $fileName;
                echo '</div>';
            }
        }
    }
} else if (isset($protectedGet['option'])) {
    // If specific plugin
    $plugin = $plugins[$protectedGet['option']];
    if($plugin != null){
        $plugin_file = PLUGINS_DIR . "computer_detail/" . $plugin->getId() . "/" . $plugin->getId() . ".php";
    }else{
        $file_extension = EXT_DL_DIR . $protectedGet['option'] . "/cd_" . $protectedGet['option'] . "/cd_" . $protectedGet['option'] .".php";
    }

    if (file_exists($plugin_file) || file_exists($file_extension)) {
        if (!AJAX) {
            if(file_exists($file_extension)){
                echo '<div class="plugin-name-' . $protectedGet['option'] . '">';
            }else{
                echo '<div class="plugin-name-' . $plugin->getId() . '">';
            }
        }
        if(file_exists($file_extension)){
            require $file_extension;
        }else{
            require $plugin_file;
        }
        if (!AJAX) {
            echo '</div>';
        }
    }
} else {
    // Else error
    msg_error('Page not found');
}

echo '</div>';

if (AJAX) {
    ob_end_clean();
}
?>
