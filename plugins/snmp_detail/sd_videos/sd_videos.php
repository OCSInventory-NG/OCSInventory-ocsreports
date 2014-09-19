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
	 * Show sd_videos data
	 * 
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
	print_item_header($l->g(61));
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	$table_name="sd_videos";
	$list_fields=array($l->g(49)=>'NAME');
	//$list_fields['SUP']= 'ID';
	$sql=prepare_sql_tab($list_fields);
	//$list_fields["PERCENT_BAR"] = 'CAPACITY';
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;
	$sql['SQL']  = $sql['SQL']." FROM %s WHERE (snmp_id=%s)";
	$sql['ARG'][]='snmp_videos';
	$sql['ARG'][]=$systemid;
	$tab_options['ARG_SQL']=$sql['ARG'];
	ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	if ($ajax){
		ob_end_clean();
		tab_req($list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$tab_options);
		ob_start();	
	}
?>