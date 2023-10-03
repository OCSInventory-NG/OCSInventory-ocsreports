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
 * Serialize / unserialize the profile from an XML file
 */
class XMLProfileSerializer {

    public function serialize(Profile $profile) {
        $doc_xml = new DOMDocument('1.0', 'UTF-8');
        $profile_xml = $doc_xml->createElement('profile');
        $profile_xml->setAttribute('label', $profile->getLabel());
        $doc_xml->appendChild($profile_xml);

        $restrictions_xml = $doc_xml->createElement('restrictions');
        $profile_xml->appendChild($restrictions_xml);

        foreach ($profile->getRestrictions() as $key => $restriction) {
            $restriction_xml = $doc_xml->createElement('restriction', $restriction);
            $restriction_xml->setAttribute('key', $key);
            $restrictions_xml->appendChild($restriction_xml);
        }

        $config_xml = $doc_xml->createElement('config');
        $profile_xml->appendChild($config_xml);

        foreach ($profile->getConfig() as $key => $value) {
            $config_elem_xml = $doc_xml->createElement('config-elem', $value);
            $config_elem_xml->setAttribute('key', $key);
            $config_xml->appendChild($config_elem_xml);
        }

        $blacklist_xml = $doc_xml->createElement('blacklist');
        $profile_xml->appendChild($blacklist_xml);

        foreach ($profile->getBlacklist() as $blacklist_elem) {
            $blacklist_elem_xml = $doc_xml->createElement('blacklist-elem', $blacklist_elem);
            $blacklist_xml->appendChild($blacklist_elem_xml);
        }

        $pages_xml = $doc_xml->createElement('pages');
        $profile_xml->appendChild($pages_xml);

        foreach ($profile->getPages() as $page) {
            $page_xml = $doc_xml->createElement('page', $page);
            $pages_xml->appendChild($page_xml);
        }

        $doc_xml->preserveWhiteSpace = false;
        $doc_xml->formatOutput = true;

        return $doc_xml->saveXML();
    }

    public function unserialize($name, $xml) {
        $profile_xml = simplexml_load_string($xml);
        $label = (string) $profile_xml['label'];

        $profile = new Profile($name, $label);

        foreach ($profile_xml->restrictions->restriction as $restriction_xml) {
            $key = (string) $restriction_xml['key'];
            $val = (string) $restriction_xml;
            $profile->setRestriction($key, $val);
        }

        foreach ($profile_xml->config->{'config-elem'} as $config_xml) {
            $key = (string) $config_xml['key'];
            $val = (string) $config_xml;
            $profile->setConfig($key, $val);
        }

        foreach ($profile_xml->blacklist->{'blacklist-elem'} as $blacklist_xml) {
            $profile->addToBlacklist((string) $blacklist_xml);
        }

        foreach ($profile_xml->pages->page as $page_xml) {
            $profile->addPage((string) $page_xml);
        }

        return $profile;
    }

}
?>