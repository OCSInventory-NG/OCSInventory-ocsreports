<?php
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
	$list_fields = array ( $_SESSION['OCS']['TAG_LBL']['TAG']   => "a.tag", 
						   $l->g(46) => "h.lastdate", 
						   'NAME'=>'h.name',
						   $l->g(949) => "ID",
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
	$tab_options['FILTRE']=array_flip($list_fields);
	$tab_options['FILTRE']['h.name']=$l->g(23);
	asort($tab_options['FILTRE']); 
	
	//BEGIN SHOW ACCOUNTINFO
	require_once('require/function_admininfo.php');
	$info_tag=find_info_accountinfo();
	foreach ($info_tag as $key=>$value){
		$info_value_tag= accountinfo_tab($value['id']);		
		if (is_array($info_value_tag)){
			$tab_options['REPLACE_VALUE'][$value['comment']]=$info_value_tag;
		}		
		if ($value['name'] != 'TAG' and $info_value_tag)
		$list_fields[$value['comment']]='a.fields_'.$value['id'];		
	}
	//END SHOW ACCOUNTINFO
	$list_fields['SUP']='h.id';
	
	$list_col_cant_del=array('SUP'=>'SUP');
	$default_fields= array($_SESSION['OCS']['TAG_LBL']['TAG']=>$_SESSION['OCS']['TAG_LBL'],$l->g(46)=>$l->g(46),'NAME'=>'NAME',$l->g(23)=>$l->g(23),
							$l->g(24)=>$l->g(24),$l->g(25)=>$l->g(25),$l->g(568)=>$l->g(568),
							$l->g(569)=>$l->g(569));
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

?>