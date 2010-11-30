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

	print_item_header($l->g(270));
	if (!isset($protectedPost['SHOW']))
	$protectedPost['SHOW'] = 'NOSHOW';
	$form_name="affich_modems";
	$table_name=$form_name;
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	$list_fields=array($l->g(49) => 'NAME',
					   $l->g(65) => 'MODEL',
					   $l->g(53) => 'DESCRIPTION',
					   $l->g(66) => 'TYPE');
	$list_col_cant_del=array($l->g(49)=>$l->g(49),$l->g(66)=>$l->g(66));
	$default_fields= $list_fields;
	$queryDetails  = "SELECT * FROM modems WHERE (hardware_id=$systemid)";
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,80,$tab_options);
	echo "</form>";



?>