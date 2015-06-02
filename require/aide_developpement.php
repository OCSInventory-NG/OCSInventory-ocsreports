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

/*
 * Fichier de fonctions pour l'aide au d�bug et au dev
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
