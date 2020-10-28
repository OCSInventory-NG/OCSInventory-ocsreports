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

/**
 * Class for Save Query
 */
class SaveQuery
{
    /**
     * Insert search into DB
     *
     * @param array $arg
     * @return boolean
     */
    public function create_search($arg) {
        $sqlQuery = "INSERT INTO `save_query`(`QUERY_NAME`, `DESCRIPTION`, `PARAMETERS`) VALUES ('%s','%s','%s')";
        $sqlArgs = $arg;
        $result = mysql2_query_secure($sqlQuery, $_SESSION['OCS']["writeServer"], $sqlArgs);

        if(!$result) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Update search into DB
     *
     * @param array $arg
     * @return boolean
     */
    public function update_search($arg) {
        $sqlQuery = "UPDATE save_query SET QUERY_NAME = '%s', DESCRIPTION = '%s', PARAMETERS = '%s' WHERE ID = %s";
        $sqlArgs = $arg;
        $result = mysql2_query_secure($sqlQuery, $_SESSION['OCS']["writeServer"], $sqlArgs);

        if(!$result) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get all query name
     *
     * @return array
     */
    public function get_search_name() {
        $sql = "SELECT ID, QUERY_NAME FROM save_query ORDER BY QUERY_NAME";
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);

        $query[0] = "---";

        while($row = mysqli_fetch_array($result)) {
            $query[$row['ID']] = $row['QUERY_NAME'];
        }

        return $query;
    }

    /**
     * Get all info of a saved search
     *
     * @param int $id
     * @return array
     */
    public function get_search_info($id) {
        $sql = "SELECT ID, QUERY_NAME, DESCRIPTION FROM save_query WHERE ID = %s";
        $sql_arg = array($id);
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $sql_arg);
        $query = [];

        while($row = mysqli_fetch_array($result)) {
            $query['ID']            = $row['ID'];
            $query['NAME']          = $row['QUERY_NAME'];
            $query['DESCRIPTION']   = $row['DESCRIPTION'];
        }

        return $query;
    }
}