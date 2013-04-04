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
  
  
  answers this page with
  $tab_options['LIEN_LBL']['Fichier']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_view_file'].'&prov=dde_wk&no_header=1&value=';
	$tab_options['LIEN_CHAMP']['Fichier']='id';
	$tab_options['LIEN_TYPE']['Fichier']='POPUP';
	$tab_options['POPUP_SIZE']['Fichier']="width=900,height=600"; 
	
	
*/

//origin = workflow teledeploy
if ($protectedGet['prov'] == "dde_wk"){
	$sql = "select FILE,FILE_NAME,FILE_TYPE,FILE_SIZE
			 FROM temp_files 
			 where id = '%s'";
	$arg=array($protectedGet["value"]);
}

if ($protectedGet['prov'] == "agent"){
	$sql= "select %s as FILE,name as FILE_NAME from deploy where name = '%s'";
	$arg=array('content',$protectedGet["value"]);	
}

if ($protectedGet['prov'] == "ssl"){
	$sql= "select FILE,FILE_NAME from ssl_store where id = '%s'";
	$arg=array($protectedGet["value"]);	
}

if (isset($sql) and $sql!=''){
	$res_document_root = mysql2_query_secure( $sql, $_SESSION['OCS']["readServer"],$arg );
	$val_document_root = mysql_fetch_array( $res_document_root );
	if (!isset($val_document_root['FILE_TYPE']) or $val_document_root['FILE_TYPE']!=''){
		$val_document_root['FILE_TYPE']="application/force-download";
	}
	if (!isset($val_document_root['FILE_SIZE']) or $val_document_root['FILE_SIZE']!=''){
		$val_document_root['FILE_SIZE']=strlen($val_document_root['FILE']);
	}
	
}

if (isset($val_document_root['FILE_NAME'])){
	// iexplorer problem
	if( ini_get("zlib.output-compression"))
		ini_set("zlib.output-compression","Off");
		
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-control: private", false);
	header("Content-type: ".$val_document_root['FILE_TYPE']);
	header("Content-Disposition: attachment; filename=\"".$val_document_root['FILE_NAME']."\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".$val_document_root['FILE_SIZE']);
	echo $val_document_root['FILE'];
	die();
}

?>