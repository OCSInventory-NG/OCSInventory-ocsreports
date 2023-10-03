<?php
/*
 * Copyright 2005-2022 OCSInventory-NG/OCSInventory-ocsreports contributors.
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


/**
 * Class for logging actions made on the interface in the history table
 */
class History {

    /**
     * Logs an action in the history table
     */
    public function addToHistory($action, $target) {
        if ($_SESSION['OCS']['LOG_GUI'] == 1) {
            $datetime = date("Y-m-d H:i:s");
            $user = $_SESSION['OCS']["loggeduser"];
            $sql = "INSERT INTO history (USER,DATETIME_ACTION,ACTION,TARGET) VALUES ('%s','%s','%s','%s')";
            $arg = array($user, $datetime, $action, $target);

            $result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
            return $result;
        }
	}

}
