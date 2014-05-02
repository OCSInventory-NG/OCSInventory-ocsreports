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
	
	public function __construct() {
		$this->urls = array();
	}

	public function getUrl($key, $default = null) {
		return isset($this->urls[$key]) ? $this->urls[$key]['value'] : $default;
	}
	
	public function getDirectory($key, $default = null) {
		return isset($this->urls[$key]) ? $this->urls[$key]['directory'] : $default;
	}
	
	public function addUrl($key, $value, $directory) {
		$this->urls[$key] = array(
			'value' => $value,
			'directory' => $directory
		);
	}
	
	public function getUrls() {
		return $this->urls;
	}
}

?>