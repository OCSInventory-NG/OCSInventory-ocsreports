<?php

/**
 * Unserialize the js files from the old txt config files
 *
 * @author   Arthur Jaouen <arthur@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://www.ocsinventory-ng.org/
 *
 */
class TxtJsSerializer {
	public function serialize($js) {
		throw new Exception('Cannot serialize OCS 2.2 js config to old (pre 2.2) txt files');
	}
	
	public function unserialize($config) {
		if (!is_array($config)) {
			return false;
		}
		
		$js = array();
		foreach ($config['JAVASCRIPT'] as $file => $path) {
			$js []= $path.$file;
		}
		
		return $js;
	}
}

?>