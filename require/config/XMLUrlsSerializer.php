<?php

/**
 * Serialize / unserialize the menu from an XML file
 *
 * @author   Arthur Jaouen <arthur@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://www.ocsinventory-ng.org/
 *
 */
class XMLUrlsSerializer {
	public function serialize(Urls $urls) {
		// TODO options for version and encoding
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
		
		$urls = new Urls($urls);
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