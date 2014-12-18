<?php

class TableRenderer {
	private static $jsIncluded = false;
	
	public function show($table, $records, $options = array()) {
		global $l, $protectedPost;
		
		$options = array_merge_recursive(array(
				'paginate' => array(
						'offset' => 0,
						'limit' => -1
				),
				'visible' => array()
		), $options);
		
		$this->includeJS();
		$this->callJS($table, $options);
	}
	
	private function includeJS() {
		global $l;
		
		if (!self::$jsIncluded) {
			$lang = array(
				"sEmptyTable" =>		$l->g(1334),
				"sInfo" =>				$l->g(1335),
				"sInfoEmpty" =>			$l->g(1336),
				"sInfoFiltered" =>		$l->g(1337),
				"sInfoPostFix" =>		"",
				"sInfoThousands" =>		$l->g(1350),
				"decimal" =>			$l->g(1351),
				"sLengthMenu" =>		$l->g(1338),
				"sLoadingRecords" =>	$l->g(1339),
				"sProcessing" =>		$l->g(1340),
				"sSearch" =>			$l->g(1341),
				"sZeroRecords" =>		$l->g(1342),
				"oPaginate" => array(
					"sFirst" =>			$l->g(1343),
					"sLast" =>			$l->g(1344),
					"sNext" =>			$l->g(1345),
					"sPrevious" =>		$l->g(1346),
				),
				"oAria" => array(
					"sSortAscending" =>		": ".$l->g(1347),
					"sSortDescending" =>	": ".$l->g(1348),
				)
			);

			echo '<script>';
			require 'require/tables/tables.js';
			echo 'tables.language = '.json_encode($lang).';';
			echo '</script>';
			
			self::$jsIncluded = true;
		}
	}
	
	private function callJS($table, $options) {
		global $protectedPost;
		
		$tableName = json_encode(htmlspecialchars($table->getName()));
		$csrfNumber = json_encode(htmlspecialchars($_SESSION['OCS']['CSRFNUMBER']));
		
		$url = isset($_SERVER['QUERY_STRING']) ? "ajax.php?".$_SERVER['QUERY_STRING'] : "";
		$url = json_encode($url.'&no_header=true&no_footer=true');
		
		$postData = json_encode($protectedPost);
		$columns = json_encode($this->showColumns($table, $options));
		
		require 'require/tables/table.html.php';
	}
	
	private function showColumns($table, $options) {
		$columns = array();
		
		foreach ($table->getColumns() as $name => $col) {
			$columns []= $this->showColumn($name, $col, $options);
		}
		
		return $columns;
	}
	
	public function showColumn($name, $col, $options) {
		$visible = $col->isRequired() || in_array($name, $options['visible']);
		$sortable = $col->isSortable();
		$searchable = $col->isSearchable();
		
		return array(
			'data' => $name,
			'class' => 'column-'.$name,
			'name' => $name,
			'defaultContent' => ' ',
			'orderable' => $sortable,
			'searchable' => $searchable,
			'visible' => $visible
		);
	}
}

?>