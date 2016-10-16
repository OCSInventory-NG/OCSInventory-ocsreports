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

    $menu = new Menu(array(
        'users' => new MenuElem($l->g(1400), "?" . PAG_INDEX . "=" . $urls->getUrl('ms_users')),
        'profiles' => new MenuElem($l->g(1401), "?" . PAG_INDEX . "=" . $urls->getUrl('ms_profiles')),
        'add_user' => new MenuElem($l->g(1403), "?" . PAG_INDEX . "=" . $urls->getUrl('ms_add_user')),
        'add_profile' => new MenuElem($l->g(1399), "?" . PAG_INDEX . "=" . $urls->getUrl('ms_add_profile')),
    ));

    $menu_renderer = new MenuRenderer();

    if ($activeMenu) {
        $menu_renderer->setActiveLink("?" . PAG_INDEX . "=" . $urls->getUrl($activeMenu));
    }

    echo '<div class="left-menu">';
    echo '<div class="navbar navbar-default">';
    echo $menu_renderer->render($menu);
    echo '</div>';
    echo '</div>';
}

?>