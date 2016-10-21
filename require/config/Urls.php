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
 * Holds the config for the urls
 */
class Urls {
    private $urls;
    private $urlNames;

    public function __construct() {
        $this->urls = array();
        $this->urlNames = array();
    }

    public function getUrl($key) {
        return isset($this->urls[$key]) ? $this->urls[$key]['value'] : null;
    }

    public function getDirectory($key) {
        return isset($this->urls[$key]) ? $this->urls[$key]['directory'] : null;
    }

    public function getUrlName($value) {
        return isset($this->urlNames[$value]) ? $this->urlNames[$value] : null;
    }

    public function addUrl($key, $value, $directory) {
        $this->urls[$key] = array(
            'value' => $value,
            'directory' => $directory
        );

        // For reverse lookup
        $this->urlNames[$value] = $key;
    }

    public function getUrls() {
        return $this->urls;
    }

    public function getUrlNames() {
        return $this->urlNames;
    }

}
?>