<?php
if (!function_exists('escape_string_security')){
	function escape_string_security($array){
		if (is_array($array)){
			foreach ($array as $key=>$value){
				$trait_array[$key]=mysql_real_escape_string($value);
			}
		}else
		$trait_array=mysql_real_escape_string($array);
		return ($trait_array);
	}
	
	if( !isset($_SESSION["lvluser"])) 
		die("FORBIDDEN");
		
	//echo gettype($_SESSION["lvluser"]);
	//echo "<br>".gettype($sadmin_profil);
	if (isset($sadmin_profil) and ($_SESSION["lvluser"]+0) !== $sadmin_profil)	
	die("FORBIDDEN");
	if (get_magic_quotes_gpc() == 0 and !function_exists('addslashes_deep')){
		$_POST=escape_string_security($_POST);
		$_GET=escape_string_security($_GET);
		$_COOKIE = escape_string_security($_COOKIE);
	}
}

?>