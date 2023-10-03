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
require_once 'require/function_users.php';

function validate_profile_create_form($data) {
    global $l;

    $errors = array();
    $profiles = get_profile_labels();

    // TODO check for field sizes
    // Check mandatory data
    $mandatory_fields = array('name', 'label', 'duplicate_profile');
    foreach ($mandatory_fields as $field) {
        if (!$data[$field]) {
            $errors[$field] [] = $l->g(1391);
        }
    }

    // Check dropdown lists
    if ($data['duplicate_profile'] && !isset($profiles[$data['duplicate_profile']])) {
        $errors['duplicate_profile'] [] = $l->g(1392);
    }

    // Check profile name regex
    if ($data['name'] && !preg_match('/^[0-9A-Za-z]+$/', $data['name'])) {
        $errors['name'] [] = $l->g(1394);
    }

    // Check profile name doesn't exist
    if ($data['name'] && isset($profiles[$data['name']])) {
        $errors['name'] [] = $l->g(1395);
    }

    return $errors;
}

function validate_profile_edit_form($data) {
    global $l;

    $errors = array();

    $yes_no = array('YES', 'NO');

    $urls = $_SESSION['OCS']['url_service'];

    foreach ($data['config'] as $key => $val) {
        if (!in_array($val, $yes_no)) {
            $errors['config_' . $key . '_'] [] = $l->g(1392);
        }
    }

    foreach ($data['blacklist'] as $key => $val) {
        if (!in_array($val, $yes_no)) {
            $errors['blacklist_' . $key . '_'] [] = $l->g(1392);
        }
    }

    foreach ($data['pages'] as $key => $val) {
        if (!$urls->getUrl($key)) {
            $errors['blacklist_' . $key . '_'] [] = $l->g(1392);
        }
    }

    return $errors;
}

function create_profile($data) {
    $profiles = get_profiles();
    $newProfile = clone $profiles[$data['duplicate_profile']];

    $newProfile->setName($data['name']);
    $newProfile->setLabel($data['label']);

    $serializer = new XMLProfileSerializer();
    $xml = $serializer->serialize($newProfile);

    if (file_put_contents(PROFILES_DIR . $newProfile->getName() . '.xml', $xml)) {
        return $newProfile->getName();
    } else {
        return false;
    }
}

function remove_profile($profile_id) {
    global $l;

    if (!is_writable(PROFILES_DIR)) {
        msg_error($l->g(2116));
    } else {
        if(file_exists(PROFILES_DIR . $profile_id . '.xml')) {
            unlink(PROFILES_DIR . $profile_id . '.xml');
        }
    }
}

function update_profile($profile_id, $data) {
    $urls = $_SESSION['OCS']['url_service'];

    $profiles = get_profiles();
    $profile = $profiles[$profile_id];
    $updatedProfile = new Profile($profile_id, $data['new_label'] ?: $profile->getLabel());

    foreach ($data['restrictions'] as $key => $val) {
        $updatedProfile->setRestriction($key, $val);
    }

    foreach ($data['config'] as $key => $val) {
        $updatedProfile->setConfig($key, $val);
    }

    foreach ($data['blacklist'] as $key => $val) {
        if ($val == 'YES') {
            $updatedProfile->addToBlacklist($key);
        }
    }

    foreach ($data['pages'] as $key => $val) {
        if ($urls->getUrl($key) && $val == 'on') {
            $updatedProfile->addPage($key);
        }
    }

    $serializer = new XMLProfileSerializer();
    $xml = $serializer->serialize($updatedProfile);

    if (file_put_contents(PROFILES_DIR . $profile->getName() . '.xml', $xml)) {
        return $profile->getName();
    } else {
        return false;
    }
}

?>