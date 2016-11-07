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

class MsStats {
    public $form_name = "stats";

    /**
     * Return header name for page
     * @return String
     */
    public function getHeaderName() {
        global $l;

        return $l->g(1251);
    }

    /**
     * Return form name for page
     * @return String
     */
    static public function getFormName() {
        return "stats";
    }

    /**
     * Check for all available stats page
     * By default : Connexion Top / Bad Connexion / Top software
     * @return Array : Class names of available pages
     */
    public function checkForStatsPages() {
        $pages_names = array();
        foreach ($_SESSION['OCS']['url_service']->getUrls() as $name => $url) {
            if (substr($name, 0, 9) == 'ms_stats_' && $url['directory'] == 'ms_stats') {
                $name = str_replace('_', '', $name);
                $pages_names[] = $name;
            }
        }

        return $pages_names;
    }

    /**
     * Generate tab data from available stats pages
     * 
     * @param Array $available_pages : Array of available stats page
     * 
     * @return Array : Data on for show tabs
     */
    public function createShowTabsArray($available_pages) {
        $data_on = array();
        foreach ($available_pages as $key => $value) {
            $class = new $value();
            $data_on[$value] = $class->getTabName();
        }
        return $data_on;
    }

    /**
     * Create and display data of the stats form
     * 
     * @param Array $post_data : Post data sended to the page
     * @param String $default_display : Default value if no onglet selected
     * 
     * @return HTML
     */
    public function generateStatsData($post_data, $default_display) {

        if (isset($post_data['onglet'])) {
            $used_class = $post_data['onglet'];
        } else {
            $used_class = $default_display;
        }

        $class = new $used_class();
        $class->showForm();
    }

}