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
	/*
	 * 
	 * Show sd_drives data
	 * 
	 */
	if(AJAX){
		ob_end_clean();
		parse_str($protectedPost['ocs']['0'], $params);
		$protectedPost+=$params;
		ob_start();
		$ajax = true;
	}
	else{
		$ajax=false;
	}
	print_item_header($l->g(1221));
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	$table_name="sd_drives";
	$tab_options=$protectedPost;
	$tab_options['form_name']=$form_name;
	$tab_options['table_name']=$table_name;
	$list_fields=array($l->g(85)=>'LETTER', 
					   $l->g(66) => 'TYPE',
					   $l->g(86)=>'FILESYSTEM',
					   $l->g(87)=>'TOTAL',
					   $l->g(45)=>'FREE',
					   $l->g(137)=>'NUMFILES',
                       $l->g(1230)=>'VOLUMN');
	//$list_fields['SUP']= 'ID';
	$sql=prepare_sql_tab($list_fields);
	$list_fields["PERCENT_BAR"] = 'CAPACITY';
	$tab_options["replace_query_arg"]['CAPACITY']=" round(100-(FREE*100/TOTAL)) ";
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
	ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	if ($ajax){
		ob_end_clean();
		tab_req($list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$tab_options);
		ob_start();
	}

?>