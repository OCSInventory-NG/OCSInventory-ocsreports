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
function array_merge_values($arr,$arr2){
	foreach ($arr2 as $key=>$values){
		array_push($arr,$values);		
	}
	return $arr;
}



if ($protectedPost['RESET']){ 
	unset($protectedPost['NAME_RESTRICT']);
	unset($protectedPost['NBRE']);
	unset($protectedPost['CLASS']);
}

if ($protectedPost['SUBMIT_FORM'])
$tab_options['CACHE']='RESET';

$sql_fin['SQL']="";
$sql_fin['ARG']=array();
$sql_list_alpha['ARG']=array();
if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES']) 
		and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1){
	$info_name_soft=array("table"=>"type_softwares_name","field"=>"name");
}elseif($_SESSION['OCS']["usecache"] == 1){
	$info_name_soft=array("table"=>"softwares_name_cache","field"=>"name");	
}else{
	$info_name_soft=array("table"=>"softwares","field"=>"name");
}

$field_name_soft=$info_name_soft['table'].".".$info_name_soft['field'];

$sql_list_alpha['SQL'] ="select substr(trim(".$field_name_soft."),1,1) alpha, ".$field_name_soft." ";
if (isset($protectedPost['NBRE']) and $protectedPost['NBRE'] != "" and isset($protectedPost['COMPAR']) and $protectedPost['COMPAR'] != ""){
	$sql_list_alpha['SQL'] .=",count(*) nb ";	
	$sql_fin['SQL']=" having nb %s %s ";
	$sql_fin['ARG']=array($protectedPost['COMPAR'],$protectedPost['NBRE']);
}
$sql_list_alpha['SQL'] .=" from ";
$and_where="";
//if ($_SESSION['OCS']["usecache"] == 1  and $protectedPost['NBRE'] == ""){
	$sql_list_alpha['SQL'] .=$info_name_soft['table']." left join dico_soft on dico_soft.extracted=".$field_name_soft;
//	$and_where=" where ";
//}else{
//	$sql_list_alpha['SQL'] .=" softwares s left join dico_soft on dico_soft.extracted=s.name ";
	/*if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES']) 
		and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1){		
		$sql_list_alpha['SQL'] .=" left join type_softwares_name type_soft_name on type_soft_name.id=s.name ";	
	}*/
	if ($_SESSION['OCS']["mesmachines"] != ""){
		$sql_list_alpha['SQL'] .=",accountinfo a where ".$_SESSION['OCS']["mesmachines"]." and a.hardware_id=s.HARDWARE_ID ";
		$and_where=" and ";
	}else
	$and_where=" where ";
//}
$sql_list_alpha['SQL'] .=$and_where." substr(trim(".$field_name_soft."),1,1) is not null ";
if (isset($protectedPost['NAME_RESTRICT']) and $protectedPost['NAME_RESTRICT'] != ""){
	$sql_list_alpha['SQL'] .=" and ".$field_name_soft." like '%s' ";
	$sql_list_alpha['ARG']=array('%'.$protectedPost['NAME_RESTRICT'].'%');	
}
/*if (isset($protectedPost['CLASS']) and $protectedPost['CLASS'] != ""){
		$sql_list_alpha['SQL'].=" and (dico_soft.formatted in ";	
		$sql_list_alpha=mysql2_prepare($sql_list_alpha['SQL'],$sql_list_alpha['ARG'],$list_soft_by_statut[$protectedPost['CLASS']]);	
		$sql_list_alpha['SQL'] .=" ) ";
	}*/
$sql_list_alpha['SQL'] .=" group by ".$field_name_soft." ".$sql_fin['SQL'];
//if ($sql_list_alpha['ARG'] != array() and $sql_fin['ARG'] != array())
	$sql_list_alpha['ARG']=array_merge_values($sql_list_alpha['ARG'],$sql_fin['ARG']);
//elseif ($sql_fin['ARG'] != array() and $sql_list_alpha['ARG'] == array())
//	$sql_list_alpha['ARG']=$sql_fin['ARG'];

	unset($_SESSION['OCS']['REQ_ONGLET_SOFT']);
