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

//fonction qui permet de gérer les colonnes par cookies
/*function cookies_tab()
{
	//si la variable de session des tableaux n'existe pas, 
	//on va chercher le cookies
	if (!isset($_SESSION['OCS']['col_tab']) and isset($_COOKIE['col_tab'])){
		foreach ($_COOKIE['col_tab'] as $key=>$value){
			//si la variable de SESSION n'existe 
			foreach ($value as $index=>$field_name){
				$_SESSION['OCS']['col_tab'][$key][$field_name]=$field_name;
			}
		}				
	}
}*/


function cookies_reset($cookies_del){
			if (isset($_COOKIE[$cookies_del]))
			setcookie( $cookies_del, FALSE, time() - 3600 ); // deleting corresponding cookie

}


function cookies_add($name,$value){
		cookies_reset($name);		
		setcookie( $name, $value, time() + 3600 * 24 * 365 ); 	
}
/*
function upload_cookies($table_name){
	unset($_SESSION['OCS']['col_tab'][$table_name]);
	if (!isset($_SESSION['OCS']['col_tab'][$table_name]) and isset($_COOKIE[$table_name])){
		$col_tab=explode("///", $_COOKIE[$table_name]);
		foreach ($col_tab as $key=>$value){
				$_SESSION['OCS']['col_tab'][$table_name][$key]=$value;
		}			
	}
	
}

*/

?>
