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

/**
 * unserialize the menu from the old txt config files
 */
class TxtMenuSerializer {

    public function serialize(Menu $menu) {
        throw new Exception('Cannot serialize OCS 2.2 menus to old (pre 2.2) txt files');
    }

    public function unserialize($config) {
        if (!is_array($config)) {
            return false;
        }

        if (isset($config['ORDER'])) {
            $order = $config['ORDER'];
        } else {
            $order = array_merge($config['ORDER_FIRST_TABLE'], $config['ORDER_SECOND_TABLE']);
        }

        // Build menu
        $menu = new Menu();
        foreach ($order as $config_elem) {
            $url = $config_elem;

            if (isset($config['MENU_NAME'][$config_elem])) {
                $lbl_index = $config['MENU_TITLE'][$config_elem];

                if (is_null($lbl_index)) {
                    $lbl = $config_elem;
                } else {
                    $lbl = $lbl_index;
                }

                $menu->addElem($config_elem, new MenuElem($lbl, $url));

                // Element has children
                foreach ($config['MENU'] as $page_name => $menu_name) {
                    if ($menu_name == $config_elem) {
                        $url = $page_name;
                        $lbl = $config['LBL'][$page_name];

                        $menu->getElem($config_elem)->addElem($page_name, new MenuElem($lbl, $url));
                    }
                }
            } else {
                // No children
                $lbl_index = $config['LBL'][$config_elem];

                if (is_null($lbl_index)) {
                    $lbl = $config_elem;
                } else {
                    $lbl = $lbl_index;
                }

                $menu->addElem($config_elem, new MenuElem($lbl, $url));
            }
        }

        return $menu;
    }

}
?>