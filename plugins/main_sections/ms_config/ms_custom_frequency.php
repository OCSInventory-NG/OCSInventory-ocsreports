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
    'WIDTH' => '80%',
    'BORDER' => '0',
    'ALIGN' => 'Center',
    'CELLPADDING' => '0',
    'BGCOLOR' => '#C7D9F5',
    'BORDERCOLOR' => '#9894B5'));
if (isset($optvalue['FREQUENCY']) && $optvalue['FREQUENCY'] == 0) {
    $optvalueselected = 'ALWAYS';
} elseif (isset($optvalue['FREQUENCY']) && $optvalue['FREQUENCY'] == -1) {
    $optvalueselected = 'NEVER';
} elseif (!isset($optvalue['FREQUENCY'])) {
    $optvalueselected = 'SERVER DEFAULT';
} else {
    $optvalueselected = 'CUSTOM';
}
$champ_value['VALUE'] = $optvalueselected;
$champ_value['ALWAYS'] = $l->g(485);
$champ_value['NEVER'] = $l->g(486);
$champ_value['CUSTOM'] = $l->g(487);
$champ_value['SERVER DEFAULT'] = $l->g(488);
if (!isset($protectedGet['origine'])) {
    $champ_value['IGNORED'] = $l->g(718);
    $champ_value['VALUE'] = 'IGNORED';
}
ligne("FREQUENCY", $l->g(494), 'radio', $champ_value, array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $optvalue['FREQUENCY'] ?? '', 'END' => $l->g(496), 'JAVASCRIPT' => $numeric));
fin_tab();
?>