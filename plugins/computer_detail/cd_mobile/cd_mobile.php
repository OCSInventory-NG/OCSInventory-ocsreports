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

	print_item_header($l->g(908));	
	
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	$form_name="affich_mobile";
	$table_name=$form_name;
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	$list_fields=array('JAVANAME' => 'JAVANAME',
					   'JAVAPATHLEVEL' => 'JAVAPATHLEVEL',
					   'JAVACOUNTRY' => 'JAVACOUNTRY',
					   'JAVACLASSPATH' => 'JAVACLASSPATH',
					   'JAVAHOME' => 'JAVAHOME');
					   
	$list_col_cant_del=$list_fields;		
	$default_fields= $list_fields;
	$queryDetails  = "SELECT * FROM javainfo WHERE (hardware_id=$systemid)";
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,80,$tab_options);
	$table_name="affich_mobile2";
	$list_fields= array('ID'=>'ID',
						'JOURNALLOG'=>'JOURNALLOG',
						'LISTENERNAME'=>'LISTENERNAME',
						'DATE'=>'DATE',
						'STATUS'=>'STATUS',
						'ERRORCODE'=>'ERRORCODE');
	$list_col_cant_del= $list_fields;
	$default_fields= $list_fields;

	$queryDetails  = "SELECT * FROM journallog WHERE (hardware_id=$systemid)";
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,80,$tab_options);
	echo "</form>";

?>