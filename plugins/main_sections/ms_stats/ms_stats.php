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

require('class/msstats.class.php');
require('class/msstatstop.class.php');
require('class/msstatsconnexion.class.php');

$stats = new MsStats();
$pages = $stats->checkForStatsPages();
$data_on = $stats->createShowTabsArray($pages);

printEnTete($stats->getHeaderName());

echo open_form($stats->getFormName(), '', '', 'form-horizontal');
show_tabs($data_on, $stats->getFormName(), "onglet", true);

echo '<div class="col col-md-10" >';
$stats->generateStatsData($protectedPost, $pages[0]);
echo "</div>";

echo close_form();
?>