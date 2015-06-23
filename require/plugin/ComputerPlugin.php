<?php

/**
 * Holds the config for a computer plugin
 *
 * @author   Arthur Jaouen <arthur@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://www.ocsinventory-ng.org/
 *
 */
class ComputerPlugin {
	private $id;
	private $system;
	private $label;
	private $category;
	private $available;
	private $hideFrame;
	
	public function __construct($id, $system, $label) {
		$this->id = $id;
		$this->system = $system;
		$this->label = $label;
		$this->category = 'other';
		$this->available = null;
		$this->hideFrame = false;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function isSystem() {
		return $this->system;
	}
	
	public function setSystem($system) {
		$this->system = $system;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function setLabel($label) {
		$this->label = $label;
	}
	
	public function getCategory() {
		return $this->category;
	}
	
	public function setCategory($category) {
		$this->category = $category;
	}
	
	public function getAvailable() {
		return $this->available;
	}
	
	public function setAvailable($available) {
		$this->available = $available;
	}
	
	public function getHideFrame() {
		return $this->hideFrame;
	}
	
	public function setHideFrame($hideFrame) {
		$this->hideFrame = $hideFrame;
		return $this;
	}
	
}

?>