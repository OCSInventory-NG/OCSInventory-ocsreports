<?php
/*
 * 
 * Show sd_modems data
 * 
 * 
 */

print_item_header($l->g(270));
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	$table_name="sd_modems";
	$list_fields=array($l->g(49) => 'NAME');
	//$list_fields['SUP']= 'ID';
	$sql=prepare_sql_tab($list_fields);
	//$list_fields["PERCENT_BAR"] = 'CAPACITY';
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;
	$sql['SQL']  = $sql['SQL']." FROM %s WHERE (snmp_id=%s)";
	$sql['ARG'][]='snmp_modems';
	$sql['ARG'][]=$systemid;
	$tab_options['ARG_SQL']=$sql['ARG'];
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$form_name,80,$tab_options);


?>