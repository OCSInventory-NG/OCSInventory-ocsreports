<?php

require_once('require/tables/Column.php');

class LinkColumn extends Column {
	private $url;
	private $idProperty;
	
	public function __construct($name, $label, $url, $options = array()) {
		$options['formatter'] = array($this, 'formatLink');
		
		$this->url = $url;
		$this->idProperty = $options['idProperty'] ?: 'id';
		
		parent::__construct($name, $label, $options);
	}
	
	public function formatLink($record) {
		// If record is an object, try to call $record->getId(), then $record->id
		if (is_object($record)) {
			$func = $this->camelize('get_'.$this->idProperty);
			if (is_callable(array($record, $func))) {
				$id = call_user_func(array($record, $func));
			} else {
				$id = $record->{$this->idProperty};
			}
		} else {
			// Else record is an array, simply access the wanted property
			$id = $record[$this->idProperty];
		}
		
		// If record is an object, try to call $record->getXxx(), then $record->xxx
		if (is_object($record)) {
			$func = $this->camelize('get_'.$this->getName());
			if (is_callable(array($record, $func))) {
				$value = call_user_func(array($record, $func));
			} else {
				$value = $record->{$col->getName()};
			}
		} else {
			// Else record is an array, simply access the wanted property
			$value = $record[$col->getName()];
		}

		$id = htmlspecialchars($id);
		$value = htmlspecialchars($value);
		
		return '<a href="'.$this->url.$id.'">'.$value.'</a>';
	}
}

?>