<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Pierre LEMMET 2005
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================
//Modified on $Date: 2010 $$Author: Erwan Goalou
$compartruevalue=array('<','>','=','');
if (!in_array($protectedGet['comp'],$compartruevalue))
	die();

$values=look_config_default_values(array('EXPORT_SEP'));
if (isset($values['tvalue']['EXPORT_SEP']) and $values['tvalue']['EXPORT_SEP'] != '')
	$separator=$values['tvalue']['EXPORT_SEP'];
else
	$separator=';';
//use cache?
if ($_SESSION['OCS']["usecache"] == 1){
	$sql['SQL']="select name from softwares_name_cache ";
	if (isset($protectedGet['soft']) and $protectedGet['soft'] != ''){
		$sql['SQL'].= " where name like '%s'";
		$sql['ARG']=array('%'.$protectedGet['soft'].'%');		
	}
	$result_search_soft = mysql2_query_secure( $sql['SQL'], $_SESSION['OCS']["readServer"],$sql['ARG']);
	$list_soft="";
	while($item_search_soft = mysql_fetch_object($result_search_soft)){
		$list_soft[]=$item_search_soft->name;	
	}
	$sql['SQL']="select count(*) nb, name from softwares s,accountinfo a where a.hardware_id=s.hardware_id and name in ";
	$sql['ARG']=array();
	$sql=mysql2_prepare($sql['SQL'],$sql['ARG'],$list_soft);

	if ($_SESSION['OCS']['TAGS'] != ""){
		$sql['SQL'].= " and a.tag in ";
		$sql=mysql2_prepare($sql['SQL'],$sql['ARG'],$_SESSION['OCS']['TAGS']);
	}
	$sql['SQL'].=" group by name ";
	if (isset($protectedGet['nb']) and $protectedGet['nb'] != '' and isset($protectedGet['comp']) and $protectedGet['comp'] != ''){
		$sql['SQL'].= "having nb %s %s";
		array_push($sql['ARG'],$protectedGet['comp']);
		array_push($sql['ARG'],$protectedGet['nb']);
	}	
}else{

	$sql['SQL']="select count(*) nb, name from softwares s,accountinfo a where a.hardware_id=s.hardware_id ";
	if (isset($protectedGet['soft']) and $protectedGet['soft'] != ''){
		$sql['SQL'].= " where name like '%s'";
		$sql['ARG']=array('%'.$protectedGet['soft'].'%');	
	}
	if ($_SESSION['OCS']['TAGS'] != ""){
		$sql['SQL'].= " and a.tag in ";
		$sql=mysql2_prepare($sql['SQL'],$sql['ARG'],$_SESSION['OCS']['TAGS']);
	}
	$sql['SQL'].=" group by name ";

	if (isset($protectedGet['nb']) and $protectedGet['nb'] != '' and isset($protectedGet['comp']) and $protectedGet['comp'] != ''){
		$sql['SQL'].= "having nb %s %s";
		array_push($sql['ARG'],$protectedGet['comp']);
		array_push($sql['ARG'],$protectedGet['nb']);
	}	
	
	
}

if (isset($protectedGet['all_computers'])){
	$fields= array("a.tag"=>$_SESSION['OCS']['TAG_LBL']['TAG'],
			   "s.name"=>$l->g(20),
			   "h.name"=>$l->g(23),
			   "h.userid"=>$l->g(24),
			   "h.description"=>$l->g(53),
			   "h.lastdate"=>$l->g(728));
	$result_search_soft = mysql2_query_secure( $sql['SQL'], $_SESSION['OCS']["readServer"],$sql['ARG']);
	while($item_search_soft = mysql_fetch_object($result_search_soft)){
		$soft[]=$item_search_soft->name;
	}

	$sql=prepare_sql_tab(array_keys($fields));
	$sql['SQL'].= " from accountinfo a, softwares s,hardware h 
					where a.hardware_id=h.id and s.hardware_id=h.id ";
	if (isset($_SESSION['OCS']['TAGS'])){
			$sql['SQL'].= " and a.tag in ";
			$sql=mysql2_prepare($sql['SQL'],$sql['ARG'],$_SESSION['OCS']['TAGS']);
	}
	//echo $sql['SQL'];
}else{
	$fields=array('nb'=>$l->g(55),'name'=>$l->g(20));
	
}


$toBeWritten=implode(';',$fields)."\r\n";
if( ini_get("zlib.output-compression"))
	ini_set("zlib.output-compression","Off");
header("Pragma: public");
header("Expires: 0");
header("Cache-control: must-revalidate, post-check=0, pre-check=0");
header("Cache-control: private", false);
header("Content-type: application/force-download");
header("Content-Disposition: attachment; filename=\"export.csv\"");
header("Content-Transfer-Encoding: binary");
$result_search_soft = mysql2_query_secure( $sql['SQL'], $_SESSION['OCS']["readServer"],$sql['ARG']);
while($item_search_soft = mysql_fetch_object($result_search_soft)){
	foreach ($fields as $key=>$values){
		$trait=explode('.',$key);
		if (isset($trait[1]))
			$fi=$trait[1];
		else
			$fi=$trait[0];
			
		$toBeWritten.= $item_search_soft->$fi.$separator;
	}
	$toBeWritten.= "\r\n";
	
}
header("Content-Length: ".strlen($toBeWritten));
	echo $toBeWritten;
die();
?>
