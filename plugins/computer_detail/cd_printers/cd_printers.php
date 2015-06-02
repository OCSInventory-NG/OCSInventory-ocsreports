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

	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	print_item_header($l->g(79));
	$form_name="affich_ports";
	$table_name=$form_name;
	echo open_form($form_name);
	$list_fields=array($l->g(49) => 'NAME',
					   $l->g(278) => 'DRIVER',
					   $l->g(279) => 'PORT',
					   $l->g(53) =>'DESCRIPTION');
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;
	$tab_options['FILTRE']=array('NAME'=>$l->g(49),'DRIVER'=>$l->g(278),'PORT'=>$l->g(279),'DESCRIPTION'=>$l->g(53));
	$queryDetails  = "SELECT * FROM printers WHERE (hardware_id=$systemid)";
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,80,$tab_options);
	echo close_form();
?>