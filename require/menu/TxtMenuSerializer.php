<?php

/**
 * unserialize the menu from the old txt config files
 *
 * @author   Arthur Jaouen <arthur@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://www.ocsinventory-ng.org/
 *
 */
class TxtMenuSerializer {
	public function serialize(Menu $menu) {
		throw new Exception('Cannot serialize OCS 2.2 menus to old (pre 2.2) txt files');
	}
	
	public function unserialize($config) {
		if (!is_array($config)) {
			return false;
		}
		// Build menu
		$menu = new Menu();
		foreach ($config['ORDER'] as $config_elem) {
			$url = $config_elem;
		
			if (isset($config['MENU_NAME'][$config_elem])) {
				$lbl_index = $config['MENU_TITLE'][$config_elem];
					
				if (is_null($lbl_index)) {
					$lbl = $config_elem;
				} else {
					$lbl = $lbl_index;
				}
					
				$menu->addElem($config_elem, new MenuElem($lbl, $url));
					
				// Element has children
				foreach ($config['MENU'] as $page_name => $menu_name) {
					if ($menu_name == $config_elem) {
						$url = $page_name;
						$lbl = $config['LBL'][$page_name];
		
						$menu->getElem($config_elem)->addElem($page_name, new MenuElem($lbl, $url));
					}
				}
			} else {
				// No children
				$lbl_index = $config['LBL'][$config_elem];
					
				if (is_null($lbl_index)) {
					$lbl = $config_elem;
				} else {
					$lbl = $lbl_index;
				}
					
				$menu->addElem($config_elem, new MenuElem($lbl, $url));
			}
		}
		
		return $menu;
	}
}

?>