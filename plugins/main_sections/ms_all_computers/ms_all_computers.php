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
//show mac address on the tab
$show_mac_addr=false;

$form_name="show_all";
$table_name="list_show_all";

if (isset($protectedGet['filtre'])){
	$protectedPost['FILTRE']=$protectedGet['filtre'];
	$protectedPost['FILTRE_VALUE']=$protectedGet['value'];	
}

//del the selection
if ($protectedPost['DEL_ALL'] != ''){
	foreach ($protectedPost as $key=>$value){
		$checkbox=explode('check',$key);
		if(isset($checkbox[1])){
			deleteDid($checkbox[1]);			
		}
	}
	$tab_options['CACHE']='RESET';
}

//delete one computer
if ($protectedPost['SUP_PROF'] != ''){	
	deleteDid($protectedPost['SUP_PROF']);
	$tab_options['CACHE']='RESET';
}

if (!isset($protectedPost['tri_'.$table_name]) or $protectedPost['tri_'.$table_name] == ""){
	$protectedPost['tri_'.$table_name]="h.lastdate";
	$protectedPost['sens_'.$table_name]="DESC";
}

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
if ($show_mac_addr)
	$list_fields2[$l->g(95)]="n.macaddr";
					   
$list_fields=array_merge ($list_fields,$list_fields2);
//asort($list_fields); 
$tab_options['FILTRE']=array_flip($list_fields);
$tab_options['FILTRE']['h.name']=$l->g(23);
asort($tab_options['FILTRE']); 
if ($_SESSION['OCS']['CONFIGURATION']['DELETE_COMPUTERS'] == "YES"){
	$list_fields['CHECK']='h.ID';
		$list_fields['SUP']='h.ID';
}
	
$list_col_cant_del=array('SUP'=>'SUP','NAME'=>'NAME','CHECK'=>'CHECK');
$default_fields2= array($_SESSION['OCS']['TAG_LBL']['TAG']=>$_SESSION['OCS']['TAG_LBL'],$l->g(46)=>$l->g(46),'NAME'=>'NAME',$l->g(23)=>$l->g(23),
						$l->g(24)=>$l->g(24),$l->g(25)=>$l->g(25),$l->g(568)=>$l->g(568),
						$l->g(569)=>$l->g(569));
$default_fields=array_merge($default_fields,$default_fields2);
$sql=prepare_sql_tab($list_fields,array('SUP','CHECK'));
$tab_options['ARG_SQL']=$sql['ARG'];
$queryDetails  = $sql['SQL']." from hardware h 
				LEFT JOIN accountinfo a ON a.hardware_id=h.id  ";
if ($show_mac_addr)
	$queryDetails  .= "	LEFT JOIN networks n ON n.hardware_id=h.id ";
	
$queryDetails  .= "LEFT JOIN bios e ON e.hardware_id=h.id 
				where deviceid<>'_SYSTEMGROUP_' 
						AND deviceid<>'_DOWNLOADGROUP_' ";
if ($show_mac_addr)
	$queryDetails  .= " AND n.status='Up'
						AND n.type='Ethernet' ";
if (isset($_SESSION['OCS']["mesmachines"]) and $_SESSION['OCS']["mesmachines"] != '')
	$queryDetails  .= "AND ".$_SESSION['OCS']["mesmachines"];
$queryDetails  .=" group by h.name";
$tab_options['LBL_POPUP']['SUP']='name';
$tab_options['LBL']['SUP']=$l->g(122);
$result_exist=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,95,$tab_options);
if ($result_exist != "" and $_SESSION['OCS']['CONFIGURATION']['DELETE_COMPUTERS'] == "YES"){
		echo "<a href=# OnClick='confirme(\"\",\"DEL_SEL\",\"".$form_name."\",\"DEL_ALL\",\"".$l->g(900)."\");'><img src='image/sup_search.png' title='Supprimer' ></a>";
		echo "<input type='hidden' id='DEL_ALL' name='DEL_ALL' value=''>";
	}
echo "</form>";
?>