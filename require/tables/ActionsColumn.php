<?php

require_once('require/tables/Column.php');

class ActionsColumn extends Column {
	public function __construct($actions, $idProperty = 'id') {
		// TODO translate
		parent::__construct('_actions', 'Actions', array(
				'required' => true,
				'sortable' => false,
				'searchable' => false,
				'formatter' => function($record, $col) use ($actions, $idProperty) {
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
					
					$actionHtml = '';
					foreach ($actions as $name => $class) {
						$actionHtml .= '<a href="#" class="row-action" data-action="'.$name.'" data-id="'.$id.'">'
								.'<span class="'.$class.'"></span>'
								.'</a>';
					}
					
					return $actionHtml;
				}
		));
	}
}

?>