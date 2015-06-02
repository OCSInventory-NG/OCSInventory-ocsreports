<?php
if (!function_exists('escape_string_security') and !defined('INC')){
	function escape_string_security($array){
		if (isset($array) and is_array($array)){
			foreach ($array as $key=>$value){
				if (!is_array($value)){
					$trait_array[$key]=mysql_real_escape_string($value);
				}
			}
			if (!isset($trait_array))
			$trait_array=array();
		}else
		$trait_array=mysql_real_escape_string($array);
		return ($trait_array);
	}
	
	if( !isset($_SESSION["lvluser"])) 
		die("FORBIDDEN");
		
	//echo gettype($_SESSION["lvluser"]);
	//echo "<br>".$sadmin_profil;
	if (isset($sadmin_profil) and ($_SESSION["lvluser"]+0) !== $sadmin_profil)	
	die("FORBIDDEN");
	if (get_magic_quotes_gpc() == 0 and !function_exists('addslashes_deep')){
		if (isset($_POST))
		$_POST=escape_string_security($_POST);
		if (isset($_GET))
		$_GET=escape_string_security($_GET);
		if (isset($_COOKIE))
		$_COOKIE = escape_string_security($_COOKIE);
	}
}

?>