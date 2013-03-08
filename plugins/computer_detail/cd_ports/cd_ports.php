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


	print_item_header($l->g(272));
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	$form_name="affich_ports";
	$table_name=$form_name;
	echo open_form($form_name);
	$list_fields=array($l->g(66) => 'TYPE',
					   $l->g(49) => 'NAME',
					   $l->g(84) => 'CAPTION',
					   $l->g(53) => 'DESCRIPTION');
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;
//	$tab_options['FILTRE']=array('NAME'=>$l->g(49),'MANUFACTURER'=>$l->g(64),'TYPE'=>$l->g(66));
	$queryDetails  = "SELECT * FROM ports WHERE (hardware_id=$systemid)";
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,80,$tab_options);
	echo close_form();
?>