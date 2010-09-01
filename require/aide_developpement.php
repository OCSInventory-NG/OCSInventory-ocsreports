<?php
/*
 * Fichier de fonctions pour l'aide au d�bug et au dev
 * 
 * 
 */
function print_r_V2($array)
{ print "<table border='1'>"; 
  foreach($array as $key=>$val) { 
  	print "<tr><td><font size=2>".$key."</td><td><font size=2>"; 
  	if (is_array($array[$key])) { 
  		print_r_V2($array[$key]); 
  		print "</td></tr>"; } 
  	else print $val."</td></tr>"; 
  	} 
  print "</table>"; 
} 

function p($array){
	print_r_V2($array);
}

?>
