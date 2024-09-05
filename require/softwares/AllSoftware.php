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

    public function software_link_treatment($chunk) {
        // First clean software_link
        $this->delete_software_link();
        // Get categories
        $allSoftCat = $this->get_software_categories_link_informations();

        $softwareCategory = [];

        if($allSoftCat && $allSoftCat->num_rows != 0) {
            while($items = mysqli_fetch_array($allSoftCat)) {
                $softwareCategory[$items['ID']]['NAME_ID'] = intval($items['NAME_ID']);
                $softwareCategory[$items['ID']]['PUBLISHER_ID'] = intval($items['PUBLISHER_ID']);
                $softwareCategory[$items['ID']]['VERSION_ID'] = intval($items['VERSION_ID']);
                $softwareCategory[$items['ID']]['CATEGORY_ID'] = intval($items['CATEGORY_ID']);
            }
        }

        // Initialize numRows to be equal of chunk
        $numRows = $chunk;
        $chunkIndex = 1;

        for($limit = 0; $chunk <= $numRows; $limit = $limit+$chunk) {
            print("[".date("Y-m-d H:i:s"). "] Process pool number ".$chunkIndex."\n");

            // Get all softwares
            $allSoft = $this->get_software_informations($chunk, $limit);

            if(!$allSoft) {
                print("[".date("Y-m-d H:i:s"). "] Failed to retrieve software\n");
                return;
            }

            $numRows = $allSoft->num_rows;

            if($limit == 0) $limit += 1;

            $software = [];

            if($allSoft && $allSoft->num_rows != 0) {
                while($item_all_soft = mysqli_fetch_array($allSoft)) {
                    $software[$item_all_soft['identifier']]['NAME_ID'] = intval($item_all_soft['NAME_ID']);
                    $software[$item_all_soft['identifier']]['PUBLISHER_ID'] = intval($item_all_soft['PUBLISHER_ID']);
                    $software[$item_all_soft['identifier']]['VERSION_ID'] = intval($item_all_soft['VERSION_ID']);
                    $software[$item_all_soft['identifier']]['CATEGORY_ID'] = null;
                    $software[$item_all_soft['identifier']]['COUNT'] = intval($item_all_soft['nb']);
                }
            }

            if(!empty($softwareCategory)) {
                foreach($software as $identifier => $values) {
                    foreach($softwareCategory as $infos) {
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
                    print("[".date("Y-m-d H:i:s"). "] Failed to insert software with identifier : ".$identifier."\n");
                }
            }

            $chunkIndex += 1;
        }
    }

    private function delete_software_link() {
        $sql = "DELETE FROM `software_link`";
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"]);
    }

    private function get_software_informations($chunk, $limit) {
        $configToLookOut = [
            'EXCLUDE_ARCHIVE_COMPUTER' => 'EXCLUDE_ARCHIVE_COMPUTER'
        ];

        $configValues = look_config_default_values($configToLookOut)['ivalue']['EXCLUDE_ARCHIVE_COMPUTER'] ?? '';

        $sql = "SELECT CONCAT(n.NAME,';',p.PUBLISHER,';',v.VERSION) as identifier, 
                s.VERSION_ID, s.NAME_ID, s.PUBLISHER_ID, 
                COUNT(DISTINCT s.HARDWARE_ID) as nb 
                FROM software s 
                LEFT JOIN software_name n ON s.NAME_ID = n.ID 
                LEFT JOIN software_publisher p ON s.PUBLISHER_ID = p.ID 
                LEFT JOIN software_version v ON s.VERSION_ID = v.ID";

        if($configValues == 1) {
            $sql .= " LEFT JOIN hardware h ON h.ID = s.HARDWARE_ID WHERE h.ARCHIVE IS NULL";
        }
                
        $sql .= " GROUP BY s.NAME_ID, s.PUBLISHER_ID, s.VERSION_ID LIMIT $limit,$chunk";

        return mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
    }

    private function get_software_categories_link_informations() {
        $sql = "SELECT * FROM `software_categories_link`";

        return mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
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

        if (isset($unlinked_hids) && $unlinked_hids >= 1) {
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

            if (isset($unlinked_ids) && $unlinked_ids >= 1) {
                $sql_del = "DELETE FROM $table WHERE ID IN (%s)";
                $arg_del = implode(",", $unlinked_ids);
                $result = mysql2_query_secure($sql_del, $_SESSION['OCS']["writeServer"], $arg_del);
            }
        }

        // clean entries in software_categories_link
        // which are no longer linked to any software entry
        $sql = "SELECT scl.ID FROM `software_categories_link` scl
            LEFT JOIN `software_name` sn ON sn.ID = scl.NAME_ID
            LEFT JOIN `software_version` sv ON sv.ID = scl.VERSION_ID
            LEFT JOIN `software_publisher` sp ON sp.ID = scl.PUBLISHER_ID
            LEFT JOIN `software` s ON sn.ID = s.NAME_ID AND sv.ID = s.VERSION_ID AND sp.ID = s.PUBLISHER_ID
            WHERE s.ID IS NULL GROUP BY scl.ID";
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

    /**
     * getOperatingSystemList
     *
     * @return Array $os
     */
    public function getOperatingSystemList() {
        GLOBAL $l;

        $query = "SELECT OSNAME FROM hardware WHERE DEVICEID<>'_SYSTEMGROUP_' AND DEVICEID<>'_DOWNLOADGROUP_' GROUP BY OSNAME ORDER BY OSNAME";

        $result = mysql2_query_secure($query, $_SESSION['OCS']["readServer"]);

        $os = [
            0 => "-----",
            "windows" => $l->g(9305),
            "unix" => $l->g(9303),
        ];

        while($item = mysqli_fetch_array($result)){
            $os[$item['OSNAME']] = $item['OSNAME'];
        }

        return $os;
    }
    
    /**
     * getGroupList
     *
     * @return Array $group
     */
    public function getGroupList() {
        $query = "SELECT NAME, ID FROM hardware WHERE DEVICEID = '_SYSTEMGROUP_' GROUP BY NAME ORDER BY NAME";

        $result = mysql2_query_secure($query, $_SESSION['OCS']["readServer"]);

        $group = [
            0 => "-----",
        ];

        while($item = mysqli_fetch_array($result)){
            $group[$item['ID']] = $item['NAME'];
        }

        return $group;
    }
    
    /**
     * getTagList
     *
     * @return Array $tag
     */
    public function getTagList() {
        $query = "SELECT TAG FROM accountinfo a "; 
        
        // Tag restriction
        if (is_defined($_SESSION['OCS']["mesmachines"]) && strpos($_SESSION['OCS']["mesmachines"], "a.TAG") !== false) {
            $query .= "WHERE ".$_SESSION['OCS']["mesmachines"]. " ";
        }

        $query .= "GROUP BY TAG ORDER BY TAG";

        $result = mysql2_query_secure($query, $_SESSION['OCS']["readServer"]);

        $tag = [
            0 => "-----",
        ];

        while($item = mysqli_fetch_array($result)){
            $tag[$item['TAG']] = $item['TAG'];
        }

        return $tag;
    }
    
    /**
     * getAssetCategoryList
     *
     * @return Array $asset
     */
    public function getAssetCategoryList() {
        $query = "SELECT CATEGORY_NAME, ID FROM assets_categories GROUP BY CATEGORY_NAME ORDER BY CATEGORY_NAME";

        $result = mysql2_query_secure($query, $_SESSION['OCS']["readServer"]);

        $asset = [
            0 => "-----",
        ];

        while($item = mysqli_fetch_array($result)){
            $asset[$item['ID']] = $item['CATEGORY_NAME'];
        }

        return $asset;
    }
    
    /**
     * generateQueryFilter
     *
     * @param  Array $filters
     * @return Array
     */
    public function generateQueryFilter($filters) {
        $queryFilter = [];
        $hId = null;

        // if isset OS / GROUP / TAG / ASSET -> initialize SQL beginning
        if(is_defined($filters['OS']) || is_defined($filters['GROUP']) || is_defined($filters['TAG']) || is_defined($filters['ASSET']) || is_defined($filters['CSV'])) {
            // Select
            $queryFilter['SELECT'] = "SELECT n.NAME, p.PUBLISHER, v.VERSION, c.CATEGORY_NAME, 
            CONCAT(n.NAME,';',p.PUBLISHER,';',v.VERSION) as id, COUNT(CONCAT(s.NAME_ID, s.PUBLISHER_ID, s.VERSION_ID)) as ";
            
            if(is_defined($filters['SUBMIT_FORM_RESTRICT']) && $filters['SUBMIT_FORM_RESTRICT']) {
                $queryFilter['SELECT'] .= "nb2 ";
                $nb = 'nb2 ';
            } else {
                $queryFilter['SELECT'] .= "nb ";
                $nb = 'nb ';
            }

            // From
            $queryFilter['FROM'] = "FROM software s LEFT JOIN software_name n ON n.ID = s.NAME_ID
            LEFT JOIN software_publisher p ON p.ID = s.PUBLISHER_ID
            LEFT JOIN software_version v ON v.ID = s.VERSION_ID
            LEFT JOIN software_categories_link cl ON cl.NAME_ID = n.ID AND cl.PUBLISHER_ID = p.ID AND cl.VERSION_ID = v.ID
            LEFT JOIN software_categories c ON c.ID = cl.CATEGORY_ID ";

            // Group by
            $queryFilter['GROUPBY'] = "GROUP BY id ";
        }
        if(
            (is_defined($filters['OS']) ||
            is_defined($filters['GROUP']) ||
            is_defined($filters['TAG']) ||
            is_defined($filters['ASSET']) ||
            is_defined($filters['CSV'])) &&
            (is_defined($filters['SHOW_METHOD']) &&
            $filters['SHOW_METHOD'] == 2)
        )
        {
            // Select
            $queryFilter['SELECT'] = "SELECT h.NAME as HARDWARE_NAME, h.ID as HARDWARE_ID, n.NAME, p.PUBLISHER, v.VERSION, c.CATEGORY_NAME,
            CONCAT(n.NAME,';',p.PUBLISHER,';',v.VERSION) as id ";

            // From
            $queryFilter['FROM'] = "
                FROM software s 
                LEFT JOIN software_name n ON n.ID = s.NAME_ID
                LEFT JOIN software_publisher p ON p.ID = s.PUBLISHER_ID
                LEFT JOIN software_version v ON v.ID = s.VERSION_ID
                LEFT JOIN software_categories_link cl ON cl.NAME_ID = n.ID AND cl.PUBLISHER_ID = p.ID AND cl.VERSION_ID = v.ID
                LEFT JOIN software_categories c ON c.ID = cl.CATEGORY_ID
                LEFT JOIN hardware h ON h.ID = s.HARDWARE_ID
            ";

            // Group by
            unset($queryFilter['GROUPBY']);
        }

        if(is_defined($filters['CSV'])) {
            if (is_array($hId)) {
                $tmp = array_unique(array_merge($hId, $filters['CSV']));
            } else {
                $tmp = $filters['CSV'];
            }
            $hId = [];
            foreach ($tmp as $key => $value) {
                $hId[$value] = $value;
            }
        }

        if(is_defined($filters['OS'])) {
            $hId = $this->getHidByOs($filters['OS'], $hId);
        }

        if(is_defined($filters['GROUP'])) {
            $query = "SELECT HARDWARE_ID FROM `groups_cache` WHERE GROUP_ID = '".$filters["GROUP"]."' GROUP BY HARDWARE_ID";
            $hId = $this->getHidByType($query, $hId);
        }

        if(is_defined($filters['TAG'])) {
            $query = "SELECT HARDWARE_ID FROM accountinfo WHERE TAG = '".$filters['TAG']."' GROUP BY HARDWARE_ID";
            $hId = $this->getHidByType($query, $hId);
        }

        if(is_defined($filters['ASSET'])) {
            $query = "SELECT ID as HARDWARE_ID FROM `hardware` WHERE CATEGORY_ID = '".$filters['ASSET']."' GROUP BY HARDWARE_ID";
            $hId = $this->getHidByType($query, $hId);
        }

        // If restrictions
        if (is_defined($_SESSION['OCS']["mesmachines"])) {
            $query = "SELECT HARDWARE_ID FROM accountinfo a WHERE ".$_SESSION['OCS']["mesmachines"]." GROUP BY HARDWARE_ID";
            $hId = $this->getHidByType($query, $hId);
        }

        if(!empty($hId)) {
            // Where
            $queryFilter['WHERE'] = "WHERE s.HARDWARE_ID IN (".implode(",", $hId).") "; 
        } else {
            $queryFilter['WHERE'] = "WHERE s.HARDWARE_ID IN (0) "; 
        }

        if(is_defined($filters['NBRE']) && is_defined($filters['COMPAR'])) {
            if(is_defined($filters['SUBMIT_FORM_RESTRICT']) && $filters['SUBMIT_FORM_RESTRICT']) {
                $nb = 'nb2 ';
            } else {
                $nb = 'nb ';
            }
            $comparator = $this->getComparator($filters['COMPAR']);
            $queryFilter['HAVING'] = "HAVING $nb".$comparator." ".$filters['NBRE']." ";
        }

        if(is_defined($filters['NAME_RESTRICT'])) {
            if(is_defined($queryFilter['HAVING'])) {
                $queryFilter['HAVING'] .= "AND n.NAME LIKE '%%".$filters['NAME_RESTRICT']."%%' ";
            } else {
                $queryFilter['HAVING'] .= "HAVING n.NAME LIKE '%%".$filters['NAME_RESTRICT']."%%' ";
            }
        }

        return $queryFilter;
    }
    
    /**
     * getComparator
     *
     * @param  String $compar
     * @return String
     */
    private function getComparator($compar) {
        switch ($compar) {
            case "lt":
                return "<";
            case "gt":
                return ">";
            case "eq":
                return "=";
            default:
                return "=";
        }
    }
    
    /**
     * getHidByType
     *
     * @param  String $query
     * @param  Array $hId
     * @return Array
     */
    private function getHidByType($query, $hId) {
        $result = mysql2_query_secure($query, $_SESSION['OCS']["readServer"]);
        $tmp = [];

        if($result) while($item = mysqli_fetch_array($result)){
            $tmp[$item['HARDWARE_ID']] = $item['HARDWARE_ID'];
        }

        if(!empty($hId) && !empty($tmp)) {
            foreach($hId as $id) {
                if(!in_array($id, $tmp)) {
                    unset($hId[$id]);
                }
            }
        } elseif(!empty($tmp)) {
            $hId = $tmp;
        }

        return $hId;
    }
    
    /**
     * getHidByOs
     *
     * @param  String $os
     * @return Array
     */
    private function getHidByOs($os, $hId) {
        $tmp = [];

        switch($os) {
            case 'windows':
                $getHIDQuery = "SELECT ID FROM hardware WHERE LOWER(OSNAME) LIKE '%$os%' AND DEVICEID<>'_SYSTEMGROUP_' AND DEVICEID<>'_DOWNLOADGROUP_' GROUP BY ID"; 
                break;
            case 'unix':
                $getHIDQuery = "SELECT ID FROM hardware WHERE LOWER(OSNAME) NOT LIKE '%windows%' AND DEVICEID<>'_SYSTEMGROUP_' AND DEVICEID<>'_DOWNLOADGROUP_' GROUP BY ID"; 
                break;
            default:
                $getHIDQuery = "SELECT ID FROM hardware WHERE OSNAME = '$os' GROUP BY ID"; 
                break;
        }

        $result = mysql2_query_secure($getHIDQuery, $_SESSION['OCS']["readServer"]);

        if($result) while($item = mysqli_fetch_array($result)){
            $tmp[$item['ID']] = $item['ID'];
        }

        if(!empty($hId) && !empty($tmp)) {
            foreach($hId as $id) {
                if(!in_array($id, $tmp)) {
                    unset($hId[$id]);
                }
            }
        } elseif(!empty($tmp)) {
            $hId = $tmp;
        }

        return $hId;
    }

    public function verifyCsv($file){
        if ($file['type'] != "text/csv") {
            return false;
        }
        $content = file_get_contents($file['tmp_name']);
        $names = preg_split("/[\r|\n]+/", $content);
        $hardware = [];
        foreach ($names as $key => $name) {
            $name = preg_replace("/[^A-Za-z0-9-_\.]/", "", $name);
            if ($name != "") {
                $sql = "SELECT ID FROM hardware WHERE NAME = '" . addslashes($name) . "'";
    
                $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
                $test = $result->num_rows;
    

                if($result && $test > 0){
                    while($item = mysqli_fetch_array($result)){
                        $hardware['result'][$item['ID']] = $item['ID'];
                    }
                } else {
                    $hardware['missing'][] = $name;
                }
            }
        }
        $_SESSION['OCS']['AllSoftware']['filter']['csv_data'] = $hardware;
        return true;
    }

}