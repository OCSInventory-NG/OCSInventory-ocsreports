<?php
if (!function_exists('escape_string_security')){
	function escape_string_security($array){
		foreach ($array as $key=>$value){
			$trait_array[$key]=mysql_real_escape_string($value);
		}
		return ($trait_array);
	}
	
	if( !isset($_SESSION["lvluser"])) 
		die("FORBIDDEN");
		
	if (isset($sadmin_profil) and $_SESSION["lvluser"] != $sadmin_profil)	
	die("FORBIDDEN");
	
	$_POST=escape_string_security($_POST);
	$_GET=escape_string_security($_GET);
}

?>