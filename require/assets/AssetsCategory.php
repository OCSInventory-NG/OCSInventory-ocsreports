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
  * Class for assets categories
  */
class AssetsCategory
{

    public $html;

    public function get_assets(){
        $sql = "SELECT * FROM assets_categories";
        $result = mysqli_query($_SESSION['OCS']["readServer"], $sql);

        while ($item_asset = mysqli_fetch_array($result)) {
            $list_asset[$item_asset['ID']]['CATEGORY_NAME'] = $item_asset['CATEGORY_NAME'];
            $list_asset[$item_asset['ID']]['SQL_QUERY'] = $item_asset['SQL_QUERY'];
            $list_asset[$item_asset['ID']]['SQL_ARGS'] = $item_asset['SQL_ARGS'];

        }
        $this->get_computer_assets($list_asset);
    }

    public function get_computer_assets($list_asset){
 
        foreach($list_asset as $values){
            $nb = [];
            $asset = explode(",", $values['SQL_ARGS']);
            $result_computer = mysql2_query_secure($values['SQL_QUERY'], $_SESSION['OCS']["readServer"], $asset);

            while ($computer = mysqli_fetch_array($result_computer)) {
                $nb[] = $computer['hardwareID'];
            }
            $nb_computer[][$values['CATEGORY_NAME']] = count($nb);
        }

        $this->get_table_html_asset($nb_computer);
    }

    public function get_table_html_asset($nb_computer){
        global $l;

        $this->html = '<table 0="[object Object]" 1="[object Object]" 2="[object Object]" border="0" style="cellspacing:0;color:#000;font-family:Roboto, Helvetica, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;">
                    <tr style="border-bottom:1px solid #ecedee; border-left:1px solid #ecedee; border-right:1px solid #ecedee;border-top:1px solid #ecedee; text-align:center;padding:15px 0;">
                      <th style="padding: 0 15px 0 0; text-align:center;">'.$l->g(49).'</th>
                      <th style="padding: 0 0 0 15px; text-align:center;">'.$l->g(2131).'</th>
                    </tr>';

        if($nb_computer != null){
            foreach($nb_computer as $value){
                foreach($value as $name => $nb){
                    $this->html .= '<tr style="border-bottom:1px solid #ecedee; border-left:1px solid #ecedee; border-right:1px solid #ecedee;text-align:center;padding:15px 0;">
                                        <td style="padding: 0 15px 0 0;">'.$name.'</td>
                                        <td style="padding: 0 0 0 15px;">'.$nb.'</td>
                                    </tr>';
                }
            }
        }

        $this->html .= '</table>';

        return $this->html;
    }
}
