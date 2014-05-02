<?php

/**
 * Holds the config for the urls
 *
 * @author   Arthur Jaouen <arthur@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://www.ocsinventory-ng.org/
 *
 */
class Urls {
	private $urls;
	private $urlNames;
	
	public function __construct() {
		$this->urls = array();
		$this->urlNames = array();
	}

	public function getUrl($key) {
		return isset($this->urls[$key]) ? $this->urls[$key]['value'] : null;
	}
	
	public function getDirectory($key) {
		return isset($this->urls[$key]) ? $this->urls[$key]['directory'] : null;
	}
	
	public function getUrlName($value) {
		return isset($this->urlNames[$value]) ? $this->urlNames[$value] : null;
	}
	
	public function addUrl($key, $value, $directory) {
		$this->urls[$key] = array(
			'value' => $value,
			'directory' => $directory
		);
		
		// For reverse lookup
		$this->urlNames[$value] = $key;
	}
	
	public function getUrls() {
		return $this->urls;
	}
	
	public function getUrlNames() {
		return $this->urlNames;
	}
}

?>