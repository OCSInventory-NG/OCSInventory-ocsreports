<?php

/**
 * Renders the computer menu
 *
 * @author   Arthur Jaouen <arthur@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://www.ocsinventory-ng.org/
 *
 */
class ComputerMenuRenderer extends MenuRenderer {
	private $computer_id;
	private $urls;
	
	public function __construct($computer_id, $urls) {
		parent::__construct();
		
		$this->computer_id = $computer_id;
		$this->urls = $urls;
	}

	protected function getUrl(MenuElem $menu_elem) {
		return "?".PAG_INDEX."=".$this->urls->getUrl('ms_computer')."&head=1&systemid=".$this->computer_id."&".$menu_elem->getUrl();
	}

	protected function getLabel(MenuElem $menu_elem) {
    	$label = $this->translateLabel($menu_elem->getLabel());
    	
    	if ($menu_elem->hasChildren() and $level == 0) {
    		$label .= ' <b class="right-caret"></b>';
    	}
    	
    	return $label;
	}
}

?>