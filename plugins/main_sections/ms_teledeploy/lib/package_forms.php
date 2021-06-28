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

function validate_package_form($data, $files) {
    global $l;

    $errors = array();

    // TODO check for field sizes
    // Check mandatory data
    $mandatory_fields = array('NAME', 'DESCRIPTION', 'OS', 'ACTION', 'ACTION_INPUT', 'DEPLOY_SPEED');
    foreach ($mandatory_fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) == false) {
            $errors[$field] [] = $l->g(1391);
        }
    }

    // Check dropdown lists
    if (isset($data['OS']) && !in_array($data['OS'], array('WINDOWS', 'LINUX', 'MAC'))) {
        $errors['OS'] [] = $l->g(1392);
    }
    if (isset($data['ACTION']) && !in_array($data['ACTION'], array('STORE', 'EXECUTE', 'LAUNCH'))) {
        $errors['ACTION'] [] = $l->g(1392);
    }
    if (isset($data['DEPLOY_SPEED']) && !in_array($data['DEPLOY_SPEED'], array('LOW', 'MIDDLE', 'HIGH', 'CUSTOM'))) {
        $errors['DEPLOY_SPEED'] [] = $l->g(1392);
    }

    // Check file upload
    if ($data['ACTION'] != 'EXECUTE' && (!isset($files['FILE']) || trim($files['FILE']['name']) == false)) {
        $errors['FILE'] [] = $l->g(1391);
    }

    // Check mandatory fields depending on others
    if ($data['DEPLOY_SPEED'] == 'CUSTOM') {
        $mandatory_fields = array('PRIORITY', 'NB_FRAGS');
        foreach ($mandatory_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) == false) {
                $errors[$field] [] = 'This field is mandatory';
            }
        }
    }
    if ($data['OS'] == 'WINDOWS') {
        if ($data['NOTIFY_USER'] == 'on') {
            $mandatory_fields = array('NOTIFY_TEXT', 'NOTIFY_COUNTDOWN');
            foreach ($mandatory_fields as $field) {
                if (!isset($data[$field]) || trim($data[$field]) == false) {
                    $errors[$field] [] = $l->g(1391);
                }
            }
        }
        if ($data['NEED_DONE_ACTION'] == 'on') {
            if (!isset($data['NEED_DONE_ACTION_TEXT']) || trim($data['NEED_DONE_ACTION_TEXT']) == false) {
                $errors['NEED_DONE_ACTION_TEXT'] [] = $l->g(1391);
            }
        }
    }

    // Check unique fields
    if ($data['NAME'] && package_name_exists(trim($data['NAME']))) {
        $errors['NAME'] [] = $l->g(1393);
    }

    return $errors;
}

?>