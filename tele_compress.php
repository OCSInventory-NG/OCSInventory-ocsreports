<?php
@session_start();
unset($_SESSION['LANGUAGE']);
$header_html="NO";
require_once("header.php");
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
	
	$res_document_root = mysql_query( $sql_document_root, $_SESSION["readServer"] );
	while( $val_document_root = mysql_fetch_array( $res_document_root ) ) {
		$document_root = $val_document_root["tvalue"];
	}
	//if no directory in base, take $_SERVER["DOCUMENT_ROOT"]
	if (!isset($document_root)){
		$document_root = $_SERVER["DOCUMENT_ROOT"]."/download/";
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
	flush();
	print $zipfile -> file();
	exit();
}
?>
