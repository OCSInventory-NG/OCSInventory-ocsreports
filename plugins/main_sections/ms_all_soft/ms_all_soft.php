<?php
//variable d'export de tous les logiciels
//=>$_SESSION['OCS']["forcedRequest"]

if ($protectedPost['RESET']){ 
	unset($protectedPost['NAME_RESTRICT']);
	unset($protectedPost['NBRE']);
	unset($protectedPost['CLASS']);
}

if ($protectedPost['SUBMIT_FORM'])
$tab_options['CACHE']='RESET';

$sql_fin="";

$sql_list_alpha ="select substr(trim(name),1,1) alpha, name ";
if (isset($protectedPost['NBRE']) and $protectedPost['NBRE'] != "" and isset($protectedPost['COMPAR']) and $protectedPost['COMPAR'] != ""){
	$sql_list_alpha .=",count(*) nb ";	
	$sql_fin=" having nb ".$protectedPost['COMPAR']." ".$protectedPost['NBRE']." ";
}
$sql_list_alpha .=" from ";
$and_where="";
if ($_SESSION['OCS']["usecache"] == 1  and $protectedPost['NBRE'] == ""){
	$sql_list_alpha .=" softwares_name_cache left join dico_soft on dico_soft.extracted=softwares_name_cache.name ";
	$and_where=" where ";
}else{
	$sql_list_alpha .=" softwares left join dico_soft on dico_soft.extracted=softwares.name ";
	if ($_SESSION['OCS']["mesmachines"] != ""){
		$sql_list_alpha .=",accountinfo a where ".$_SESSION['OCS']["mesmachines"]." and a.hardware_id=softwares.HARDWARE_ID ";
		$and_where=" and ";
	}else
	$and_where=" where ";
}
$sql_list_alpha .=$and_where." substr(trim(name),1,1) is not null ";
if (isset($protectedPost['NAME_RESTRICT']) and $protectedPost['NAME_RESTRICT'] != "")
	$sql_list_alpha .=" and name like '%".$protectedPost['NAME_RESTRICT']."%' ";
if (isset($protectedPost['CLASS']) and $protectedPost['CLASS'] != ""){
		$sql_list_alpha.=" and (dico_soft.formatted in ('".implode("','",$list_soft_by_statut[$protectedPost['CLASS']])."') ".$sql_default." ) ";		
	}
$sql_list_alpha .=" group by name ".$sql_fin;

