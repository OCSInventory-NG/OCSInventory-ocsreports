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

	print_item_header($l->g(1266));
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	$form_name="affich_vm";
	$table_name=$form_name;
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";	
	$list_fields=array($l->g(49) => 'NAME',
					   $l->g(1046) => 'STATUS',
					   $l->g(25) => 'SUBSYSTEM',
					   $l->g(66) =>'VMTYPE',
					   'UUID'=>'UUID',
					   $l->g(54) =>'VCPU',
					   $l->g(26) =>'MEMORY'
					   );
	$list_col_cant_del=$list_fields;		
	$default_fields= $list_fields;
	$queryDetails  = "SELECT * FROM virtualmachines WHERE (hardware_id=$systemid)";
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,80,$tab_options);
	echo "</form>";


?>