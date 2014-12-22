<?php

require_once('require/tables/Column.php');

class ActionsColumn extends Column {
	public function __construct($actions, $idProperty = 'id') {
		global $l;
		
		parent::__construct('_actions', $l->g(1381), array(
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
					foreach ($actions as $url => $class) {
						$actionHtml .= '<a href="'.$url.$id.'" class="row-action">'
								.'<span class="'.$class.'"></span>'
								.'</a>';
					}
					
					return $actionHtml;
				}
		));
	}
}

?>