//execute the query only if necessary 
if($_SESSION['OCS']['REQ_ONGLET_SOFT'] != $sql_list_alpha or !isset($protectedPost['onglet'])){
	$result_list_alpha = mysql_query( $sql_list_alpha, $_SESSION['OCS']["readServer"]);
 	while($item_list_alpha = mysql_fetch_object($result_list_alpha)){
 		if (strtoupper($item_list_alpha -> alpha) != ""){
				if (!isset($protectedPost['onglet']))
					$protectedPost['onglet']=strtoupper($item_list_alpha -> alpha);
				$list_alpha[strtoupper($item_list_alpha -> alpha)]=strtoupper($item_list_alpha -> alpha);
				if (!isset($first)){
					$first=$list_alpha[strtoupper($item_list_alpha -> alpha)];				
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
echo "<font color=red size=3><b>".$l->g(767)."</b></font>";


//utilisation du cache
if ($_SESSION['OCS']["usecache"] == 1){
	$search_soft="select name from softwares_name_cache left join dico_soft on dico_soft.extracted=softwares_name_cache.name ";
	$forcedRequest=$search_soft;
	$search_soft.=" where name like '".$protectedPost['onglet']."%'";
	$and_where=" where ";
	if (isset($protectedPost['NAME_RESTRICT']) and $protectedPost['NAME_RESTRICT'] != ""){
		$forcedRequest.= $and_where." name like '%".$protectedPost['NAME_RESTRICT']."%' ";
		$search_soft.=" and name like '%".$protectedPost['NAME_RESTRICT']."%' ";		
		$and_where=" and ";
	}
	if (isset($protectedPost['CLASS']) and $protectedPost['CLASS'] != ""){
	//	$fin_sql=" and dico_soft.extracted is not null ";
		$forcedRequest.= $and_where." (dico_soft.formatted in ('".implode("','",$list_soft_by_statut[$protectedPost['CLASS']])."') ".$sql_default." ) and ";
		$search_soft.=" and (dico_soft.formatted in ('".implode("','",$list_soft_by_statut[$protectedPost['CLASS']])."') ".$sql_default." ) ";		
	}
	//echo $search_soft;
	$result_search_soft = mysql_query( $search_soft, $_SESSION['OCS']["readServer"]);
	$list_soft="";
	//$count_soft=0;
	while($item_search_soft = mysql_fetch_object($result_search_soft)){
		$list_soft[]=mysql_escape_string($item_search_soft->name);	
		//$count_soft++;	
	}
}
if ($list_soft != ""){
	$and_where="";
	$sql="select  name , count(name) nb from softwares ";

	if (isset($_SESSION['OCS']["mesmachines"]) and $_SESSION['OCS']["mesmachines"] != ''){
		$sql.=",accountinfo a where ".$_SESSION['OCS']["mesmachines"]." and a.hardware_id=softwares.HARDWARE_ID";
		$and_where=" and ";
	}else
	$and_where=" where ";	
	$_SESSION['OCS']["forcedRequest"]=$sql.$and_where." name in (".$forcedRequest.")";
	$sql.=$and_where." name in ('".implode("','",$list_soft)."')";
	//$sql.=$fin_sql;
}else{
	$and_where="";
	$sql="select  name, count(name) nb from softwares ";
	if (isset($_SESSION['OCS']["mesmachines"]) and $_SESSION['OCS']["mesmachines"] != ''){
		$sql.=",accountinfo a where ".$_SESSION['OCS']["mesmachines"]." and a.hardware_id=softwares.HARDWARE_ID";
		$and_where=" and ";
	}else
	$and_where=" where ";
	$_SESSION['OCS']["forcedRequest"]=$sql;
	$sql.=$and_where." name like '".$protectedPost['onglet']."%'";
	if (isset($protectedPost['NAME_RESTRICT']) and $protectedPost['NAME_RESTRICT'] != ""){
		$sql.=" and name like '%".$protectedPost['NAME_RESTRICT']."%' ";	
		$_SESSION['OCS']["forcedRequest"].=$and_where."name like '%".$protectedPost['NAME_RESTRICT']."%'" ;
	}
}

if (isset($sql)){
	$sql.=" group by name";
	$_SESSION['OCS']["forcedRequest"].=" group by name";
	if ($sql_fin != ''){
	$sql.=$sql_fin;
	$_SESSION['OCS']["forcedRequest"].=$sql_fin;
	}
	$list_fields= array('name'=>'name',
						'nbre'=>'nb'
						);
	$default_fields= $list_fields;
	$list_col_cant_del=$default_fields;
	$tab_options['LIEN_LBL']['nbre']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_multi_search'].'&prov=allsoft&value=';
	$tab_options['LIEN_CHAMP']['nbre']='name';
	//$tab_options['nbre'][]
	$result_exist=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql,$form_name,80,$tab_options); 
}

echo "<br><div class='mvt_bordure'>";

echo "<b>".$l->g(735)."</b><BR>";
echo $l->g(382).": ".show_modif($protectedPost['NAME_RESTRICT'],'NAME_RESTRICT',0);
echo "&nbsp;".$l->g(381).": ".show_modif(array('<'=>'<','>'=>'>','='=>'='),'COMPAR',2);
echo show_modif($protectedPost['NBRE'],'NBRE',0,'',array('MAXLENGTH'=>100,'SIZE'=>10,'JAVASCRIPT'=>$numeric));
//echo "<input type='input' name='NBRE' value='".$protectedPost['NBRE']."' ".$numeric.">";
	echo "<br><a href='index.php?".PAG_INDEX."=".$pages_refs['ms_soft_csv']."&no_header=1'>".$l->g(183)." ".$l->g(765)."</a>";
if ($protectedPost['COMPAR'] == '<' and $protectedPost['NBRE']<=15 and $protectedPost['NBRE'] != "")
echo "<br><a href='index.php?".PAG_INDEX."=".$pages_refs['ms_exportallsoft']."&no_header=1'>".$l->g(912)."</a>";		
	echo "<br><input type='submit' value='".$l->g(393)."' name='SUBMIT_FORM'><input type='submit' value='".$l->g(396)."' name='RESET'>";

echo '</div>';
echo '</div>';
echo "</form>";
?>
