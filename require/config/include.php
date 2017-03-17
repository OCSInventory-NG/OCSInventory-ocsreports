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

?>