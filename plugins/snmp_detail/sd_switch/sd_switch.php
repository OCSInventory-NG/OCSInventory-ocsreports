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
	 * Show sd_switch data
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
	
	print_item_header($l->g(1218));
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	$table_name="sd_switch";
	$tab_options=$protectedPost;
	$tab_options['form_name']=$form_name;
	$tab_options['table_name']=$table_name;
		
		/*MANUFACTURER 
	REFERENCE    
	TYPE         
	SOFTVERSION  
	FIRMVERSION  
	SERIALNUMBER 
	REVISION     
	DESCRIPTION  
	*/
	$list_fields=array($l->g(64)=>'MANUFACTURER',
					   $l->g(1235)=>'REFERENCE',
					   $l->g(66)=>'TYPE',
					   $l->g(1236)=>'SOFTVERSION',
					   $l->g(1237)=>'FIRMVERSION',
					   $l->g(36)=>'SERIALNUMBER',
					   $l->g(18)=>'REVISION',
					   $l->g(53)=>'DESCRIPTION'
					   );
	//$list_fields['SUP']= 'ID';
	$sql=prepare_sql_tab($list_fields);
	//$list_fields["PERCENT_BAR"] = 'CAPACITY';
	$list_col_cant_del=array($l->g(64)=>$l->g(64),$l->g(53)=>$l->g(53));
	$default_fields= $list_fields;
	$sql['SQL']  = $sql['SQL']." FROM %s WHERE (snmp_id=%s)";
	$sql['ARG'][]='snmp_switchs';
	$sql['ARG'][]=$systemid;
	$tab_options['ARG_SQL']=$sql['ARG'];
	ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	if ($ajax){
		ob_end_clean();
		tab_req($list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$tab_options);
		ob_start();	
	}
?>