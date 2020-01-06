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

function connexion_local_read() {
    global $link_ocs, $db_ocs;
    require_once(CONF_MYSQL);
    require_once('require/pdo/PdoConnect.php');
    //connection OCS
    $db_ocs = DB_NAME;

    try {
        $dbh = new PdoConnect(SERVER_READ, COMPTE_BASE, PSWD_BASE);
    } catch (PDOException $e) {
        echo "<br><center><font color=red><b>ERROR: MySql connection problem<br>" . $e . "</b></font></center>";
        die();
    }

    //fin connection OCS
}

function connexion_local_write() {
    global $link_ocs, $db_ocs;
    require_once(CONF_MYSQL);
    require_once('require/pdo/PdoException.php');
    //connection OCS
    $db_ocs = DB_NAME;

    try {
        $dbh = new PdoConnect(SERVER_READ, COMPTE_BASE, PSWD_BASE);
    } catch (PDOException $e) {
        echo "<br><center><font color=red><b>ERROR: MySql connection problem<br>" . $e . "</b></font></center>";
        die();
    }

    //fin connection OCS
}

?>