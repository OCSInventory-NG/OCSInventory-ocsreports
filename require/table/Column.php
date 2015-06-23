<?php

/**
 * Handle properties of every column of the table 
 *
 * @author   Mickael Alibert <mickael.alibert@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://www.ocsinventory-ng.org/
 *
 */
abstract class Column {
	
	private $label;
	private $key;
	private $visible;
	private $deletable;
	private $sortable;
	
	
	
	public function __construct($key,$label,$visible,$deletable,$sortable) {
		$this->key = $key;
		$this->label = $label;
		$this->visible = $visible;
		$this->deletable = $deletable;
		$this->sortable = $sortable;
	}

	
	/*
	 * Return the column display content
	 */
	public function getLabel(){
		return $this->label;
	}
	
	/*
	 * Set the column display content
	 */
	public function setLabel($label){
		$this->label = $label;
	}

	
	/*
	 * 	@params ( false || true ) 
	 *  Set the display options of the column : 
	 *  visibility : displayed or not
	 * 	deletable : appears in Hide/Show list
	 * 	sortable : up and down arrows on the right
	 * 
	 */
	public function setVisible($visible){
		$this->visible = $visible;
	}
	
	public function setDeletable($deletable){
		$this->deletable = $deletable;
	}
	
	public function setSortable ($sortable){
		$this->sortable = $sortable;
	}
	
	
	/*
	 * Return the display options of the column 
	 */
	public function isVisible(){
		return $this->visible;
	}
	public function isDeletable(){
		return $this->deletable ;
	}
	public function isSortable (){
		return $this->sortable ;
	}
	
	
}
?>