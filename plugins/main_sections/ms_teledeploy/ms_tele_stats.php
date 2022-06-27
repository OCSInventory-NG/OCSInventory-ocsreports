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

if ($_SESSION['OCS']['profile']->getConfigValue('TELEDIFF') == "YES" && is_defined($protectedPost["ACTION"])) {
    require('require/function_server.php');
    if ($protectedPost["ACTION"] == "VAL_SUCC") {
        $result_line_delete = find_device_line('SUCCESS%', $protectedGet["stat"]);
    }
    if ($protectedPost["ACTION"] == "DEL_ALL") {
        $result_line_delete = find_device_line('NOTNULL', $protectedGet["stat"]);
    }
    if ($protectedPost["ACTION"] == "DEL_NOT") {
        $result_line_delete = find_device_line('NULL', $protectedGet["stat"]);
    }

    if (isset($result_line_delete) && is_array($result_line_delete)) {
        require('require/function_telediff.php');
        foreach ($result_line_delete as $key => $value) {
            desactive_packet($value, $key);
        }
    }
}

$form_name = "show_stats";
$table_name = $form_name;
echo open_form($form_name, '', '', 'form-horizontal');

$sql = "SELECT name FROM download_available WHERE fileid='%s'";
$arg = $protectedGet["stat"];
$res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
$row = mysqli_fetch_object($res);
printEnTete($l->g(498) . " <b>" . $row->name . "</b> (" . $l->g(296) . ": " . $protectedGet["stat"] . " )");
echo "</br></br></br>";

//count max values for stats
$sql_count = "SELECT COUNT(d.id) as nb
			FROM devices d, download_enable e
			WHERE e.fileid='%s'
 				AND e.id=d.ivalue
				AND d.name='DOWNLOAD'
				AND d.hardware_id NOT IN (SELECT id FROM hardware WHERE deviceid='_SYSTEMGROUP_' or deviceid='_DOWNLOADGROUP_')";
$arg = $protectedGet["stat"];
$rescount = mysql2_query_secure($sql_count, $_SESSION['OCS']["readServer"], $arg);
$row = mysqli_fetch_object($rescount);
$total = $row->nb;
if ($total <= 0) {
    msg_error($l->g(837));
    require_once(FOOTER_HTML);
    die();
}
$sqlStats = "SELECT COUNT(d.id) as nb, d.tvalue as txt
				FROM devices d, download_enable e
				WHERE e.fileid='%s'
	 				AND e.id=d.ivalue
					AND d.name='DOWNLOAD'
					AND d.hardware_id NOT IN (SELECT id FROM hardware WHERE deviceid='_SYSTEMGROUP_' or deviceid='_DOWNLOADGROUP_')
					and d.tvalue not like '%s'
					and d.tvalue not like '%s'
					and d.tvalue is not null
					group by d.tvalue
			union
				SELECT COUNT(d.id) as nb, '%s'
				FROM devices d, download_enable e
				WHERE e.fileid='%s'
	 				AND e.id=d.ivalue
					AND d.name='DOWNLOAD'
					AND d.hardware_id NOT IN (SELECT id FROM hardware WHERE deviceid='_SYSTEMGROUP_' or deviceid='_DOWNLOADGROUP_')
					and (d.tvalue like '%s'
					or d.tvalue  like '%s')
			union
				SELECT COUNT(d.id) as nb, '%s'
				FROM devices d, download_enable e
				WHERE e.fileid='%s'
	 				AND e.id=d.ivalue
					AND d.name='DOWNLOAD'
					AND d.hardware_id NOT IN (SELECT id FROM hardware WHERE deviceid='_SYSTEMGROUP_' or deviceid='_DOWNLOADGROUP_')
					and d.tvalue is null";

$arg = array($arg, 'EXIT_CODE%', 'ERR%', 'ERRORS', $arg, 'EXIT_CODE%', 'ERR%', 'WAITING', $arg);
$resStats = mysql2_query_secure($sqlStats . " ORDER BY nb DESC", $_SESSION['OCS']["readServer"], $arg);
$i = 0;
while ($row = mysqli_fetch_object($resStats)) {
    $txt_status = strtoupper($row->txt);
    $name_value[$i] = $txt_status;
    $link[$i] = $txt_status;

    $lang = array(
        'ERRORS' => 956,
        'NOTIFIED' => 1000,
        'SUCCESS' => 572,
        'WAITING' => 482,
    );
    if(isset($lang[$txt_status]))
        $name_value[$i] = strtoupper($l->g($lang[$txt_status]));

    $pourc = round(($row->nb * 100) / $total, 2);
    $legend[$i] = $name_value[$i] . " (" . $pourc . "%)";
    $lbl[$i] = $name_value[$i] . "<br>(" . $pourc . "%)";
    $count_value[$i] = $row->nb;
    if (isset($arr_FCColors[$i])) {
        $color[$i] = $arr_FCColors[$i];
    } else {
        $color[$i] = $arr_FCColors[$i - 10];
    }
    $color[$i] = "plotProps: {fill: \"" . $color[$i] . "\"}";
    $i++;
}
echo "<div class='col-md-12 col-xs-offset-0 col-md-offset-3'>";
$stats = new StatsChartsRenderer;
$name = ["teledeploy_stats" => "teledeploy_stats"];
$stats->createChartCanvas($name);
$chart["teledeploy_stats"] = ["name" => [0 => "teledeploy_stats"],
                              "count" => $count_value,
                              "name_value" => $legend];
