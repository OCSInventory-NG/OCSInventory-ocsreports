<?php
/*
 * Copyright 2005-2020 OCSInventory-NG/OCSInventory-ocsreports contributors.
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
  * Class for PackageBuilder
  */
class PackageBuilderFormInteractions
{

    /**
     *  Generate interaction categories
     */
    public function generateInteractionCategories($systemInfos) {
        global $l;
        $html = "";

        foreach($systemInfos->categories as $category) {
            foreach($category as $categoryInfos) {
                $html .= '  <div class="col-md-4">
                                <div class="panel panel-default panel-default-ocs-deploy">
                                    <div class="panel-heading panel-heading-ocs-deploy">
                                        <h4 class="panel-title">';
                $html .= '<a data-toggle="collapse" data-parent="#accordion'.$systemInfos->id.'" href="#'.$categoryInfos->id.'">'.strtoupper($l->g(intval($categoryInfos->name))).'</a>';
                $html .= '              </h4>
                                    </div>
                                </div>
                            </div>';
            }
        }
        return $html;
    }

}


            