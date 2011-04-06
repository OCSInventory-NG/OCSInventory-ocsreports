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

require_once('require/function_computers.php');


if (isset($protectedGet['filtre'])){
	$protectedPost['FILTRE']=$protectedGet['filtre'];
	$protectedPost['FILTRE_VALUE']=$protectedGet['value'];	
}

//cas d'une suppression de machine
if ($protectedPost['SUP_PROF'] != ''){	
	deleteDid($protectedPost['SUP_PROF']);
	$tab_options['CACHE']='RESET';
}

if (!isset($protectedPost['tri2']) or $protectedPost['tri2'] == ""){
	$protectedPost['tri2']="h.lastdate";
	$protectedPost['sens']="DESC";
}
	$form_name="show_all";
	$table_name="list_show_all";
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	//BEGIN SHOW ACCOUNTINFO
	require_once('require/function_admininfo.php');
	$accountinfo_value=interprete_accountinfo($list_fields,$tab_options);
	if (array($accountinfo_value['TAB_OPTIONS']))
		$tab_options=$accountinfo_value['TAB_OPTIONS'];
	if (array($accountinfo_value['DEFAULT_VALUE']))
		$default_fields=$accountinfo_value['DEFAULT_VALUE'];
	$list_fields=$accountinfo_value['LIST_FIELDS'];
	//END SHOW ACCOUNTINFO
	$list_fields2 = array ( $l->g(46) => "h.lastdate", 
						   'NAME'=>'h.name',
						   $l->g(949) => "h.ID",
						   $l->g(24) => "h.userid",
						   $l->g(25) => "h.osname",
						   $l->g(568) => "h.memory",
						   $l->g(569) => "h.processors",
						   $l->g(33) => "h.workgroup",
						   $l->g(275) => "h.osversion",
						   $l->g(286) => "h.oscomments",
						   $l->g(350) => "h.processort",
						   $l->g(351) => "h.processorn",
						   $l->g(50) => "h.swap",
						   $l->g(352) => "lastcome",
						   $l->g(353) => "h.quality",
						   $l->g(354) => "h.fidelity",
						   $l->g(53) => "h.description",
						   $l->g(355) => "h.wincompany",
						   $l->g(356) => "h.winowner",
						   $l->g(357) => "h.useragent",
						   $l->g(64) => "e.smanufacturer",
						   $l->g(284) => "e.bmanufacturer",
						   $l->g(36) => "e.ssn",
						   $l->g(65) => "e.smodel",
						   $l->g(209) => "e.bversion",
						   $l->g(34) => "h.ipaddr",
						   $l->g(557) => "h.userdomain");
						   
	$list_fields=array_merge ($list_fields,$list_fields2);
	//asort($list_fields); 
	$tab_options['FILTRE']=array_flip($list_fields);
	$tab_options['FILTRE']['h.name']=$l->g(23);
	asort($tab_options['FILTRE']); 
	$list_fields['SUP']='ID';
	
	$list_col_cant_del=array('SUP'=>'SUP');
	$default_fields2= array($_SESSION['OCS']['TAG_LBL']['TAG']=>$_SESSION['OCS']['TAG_LBL'],$l->g(46)=>$l->g(46),'NAME'=>'NAME',$l->g(23)=>$l->g(23),
							$l->g(24)=>$l->g(24),$l->g(25)=>$l->g(25),$l->g(568)=>$l->g(568),
							$l->g(569)=>$l->g(569));
	$default_fields=array_merge($default_fields,$default_fields2);
	$sql=prepare_sql_tab($list_fields,array('SUP'));
	$tab_options['ARG_SQL']=$sql['ARG'];
	$queryDetails  = $sql['SQL']." from hardware h 
					LEFT JOIN accountinfo a ON a.hardware_id=h.id 
					LEFT JOIN bios e ON e.hardware_id=h.id where deviceid<>'_SYSTEMGROUP_' AND deviceid<>'_DOWNLOADGROUP_' ";
	if (isset($_SESSION['OCS']["mesmachines"]) and $_SESSION['OCS']["mesmachines"] != '')
		$queryDetails  .= "AND ".$_SESSION['OCS']["mesmachines"];
	//$queryDetails  .=" limit 200";
	$tab_options['LBL_POPUP']['SUP']='name';
	$tab_options['LBL']['SUP']=$l->g(122);

	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,95,$tab_options);
	echo "</form>";



/*
	$form_name="show_all";
	$table_name="list_show_all";
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	$list_fields=array('HARDWARE','BIOS','ACCOUNTINFO');
	$result=lbl_column($list_fields);
	$list_fields=$result['FIELDS'];
	$default_fields=$result['DEFAULT_FIELDS'];
	$tab_options['FILTRE']=array_flip($list_fields);
	asort($tab_options['FILTRE']); 
	$list_fields['SUP']='h.ID';	
	$list_col_cant_del=array('SUP'=>'SUP');
	$sql=prepare_sql_tab($list_fields,array('SUP'));
	$tab_options['ARG_SQL']=$sql['ARG'];
	$queryDetails  = $sql['SQL']." from hardware h 
					LEFT JOIN accountinfo a ON a.hardware_id=h.id 
					LEFT JOIN bios b ON b.hardware_id=h.id where deviceid<>'_SYSTEMGROUP_' AND deviceid<>'_DOWNLOADGROUP_' ";
	if (isset($_SESSION['OCS']["mesmachines"]) and $_SESSION['OCS']["mesmachines"] != '')
		$queryDetails  .= "AND ".$_SESSION['OCS']["mesmachines"];

	$tab_options['LBL_POPUP']['SUP']='h.NAME';
	$tab_options['LBL']['SUP']=$l->g(122);
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,95,$tab_options);
	echo "</form>";
*/
?>