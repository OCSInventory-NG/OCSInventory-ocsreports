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
class PackageBuilderParseXml
{
    const XML_OPERATING_SYSTEM  = 'config/teledeploy/operatingsystems/operatingsystems.xml';
    const XML_INTERACTION       = 'config/teledeploy/interactions/';

    public function parseOperatingSystem() {
        return simplexml_load_file(self::XML_OPERATING_SYSTEM);
    }

    public function parseInteractions($name) {

    }

}