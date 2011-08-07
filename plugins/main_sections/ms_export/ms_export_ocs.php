<?php
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Erwan GOALOU 2011 (erwan(at)ocsinventory-ng(pt)org)
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================

$sql="select * from hardware where id=%s";
$arg=$protectedGet['systemid'];
$res=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$arg);
$item_hardware = mysql_fetch_object($res);
$xml= "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
$table_not_use=array('accountinfo','groups_cache','download_history','devices');
$xml.= "<REQUEST>\n";
$xml.= "\t<DEVICEID>".$item_hardware->DEVICEID."</DEVICEID>\n";
$xml.= "\t<CONTENT>\n";
foreach ($_SESSION['OCS']['SQL_TABLE_HARDWARE_ID'] as $tablename){
	if (!in_array($tablename,$table_not_use)){
		//$sql= prepare_sql_tab($_SESSION['OCS']['SQL_TABLE'][$tablename]);
		$sql="select * from %s where hardware_id=%s";
		$arg=array($tablename,$protectedGet['systemid']);
		
		$res=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$arg);
		while ($item = mysql_fetch_object($res)){
			$xml.= "\t\t<".strtoupper($tablename).">\n";
			foreach($_SESSION['OCS']['SQL_TABLE'][$tablename] as $field_name=>$field_type){
				if ($field_name != 'HARDWARE_ID'){
					if(replace_entity_xml($item->$field_name) != ''){
						$xml.= "\t\t\t<".$field_name.">";
						$xml.= replace_entity_xml($item->$field_name);
						$xml.= "</".$field_name.">\n";
					}else{
						$xml.= "\t\t\t<".$field_name." />\n";
						
					}
				}
			}
			$xml.= "\t\t</".strtoupper($tablename).">\n";
		}
	}
	
}
//HARDWARE INFO
$xml.= "\t\t<HARDWARE>\n";
foreach($_SESSION['OCS']['SQL_TABLE']['hardware'] as $field_name=>$field_type){
		if ($field_name != 'ID' and $field_name != 'DEVICEID'){
			if(replace_entity_xml($item_hardware->$field_name) != ''){
				$xml.= "\t\t\t<".$field_name.">";
				$xml.= replace_entity_xml($item_hardware->$field_name);
				$xml.= "</".$field_name.">\n";
			}else
				$xml.= "\t\t\t<".$field_name." />\n";
		}
}
$xml.= "\t\t</HARDWARE>\n";

//ACCOUNTINFO VALUES
$sql="select * from accountinfo where hardware_id=%s";
$arg=$protectedGet['systemid'];
$res=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$arg);
$item_accountinfo = mysql_fetch_object($res);

foreach($_SESSION['OCS']['SQL_TABLE']['accountinfo'] as $field_name=>$field_type){
		if ($field_name != 'HARDWARE_ID'){
			$xml.= "\t\t<ACCOUNTINFO>\n";
			$xml.= "\t\t\t<KEYNAME>".$field_name."</KEYNAME>\n";
			if (replace_entity_xml($item_accountinfo->$field_name) != '')
				$xml.= "\t\t\t<KEYVALUE>".replace_entity_xml($item_accountinfo->$field_name)."</KEYVALUE>\n";
			else
				$xml.= "\t\t\t<KEYVALUE />\n";
			$xml.= "\t\t</ACCOUNTINFO>\n";
		}
}


$xml.="\t</CONTENT>\n";
$xml.="\t<QUERY>INVENTORY</QUERY>\n";
	$xml.="</REQUEST>\n";	
	
if ($xml != ""){
	// iexplorer problem
	if( ini_get("zlib.output-compression"))
		ini_set("zlib.output-compression","Off");
		
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-control: private", false);
	header("content-type: text/xml ");
	header("Content-Disposition: attachment; filename=\"".$item_hardware->DEVICEID.".xml\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".strlen($xml));
	echo $xml,
	die();
}else{
	$ban_head='no';
	require_once (HEADER_HTML);
	msg_error($l->g(920));
	require_once(FOOTER_HTML);
	die();
}


?>