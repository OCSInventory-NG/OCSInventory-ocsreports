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
  * Class for software categories
  */
class AllSoftware
{

    public function software_link_treatment() {
        // First clean software_link
        $delSoftLink = $this->delete_software_link();
        // Get all softwares
        $allSoft = $this->get_software_informations();
        // Get categories
        $allSoftCat = $this->get_software_categories_link_informations();

        $software = [];
        $softwareCategory = [];

        if($allSoft && $allSoft->num_rows != 0) {
            while($item_all_soft = mysqli_fetch_array($allSoft)) {
                $software[$item_all_soft['identifier']]['NAME_ID'] = intval($item_all_soft['NAME_ID']);
                $software[$item_all_soft['identifier']]['PUBLISHER_ID'] = intval($item_all_soft['PUBLISHER_ID']);
                $software[$item_all_soft['identifier']]['VERSION_ID'] = intval($item_all_soft['VERSION_ID']);
                $software[$item_all_soft['identifier']]['CATEGORY_ID'] = null;
                $software[$item_all_soft['identifier']]['COUNT'] = intval($item_all_soft['nb']);
            }
        }

        if($allSoftCat && $allSoftCat->num_rows != 0) {
            while($items = mysqli_fetch_array($allSoftCat)) {
                $softwareCategory[$items['ID']]['NAME_ID'] = intval($items['NAME_ID']);
                $softwareCategory[$items['ID']]['PUBLISHER_ID'] = intval($items['PUBLISHER_ID']);
                $softwareCategory[$items['ID']]['VERSION_ID'] = intval($items['VERSION_ID']);
                $softwareCategory[$items['ID']]['CATEGORY_ID'] = intval($items['CATEGORY_ID']);
            }
        }

        if(!empty($softwareCategory)) {
            foreach($software as $identifier => $values) {
                foreach($softwareCategory as $key => $infos) {
                    if($values['NAME_ID'] == $infos['NAME_ID'] && $values['PUBLISHER_ID'] == $infos['PUBLISHER_ID'] && $values['VERSION_ID'] == $infos['VERSION_ID']) {
                        $software[$identifier]['CATEGORY_ID'] = $infos['CATEGORY_ID'];
                    }
                }
            }
        }

        foreach($software as $identifier => $values) {
            $sql = "INSERT INTO `software_link` (`IDENTIFIER`, `NAME_ID`, `PUBLISHER_ID`, `VERSION_ID`, `CATEGORY_ID`, `COUNT`)";
            if($values['CATEGORY_ID'] == null) {
                $sql .= " VALUES ('%s', %s, %s, %s, NULL, %s)";
                $arg = array($identifier, $values['NAME_ID'], $values['PUBLISHER_ID'], $values['VERSION_ID'], $values['COUNT']);
            } else {
                $sql .= " VALUES ('%s', %s, %s, %s, %s, %s)";
                $arg = array($identifier, $values['NAME_ID'], $values['PUBLISHER_ID'], $values['VERSION_ID'], $values['CATEGORY_ID'], $values['COUNT']);
            }

            $result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);

            if(!$result) {
                error_log(print_r("An error occure when attempt to insert software with identifier : ".$identifier, true));
            }
        }

    }

    private function delete_software_link() {
        $sql = "DELETE FROM `software_link`";
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"]);
    }

    private function get_software_informations() {
        $sql = "SELECT CONCAT(n.NAME,';',p.PUBLISHER,';',v.VERSION) as identifier, 
                s.VERSION_ID, s.NAME_ID, s.PUBLISHER_ID, 
                COUNT(CONCAT(s.NAME_ID, s.PUBLISHER_ID, s.VERSION_ID)) as nb 
                FROM software s 
                LEFT JOIN software_name n ON s.NAME_ID = n.ID 
                LEFT JOIN software_publisher p ON s.PUBLISHER_ID = p.ID 
                LEFT JOIN software_version v ON s.VERSION_ID = v.ID
                GROUP BY s.NAME_ID, s.PUBLISHER_ID, s.VERSION_ID";

        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);

        return $result;
    }

    private function get_software_categories_link_informations() {
        $sql = "SELECT * FROM `software_categories_link`";
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);

        return $result;
    }

     /**
      * Search for softwares w/ hardware_ids that are no longer registered
      * in hardware table and delete them from software table
      * + clean related tables if necessary
      */
    public function software_cleanup() {
        $sql = "SELECT software.HARDWARE_ID FROM `software` 
                LEFT JOIN `hardware` ON software.HARDWARE_ID = hardware.ID
                WHERE hardware.ID IS NULL GROUP BY software.HARDWARE_ID";
        $result = mysql2_query_secure($sql, $_SESSION['OCS']['readServer']);
        $i = 0;
        while ($hid = mysqli_fetch_array($result)) {
            $unlinked_hids[$i] = $hid['HARDWARE_ID'];
            $i++;
        }

        if ($unlinked_hids >= 1) {
            $sql_del = "DELETE FROM software WHERE HARDWARE_ID IN (%s)";
            $arg_del = implode(",", $unlinked_hids);
            $result = mysql2_query_secure($sql_del, $_SESSION['OCS']["writeServer"], $arg_del);
        }

        // clean entries in software_name/vers/publi tables 
        // which are no longer linked to any software entry
        $target_tables = array('software_name' => 'NAME_ID', 'software_version' => 'VERSION_ID', 'software_publisher' => 'PUBLISHER_ID');
        foreach ($target_tables as $table => $field) {
            $sql = "SELECT $table.ID FROM `$table` 
                LEFT JOIN `software` ON $table.ID = software.$field 
                WHERE software.$field IS NULL GROUP BY $table.ID";
            $result = mysql2_query_secure($sql, $_SESSION['OCS']['readServer']);
            $i = 0;
            $unlinked_ids = array();
            while ($ids = mysqli_fetch_array($result)) {
                $unlinked_ids[$i] = $ids['ID'];
                $i++;
            }

            if ($unlinked_ids >= 1) {
                $sql_del = "DELETE FROM $table WHERE ID IN (%s)";
                $arg_del = implode(",", $unlinked_ids);
                $result = mysql2_query_secure($sql_del, $_SESSION['OCS']["writeServer"], $arg_del);
            }
        }

        // clean entries in software_categories_link
        // which are no longer linked to any software entry
        foreach ($target_tables as $table => $field) {
            $sql = "SELECT scl.ID FROM `software_categories_link` scl
                LEFT JOIN `$table` ON $table.ID = scl.$field
                WHERE $table.ID IS NULL GROUP BY scl.ID";
            $result = mysql2_query_secure($sql, $_SESSION['OCS']['readServer']);
            $i = 0;
            $unlinked_ids = array();
            while ($ids = mysqli_fetch_array($result)) {
                $unlinked_ids[$i] = $ids['ID'];
                $i++;
            }

            if ($unlinked_ids >= 1) {
                $sql_del = "DELETE FROM `software_categories_link` WHERE ID IN (%s)";
                $arg_del = implode(",", $unlinked_ids);
                $result = mysql2_query_secure($sql_del, $_SESSION['OCS']["writeServer"], $arg_del);
            }
        }
    }

}