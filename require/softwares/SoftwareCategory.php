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
class SoftwareCategory
{

    /**
     * Get all categories for onglet
     * @return array
     */
    public function onglet_cat(){
        $sql_list_cat = "SELECT `ID`, `CATEGORY_NAME` FROM `software_categories`";
        $result_list_cat = mysqli_query($_SESSION['OCS']["readServer"], $sql_list_cat);
        $i = 1;
        while ($item_list_cat = mysqli_fetch_array($result_list_cat)) {
            if ($i == 1) {
                $list_cat['first_onglet'] = $i;
            }
            $list_cat[$i] = $item_list_cat['CATEGORY_NAME'];
            $list_cat['category_name'][$item_list_cat['CATEGORY_NAME']] = $item_list_cat['ID'];
            $i++;
        }
        $list_cat['i'] = $i;
        return ($list_cat);
    }

    /**
     * Insert new category
     * @param string $catName
     * @return boolean
     */
    public function add_category($catName){
        $sql_verif = "SELECT `CATEGORY_NAME` FROM `software_categories` WHERE `CATEGORY_NAME` = '%s'";
        $arg_verif = array($catName);
        $result_verif = mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"], $arg_verif);

        $item = mysqli_fetch_array($result_verif);

        if($item != null){
            return(false);
        }else{
            $sql = "INSERT INTO `software_categories` (`CATEGORY_NAME`) values('%s');";
            $arg_sql = array($catName);

            $result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg_sql);
            return ($result);
        }
    }

    /**
     * Search all categories
     * @return array
     */
    public function search_all_cat(){
        $sql_list_cat = "SELECT `ID`, `CATEGORY_NAME` FROM `software_categories`";
        $result_list_cat = mysqli_query($_SESSION['OCS']["readServer"], $sql_list_cat);

        $list_cat[0] = " ";
        while ($item_list_cat = mysqli_fetch_array($result_list_cat)) {
            $list_cat[$item_list_cat['ID']] = $item_list_cat['CATEGORY_NAME'];
        }
        return ($list_cat);
    }

    /**
     * Insert RegEx
     * @param  int $id_cat
     * @param  string $regExp
     * @return boolean
     */
    public function insert_exp($id_cat, $regExp){
        $sql_reg = "INSERT INTO `software_category_exp` (`CATEGORY_ID`, `SOFTWARE_EXP`) values(%s, '%s')";
        $arg_reg = array($id_cat, $regExp);

        $result = mysql2_query_secure($sql_reg, $_SESSION['OCS']["writeServer"], $arg_reg);
        return ($result);
    }

    /**
     * get regEx values for table
     * @param protectedPost $onglet_active
     * @return array
     */
    public function display_reg($onglet_active){
        $sql = "SELECT `SOFTWARE_EXP` FROM `software_category_exp` WHERE `CATEGORY_ID`= '%s'";
        $arg_sql = array($onglet_active);
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg_sql);

        while ($item = mysqli_fetch_array($result)) {
            $list[] = $item['SOFTWARE_EXP'];
        }
        return ($list);
    }
}
