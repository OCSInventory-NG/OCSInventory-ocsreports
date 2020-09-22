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
class PackageBuilderForm
{
    private $packageBuildeFormInteractions;
    private $packageBuilderFormOperatingSystem;
    private $packageBuilderParseXml;

    function __construct($packageBuildeFormInteractions, $packageBuilderFormOperatingSystem, $packageBuilderParseXml) {
        $this->packageBuilderFormOperatingSystem = $packageBuilderFormOperatingSystem;
        $this->packageBuildeFormInteractions = $packageBuildeFormInteractions;
        $this->packageBuilderParseXml = $packageBuilderParseXml;
    }

    /**
     *  Generate Operating System
     */
    public function generateOperatingSystem() {
        $html = '<div id="operatingsystem"><div class="row">';
        
        $operating = $this->packageBuilderParseXml->parseOperatingSystem();
        foreach($operating->osdefinition as $system) {
            $html .= $this->packageBuilderFormOperatingSystem->generateTile($system);
        }
        $html .= '</div></div>';

        return $html;
    }

    /**
     *  Generate Interactions
     */
    public function generateInteractions() {
        $operatingInfos = $this->packageBuilderParseXml->parseOperatingSystem();
        $html = "";

        foreach($operatingInfos as $systemInfos) {

            var_dump($systemInfos->linkedinteractions->interaction);
            $html .= '<div id="'.$systemInfos->id.'Interactions" style="display:none;"><div class="panel-group" id="accordion'.$systemInfos->id.'">';

            // Generate categories
            $html .= '<div class="row">';
            $html .= $this->packageBuildeFormInteractions->generateInteractionCategories($systemInfos);
            $html .= '</div>';

            // Generate interactions



            $html .= '</div></div>';
        }

        return $html;
    }
}