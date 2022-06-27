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
class XMLUrlsSerializer {

    public function serialize(Urls $urls) {
        $doc_xml = new DOMDocument('1.0', 'UTF-8');
        $urls_xml = $doc_xml->createElement('urls');
        $doc_xml->appendChild($urls_xml);

        foreach ($urls->getUrls() as $key => $url) {
            $url_elem_xml = $doc_xml->createElement('url');

            $url_value_xml = $doc_xml->createElement('value', $url['value']);
            $url_directory_xml = $doc_xml->createElement('directory', $url['directory']);

            $url_elem_xml->setAttribute('key', $key);
            $url_elem_xml->appendChild($url_value_xml);
            $url_elem_xml->appendChild($url_directory_xml);

            $urls_xml->appendChild($url_elem_xml);
        }

        $doc_xml->preserveWhiteSpace = false;
        $doc_xml->formatOutput = true;

        return $doc_xml->saveXML();
    }

    public function unserialize($xml) {
        $urls_xml = simplexml_load_string($xml);

        $urls = new Urls($urls_xml);
        foreach ($urls_xml->{'url'} as $url_elem_xml) {
            $url_key = (string) $url_elem_xml['key'];
            $url_val = (string) $url_elem_xml->value;
            $url_directory = (string) $url_elem_xml->directory;

            $urls->addUrl($url_key, $url_val, $url_directory);
        }

        return $urls;
    }

}
?>