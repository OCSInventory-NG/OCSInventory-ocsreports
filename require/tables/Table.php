<?php

require_once('require/tables/Column.php');

class Table {
	private $name;
	private $columns;
	
	public function __construct($name) {
		$this->name = $name;
		$this->columns = array();
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function addColumn(Column $col) {
		$this->columns[$col->getName()] = $col;
		return $this;
	}
	
	public function getColumns() {
		return $this->columns;
	}
	
	public function getColumn($name) {
		return $this->columns[$name];
	}
}

?>