$stats->createChart($chart);
echo "</div>";
echo "</br>";

if ($_SESSION['OCS']['profile']->getConfigValue('TELEDIFF') == "YES") {

    echo "<table class='table table-striped table-condensed table-hover cell-border dataTable' role='grid' style='margin: auto; width: 50%; border: 1px solid #dddddd;' width='100%'><thead><tr role='row'>";
    echo "<th tabindex='0' aria-controls='affich_stat' rowspan='1' colspan='1' style='width: 33%;'><a OnClick='pag(\"VAL_SUCC\",\"ACTION\",\"" . $form_name . "\");'><font>" . $l->g(483) . "</font></a></th>";
    echo "<th tabindex='0' aria-controls='affich_stat' rowspan='1' colspan='1' style='width: 33%;'><a OnClick='pag(\"DEL_ALL\",\"ACTION\",\"" . $form_name . "\");'><font>" . $l->g(571) . "</font></a></th>";
    echo "<th tabindex='0' aria-controls='affich_stat' rowspan='1' colspan='1' style='width: 33%;'><a OnClick='pag(\"DEL_NOT\",\"ACTION\",\"" . $form_name . "\");'><font>" . $l->g(575) . "</font></a></th>";
    echo "</tr></thead></table><br><br>";
    echo "<input type='hidden' id='ACTION' name='ACTION' value=''>";
}
echo "<div class='tableContainer'>
      <div id='affich_regex_wrapper' class='dataTables_wrapper form-inline'>
      <div>
      <div class='dataTables_scroll'>
      <table class='table table-striped table-condensed table-hover cell-border dataTable' role='grid' style='margin: auto; width: 70%; border: 1px solid #dddddd;' width='100%'><thead><tr role='row'>
      <th tabindex='0' aria-controls='affich_stat' rowspan='1' colspan='1' style='width: 5%;'>&nbsp;</th>
      <th tabindex='0' aria-controls='affich_stat' rowspan='1' colspan='1' style='width: 33%;'><font>" . $l->g(81) . "</font></th>
      <th tabindex='0' aria-controls='affich_stat' rowspan='1' colspan='1' style='width: 33%;'><font>" . $l->g(55) . "</font></th></tr></thead>
      <tbody>";
$j = 0;
$nb = 0;
while ($j < $i) {
    $nb += $count_value[$j];
    echo "<tr class='odd'>";
    if (isset($arr_FCColors[$j])) {
        echo "<td valign='top' colspan='1' style='background-color: #" . $arr_FCColors[$j] . ";'>";
    } else {
        echo "<td valign='top' colspan='1'>";
    }
    echo "&nbsp;</td><td valign='top' colspan='1'>" . $name_value[$j] . "</td><td valign='top' colspan='1'>
			<a href='index.php?" . PAG_INDEX . "=" . $pages_refs['ms_multi_search'] . "&prov=stat&id_pack=" . $protectedGet["stat"] . "&stat=" . urlencode($link[$j]) . "'>" . $count_value[$j] . "</a>";
    if (substr_count($link[$j], 'SUC')) {
        echo "<a href=\"index.php?" . PAG_INDEX . "=" . $pages_refs['ms_speed_stat'] . "&head=1&ta=" . urlencode($link[$j]) . "&stat=" . $protectedGet["stat"] . "\">&nbsp<span class='glyphicon glyphicon-stats'></span></a>";
    }
    echo "	</td></tr>";
    $j++;
}
echo "<tr bgcolor='#C7D9F5'><td valign='top' colspan='1' bgcolor='white'>&nbsp;</td><td valign='top' colspan='1'><font><b>" . $l->g(87) . "</b></font></td><td><font><b>" . $nb . "</b></font></td></tr>";
echo "</tbody></table></div></div></div></div></div><br><br><br><br>";
echo close_form();
?>
