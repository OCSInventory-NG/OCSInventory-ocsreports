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
    public function create_search($arg, $whoCanSee) {
        switch($whoCanSee) {
            case "USER":
                $sqlQuery = "INSERT INTO `save_query`(`QUERY_NAME`, `DESCRIPTION`, `PARAMETERS`, `WHO_CAN_SEE`, `USER_ID`) VALUES ('%s','%s','%s','%s','%s')";
                array_push($arg, $whoCanSee, $_SESSION['OCS']['loggeduser']);
                $sqlArgs = $arg;
                break;
            case "GROUP":
                $sqlQuery = "INSERT INTO `save_query`(`QUERY_NAME`, `DESCRIPTION`, `PARAMETERS`, `WHO_CAN_SEE`, `GROUP_ID`) VALUES ('%s','%s','%s','%s', %s)";
                array_push($arg, $whoCanSee, $_SESSION['OCS']['user_group']);
                $sqlArgs = $arg;
                break;
            default:
                $sqlQuery = "INSERT INTO `save_query`(`QUERY_NAME`, `DESCRIPTION`, `PARAMETERS`, `WHO_CAN_SEE`) VALUES ('%s','%s','%s','%s')";
                array_push($arg, $whoCanSee);
                $sqlArgs = $arg;
                break;
        }

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
    public function update_search($arg, $who_can_see, $id_search) {
        switch($who_can_see) {
            case "USER":
                $sqlQuery = "UPDATE save_query SET QUERY_NAME = '%s', DESCRIPTION = '%s', PARAMETERS = '%s', WHO_CAN_SEE = '%s', USER_ID = '%s', GROUP_ID = NULL WHERE ID = %s";
                array_push($arg, $who_can_see, $_SESSION['OCS']['loggeduser'], $id_search);
                $sqlArgs = $arg;
                break;
            case "GROUP":
                $sqlQuery = "UPDATE save_query SET QUERY_NAME = '%s', DESCRIPTION = '%s', PARAMETERS = '%s', WHO_CAN_SEE = '%s', GROUP_ID = %s, USER_ID = NULL WHERE ID = %s";
                array_push($arg, $who_can_see, $_SESSION['OCS']['user_group'], $id_search);
                $sqlArgs = $arg;
                break;
            default:
                $sqlQuery = "UPDATE save_query SET QUERY_NAME = '%s', DESCRIPTION = '%s', PARAMETERS = '%s', WHO_CAN_SEE = '%s', GROUP_ID = NULL, USER_ID = NULL WHERE ID = %s";
                array_push($arg, $who_can_see, $id_search);
                $sqlArgs = $arg;
                break;
        }

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
        $sql = "SELECT ID, QUERY_NAME FROM save_query WHERE WHO_CAN_SEE = 'ALL' OR USER_ID = '%s'";
        $arg = array($_SESSION['OCS']['loggeduser']);
        
        if($_SESSION['OCS']['user_group'] != null && $_SESSION['OCS']['user_group'] != "") {
            $sql .= " OR GROUP_ID = %s";
            array_push($arg, $_SESSION['OCS']['user_group']);
        }

        $sql .= " ORDER BY QUERY_NAME";
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);

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
        $sql = "SELECT ID, QUERY_NAME, DESCRIPTION, WHO_CAN_SEE FROM save_query WHERE ID = %s";
        $sql_arg = array($id);
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $sql_arg);
        $query = [];

        while($row = mysqli_fetch_array($result)) {
            $query['ID']            = $row['ID'];
            $query['NAME']          = $row['QUERY_NAME'];
            $query['DESCRIPTION']   = $row['DESCRIPTION'];
            $query['WHO_CAN_SEE']   = $row['WHO_CAN_SEE'];
        }

        return $query;
    }
}