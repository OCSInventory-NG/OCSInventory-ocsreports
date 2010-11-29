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
 * Show sd_networks data
 * 
 */

print_item_header($l->g(82));
	$table_name="sd_networks";
 
	$list_fields=array($l->g(53)=>'DESCRIPTION',
					   $l->g(95)=>'MACADDR',
					   'DEVICEMACADDR'=>'DEVICEMACADDR',
					   $l->g(271)=>'SLOT',
					   $l->g(1046)=>'STATUS',
					   $l->g(268)=>'SPEED',
					   $l->g(66) => 'TYPE',
					   'DEVICEADDRESS'=>'DEVICEADDRESS',
					   'DEVICENAME'=>'DEVICENAME',	
					   $l->g(280)=>'TYPEMIB',			   
					   $l->g(34)=>'IPADDR',
					   $l->g(208)=>'IPMASK',
					   $l->g(207)=>'IPGATEWAY',
					   $l->g(316)=>'IPSUBNET',
					   $l->g(281)=>'IPDHCP',
					   $l->g(278)=>'DRIVER',
					   'VIRTUALDEV'=>'VIRTUALDEV'
					   );
	//$list_fields['SUP']= 'ID';
	$sql=prepare_sql_tab($list_fields);
	//$list_fields["PERCENT_BAR"] = 'CAPACITY';
	$list_col_cant_del=array($l->g(53)=>$l->g(53));
	$default_fields= array($l->g(53)=>$l->g(53),$l->g(34)=>$l->g(34),$l->g(95)=>$l->g(95),$l->g(1046)=>$l->g(1046),$l->g(280)=>$l->g(280));
	$sql['SQL']  = $sql['SQL']." FROM %s WHERE (snmp_id=%s)";
	$sql['ARG'][]='snmp_networks';
	$sql['ARG'][]=$systemid;
	$tab_options['ARG_SQL']=$sql['ARG'];
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$form_name,80,$tab_options);


?>