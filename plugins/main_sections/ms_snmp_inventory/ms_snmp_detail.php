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
if (AJAX) {
    parse_str($protectedPost['ocs']['0'], $params);
    $protectedPost += $params;
    ob_start();
}

require('require/snmp/Snmp.php');

$snmp = New OCSSnmp();

// Initialize empty value
$getType    = null;
$getId      = null;

// Clean protectedGet
if(isset($protectedGet['type']))    $getType = preg_replace("/[^A-Za-z0-9\._]/", "", $protectedGet['type']);
if(isset($protectedGet['id']))      $getId = preg_replace("/[^0-9]/", "", $protectedGet['id']);

// Retrieve all equipment informations
$equipmentDetails   = $snmp->getDetails($getType, $getId);
$reconciliation     = $snmp->getReconciliationColumn($getType);

// Print reconciliation field as title
printEnTete($equipmentDetails[$reconciliation]);

echo "<div class='col col-md-12'>";

// XML export button
if ($_SESSION['OCS']['profile']->getRestriction('EXPORT_XML', 'NO') == "NO") {
    echo '<div>';
    echo '<button class= "btn btn-action" onclick=\'location.href="index.php?'.PAG_INDEX.'='.$urls->getUrl('ms_export_snmp').'&no_header=1&type='.$getType.'&id='.$getId.'";\' target="_blank">'.$l->g(1304).'</button>';
    echo '</div>';
}

echo "<div class='col col-md-3'></div>";
echo "<div class='col col-md-6' style='margin-top:30px;'>";
echo "<div class='row'>";

foreach($equipmentDetails as $label => $detail) {
    if($label != "ID") {
        echo "<div class='col' align='left'>";
        if($label == "LASTDATE") {
            echo '<span class="summary-header text-left" style="vertical-align:middle;">'.$l->g(46).' :</span>';
        } else {
            echo '<span class="summary-header text-left" style="vertical-align:middle;">'.$label.' :</span>';
        }
        echo '<span class="summary-value text-left" style="vertical-align:middle;">'.$detail.'</span>';
        echo "</div><hr>";
    }
}

echo "</div>";
echo "</div>";
echo "</div>";

if (AJAX) {
    ob_end_clean();
}
