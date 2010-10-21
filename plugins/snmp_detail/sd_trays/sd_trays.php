<?php
print_item_header('sd_trays');
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	$form_name="sd_trays";
	$table_name=$form_name;
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	$list_fields=array('SNMP_ID' => 'SNMP_ID',
					   'TRAYS_NAME' => 'NAME',
					   'DESCRIPTION'=>'DESCRIPTION',
					   'LEVEL'=>'LEVEL',
					   'MAXCAPACITY'=>'MAXCAPACITY');
	//$list_fields['SUP']= 'ID';
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;
	$sql=prepare_sql_tab($list_fields);
	$sql['SQL']  = $sql['SQL']." FROM %s WHERE (snmp_id=%s)";
	$sql['ARG'][]='snmp_trays';
	$sql['ARG'][]=$systemid;
	$tab_options['ARG_SQL']=$sql['ARG'];
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$form_name,80,$tab_options);
	echo "</form>";


?>