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
 * Fichier de fonctions pour l'aide au débug et au dev
 * 
 * 
 */
function print_r_V2($array)
{ 
	$array=strip_tags_array($array);
	print "<table border='1'>"; 
	if (is_array($array)){
	  foreach($array as $key=>$val) { 
	  	print "<tr><td><font size=2>".$key."</td><td><font size=2>"; 
	  	if (is_array($array[$key])) { 
	  		print_r_V2($array[$key]); 
	  		print "</td></tr>"; } 
	  	else print $val."</td></tr>"; 
	  	} 
	  print "</table>"; 
	}else
	print_r($array);
} 

function p($array){
	print_r_V2($array);
}

?>
