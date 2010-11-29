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
 * Show sd_cards data
 * 
 * 
 */

print_item_header($l->g(1219));
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	$table_name="sd_cards";
	$list_fields=array($l->g(64)=>'MANUFACTURER', 
					   $l->g(66) => 'TYPE',
					   $l->g(36)=>'SERIALNUMBER',
					   $l->g(53)=>'DESCRIPTION',
					   $l->g(277)=>'REVISION',     
					   $l->g(1229)=>'FIRMWARE',     
                       $l->g(20)=>'SOFTWARE');
	//$list_fields['SUP']= 'ID';
	$sql=prepare_sql_tab($list_fields);
	//$list_fields["PERCENT_BAR"] = 'CAPACITY';
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;
	$sql['SQL']  = $sql['SQL']." FROM %s WHERE (snmp_id=%s)";
	$sql['ARG'][]='snmp_cards';
	$sql['ARG'][]=$systemid;
	$tab_options['ARG_SQL']=$sql['ARG'];
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$form_name,80,$tab_options);


?>