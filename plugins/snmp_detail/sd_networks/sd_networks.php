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
	if ((array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')){
		ob_end_clean();
		parse_str($protectedPost['ocs']['0'], $params);
		$protectedPost+=$params;
		ob_start();
		$ajax = true;
	}
	else{
		$ajax=false;
	}
	print_item_header($l->g(82));
	$table_name="sd_networks";
	$tab_options=$protectedPost;
	$tab_options['form_name']=$form_name;
	$tab_options['table_name']=$table_name;
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
					   'VIRTUALDEV'=>'VIRTUALDEV',
					   'DEVICEPORT'=>'DEVICEPORT'
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
	
	//link if macadd match with computer
	/*$tab_options['LIEN_LBL'][$l->g(95)][]="index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&systemid=";
	$tab_options['LIEN_CHAMP'][$l->g(95)][]='ID';
	$tab_options['NO_LIEN_CHAMP']['SQL'][$l->g(95)][]="select ID from networks where macaddr='%s'";
	$tab_options['NO_LIEN_CHAMP']['ARG'][$l->g(95)][]='MACADDR';
//	$tab_options['NO_LIEN_CHAMP']['SQL'][$key]
	//link if macadd match with snmp device
	$tab_options['LIEN_LBL'][$l->g(95)][]="index.php?".PAG_INDEX."=".$pages_refs['ms_snmp_detail']."&head=1&id=";
	$tab_options['LIEN_CHAMP'][$l->g(95)][]='ID';
	$tab_options['NO_LIEN_CHAMP']['SQL'][$l->g(95)][]="select ID from snmp where macaddr='%s'";
	$tab_options['NO_LIEN_CHAMP']['ARG'][$l->g(95)][]='MACADDR';*/
	ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	if ($ajax){
		ob_end_clean();
		tab_req($list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$tab_options);
		ob_start();
	}

?>