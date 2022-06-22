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
 * MenuElem class
 *
 * The class generate one menu element     
 */
class MenuElem extends Menu {
    private $_label;
    private $_url;

    /**
     * The constructor
     *
     * @param string $_label Label
     * @param string $_url Url
     * @param array  $_children Children
     * @param number $_priority The priority of the MenuElem
     */
    public function __construct($_label, $_url, array $_children = array(), $_priority = 0) {
        $this->_label = $_label;
        $this->_url = $_url;

        parent::__construct($_children, $_priority);
    }

    /**
     * Get the MenuElem label
     * 
     * @return string $this->label 
     */
    public function getLabel() {
        return $this->_label;
    }

    /**
     * Set the MenuElem label
     * 
     * @param string $_label MenuElem label
     * 
     * @return MenuElem
     */
    public function setLabel($_label) {
        $this->_label = $_label;
        return $this;
    }

    /**
     * Get MenuElem url
     * 
     * @return string $this->_url
     */
    public function getUrl() {
        return $this->_url;
    }

    /**
     * Set MenuElem url
     * 
     * @param string $_url MenuElem url
     * 
     * @return MenuElem
     */
    public function setUrl($_url) {
        $this->_url = $_url;
        return $this;
    }

}