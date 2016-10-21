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

function show_users_left_menu($activeMenu = null) {
    global $l;
    $urls = $_SESSION['OCS']['url_service'];

    $menu = array(
        'admin_user' => array($l->g(1400), 'ms_users'),
        'admin_profiles' => array($l->g(1401), 'ms_profiles'),
        'admin_add_user' => array($l->g(1403), 'ms_add_user'),
        'admin_add_profile' => array($l->g(1399), 'ms_add_profile'),
    );


    echo '<ul class="nav nav-pills nav-stacked navbar-left">';
    foreach ($menu as $key=>$value){

        echo "<li ";
        if ($activeMenu == $value[1]) {
            echo "class='active'";
        }
        echo " ><a href='?function=".$key."'>".$value[0]."</a></li>";
    }
    echo '</ul>';
}

?>