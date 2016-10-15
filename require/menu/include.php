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

require_once 'Menu.php';
require_once 'MenuElem.php';

require_once 'MenuRenderer.php';
require_once 'MainMenuRenderer.php';
require_once 'ComputerMenuRenderer.php';

require_once 'XMLMenuSerializer.php';
require_once 'TxtMenuSerializer.php';

function migrate_menus_2_2($config) {
	$txt_serializer = new TxtMenuSerializer();
	$xml_serializer = new XMLMenuSerializer();
	
	$filename = 'config/main_menu.xml';
	$menu = $txt_serializer->unserialize($config);
	$xml = $xml_serializer->serialize($menu);
	
	file_put_contents($filename, $xml);
}

function show_menu() {
	if (!file_exists(DOCUMENT_REAL_ROOT.'/config/main_menu.xml')) {
		migrate_config_2_2();
	}

	// Build menu
	$profile = $_SESSION['OCS']['profile'];
	$urls = $_SESSION['OCS']['url_service'];

	$menu_serializer = new XMLMenuSerializer();
	$menu = $menu_serializer->unserialize(file_get_contents('config/main_menu.xml'));
	$renderer = new MainMenuRenderer($profile, $urls);
	echo $renderer->render($menu);
}

?>