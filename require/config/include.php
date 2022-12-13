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

require_once 'Urls.php';
require_once 'XMLUrlsSerializer.php';
require_once 'TxtUrlsSerializer.php';

require_once 'XMLJsSerializer.php';
require_once 'TxtJsSerializer.php';

require_once 'Profile.php';
require_once 'TxtProfileSerializer.php';
require_once 'XMLProfileSerializer.php';

function migrate_config_2_2() {
    global $l;

    if (!is_writable(CONFIG_DIR)) {
        msg_error($l->g(2029));
        exit;
    }

    require_once('require/function_files.php');

    $config = read_config_file();
    migrate_urls_2_2($config);
    migrate_js_2_2($config);
    migrate_profiles_2_2();
    migrate_menus_2_2($config);
}

function migrate_urls_2_2($config) {
    $txt_serializer = new TxtUrlsSerializer();
    $xml_serializer = new XMLUrlsSerializer();
    $filename = CONFIG_DIR . 'urls.xml';
    $urls = $txt_serializer->unserialize($config);
    $xml = $xml_serializer->serialize($urls);

    file_put_contents($filename, $xml);
}

function migrate_js_2_2($config) {
    $txt_serializer = new TxtJsSerializer();
    $xml_serializer = new XMLJsSerializer();

    $filename = CONFIG_DIR . 'js.xml';
    $js = $txt_serializer->unserialize($config);
    $xml = $xml_serializer->serialize($js);

    file_put_contents($filename, $xml);
}

function migrate_profiles_2_2() {
    global $l;

    if (!file_exists(PROFILES_DIR)) {
        mkdir(PROFILES_DIR);
    }

    if (!is_writable(PROFILES_DIR)) {
        msg_error($l->g(2116));
        exit;
    }

    $txt_serializer = new TxtProfileSerializer();
    $xml_serializer = new XMLProfileSerializer();

    foreach (scandir($_SESSION['OCS']['CONF_PROFILS_DIR']) as $file) {
        if (preg_match('/^(.+)_config\.txt$/', $file, $matches) && $matches[1] != '4all') {
            $profile_name = $matches[1];
            $profile_data = read_profil_file($profile_name);

            $profile = $txt_serializer->unserialize($profile_name, $profile_data);
            $xml = $xml_serializer->serialize($profile);

            file_put_contents(PROFILES_DIR . $profile_name . '.xml', $xml);
        }
    }
}

/**
 * Update TYPE accountinfo
 */
function migrate_adminData_2_5(){
    //4,6 and 7 correspond to the old values type of administrative data and 5,14 and 11 are the new
    //4 -> old type of checkbox fields
    //6 -> old type of date fields
    //7 -> old type of radio button fields
    $type_replace = array( '4' => '5',
              '6' => '14',
              '7' => '11');

    $sql = "SELECT TYPE FROM accountinfo_config";

    $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
    if($result) foreach ($result as $type){
      if($type['TYPE'] == '4' || $type['TYPE'] == '6' || $type['TYPE'] == '7'){
        $sql_replace = "UPDATE accountinfo_config SET TYPE = '%s' WHERE TYPE = '%s'";
        $arg_replace = array($type_replace[$type['TYPE']], $type['TYPE']);

        mysql2_query_secure($sql_replace, $_SESSION['OCS']["writeServer"], $arg_replace);
      }
    }
}

/**
 * migrate_snmp_2_10_1 : add lastdate column to existing snmp types
 *
 * @return void
 */
function migrate_snmp_2_10_1() {
    // If SNMP is enable
    $isEnable = look_config_default_values(array("SNMP" => "SNMP"))['ivalue']['SNMP'] ?? 0;

    if($isEnable) {
        $sqlColumnExists = "SELECT COUNT(*) as nb FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%s' AND COLUMN_NAME = 'LASTDATE'";
        $sqlAlter = "ALTER TABLE `%s` ADD COLUMN `LASTDATE` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `ID`";
    
        // Get all table type name
        $sql = "SELECT DISTINCT `TABLE_TYPE_NAME` FROM `snmp_types`";
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);

        if($result) foreach ($result as $table_name){
            // Verif if the column already exists
            $resultColumnExists = mysql2_query_secure($sqlColumnExists, $_SESSION['OCS']["readServer"], array($table_name['TABLE_TYPE_NAME']));

            if($resultColumnExists) foreach ($resultColumnExists as $nb){
                if(!$nb['nb']) {
                    mysql2_query_secure($sqlAlter, $_SESSION['OCS']["writeServer"], array($table_name['TABLE_TYPE_NAME']));
                }
            }
        }
    }
}

?>
