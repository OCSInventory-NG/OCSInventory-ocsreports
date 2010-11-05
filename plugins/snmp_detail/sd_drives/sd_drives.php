<?php
/*
 * 
 * Show sd_drives data
 * 
 */

print_item_header($l->g(1221));
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	$table_name="sd_drives";
	$list_fields=array($l->g(85)=>'LETTER', 
					   $l->g(66) => 'TYPE',
					   $l->g(86)=>'FILESYSTEM',
					   $l->g(87)=>'TOTAL',
					   $l->g(45)=>'FREE',     
					   'Numfiles'=>'NUMFILES',     
                       $l->g(1230)=>'VOLUMN');
	//$list_fields['SUP']= 'ID';
	$sql=prepare_sql_tab($list_fields);
	$list_fields["PERCENT_BAR"] = 'CAPACITY';
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;
	$sql['SQL']  = $sql['SQL']." , round(100-(FREE*100/TOTAL)) AS CAPACITY FROM %s WHERE (snmp_id=%s)";
	$sql['ARG'][]='snmp_drives';
	$sql['ARG'][]=$systemid;
	$tab_options['ARG_SQL']=$sql['ARG'];
	$tab_options['LBL']['PERCENT_BAR']=$l->g(1125);
	
	$tab_options['REPLACE_WITH_LIMIT']['DOWN'][$l->g(1104)]=0;
	$tab_options['REPLACE_WITH_LIMIT']['DOWNVALUE'][$l->g(1104)]=$msq_tab_error;
	$tab_options['REPLACE_WITH_LIMIT']['DOWN'][$l->g(1225)]=0;
	$tab_options['REPLACE_WITH_LIMIT']['DOWNVALUE'][$l->g(1225)]=$msq_tab_error;
	$tab_options['REPLACE_WITH_LIMIT']['DOWN']['PERCENT_BAR']=0;
	$tab_options['REPLACE_WITH_LIMIT']['DOWNVALUE']['PERCENT_BAR']=$msq_tab_error;
	$tab_options['REPLACE_WITH_LIMIT']['UP']['PERCENT_BAR']=100;
	$tab_options['REPLACE_WITH_LIMIT']['UPVALUE']['PERCENT_BAR']=$msq_tab_error;
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$form_name,80,$tab_options);


?>