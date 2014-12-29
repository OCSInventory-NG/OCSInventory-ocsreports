<?php

class AjaxTableRenderer {
	public function show($table, $records) {
		global $l, $protectedPost;
		
		$data = array();

		foreach ($records as $record) {
			$recordData = array();
			foreach ($table->getColumns() as $name => $col) {
				$recordData[$name] = $col->format($record);
			}
			$data []= $recordData;
		}
		
		echo json_encode(array(
			'customized' => false,
			'draw' => $_POST['draw'],
			'data' => $data,
			'recordsFiltered' => count($records),
			'recordsTotal' => count($records)
		));
	}
}

?>