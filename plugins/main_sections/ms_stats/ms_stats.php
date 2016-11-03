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
require('require/function_stats.php');

$form_name = "stats";
$table_name = $form_name;
printEnTete($l->g(1251));
echo open_form($form_name, '', '', 'form-horizontal');
$plugin = false;
$stats = '';

foreach ($_SESSION['OCS']['url_service']->getUrls() as $name => $url) {
    if (substr($name, 0, 9) == 'ms_stats_' && $url['directory'] == 'ms_stats') {
        $plugin = true;
        require_once($name . ".php");
    }
}

if ($plugin){
	//Create the chart - Column 3D Chart with data from strXML variable using dataXML method
	show_tabs($data_on,$form_name,"onglet",true);
	echo '<div class="col col-md-10" >';
	echo $stats;		
	echo "</div>";
}else
	msg_warning($l->g(1262));
echo close_form();

?>