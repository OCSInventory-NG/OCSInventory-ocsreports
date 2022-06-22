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
 * Renders the main menu
 */
class MainMenuRenderer extends MenuRenderer {
    private $profile;
    private $urls;

    public function __construct($profile, $urls) {
        parent::__construct();

        $this->profile = $profile;
        $this->urls = $urls;
    }

    protected function canSeeElem(MenuElem $menu_elem) {
        return $menu_elem->hasChildren() || $this->profile->hasPage($menu_elem->getUrl());
    }

    /**
     * Add value to profile pages
     * This is needed for the extension engine
     *
     * @param $value : value to add in profiles pages
     */
    protected function addValueToProfileAndUrls($value, $extMapName){
        $this->urls->addUrl($value, $value, EXT_DL_DIR.$extMapName."/".$value);
    }

    protected function getUrl(MenuElem $menu_elem) {
        return "?" . PAG_INDEX . "=" . $this->urls->getUrl($menu_elem->getUrl());
    }

}
?>