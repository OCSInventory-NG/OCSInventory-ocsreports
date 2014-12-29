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
					foreach ($actions as $action) {
						$actionHtml .= '<a href="'.sprintf($action->getUrl(), $id).'" class="row-action"';

						if ($action->getMethod() != 'GET') {
							$actionHtml .= ' data-method="'.$action->getMethod().'"';
						}
						
						if ($action->isAjax()) {
							$actionHtml .= ' data-ajax="true"';
						}
						
						if ($action->getConfirm()) {
							$actionHtml .= ' data-confirm="'.htmlspecialchars($action->getConfirm()).'"';
						}
						
						$actionHtml .= '>';

						if ($action->getIcon()) {
							$actionHtml .= '<span class="glyphicon glyphicon-'.$action->getIcon().'"></span>';
						}
						
						if ($action->getLabel()) {
							if ($action->getIcon()) $actionHtml .= ' ';
							$actionHtml .= '<span class="action-label">'.htmlspecialchars($action->getLabel()).'</span>';
						}
						
						$actionHtml .= '</a>';
					}
					
					return $actionHtml;
				}
		));
	}
}

?>