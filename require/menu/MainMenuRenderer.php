<?php

/**
 * Renders the main menu
 *
 * @author   Arthur Jaouen <arthur@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://www.ocsinventory-ng.org/
 *
 */
class MainMenuRenderer extends MenuRenderer {
	private $profile;
	private $urls;
	
	public function __construct($profile, $urls) {
		parent::__construct();
	
		$this->profile = $profile;
		$this->urls = $urls;
	}
	
	protected function canSeeElem(MenuElem $menu_elem) {
		return $menu_elem->hasChildren() or $this->profile->hasPage($menu_elem->getUrl());
	}

	protected function getUrl(MenuElem $menu_elem) {
		return "?".PAG_INDEX."=".$this->urls->getUrl($menu_elem->getUrl());
	}

}

?>