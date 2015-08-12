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
require_once(TC_LIB_BARCODE);
require_once('require/function_admininfo.php');
if (is_numeric($protectedGet['systemid']) and array_key_exists($protectedGet['default_value'],$array_qr_action)){
	if ($array_qr_action[$protectedGet['default_value']]['TYPE'] == 'url')
		$msg = $array_qr_action[$protectedGet['default_value']]['VALUE'];
	else{
		$fields_info=explode('.',$array_qr_action[$protectedGet['default_value']]['VALUE']);
		if ($fields_info[0] == 'hardware')
			$hardware_id='id';
		else
			$hardware_id='hardware_id';
		$sql="select %s from %s where %s='%s'";
		$arg=array($fields_info[1],$fields_info[0],$hardware_id,$protectedGet['systemid']);
		$res=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$arg);
		$val= mysqli_fetch_array($res);
		$msg=$val[$fields_info[1]];
	}

	$barcode = new \Com\Tecnick\Barcode\Barcode();
	$qrcode = $barcode->getBarcodeObj('QRCODE,H', $msg, 400, 400, 'black',array(20,20,20,20));
	$qrcode->getPng();
}
