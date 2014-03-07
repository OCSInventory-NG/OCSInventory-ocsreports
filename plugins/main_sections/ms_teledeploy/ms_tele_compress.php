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

if (!is_numeric($protectedGet["timestamp"]))
die();
header("content-type: application/zip");
header("Content-Disposition: attachment; filename=".$protectedGet["timestamp"].".zip");
if(isset($protectedGet["timestamp"])){
	require_once("libraries/zip.lib.php");
	$zipfile = new zipfile();
	//looking for the directory for pack
	if ($protectedGet['type'] == "server")
	$sql_document_root="select tvalue from config where NAME='DOWNLOAD_REP_CREAT'";
	else
	$sql_document_root="select tvalue from config where NAME='DOWNLOAD_PACK_DIR'";
	
	$res_document_root = mysqli_query( $_SESSION['OCS']["readServer"],$sql_document_root );
	while( $val_document_root = mysqli_fetch_array( $res_document_root ) ) {
		$document_root = $val_document_root["tvalue"].'/download/';
	}
	//echo $document_root;
	//if no directory in base, take $_SERVER["DOCUMENT_ROOT"]
	if (!isset($document_root)){
		$document_root = DOCUMENT_ROOT."download/";
		if ($protectedGet['type'] == "server")
			$document_root .="server/";
	}
	$rep = $document_root.$protectedGet["timestamp"]."/";
	//echo $rep;
	$dir = opendir($rep);
	while($f = readdir($dir))
	   if(is_file($rep.$f))
	     $zipfile -> addFile(implode("",file($rep.$f)),basename($rep.$f));
	closedir($dir);
	print $zipfile -> file();
	exit();
}
?>
