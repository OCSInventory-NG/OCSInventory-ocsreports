<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */
/*
 * Fichier de fonctions pour les statistiques
 */
//We also initiate a counter variable to help us cyclically rotate through
//the array of colors.
$FC_ColorCounter=0;

$arr_FCColors[0] = "1941A5" ;//Dark Blue
$arr_FCColors[1] = "AFD8F8";
$arr_FCColors[2] = "F6BD0F";
$arr_FCColors[3] = "8BBA00";
$arr_FCColors[4] = "A66EDD";
$arr_FCColors[5] = "F984A1" ;
$arr_FCColors[6] = "CCCC00" ;//Chrome Yellow+Green
$arr_FCColors[7] = "999999" ;//Grey
$arr_FCColors[8] = "0099CC" ;//Blue Shade
$arr_FCColors[9] = "FF0000" ;//Bright Red 
$arr_FCColors[10] = "006F00" ;//Dark Green
$arr_FCColors[11] = "0099FF"; //Blue (Light)
$arr_FCColors[12] = "FF66CC" ;//Dark Pink
$arr_FCColors[13] = "669966" ;//Dirty green
$arr_FCColors[14] = "7C7CB4" ;//Violet shade of blue
$arr_FCColors[15] = "FF9933" ;//Orange
$arr_FCColors[16] = "9900FF" ;//Violet
$arr_FCColors[17] = "99FFCC" ;//Blue+Green Light
$arr_FCColors[18] = "CCCCFF" ;//Light violet
$arr_FCColors[19] = "669900" ;//Shade of green

//getFCColor method helps return a color from arr_FCColors array. It uses
//cyclic iteration to return a color from a given index. The index value is
//maintained in FC_ColorCounter

function find_ivalues($packid){
	$sql = "SELECT id FROM download_enable WHERE fileid='%s'";
	$arg = $packid;
	$res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
	while ($row=mysqli_fetch_array($res)) {
		$result[] = $row['id'];
	}
	return $result;
}

function find_device_line($status,$packid){

	//get all ivalues
	$ivalues = find_ivalues($packid);

	//get hardwareid foreach ivalue
	foreach ($ivalues as $value) {
		$sql="select hardware_id,ivalue from devices where name='DOWNLOAD' and tvalue";
		if ($status == "NULL"){
			$sql.= " IS NULL ";
			$arg = $value;
		}elseif ($status == "NOTNULL"){
			$sql.= " IS NOT NULL ";
			$arg = $value;
		}else{
			$sql.= " LIKE '%s' ";
			$arg=array($status,$value);
		}			
		$sql.=	"AND ivalue='%s' " .
				"AND hardware_id NOT IN (SELECT id FROM hardware WHERE deviceid='_SYSTEMGROUP_')";
		
		$res =mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);		
		while ($row=mysqli_fetch_object($res)){
			$result[$value][]=$row->hardware_id;
		}
	}
	return $result;
}

?>