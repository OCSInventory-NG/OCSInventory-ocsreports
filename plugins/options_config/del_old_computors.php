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
$time_to_delete=10; //minutes
$max_computer_delete=50;


$sql="select IVALUE,TVALUE from config where NAME='INVENTORY_VALIDITY'";
$result = mysqli_query($_SESSION['OCS']["readServer"],$sql) or die(mysqli_error($_SESSION['OCS']["readServer"]));
$value=mysqli_fetch_array($result);
if (isset($value['IVALUE']) && $value['IVALUE']!= 0){
	$timestamp_now=mktime(0,date("i"),0,date("m"),date("d"),date("Y"));
	echo $timestamp_now;
	echo "<br>".$value['TVALUE'];
	if ($value['TVALUE']<$timestamp_now || $value['TVALUE'] == null){		
		$timestamp_limit=mktime(0,date("i"),0,date("m"),date("d")-$value['IVALUE'],date("Y"));
		$sql="update config set TVALUE='".mktime(0,date("i")+$time_to_delete,0,date("m"),date("d"),date("Y"))."' where NAME='INVENTORY_VALIDITY'";
		mysqli_query($_SESSION['OCS']["writeServer"],$sql);
		$sql="select id,lastdate,name from hardware where UNIX_TIMESTAMP(lastdate) < ".$timestamp_limit." limit ".$max_computer_delete;
		$res = mysqli_query($_SESSION['OCS']["readServer"], $sql) or die(mysqli_error($_SESSION['OCS']["readServer"]));
		while( $val = mysqli_fetch_array($res) ){
			addLog("DELETE ".$val['name'], $l->g(820)." => ".$val['lastdate']." DATE < ".date("d/m/y H:i:s",$timestamp_limit));			
		}        
	}
	//dans 10 minutes

}

?>
