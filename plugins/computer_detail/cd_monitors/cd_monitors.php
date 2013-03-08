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

	print_item_header($l->g(97));
	if (!isset($protectedPost['SHOW']))
	$protectedPost['SHOW'] = 'NOSHOW';
	$form_name="affich_monitors";
	$table_name=$form_name;
	echo open_form($form_name);
	$list_fields=array($l->g(64) => 'MANUFACTURER',
					   $l->g(80) => 'CAPTION',
					   $l->g(360) => 'DESCRIPTION',
					   $l->g(66) => 'TYPE',
					   $l->g(36)=> 'SERIAL');
	if($show_all_column)
		$list_col_cant_del=$list_fields;
	else
		$list_col_cant_del=array($l->g(64)=>$l->g(64),$l->g(36)=>$l->g(36));
		
	$default_fields= $list_fields;
	$queryDetails  = "SELECT * FROM monitors WHERE (hardware_id=$systemid)";
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,80,$tab_options);
	echo close_form();
?>