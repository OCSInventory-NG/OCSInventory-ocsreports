<?php
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Erwan GOALOU 2010 (erwan(at)ocsinventory-ng(pt)org)
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================

	print_item_header($l->g(92));
		if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	$form_name="affich_drives";
	$table_name=$form_name;
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	$list_fields=array($l->g(85) => 'LETTER',
					   $l->g(66) => 'TYPE',
					   $l->g(70) => 'VOLUMN',
					   $l->g(86) => 'FILESYSTEM',
					   $l->g(88)." (MB)"=>'FREE',
					   $l->g(87)." (MB)"=> 'TOTAL',
					   "PERCENT_BAR" => 'CAPACITY');
					   
	if($show_all_column)
		$list_col_cant_del=$list_fields;
	else
		$list_col_cant_del=array('PERCENT_BAR'=>'PERCENT_BAR',$l->g(85)=>$l->g(85));
		
	$default_fields= $list_fields;
	$tab_options['LBL']['PERCENT_BAR']=$l->g(83);
	$queryDetails  = "SELECT *, round(100-(FREE*100/TOTAL)) AS CAPACITY FROM drives WHERE (hardware_id=$systemid)";
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,80,$tab_options);
	echo "</form>";

?>