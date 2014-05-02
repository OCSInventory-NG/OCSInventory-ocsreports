<?php

/**
 * Serialize / unserialize the profile from an XML file
 *
 * @author   Arthur Jaouen <arthur@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://www.ocsinventory-ng.org/
 *
 */
class XMLProfileSerializer {
	public function serialize(Profile $profile) {
		// TODO options for version and encoding
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
		
		foreach ($profile->getPages() as $key => $page) {
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