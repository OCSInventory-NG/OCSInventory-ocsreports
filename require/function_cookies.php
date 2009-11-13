<?php
//fonction qui permet de g�rer les colonnes par cookies
function cookies_tab()
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
}


function cookies_reset($cookies_del){
			if (isset($_COOKIE[$cookies_del]))
			setcookie( $cookies_del, FALSE, time() - 3600 ); // deleting corresponding cookie

}


function cookies_add($name,$value){
		cookies_reset($name);		
		setcookie( $name, $value, time() + 3600 * 24 * 365 ); 	
}

function upload_cookies($table_name){
	unset($_SESSION['OCS']['col_tab'][$table_name]);
	if (!isset($_SESSION['OCS']['col_tab'][$table_name]) and isset($_COOKIE[$table_name])){
		$col_tab=explode("///", $_COOKIE[$table_name]);
		foreach ($col_tab as $key=>$value){
				$_SESSION['OCS']['col_tab'][$table_name][$key]=$value;
		}			
	}
	
}


?>