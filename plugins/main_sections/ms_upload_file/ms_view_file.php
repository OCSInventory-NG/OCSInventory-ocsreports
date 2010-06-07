<?php
/*
  
  
  answers this page with
  $tab_options['LIEN_LBL']['Fichier']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_view_file'].'&prov=dde_wk&no_header=1&value=';
	$tab_options['LIEN_CHAMP']['Fichier']='id';
	$tab_options['LIEN_TYPE']['Fichier']='POPUP';
	$tab_options['POPUP_SIZE']['Fichier']="width=900,height=600"; 
	
	
*/

//on vient du formulaire de télédploiement
if ($protectedGet['prov'] == "dde_wk"){
	$queryDetails = "select FILE,FILE_NAME,FILE_TYPE,FILE_SIZE
			 FROM temp_files 
			 where id = '".$protectedGet["value"]."' and author='".$_SESSION['OCS']['loggeduser']."'";
	$res_document_root = mysql_query( $queryDetails, $_SESSION['OCS']["readServer"] ) or die(mysql_error($_SESSION['OCS']["readServer"]));;
	$val_document_root = mysql_fetch_array( $res_document_root );
	
	
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