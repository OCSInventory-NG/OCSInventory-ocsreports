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
  * This class implement basic behavior for group search management
  */
 class GroupSearch
 {

   /**
    * Get all group name
    * @return array $table
    */
    public function get_group_name() {
        $sql = 'SELECT h.NAME, h.ID FROM hardware h INNER JOIN `groups` g ON g.HARDWARE_ID = h.ID';
        $tableList = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);

        while ($tableInfos = mysqli_fetch_array($tableList)) {
            $table[$tableInfos['ID']] = $tableInfos['NAME'];
        }

        return $table;
    }

    /**
     * Get all id for a group
     * @param int $group_id
     * @return string $listGroup
     */
    public function get_all_id($group_id) {
        $sql = 'SELECT DISTINCT HARDWARE_ID FROM groups_cache WHERE GROUP_ID = %s';
        $arg_sql = array($group_id);

        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg_sql);
        $list = array();
        while ($idList = mysqli_fetch_array($result)) {
          if($idList['HARDWARE_ID'] != null){
              $list[$idList['HARDWARE_ID']] = $idList['HARDWARE_ID'];
          }
        }

        if(!empty($list)){
          $listGroup = implode(',', $list);
        } else {
          $listGroup = "0";
        }

        return $listGroup;
    }

 }
