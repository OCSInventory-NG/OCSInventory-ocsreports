<?php

/**
 * Handle Column Objects arrays for the table
 *
 * @author   Mickael Alibert <mickael.alibert@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://www.ocsinventory-ng.org/
 *
 */
class Columns {
	private $allcolumns;
	private $columnscantdel;
	private $columnscandel;
	private $columnsvisible;
	private $columnsspecial;
	private $defaultcolumns;
	
	public function __construct() {
			$this->allcolumns = array();
			$this->columnscantdel = array();
			$this->columnscandel = array();
			$this->columnsvisible = array();
			$this->columnsspecial = array(
				"CHECK","SUP",
				"GROUP_NAME",
				"NULL","MODIF",
				"SELECT","ZIP",
				"OTHER","STAT",
				"ACTIVE","MAC",
				"MD5_DEVICEID",
			);
	}
	
	/*
	 * Return an array containing all implemented columns of the Table
	 */
	public function getColumnsSimple() {
		return $this->allcolumns;
	}
	
	/*
	 * Return an array containing all implemented columns of the Table
	 * 	sorted by properties
	 * 
	 */
	public function getColumns() {
		$columnsreturn = array();
		foreach($this->getColumnsVisible() as $visible){
			$columnsreturn['visible'][$visible]=$this->allcolumns [$visible];
		}
		foreach($this->getColumnsCantDel() as $cantdel){
			$columnsreturn['cantdel'][$cantdel]=$this->allcolumns [$cantdel];
		}
		foreach($this->getColumnsSpecial() as $special){
			$columnsreturn['special'][$special]=$this->allcolumns [$special];
		}
	}
	
	/*
	 * Get the displayed column with the key corresponding to $key
	 * 
	 */
	public function getColumn($key){
		if(array_key_exists($key, $this->allcolumns)){
			return $this->allcolumns[$key];
		}else{
			return false;
		}
	}
	
	/*
	 * Add a column, returning it afterwards
	 */
	public function addColumn($key,$label,$visible,$deletable,$sortable){
		$column = $this->getColumn($key);
		if (!$column){
			$this->allcolumns[$key]=new Column($key,$label,$visible,$deletable,$sortable);
			if ($visible){
				$this->columnsvisible[]=$key;
			}
			if ($cantdel){
				$this->columnscantdel[]=$key;
			}
			return $this->allcolumns[$key];
		}else{
			return $column;
		}		
	}
	
	/*
	 * Set visibility false for the column 
	 */
	public function hideColumn($key) {
		if ($column){
			if(in_array($key, $this->columnsvisible)){
				unset($this->columnsvisible[$key]);
			}
			$column->setVisible(false);
		}else{
			return false;
		}
	}
	
	/*
	 * Set visibility true for the column 
	 */
	public function showColumn($key) {
		$column = $this->getColumn($key);
		if ($column){
			if(!in_array($key, $this->columnsvisible)){
				$this->columnsvisible[]=$key;
			}
			$column->setVisible(true);
		}else{
			return false;
		}
		
	}
}

?>