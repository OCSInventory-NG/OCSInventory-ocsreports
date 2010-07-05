<?php
//====================================================================================
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================

require_once('require/fonction.inc.php');

//http://dcabasson.developpez.com/articles/javascript/ajax/ajax-autocompletion-pas-a-pas/
header('Content-Type: text/xml;charset=utf-8');
echo utf8_encode("<?xml version='1.0' encoding='UTF-8' ?><options>");

//connecOCS();
$sql ="SELECT DISTINCT softwares.NAME FROM softwares_name_cache softwares WHERE softwares.NAME NOT LIKE '%Correctif%' AND softwares.NAME NOT LIKE '%Mise a jour%' ORDER BY softwares.NAME";
$query= mysql_query($sql,$_SESSION['OCS']["readServer"]);
while($row=mysql_fetch_array($query,MYSQL_ASSOC))  // or die ('erreur dans le fetch_array' .mysql_error()))
	{
	$liste[]=$row;
	}
if (isset($_GET['debut'])) {
    $debut = utf8_decode($_GET['debut']);
} else {
    $debut = "";
}
$debut = strtolower($debut);  // met la premiere lettre en majuscule

$MAX_RETURN=10;
$i= 0;

foreach($liste as $element)
	{
	if($i<$MAX_RETURN && strtolower(substr($element['NAME'], 0, strlen($debut)))==$debut) 
		{
		 echo(utf8_encode("<option>".$element['NAME']."</option>"));
		 $i++;
		}
	}
echo "\n".'</options>';
 die();
?>
