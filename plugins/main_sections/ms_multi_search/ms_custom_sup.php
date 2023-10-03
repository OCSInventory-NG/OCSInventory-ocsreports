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
require_once('require/function_search.php');
require_once('require/function_computers.php');
PrintEnTete($l->g(985));
$form_name = "del_affect";
echo open_form($form_name, '', '', 'form-horizontal');
$list_id = multi_lot($form_name, $l->g(601));
echo "<div class='col col-md-12'>";
if (is_defined($protectedPost['SUP'])) {
    $array_id = explode(',', $list_id);
    foreach ($array_id as $hardware_id) {
        deleteDid($hardware_id);
    }
}
if ($list_id) {
    echo "<input type='submit' class='btn btn-success' value=".$l->g(122)." name='SUP'>";
    echo "</div>";
}
echo close_form();
