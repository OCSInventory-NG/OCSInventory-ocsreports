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
$values=look_config_default_values(array('EXPORT_SEP'));
if (isset($values['tvalue']['EXPORT_SEP']) and $values['tvalue']['EXPORT_SEP'] != '')
	$separator=$values['tvalue']['EXPORT_SEP'];
else
	$separator=';';


if( isset($_SESSION['OCS']["forcedRequest"] )) {
	$lareq = $_SESSION['OCS']["forcedRequest"];
}
else
	die();//$lareq = $_SESSION['OCS']["storedRequest"]->getFullRequest();

$lareq = str_replace("h.id AS \"h.id\",","",$lareq);
$lareq = str_replace("deviceid AS \"deviceid\",","",$lareq);
$lareq = str_replace("h.n.ipmask","n.ipmask",$lareq);

//echo $lareq;die();
$result=mysql_query($lareq, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
//echo "requete:".$lareq;
// iexplorer problem
if( ini_get("zlib.output-compression"))
	ini_set("zlib.output-compression","Off");
	
header("Pragma: public");
header("Expires: 0");
header("Cache-control: must-revalidate, post-check=0, pre-check=0");
header("Cache-control: private", false);
header("Content-type: application/force-download");
header("Content-Disposition: attachment; filename=\"export.csv\"");
header("Content-Transfer-Encoding: binary");

$toBeWritten = "";

while( $colname = mysql_fetch_field($result) ) {
	$toBeWritten .=$colname->name.$separator;
	$cols[] = $colname->name;
}
$toBeWritten .="\r\n";
//writeTab($fp,$cols);

while( $cont = mysql_fetch_array($result,MYSQL_ASSOC) ) {
	foreach ($cols as $k=>$v){
		$toBeWritten.=$cont[$v].$separator;
	}
	$toBeWritten .="\r\n";
	//writeTab($fp,$cont,$nameIndex);
}

header("Content-Length: ".strlen($toBeWritten));
echo $toBeWritten;

?>
