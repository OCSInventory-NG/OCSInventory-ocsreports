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
global $l;

if (!AJAX) {
	require_once 'views/users_views.php';
	require_once 'views/profile_create_form.php';

    echo "<div class='col col-md-2'>";
    show_users_left_menu('ms_add_profile');
    echo "</div>";
	
	echo '<div class="col col-md-10">';

    if (!is_writable(PROFILES_DIR)) {
        msg_error($l->g(2116));
    } else {
        show_profile_create_form();
    }

    echo '</div>';
} else {
    require_once 'lib/profile_functions.php';

    if (!is_writable(PROFILES_DIR)) {
        $response = array(
            'status' => 'error',
            'message' => $l->g(2116)
        );
    } else if ($errors = validate_profile_create_form($protectedPost)) {
        $response = array(
            'status' => 'error',
            'message' => $l->g(1404),
            'errors' => $errors
        );
    } else if ($profile_id = create_profile($protectedPost)) {
        $response = array(
            'status' => 'success',
            'message' => $l->g(1405),
            'profile_id' => $profile_id
        );
    } else {
        $response = array(
            'status' => 'error',
            'message' => $l->g(1406)
        );
    }

    header('Content-type: application/json');
    echo json_encode($response);
}
?>