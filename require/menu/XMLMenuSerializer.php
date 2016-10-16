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
 * Serialize / unserialize the menu from an XML file
 */
class XMLMenuSerializer {

    public function serialize(Menu $menu) {
        $doc_xml = new DOMDocument('1.0', 'UTF-8');
        $menu_xml = $doc_xml->createElement('menu');
        $doc_xml->appendChild($menu_xml);

        foreach ($menu->getChildren() as $id => $menu_elem) {
            $this->serializeElem($doc_xml, $menu_xml, $id, $menu_elem);
        }

        $doc_xml->preserveWhiteSpace = false;
        $doc_xml->formatOutput = true;

        return $doc_xml->saveXML();
    }

    private function serializeElem(DOMDocument $doc_xml, DOMElement $parent_xml, $id, MenuElem $menu_elem) {
        $menu_elem_xml = $doc_xml->createElement('menu-elem');
        $menu_elem_xml->setAttribute('id', $id);

        $label_xml = $doc_xml->createElement('label', $menu_elem->getLabel());
        $url_xml = $doc_xml->createElement('url', $menu_elem->getUrl());

        $menu_elem_xml->appendChild($label_xml);
        $menu_elem_xml->appendChild($url_xml);

        $menu_children = $menu_elem->getChildren();
        if ($menu_children) {
            $submenu_xml = $doc_xml->createElement('submenu');
            $menu_elem_xml->appendChild($submenu_xml);
            foreach ($menu_children as $child_id => $child_elem) {
                $this->serializeElem($doc_xml, $submenu_xml, $child_id, $child_elem);
            }
        }

        $parent_xml->appendChild($menu_elem_xml);
    }

    public function unserialize($xml) {
        $menu_xml = simplexml_load_string($xml);

        $menu = new Menu();
        foreach ($menu_xml->{'menu-elem'} as $menu_elem_xml) {
            $menu_elem_id = (string) $menu_elem_xml['id'];
            $menu_elem = $this->unserializeElem($menu_elem_xml);
            $menu->addElem($menu_elem_id, $menu_elem);
        }

        return $menu;
    }

    private function unserializeElem($menu_elem_xml) {
        $label = (string) $menu_elem_xml->label;
        $url = (string) $menu_elem_xml->url;
        $submenu_xml = $menu_elem_xml->submenu->{'menu-elem'};

        $menu_elem = new MenuElem($label, $url);

        if ($submenu_xml) {
            foreach ($submenu_xml as $sub_elem_xml) {
                $sub_id = (string) $sub_elem_xml['id'];
                $sub_elem = $this->unserializeElem($sub_elem_xml);
                $menu_elem->addElem($sub_id, $sub_elem);
            }
        }

        return $menu_elem;
    }

}
?>