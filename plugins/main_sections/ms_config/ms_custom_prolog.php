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
debut_tab(array('CELLSPACING' => '5',
    'WIDTH' => '70%',
    'BORDER' => '0',
    'ALIGN' => 'Center',
    'CELLPADDING' => '0',
    'BGCOLOR' => '#C7D9F5',
    'BORDERCOLOR' => '#9894B5'));

if (!isset($optvalue['PROLOG_FREQ'])) {
    $optvalueselected = 'SERVER DEFAULT';
} else {
    $optvalueselected = 'CUSTOM';
}
$champ_value['VALUE'] = $optvalueselected;
$champ_value['CUSTOM'] = $l->g(487);
$champ_value['SERVER DEFAULT'] = $l->g(488);
if (!isset($protectedGet['origine'])) {
    $champ_value['IGNORED'] = $l->g(718);
    $champ_value['VALUE'] = 'IGNORED';
}
ligne("PROLOG_FREQ", $l->g(724), 'radio', $champ_value, array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $optvalue['PROLOG_FREQ'] ?? '', 'END' => $l->g(730), 'JAVASCRIPT' => $numeric));
unset($champ_value);

if (!isset($optvalue['INVENTORY_ON_STARTUP'])) {
    $optvalueselected = 'SERVER DEFAULT';
} elseif ($optvalue['INVENTORY_ON_STARTUP'] == 0) {
    $optvalueselected = 'OFF';
} elseif ($optvalue['INVENTORY_ON_STARTUP'] == 1) {
    $optvalueselected = 'ON';
}
$champ_value['VALUE'] = $optvalueselected;
$champ_value['ON'] = 'ON';
$champ_value['OFF'] = 'OFF';
$champ_value['SERVER DEFAULT'] = $l->g(488);
if (!isset($protectedGet['origine'])) {
    $champ_value['IGNORED'] = $l->g(718);
    $champ_value['VALUE'] = 'IGNORED';
}
ligne("INVENTORY_ON_STARTUP", $l->g(2121), 'radio', $champ_value);
unset($champ_value);

fin_tab();
?>