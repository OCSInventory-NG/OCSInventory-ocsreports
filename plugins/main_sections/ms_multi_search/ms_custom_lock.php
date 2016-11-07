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
PrintEnTete($l->g(976));
$form_name = "lock_affect";
echo open_form($form_name);
echo "<div align=center>";
$list_id = multi_lot($form_name, $l->g(601));
if (is_defined($protectedPost['LOCK'])) {
    if (isset($_SESSION['OCS']["mesmachines"])) {
        $_SESSION['OCS']["TRUE_mesmachines"] = $_SESSION['OCS']["mesmachines"];
    } else {
        $_SESSION['OCS']["TRUE_mesmachines"] = array();
    }
    $_SESSION['OCS']["mesmachines"] = " a.hardware_id in (" . $list_id . ")";
    echo "<script type='text/javascript'> window.opener.document.multisearch.submit();self.close();</script>";
}

if ($protectedPost['CHOISE'] != "") {
    echo "<br><br><b>" . $l->g(978) . "</b>";
    echo "<br><br>" . $l->g(979);
    echo "<br><br><input type='submit' value=" . $l->g(977) . " name='LOCK' class='btn'>";
}
echo "</div>";
echo close_form();
?>