/*p($sql_fin['ARG']);
p($sql_list_alpha['ARG']);*/
//execute the query only if necessary 
if($_SESSION['OCS']['REQ_ONGLET_SOFT'] != $sql_list_alpha or !isset($protectedPost['onglet'])){
	$result_list_alpha = mysql2_query_secure( $sql_list_alpha['SQL'], $_SESSION['OCS']["readServer"],$sql_list_alpha['ARG']);
 	while($item_list_alpha = mysql_fetch_object($result_list_alpha)){
 		
 		if (strtoupper($item_list_alpha -> alpha) == '"')
					$car="'";
				else
					$car=strtoupper($item_list_alpha -> alpha);
					
 		if ($car != ""){
				if (!isset($protectedPost['onglet']))
					$protectedPost['onglet']=$car;
				$list_alpha[$car]=$car;
				if (!isset($first)){
					$first=$car;				
				}
 		}
	}
	
	if (!isset($list_alpha[$protectedPost['onglet']])){
		$protectedPost['onglet']=$first;
	}
	$_SESSION['OCS']['REQ_ONGLET_SOFT']= $sql_list_alpha;
	$_SESSION['OCS']['ONGLET_SOFT']=$list_alpha;
}
$form_name = "all_soft";
$table_name="all_soft";
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";

onglet($_SESSION['OCS']['ONGLET_SOFT'],$form_name,"onglet",20);
echo '<div class="mlt_bordure" >';
if ((isset($protectedPost['NAME_RESTRICT']) and $protectedPost['NAME_RESTRICT'] != "") or
	((isset($protectedPost['NBRE']) and $protectedPost['NBRE'] != "")))
	msg_warning($l->g(767));


//use cache
if ($_SESSION['OCS']["usecache"] == 1){
	$search_soft['SQL']="select name,id from softwares_name_cache";
	//$forcedRequest['SQL']=$search_soft['SQL'];
	$search_soft['SQL'].=" where name like '%s'";
	$search_soft['ARG']=array($protectedPost['onglet']."%");
	$and_where=" where ";
	if (isset($protectedPost['NAME_RESTRICT']) and $protectedPost['NAME_RESTRICT'] != ""){
		//$forcedRequest['SQL'].= $and_where." name like '%s' ";
		//$forcedRequest['ARG']=array("%".$protectedPost['NAME_RESTRICT']."%");
		$search_soft['SQL'].=" and name like '%s' ";	
		array_push($search_soft['ARG'],"%".$protectedPost['NAME_RESTRICT']."%");	
		$and_where=" and ";
	}

	/*if (isset($protectedPost['CLASS']) and $protectedPost['CLASS'] != ""){
	//	$fin_sql=" and dico_soft.extracted is not null ";
		$forcedRequest.= $and_where." (dico_soft.formatted in ('".implode("','",$list_soft_by_statut[$protectedPost['CLASS']])."') ) and ";
		$search_soft.=" and (dico_soft.formatted in ('".implode("','",$list_soft_by_statut[$protectedPost['CLASS']])."') ) ";		
	}*/

	$result_search_soft = mysql2_query_secure( $search_soft['SQL'], $_SESSION['OCS']["readServer"],$search_soft['ARG']);
	$list_soft="";
	while($item_search_soft = mysql_fetch_object($result_search_soft)){
		if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES']) 
		and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1){		
			$list_soft[]=$item_search_soft->id;
		}else{
			$list_soft[]=$item_search_soft->name;
		}	
	}
}
if ((!isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES']) 
		or $_SESSION['OCS']['USE_NEW_SOFT_TABLES']!= 1) and $_SESSION['OCS']["usecache"] == 1){
	$field_name_soft="s.name";
}


