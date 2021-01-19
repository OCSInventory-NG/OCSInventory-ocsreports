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
  * Class for PackageBuilderParseXml
  */
class PackageBuilderParseXml
{
    const XML_OPERATING_SYSTEM  = CONFIG_DIR.'teledeploy/operatingsystems/operatingsystems.xml';
    const XML_INTERACTION       = CONFIG_DIR.'teledeploy/interactions/';
    const XML_OPTION            = CONFIG_DIR.'teledeploy/options/';

    /**
     *  Parse Operating System XML
     */
    public function parseOperatingSystem() {
        return simplexml_load_file(self::XML_OPERATING_SYSTEM);
    }

    /**
     *  Parse Interactions XML
     */
    public function parseInteractions($name) {
        return simplexml_load_file(self::XML_INTERACTION.$name.'.xml');
    }

    /**
     *  Parse Options XML
     */
    public function parseOptions($name) {
        return simplexml_load_file(self::XML_OPTION.$name.'.xml', null, LIBXML_NOCDATA);
    }

}