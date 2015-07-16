<?php

/**
 * Serialize / unserialize the js config from an XML file
 *
 * @author   Arthur Jaouen <arthur@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://www.ocsinventory-ng.org/
 *
 */
class XMLJsSerializer {
	public function serialize($js) {
		$doc_xml = new DOMDocument('1.0', 'UTF-8');
		$js_xml = $doc_xml->createElement('javascripts');
		$doc_xml->appendChild($js_xml);
		
		foreach ($js as $file) {
			$js_elem_xml = $doc_xml->createElement('file', $file);
			$js_xml->appendChild($js_elem_xml);
		}
		
		$doc_xml->preserveWhiteSpace = false;
		$doc_xml->formatOutput = true;
		
		return $doc_xml->saveXML();
	}
	
	public function unserialize($xml) {
		$js_xml = simplexml_load_string($xml);

		$js = array();
		foreach ($js_xml->{'file'} as $file) {
			$js []= (string) $file;
		}
		
		return $js;
	}
}

?>