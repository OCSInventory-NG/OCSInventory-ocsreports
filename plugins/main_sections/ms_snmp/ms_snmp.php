<?php
require_once('require/function_snmp.php');

//delete snmp
if ($protectedPost['SUP_PROF'] != ''){	
	deleteDid_snmp($protectedPost['SUP_PROF']);
	$tab_options['CACHE']='RESET';
}

if (!isset($protectedPost['tri2']) or $protectedPost['tri2'] == ""){
	$protectedPost['tri2']="h.lastdate";
	$protectedPost['sens']="DESC";
}
	$form_name="show_all_snmp";
	$table_name=$form_name;
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	$list_fields=array('ID'=>'ID',
					   'NAME_SNMP'=>'NAME',
					   $l->g(352)=>'UPTIME',
					   $l->g(95)=>'MACADDR',
					   $l->g(34)=>'IPADDR',
					   'CONTACT'=>'CONTACT',
					   $l->g(295) =>'LOCATION',
					   $l->g(33) =>'DOMAIN',
					   $l->g(66)=>'TYPE',
					   'SNMPDEVICEID'=>'SNMPDEVICEID'
					);			

	$tab_options['FILTRE']=array_flip($list_fields);
	$tab_options['FILTRE']['NAME']=$l->g(49);
	asort($tab_options['FILTRE']); 
	$list_fields['SUP']='ID';
	
	$list_col_cant_del=array('SUP'=>'SUP');
	$default_fields= $list_fields;
	$sql=prepare_sql_tab($list_fields,array('SUP'));
	$tab_options['ARG_SQL']=$sql['ARG'];
	$queryDetails  = $sql['SQL']." from snmp ";
	//$queryDetails  .=" limit 200";
	$tab_options['LBL_POPUP']['SUP']='NAME';
	$tab_options['LBL']['SUP']=$l->g(122);
	
	$tab_options['LIEN_LBL']['NAME_SNMP']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_snmp_detail'].'&head=1&id=';
	$tab_options['LIEN_CHAMP']['NAME_SNMP']='ID';
	$tab_options['LBL']['NAME_SNMP']=$l->g(49);
	//$tab_options['NO_LIEN_CHAMP']['NAME']=array(0);
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,95,$tab_options);
	echo "</form>";







?>