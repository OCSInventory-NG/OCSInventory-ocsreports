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

function search_all_item($onglet,$sous_onglet){
	$result_search_soft = mysql_query( $_SESSION['OCS']['query_dico'], $_SESSION['OCS']["readServer"]);
	while($item_search_soft = mysql_fetch_object($result_search_soft)){
	 		$list[]=$item_search_soft->SOFT_NAME_CHECKSUM;
	}	
	return $list;	
}

function del_soft($onglet,$list_soft){
	if ($_SESSION['OCS']['usecache'])
	$table="softwares_name_cache";
	else
	$table="softwares";
		
	$sql_soft_name="select distinct NAME from ".$table." where ID in (".implode(",",$list_soft).")";
	$result_soft_name = mysql_query( $sql_soft_name, $_SESSION['OCS']["readServer"]);
	while($item_soft_name = mysql_fetch_object($result_soft_name)){
	 		$list_soft_name[]=str_replace('"','\"',$item_soft_name->NAME);
	}
	if($onglet == "CAT" or $onglet == "UNCHANGED")	
		$sql_delete="delete from dico_soft where extracted in (\"".implode("\",\"",$list_soft_name)."\")";
	if($onglet == "IGNORED")	
		$sql_delete="delete from dico_ignored where extracted in (\"".implode("\",\"",$list_soft_name)."\")";	
	//	echo $sql_delete."<br>";
	mysql_query($sql_delete, $_SESSION['OCS']["writeServer"]);	
}

function del_soft_by_checksum($onglet, $soft_checksum){
	if($onglet == "CAT" or $onglet == "UNCHANGED")	
		$sql_delete="delete from dico_soft where MD5(extracted) = '%s'";
	if($onglet == "IGNORED")	
		$sql_delete="delete from dico_ignored where MD5(extracted) = '%s'";	
	
	mysql2_query_secure($sql_delete, $_SESSION['OCS']["writeServer"], array($soft_checksum));	
}


function trans($onglet,$list_soft,$affect_type,$new_cat,$exist_cat){
	global $l;
	if ($_SESSION['OCS']['usecache'])
	$table="softwares_name_cache";
	else
	$table="softwares";
	//verif is this cat exist
	if ($new_cat != ''){
		$sql_verif="select extracted from dico_soft where formatted ='".mysql_real_escape_string($new_cat)."'";
		$result_search_soft = mysql_query( $sql_verif, $_SESSION['OCS']["readServer"]);
	 	$item_search_soft = mysql_fetch_object($result_search_soft);
	 	if (isset($item_search_soft->extracted) or $new_cat == "IGNORED" or $new_cat == "UNCHANGED"){
	 		$already_exist=TRUE;
	 	}
	}
	
	if ($onglet == "NEW"){
		$table="softwares";
		$ok=TRUE;		
	}else{
		if (!isset($already_exist))	{
			del_soft($onglet,$list_soft);
		}		
		$ok = TRUE;
	}	

	if ($ok == TRUE){
		if ($affect_type== "EXIST_CAT"){
				if ($exist_cat == "IGNORED"){			
					$sql="insert dico_ignored (extracted) select distinct NAME from ".$table." where ID in (".implode(",",$list_soft).")";						
				}elseif($exist_cat == "UNCHANGED"){
					$sql="insert dico_soft (extracted,formatted) select distinct NAME,NAME from ".$table." where ID in (".implode(",",$list_soft).")";			
				}else
					$sql="insert dico_soft (extracted,formatted) select distinct NAME,'".mysql_real_escape_string($exist_cat)."' from ".$table." where ID in (".implode(",",$list_soft).")";
		}else{
		 	if (!isset($already_exist)){
		 		$sql="insert dico_soft (extracted,formatted) select distinct NAME,'".mysql_real_escape_string($new_cat)."' from ".$table." where ID in (".implode(",",$list_soft).")";
		 	}else
		 		echo "<script>alert('".$l->g(771)."')</script>";			
		}
		if ($sql!=''){
		//	echo $sql;
			mysql_query($sql, $_SESSION['OCS']["writeServer"]);	
		}
	}
	
}

function trans_by_checksum($onglet,$list_soft,$affect_type,$new_cat,$exist_cat){
	global $l;

	if ($_SESSION['OCS']['usecache'])
		$table="softwares_name_cache";
	else
		$table="softwares";
	
	//verif is this cat exist
	if ($new_cat != ''){
		$sql_verif="select extracted from dico_soft where formatted ='".mysql_real_escape_string($new_cat)."'";
		$result_search_soft = mysql_query( $sql_verif, $_SESSION['OCS']["readServer"]);
	 	$item_search_soft = mysql_fetch_object($result_search_soft);
	 	if (isset($item_search_soft->extracted) or $new_cat == "IGNORED" or $new_cat == "UNCHANGED"){
	 		$already_exist=TRUE;
	 	}
	}

	if ($onglet == "NEW") {
		$table="softwares";
	} else if (!isset($already_exist)) {
		foreach ($list_soft as $soft_checksum) {
			del_soft_by_checksum($onglet, $soft_checksum);
		}
	}
	
	$placeholders = array();
	foreach ($list_soft as $soft_checksum) {
		$placeholders []= "'%s'";
	}

	if ($affect_type== "EXIST_CAT") {
		if ($exist_cat == "IGNORED") {		
			$sql="insert dico_ignored (extracted) select distinct NAME from ".$table." where MD5(NAME) in (".implode(",",$placeholders).")";						
		} elseif($exist_cat == "UNCHANGED") {
			$sql="insert dico_soft (extracted,formatted) select distinct NAME,NAME from ".$table." where MD5(NAME) in (".implode(",",$placeholders).")";			
		} else {
			$sql="insert dico_soft (extracted,formatted) select distinct NAME,'".mysql_real_escape_string($exist_cat)."' from ".$table." where MD5(NAME) in (".implode(",",$placeholders).")";
		}
	} else {
	 	if (!isset($already_exist)) {
	 		$sql="insert dico_soft (extracted,formatted) select distinct NAME,'".mysql_real_escape_string($new_cat)."' from ".$table." where MD5(NAME) in (".implode(",",$placeholders).")";
	 	} else {
	 		echo "<script>alert('".$l->g(771)."')</script>";			
	 	}
	}
	
	if ($sql!='') {
		mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $list_soft);
	}
}
?>
