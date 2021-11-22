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
 * Holds the config for a profile
 */
class Profile {
    private $restrictions;
    private $config;
    private $blacklist;
    private $pages;

    public function __construct(private $name, private $label) {
        $this->restrictions = array();
        $this->config = array();
        $this->blacklist = array();
        $this->pages = array();
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getLabel() {
        return $this->label;
    }

    public function getLabelTranslated() {
        global $l;

        if (preg_match('/^g\(\d+\)$/', $this->label)) {
            return $l->g(substr(substr($this->label, 2), 0, -1));
        } else {
            return $this->label;
        }
    }

    public function setLabel($label) {
        $this->label = $label;
    }

    public function getRestrictions() {
        return $this->restrictions;
    }

    public function getRestriction($key, $default = null) {
        return isset($this->restrictions[$key]) ? $this->restrictions[$key] : $default;
    }

    public function setRestriction($key, $restriction) {
        $this->restrictions[$key] = $restriction;
    }

    public function removeRestriction($key) {
        unset($this->restrictions[$key]);
    }

    public function getConfig() {
        return $this->config;
    }

    public function getConfigValue($key, $default = null) {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }

    public function setConfig($key, $val) {
        $this->config[$key] = $val;
    }

    public function removeConfig($key) {
        unset($this->config[$key]);
    }

    public function getBlacklist() {
        return $this->blacklist;
    }

    public function hasInBlacklist($value) {
        return array_search($value, $this->blacklist) !== false;
    }

    public function addToBlacklist($value) {
        if (!$this->hasInBlacklist($value)) {
            $this->blacklist [] = $value;
        }
    }

    public function removeFromBlacklist($value) {
        $index = array_search($value, $this->blacklist);
        if ($index !== false) {
            array_splice($this->blacklist, $index, 1);
        }
    }

    public function getPages() {
        return $this->pages;
    }

    public function hasPage($name) {
        return array_search($name, $this->pages) !== false;
    }

    public function addPage($name) {
        if (!$this->hasPage($name)) {
            $this->pages [] = $name;
        }
    }

    public function removePage($name) {
        $index = array_search($name, $this->pages);
        if ($index !== false) {
            array_splice($this->pages, $index, 1);
        }
    }

}
?>