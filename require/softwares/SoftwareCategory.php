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

    public $html;

    /**
     * Get all categories for onglet
     * @return array
     */
    public function onglet_cat(){
        $sql_list_cat = "SELECT `ID`, `CATEGORY_NAME`, `OS` FROM `software_categories`";
        $result_list_cat = mysqli_query($_SESSION['OCS']["readServer"], $sql_list_cat);
        $i = 1;
        while ($item_list_cat = mysqli_fetch_array($result_list_cat)) {
            if ($i == 1) {
                $list_cat['first_onglet'] = $i;
            }
            $list_cat[$i] = $item_list_cat['CATEGORY_NAME'];
            $list_cat['category_name'][$item_list_cat['CATEGORY_NAME']] = $item_list_cat['ID'];
            $list_cat['OS'][$item_list_cat['CATEGORY_NAME']] = $item_list_cat['OS'];
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
    public function add_category($catName, $osVersion){
        $sql_verif = "SELECT `CATEGORY_NAME` FROM `software_categories` WHERE `CATEGORY_NAME` = '%s'";
        $arg_verif = array($catName);
        $result_verif = mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"], $arg_verif);

        $item = mysqli_fetch_array($result_verif);

        if($item != null){
            return(false);
        }else{
            $sql = "INSERT INTO `software_categories` (`CATEGORY_NAME`, `OS`) values('%s', '%s');";
            $arg_sql = array($catName, $osVersion);

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
    public function insert_exp($id_cat, $regExp, $sign = null, $version = null, $vendor = null){
        if($vendor == '0'){
          $vendor = null;
        }

        if($version == '0'){
          $version = null;
          $sign = null;
        }

        $sql_reg = "INSERT INTO `software_category_exp` (`CATEGORY_ID`, `SOFTWARE_EXP`, `SIGN_VERSION`, `VERSION`, `PUBLISHER`) values(%s, '%s', '%s', '%s', '%s')";
        $arg_reg = array($id_cat, $regExp, $sign, $version, $vendor);

        $result = mysql2_query_secure($sql_reg, $_SESSION['OCS']["writeServer"], $arg_reg);
        return ($result);
    }

    /**
     * get regEx values for table
     * @param protectedPost $onglet_active
     * @return array
     */
    public function display_reg($onglet_active){
        $sql = "SELECT * FROM `software_category_exp` WHERE `CATEGORY_ID`= '%s'";
        $arg_sql = array($onglet_active);
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg_sql);

        while ($item = mysqli_fetch_array($result)) {
            $list[] = ['ID' => $item['ID'],
                      'NAME' => $item['SOFTWARE_EXP'],
                      'SIGN' => $item['SIGN_VERSION'],
                      'VERSION' => $item['VERSION'],
                      'PUBLISHER' => $item['PUBLISHER']];
        }
        return ($list);
    }

    public function get_table_html_soft(){
        global $l;
        $cat = $this->search_all_cat();
        unset($cat['0']);

        $this->html = '<table 0="[object Object]" 1="[object Object]" 2="[object Object]" border="0" style="cellspacing:0;color:#000;font-family:Roboto, Helvetica, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;">
                        <tr style="border-bottom:1px solid #ecedee; border-left:1px solid #ecedee; border-right:1px solid #ecedee;border-top:1px solid #ecedee; text-align:center;padding:15px 0;">
                          <th style="padding: 0 15px 0 0; text-align:center;">'.$l->g(49).'</th>
                          <th style="padding: 0 0 0 15px; text-align:center;">'.$l->g(2131).'</th>
                        </tr>';

        foreach ($cat as $key => $value){
          $sql = "SELECT `NAME` FROM software_name WHERE CATEGORY_ID = %s";
          $arg = array($key);
          $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);

          while ($computer = mysqli_fetch_array($result)) {
              $nb[$value][] = $computer['NAME'];
          }
          if($nb[$value] != null){
            $nb_computer[$value] = count($nb[$value]);
          }
        }
        if($nb_computer != null){
            foreach($nb_computer as $name => $nb){
                $this->html .= '<tr style="border-bottom:1px solid #ecedee; border-left:1px solid #ecedee; border-right:1px solid #ecedee;text-align:center;padding:15px 0;">
                                <td style="padding: 0 15px 0 0;">'.$name.'</td>
                                <td style="padding: 0 0 0 15px;">'.$nb.'</td>
                                </tr>';
            }
        }else{
            foreach($cat as $key => $value){
                $this->html .= "<tr style='border-bottom:1px solid #ecedee; border-left:1px solid #ecedee; border-right:1px solid #ecedee;text-align:center;padding:15px 0;'>
                            <td style='padding: 0 15px 0 0;'>".$value."</td>
                            <td style='padding: 0 0 0 15px;'>0</td>
                        </tr>";
            }
        }

        $this->html .= '</table>';

        return $this->html;
    }

    /**
     * Search version of soft
     * @param  string $softName [description]
     * @return [type]           [description]
     */
    public function search_version($softName){
        global $l;

        $softName = str_replace("*", "", $softName);
        $softName = str_replace("?", "", $softName);

        $sql = "SELECT DISTINCT s.VERSION_ID, v.VERSION FROM software s 
                LEFT JOIN software_version v ON v.ID = s.VERSION_ID
                LEFT JOIN software_name n ON n.ID = s.NAME_ID 
                WHERE n.NAME LIKE '%$softName%' ORDER BY v.VERSION";
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);

        $version[0] = " ";
        while ($soft = mysqli_fetch_array($result)) {
            $version[$soft['VERSION']] = $soft['VERSION'];
        }
        return $version;
    }

    /**
     * Search vendor of soft
     * @param  string $softName [description]
     * @return [type]           [description]
     */
    public function search_vendor($softName){
        global $l;

        $softName = str_replace("*", "", $softName);
        $softName = str_replace("?", "", $softName);

        $sql = "SELECT DISTINCT s.PUBLISHER_ID, p.PUBLISHER FROM software s 
                LEFT JOIN software_publisher p ON p.ID = s.PUBLISHER_ID
                LEFT JOIN software_name n ON n.ID = s.NAME_ID 
                WHERE n.NAME LIKE '%$softName%' ORDER BY p.PUBLISHER";
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);

        $vendor[0] = " ";
        while ($soft = mysqli_fetch_array($result)) {
            $vendor[$soft['PUBLISHER']] = $soft['PUBLISHER'];
        }
        return $vendor;
    }

    /**
     * Merge array
     * @param  array $arr  [description]
     * @param  array $arr2 [description]
     * @return array       [description]
     */
    public function array_merge_values($arr, $arr2) {
        foreach ($arr2 as $values) {
            array_push($arr, $values);
        }
        return $arr;
    }

    /**
     * Get all categories for onglet in computer details
     * @param  int $computerID [description]
     * @return array
     */
    public function onglet_cat_cd($computerID){
        $sql = "SELECT n.CATEGORY_ID FROM software_name n 
                LEFT JOIN software s ON n.ID = s.NAME_ID 
                WHERE hardware_id = %s GROUP BY CATEGORY_ID";
        $sql_arg = array($computerID);
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $sql_arg);
        while ($idCat = mysqli_fetch_array($result)) {
            $id[$idCat['CATEGORY_ID']] = $idCat['CATEGORY_ID'];
        }
        $cat = implode(',', $id);

        if($id != null){
            $sql_list_cat = "SELECT `ID`, `CATEGORY_NAME`, `OS` FROM `software_categories` WHERE ID IN (%s)";
            $sql_list_arg = array($cat);

            $result_list_cat = mysql2_query_secure($sql_list_cat, $_SESSION['OCS']["readServer"], $sql_list_arg);
            $i = 1;
            if($result_list_cat != false){
                while ($item_list_cat = mysqli_fetch_array($result_list_cat)) {
                    if ($i == 1) {
                        $list_cat['first_onglet'] = $i;
                    }
                    $list_cat[$i] = $item_list_cat['CATEGORY_NAME'];
                    $list_cat['category_name'][$item_list_cat['CATEGORY_NAME']] = $item_list_cat['ID'];
                    $list_cat['OS'][$item_list_cat['CATEGORY_NAME']] = $item_list_cat['OS'];
                    $i++;
                }
            }
            $list_cat['i'] = $i;
        }
        return ($list_cat);
    }
}
