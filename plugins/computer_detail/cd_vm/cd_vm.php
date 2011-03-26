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
	$list_fields=array($l->g(49).' VM' => 'vm.NAME',
					   $l->g(1046).' VM' => 'vm.STATUS',
					   $l->g(25).' VM' => 'vm.SUBSYSTEM',
					   $l->g(66).' VM'  =>'vm.VMTYPE',
					   'UUID'=>'vm.UUID',
					   $l->g(54).' VM' =>'vm.VCPU',
					   $l->g(26).' VM' =>'vm.MEMORY',
					   'NAME'=>'h.name',
					   $l->g(25) => "h.osname",
					   );
	$list_col_cant_del=$list_fields;		
	$default_fields= $list_fields;
	$sql=prepare_sql_tab($list_fields);
	$sql['SQL']  .= ",h.ID FROM virtualmachines vm left join hardware h on h.uuid=vm.uuid  WHERE (hardware_id=%s)";
	array_push($sql['ARG'],$systemid);
	$tab_options['ARG_SQL']=$sql['ARG'];
	$tab_options['ARG_SQL_COUNT']=$systemid;
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$form_name,80,$tab_options);
	echo "</form>";


?>