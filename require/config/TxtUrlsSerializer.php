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
 * Unserialize the urls from the old txt config files
 */
class TxtUrlsSerializer {

    public function serialize(Urls $urls) {
        throw new Exception('Cannot serialize OCS 2.2 urls to old (pre 2.2) txt files');
    }

    public function unserialize($config) {
        if (!is_array($config)) {
            return false;
        }

        $urls = new Urls();
        foreach ($config['URL'] as $key => $val) {
            $urls->addUrl($key, $val, $config['DIRECTORY'][$key]);
        }

        return $urls;
    }

}
?>