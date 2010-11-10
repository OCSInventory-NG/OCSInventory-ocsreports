<?php
require_once('require/function_snmp.php');

//delete snmp
if ($protectedPost['SUP_PROF'] != ''){	
	deleteDid_snmp($protectedPost['SUP_PROF']);
	$tab_options['CACHE']='RESET';
}

if (isset($protectedPost['del_check']) and $protectedPost['del_check'] != ''){
	deleteDid_snmp($protectedPost['del_check']);
	$tab_options['CACHE']='RESET';
}


if (!isset($protectedPost['tri2']) or $protectedPost['tri2'] == ""){
	$protectedPost['tri2']="h.lastdate";
	$protectedPost['sens']="DESC";
}
	$form_name="show_all_snmp";
	$table_name=$form_name;
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	$list_fields=array('TAG'=>'TAG',
					   'NAME_SNMP'=>'NAME',
					   $l->g(352)=>'UPTIME',
					   $l->g(95)=>'MACADDR',
					   $l->g(34)=>'IPADDR',
					   $l->g(1227)=>'CONTACT',
					   $l->g(295) =>'LOCATION',
					   $l->g(33) =>'DOMAIN',
					   $l->g(66)=>'TYPE',
					   $l->g(1228)=>'SNMPDEVICEID'
					);			

	$tab_options['FILTRE']=array_flip($list_fields);
	$tab_options['FILTRE']['NAME']=$l->g(49);
	asort($tab_options['FILTRE']); 
	$list_fields['SUP']='ID';
	$list_fields['CHECK']='ID';
	
	$list_col_cant_del=array('SUP'=>'SUP','CHECK'=>'CHECK');
	$default_fields= array('TAG'=>'TAG','NAME_SNMP'=>'NAME_SNMP',$l->g(34)=>$l->g(34),$l->g(95)=>$l->g(95));
	$sql=prepare_sql_tab($list_fields,$list_col_cant_del);
	$tab_options['ARG_SQL']=$sql['ARG'];
	$queryDetails  = $sql['SQL'].",ID from snmp s 
							left join snmp_accountinfo s_a on s.id=s_a.snmp_id ";
	$tab_options['LBL_POPUP']['SUP']='NAME';
	$tab_options['LBL']['SUP']=$l->g(122);
	
	$tab_options['LIEN_LBL']['NAME_SNMP']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_snmp_detail'].'&head=1&id=';
	$tab_options['LIEN_CHAMP']['NAME_SNMP']='ID';
	$tab_options['LBL']['NAME_SNMP']=$l->g(49);
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,95,$tab_options);
	$img['image/sup_search.png']=$l->g(162);
	del_selection($form_name);
	echo "</form>";







?>