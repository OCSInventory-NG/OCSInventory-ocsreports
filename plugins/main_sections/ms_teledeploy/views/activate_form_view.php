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

function show_activate_form($timestamp, $data, $errors) {
    // @TODO translations
    $package = get_package_info($timestamp);
    $redistrib_pack = get_redistrib_package_info($timestamp);

    echo '<h3>Package ' . $package['NAME'] . ' (' . $package['FILEID'] . ')</h3>';

    echo '<a href="#" target="_blank">Download</a>';
    echo ' - <a href="#" target="_blank">View info file</a>';


    echo open_form('activate_pack');
    echo '<div class="form-frame">';

    show_form_field($data, $errors, 'select', 'METHOD', 'Activation method', array(
        'type' => 'radio',
        'options' => array(
            ''
        )
    ));

    echo '</div>';

    echo close_form();
}

?>