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
    private $packageBuilderFormOptions;
    
    /**
     * Method __construct
     *
     * @param $packageBuildeFormInteractions $packageBuildeFormInteractions [explicite description]
     * @param $packageBuilderFormOperatingSystem $packageBuilderFormOperatingSystem [explicite description]
     * @param $packageBuilderParseXml $packageBuilderParseXml [explicite description]
     * @param $packageBuilderFormOptions $packageBuilderFormOptions [explicite description]
     *
     * @return void
     */
    function __construct($packageBuildeFormInteractions, $packageBuilderFormOperatingSystem, $packageBuilderParseXml, $packageBuilderFormOptions) {
        $this->packageBuilderFormOperatingSystem = $packageBuilderFormOperatingSystem;
        $this->packageBuildeFormInteractions = $packageBuildeFormInteractions;
        $this->packageBuilderParseXml = $packageBuilderParseXml;
        $this->packageBuilderFormOptions = $packageBuilderFormOptions;
    }
    
    /**
     * Method generateOperatingSystem
     *
     * @return string
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
     * Method generateInteractions
     *
     * @return string
     */
    public function generateInteractions() {
        $operatingInfos = $this->packageBuilderParseXml->parseOperatingSystem();
        $html = "";

        foreach($operatingInfos as $systemInfos) {

            $html .= '<div id="'.$systemInfos->id.'Interactions" style="display:none;"><div class="panel-group" id="accordion'.$systemInfos->id.'">';

            // Generate categories
            $html .= '<div class="row">';
            $html .= $this->packageBuildeFormInteractions->generateInteractionCategories($systemInfos);
            $html .= '</div>';


            // Generate interactions
            foreach($systemInfos->linkedinteractions->category as $interactions) {
                $html .= '  <div class="panel panel-default panel-default-ocs-deploy">
                                <div id="'.$interactions->attributes()->id.'" class="panel-collapse collapse">
                                    <div class="panel-body panel-body-ocs-deploy">
                                        <div class="row">';
                $html .= $this->packageBuildeFormInteractions->generateInteractionCollapse($interactions);                
                $html .= '</div></div></div></div>';
            }

            $html .= '</div></div>';
        }

        return $html;
    }
    
    /**
     * Method generateOptions
     *
     * @param $os $os [explicite description]
     * @param $linkedOption $linkedOption [explicite description]
     * @param $language $language [explicite description]
     *
     * @return string
     */
    public function generateOptions($os, $linkedOption, $language) {
        global $l;
        $l = new language($language);

        $optionInfos = $this->packageBuilderParseXml->parseOptions($linkedOption);
        
        $html = '<h3 class="text-center">'.$l->g(intval($optionInfos->name)).'</h3></br>';
        $html .= $this->packageBuilderFormOptions->generateOptions($optionInfos, $l);

        return $html;
    }
    
    /**
     * Method generateResume
     *
     * @param $packageInfos $packageInfos [explicite description]
     *
     * @return void
     */
    public function generateResume($packageInfos) {
        global $l;

        $html = '<h3 class="text-center">Resume</h3></br>';
        $html .= '<p><b>Name :</b> '.$packageInfos['NAME'].'<p>';
        $html .= '<p><b>Description :</b> '.$packageInfos['DESCRIPTION'].'<p>';
        $html .= '<p><b>Fragment number :</b> '.$packageInfos['FRAG'].'<p>';
        $html .= '<p><b>Size :</b> '.$packageInfos['SIZE'].'<p>';
        $html .= '<p><b>Priority :</b> '.$packageInfos['PRIO'].'<p><br>';

        return $html;
    }
}