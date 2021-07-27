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
require('require/charts/StatsChartsRenderer.php');

$year_month['Dec'] = 12;
$year_month['Nov'] = 11;
$year_month['Oct'] = 10;
$year_month['Sep'] = 9;
$year_month['Aug'] = 8;
$year_month['Jul'] = 7;
$year_month['Jun'] = 6;
$year_month['May'] = 5;
$year_month['Apr'] = 4;
$year_month['Mar'] = 3;
$year_month['Feb'] = 2;
$year_month['Jan'] = 1;

$sql = "SELECT COUNT(*) c 
		FROM devices d, download_enable d_e,download_available d_a
		WHERE d.name='DOWNLOAD'
		AND d_e.id=d.ivalue
		AND d_a.fileid=d_e.fileid
		AND d_e.fileid='%s'";

$arg = $protectedGet['stat'];
$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
$item = mysqli_fetch_object($result);
$total_mach = $item->c;

if ($total_mach <= 0) {
	msg_error($l->g(837));
	require_once(FOOTER_HTML);
	die();
}

$sql = "SELECT d.hardware_id AS id,d.comments AS date_valid
		FROM devices d,download_enable d_e,download_available d_a
		WHERE d.name='DOWNLOAD'
		AND tvalue='%s'
		AND comments IS NOT NULL
		AND d_e.id=d.ivalue
		AND d_a.fileid=d_e.fileid
		AND d_e.fileid='%s'";

$arg = array(urldecode($protectedGet['ta']), $protectedGet['stat']);
$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
$nb_4_hour = array();

while ($item = mysqli_fetch_object($result)) {
	unset($data_temp, $day, $year, $hour_temp, $hour);
	$data_temp = explode(' ', $item->date_valid);

	if ($data_temp[2] != '') {
		$day = $data_temp[2];
	} else {
		$day = $data_temp[3];
	}

	$month = $data_temp[1];

	if (isset($data_temp[5])) {
		$year = $data_temp[5];
	} else {
		$year = $data_temp[4];
	}

	if ($data_temp[2] != '') {
		$hour_temp = explode(':', $data_temp[3]);
	} else {
		$hour_temp = explode(':', $data_temp[4]);
	}

	$timestamp = mktime($hour_temp[0], 0, 0, $year_month[$month], $day, $year);

	if (isset($nb_4_hour[$timestamp])) {
		$nb_4_hour[$timestamp] ++;
	} else {
		$nb_4_hour[$timestamp] = 1;
	}
}

ksort($nb_4_hour);
$i = 0;
$data = array();

foreach ($nb_4_hour as $key => $value) {
	$ancienne += $value;
	$data[$i] = round((($ancienne * 100) / $total_mach), 2);
	$legende[$i] = date($l->g(1242), $key);
	$i++;
}

if (isset($data) && count($data) != 1) {
	$stats = new StatsChartsRenderer;
	$name = ["teledeploy_speed" => "teledeploy_speed"];
	$stats->createChartCanvas($name, false, false);
	$stats->createPointChart("teledeploy_speed", $legende, $data, $l->g(1125));
} else {
	msg_warning($l->g(989));
}
?>
