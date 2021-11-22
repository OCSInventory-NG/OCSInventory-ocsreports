<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

/**
 * Renders the computer menu
 */
class ComputerMenuRenderer extends MenuRenderer {
    public function __construct(private $computer_id, private $urls) {
        parent::__construct();
    }

	public function getUrl(MenuElem $menu_elem) {
		return "?".PAG_INDEX."=".$this->urls->getUrl('ms_computer')."&head=1&systemid=".$this->computer_id."&".$menu_elem->getUrl();
	}

	public function getLabel(MenuElem $menu_elem) {
    	$label = $this->translateLabel($menu_elem->getLabel());
    	
    	if ($menu_elem->hasChildren() and $level == 0) {
    		$label .= ' <b class="right-caret"></b>';
    	}
    	
    	return $label;
	}

}
?>