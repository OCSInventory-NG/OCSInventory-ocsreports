<?php

/**
 * Unserialize the urls from the old txt config files
 *
 * @author   Arthur Jaouen <arthur@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://www.ocsinventory-ng.org/
 *
 */
class TxtUrlsSerializer {
	public function serialize(Urls $urls) {
		throw new Exception('Cannot serialize OCS 2.2 urls to old (pre 2.2) txt files');
	}
	
	public function unserialize($config) {
		if (!is_array($config)) {
			return false;
		}
		
		$urls = new Urls();
		foreach ($config['URL'] as $key => $val) {
			$urls->addUrl($key, $val, $config['DIRECTORY'][$key]);
		}
		
		return $urls;
	}
}

?>