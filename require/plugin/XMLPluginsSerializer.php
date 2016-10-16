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
 * Serialize / unserialize the plugins from an XML file
 */
class XMLPluginsSerializer {

    public function unserialize($xml) {
        $plugins_xml = simplexml_load_string($xml);

        $plugins = array();
        foreach ($plugins_xml->plugin as $plugin_xml) {
            $id = (string) $plugin_xml['id'];
            $label = (string) $plugin_xml->label;
            $system = (bool) $plugin_xml->system;
            $cat = (string) $plugin_xml->category;
            $available = (string) $plugin_xml->available;
            $hide_frame = (string) $plugin_xml->hide_frame;

            $plugin = new ComputerPlugin($id, $system, $label);
            if ($cat) {
                $plugin->setCategory($cat);
            }
            if ($available) {
                $plugin->setAvailable($available);
            }
            if ($hide_frame) {
                $plugin->setHideFrame($hide_frame);
            }

            $plugins[$id] = $plugin;
        }

        return $plugins;
    }

}
?>