if ($list_soft != ""){
	$and_where="";
	if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES']) 
		and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1){		
		$sql_re['SQL']="select  ".$info_name_soft['table'].".name , count(s.name) nb, s.name id from softwares s left join ".$info_name_soft['table']." on ".$info_name_soft['table'].".id=s.name ";	
	}else
		$sql_re['SQL']="select  s.name , count(s.name) nb, s.name id from softwares s ";
	if (isset($_SESSION['OCS']["mesmachines"]) and $_SESSION['OCS']["mesmachines"] != ''){
		$sql_re['SQL'].=",accountinfo a where ".$_SESSION['OCS']["mesmachines"]." and a.hardware_id=s.HARDWARE_ID";
		$and_where=" and ";
	}else
	$and_where=" where ";	
	//$_SESSION['OCS']["forcedRequest"]=$sql['SQL'].$and_where." name in (".$forcedRequest.")";
	$sql_re['SQL'].=$and_where." s.name in ";
	$sql_re['ARG']=array();
	$sql=mysql2_prepare($sql_re['SQL'],$sql_re['ARG'],$list_soft);
	//$sql['ARG']=('".implode("','",$list_soft)."')";
	//$sql.=$fin_sql;
}else{
	$and_where="";
	$sql['SQL']="select  ".$field_name_soft.", count(s.name) nb, s.name id from softwares ";
	if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES']) 
		and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1){		
		$sql['SQL'] .=" left join ".$info_name_soft['table']." on ".$info_name_soft['table'].".id=softwares.name ";	
	}
	$sql['ARG']=array();
	if (isset($_SESSION['OCS']["mesmachines"]) and $_SESSION['OCS']["mesmachines"] != ''){
		$sql['SQL'].=",accountinfo a where ".$_SESSION['OCS']["mesmachines"]." and a.hardware_id=s.HARDWARE_ID";
		$and_where=" and ";
	}else
	$and_where=" where ";
	//$_SESSION['OCS']["forcedRequest"]=$sql;
	$sql['SQL'].=$and_where." ".$field_name_soft." like '%s'";
	array_push($sql['ARG'],$protectedPost['onglet']."%");
	if (isset($protectedPost['NAME_RESTRICT']) and $protectedPost['NAME_RESTRICT'] != ""){
		$sql['SQL'].=" and ".$field_name_soft." like '%s' ";	
		array_push($sql['ARG'],"%".$protectedPost['NAME_RESTRICT']."%");
		//$_SESSION['OCS']["forcedRequest"].=$and_where."name like '%".$protectedPost['NAME_RESTRICT']."%'" ;
	}
}

if (isset($sql)){
	$sql['SQL'].=" group by ".$field_name_soft;
//	$_SESSION['OCS']["forcedRequest"].=" group by name";
	if ($sql_fin['SQL'] != ''){
		$sql['SQL'].=$sql_fin['SQL'];
		$sql['ARG']= array_merge_values($sql['ARG'],$sql_fin['ARG']);
//	$_SESSION['OCS']["forcedRequest"].=$sql_fin;
	}
	$list_fields= array('name'=>'name',
						'nbre'=>'nb'
						);
	$default_fields= $list_fields;
	$list_col_cant_del=$default_fields;
	$tab_options['LIEN_LBL']['nbre']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_multi_search'].'&prov=allsoft&value=';
	$tab_options['LIEN_CHAMP']['nbre']='id';
	$tab_options['LBL']['name']=$l->g(847);
	$tab_options['LBL']['nbre']=$l->g(1120);
	$tab_options['ARG_SQL']=$sql['ARG'];
	$result_exist=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$form_name,80,$tab_options); 
}

echo "<br><div class='mvt_bordure'>";

echo "<b>".$l->g(735)."</b><br><br>";
echo $l->g(382).": ".show_modif($protectedPost['NAME_RESTRICT'],'NAME_RESTRICT',0);
echo "&nbsp;".$l->g(381).": ".show_modif(array('<'=>'<','>'=>'>','='=>'='),'COMPAR',2);
echo show_modif($protectedPost['NBRE'],'NBRE',0,'',array('MAXLENGTH'=>100,'SIZE'=>10,'JAVASCRIPT'=>$numeric));
//echo "<input type='input' name='NBRE' value='".$protectedPost['NBRE']."' ".$numeric.">";
echo "<br><a href='index.php?".PAG_INDEX."=".$pages_refs['ms_soft_csv']."&no_header=1&soft=".$protectedPost['NAME_RESTRICT']."&nb=".$protectedPost['NBRE']."&comp=".htmlentities($protectedPost['COMPAR'])."'><br>".$l->g(183)." ".$l->g(765)."<br></a>";
if ($protectedPost['COMPAR'] == '<' and $protectedPost['NBRE']<=15 and $protectedPost['NBRE'] != "")
echo "<br><a href='index.php?".PAG_INDEX."=".$pages_refs['ms_soft_csv']."&no_header=1&soft=".$protectedPost['NAME_RESTRICT']."&nb=".$protectedPost['NBRE']."&comp=".htmlentities($protectedPost['COMPAR'])."&all_computers=yes'>".$l->g(912)."</a><br>";
echo "<br><input type='submit' value='".$l->g(393)."' name='SUBMIT_FORM'><input type='submit' value='".$l->g(396)."' name='RESET'>";

echo '</div>';
echo '</div>';
echo "</form>";
?>
