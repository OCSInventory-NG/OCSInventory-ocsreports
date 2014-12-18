<?php

require_once('require/tables/Column.php');

class CheckboxColumn extends Column {
	public function __construct($idProperty = 'id') {
		// TODO translate
		parent::__construct('_checkbox', '<input type="checkbox" class="check-all" name="check-all"/>', array(
				'required' => true,
				'sortable' => false,
				'searchable' => false,
				'formatter' => function($record, $col) use ($idProperty) {
					// If record is an object, try to call $record->getId(), then $record->id
					if (is_object($record)) {
						$func = $this->camelize('get_'.$idProperty);
						if (is_callable(array($record, $func))) {
							$id = call_user_func(array($record, $func));
						} else {
							$id = $record->$idProperty;
						}
					} else {
						// Else record is an array, simply access the wanted property
						$id = $record[$idProperty];
					}
					
					$id = htmlspecialchars($id);
					
					return '<input type="checkbox" class="check-row" name="check['.$id.']"/>';
				}
		));
	}
}

?>