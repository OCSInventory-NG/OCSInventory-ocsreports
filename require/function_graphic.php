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
echo "<LINK REL='StyleSheet' TYPE='text/css' HREF='css/graphic.css'>\n";

function percent_bar($status) {
    if (!is_numeric($status)) {
        return $status;
    }
    if (($status < 0) || ($status > 100)) {
        return $status;
    }
    return "<div class='progress'><!--" . str_pad($status, 3, "0", STR_PAD_LEFT) . "-->
            <div class='progress-bar progress-bar-success' role='progressbar' aria-valuenow='" . $status . "' aria-valuemin='0' aria-valuemax='100' style='width:" . $status . "%;'>
            <font color='#333'><b>" . $status . "%</b></font></div>
            </div>";
}

?>
