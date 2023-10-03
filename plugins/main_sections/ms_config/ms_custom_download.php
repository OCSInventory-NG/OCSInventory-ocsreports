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
//PrintEnTete( $l->g(710));
debut_tab(array('CELLSPACING' => '5',
    'WIDTH' => '70%',
    'BORDER' => '0',
    'ALIGN' => 'Center',
    'CELLPADDING' => '0',
    'BGCOLOR' => '#C7D9F5',
    'BORDERCOLOR' => '#9894B5'));

if (!isset($optvalue['DOWNLOAD_SWITCH'])) {
    $optvalueselected = 'SERVER DEFAULT';
} elseif ($optvalue['DOWNLOAD_SWITCH'] == 0) {
    $optvalueselected = 'OFF';
} elseif ($optvalue['DOWNLOAD_SWITCH'] == 1) {
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
ligne("DOWNLOAD_SWITCH", $l->g(417), 'radio', $champ_value);
unset($champ_value);

if (!isset($optvalue['DOWNLOAD_CYCLE_LATENCY'])) {
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
ligne("DOWNLOAD_CYCLE_LATENCY", $l->g(720), 'radio', $champ_value, array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $optvalue['DOWNLOAD_CYCLE_LATENCY'] ?? '', 'END' => $l->g(511), 'JAVASCRIPT' => $numeric));

if (!isset($optvalue['DOWNLOAD_FRAG_LATENCY'])) {
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
ligne("DOWNLOAD_FRAG_LATENCY", $l->g(721), 'radio', $champ_value, array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $optvalue['DOWNLOAD_FRAG_LATENCY'] ?? '', 'END' => $l->g(511), 'JAVASCRIPT' => $numeric));

if (!isset($optvalue['DOWNLOAD_PERIOD_LATENCY'])) {
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
ligne("DOWNLOAD_PERIOD_LATENCY", $l->g(722), 'radio', $champ_value, array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $optvalue['DOWNLOAD_PERIOD_LATENCY'] ?? '', 'END' => $l->g(511), 'JAVASCRIPT' => $numeric));

if (!isset($optvalue['DOWNLOAD_PERIOD_LENGTH'])) {
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
ligne("DOWNLOAD_PERIOD_LENGTH", $l->g(723), 'radio', $champ_value, array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $optvalue['DOWNLOAD_PERIOD_LENGTH'] ?? '', 'JAVASCRIPT' => $numeric));

if (!isset($optvalue['DOWNLOAD_TIMEOUT'])) {
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
ligne("DOWNLOAD_TIMEOUT", $l->g(424), 'radio', $champ_value, array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $optvalue['DOWNLOAD_TIMEOUT'] ?? '', 'END' => $l->g(496), 'JAVASCRIPT' => $numeric));

fin_tab();
?>