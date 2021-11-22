<?php
/*
 * Copyright 2005-2019 OCSInventory-NG/OCSInventory-ocsreports contributors.
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
 *  CommandLine class
 */
class CommandLine
{
    public function get_mib_oid($file) {
        $oids = [];

        $champs = array('SNMP_MIB_DIRECTORY' => 'SNMP_MIB_DIRECTORY');
        $values = look_config_default_values($champs);
        $cmd = "snmptranslate -Tz -m ".$values['tvalue']['SNMP_MIB_DIRECTORY']."/".escapeshellarg($file);
        $result_cmd = shell_exec(escapeshellcmd($cmd));
        $result_cmd = preg_split("/\r\n|\n|\r/", $result_cmd);
        $result_cmd = str_replace('"', "", $result_cmd);

        foreach ($result_cmd as $oid) {
            $split = preg_split('/\t/', $oid, null, PREG_SPLIT_NO_EMPTY);
            if($split[0] != "") {
                $oids[$split[0]] = $split[1]; 
            } 
        }
        return $oids;
    }
}