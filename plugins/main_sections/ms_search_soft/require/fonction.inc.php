<?php
//====================================================================================
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================


function remplirListe($input_name) 
{
//connecOCS();
//requete SQL avec filtre sur les logiciels des pc linux et Correctifs, mise a jour windows

$sql="SELECT DISTINCT softwares.NAME FROM softwares_name_cache softwares  WHERE  softwares.NAME NOT LIKE '%Correctif%' AND softwares.NAME NOT LIKE '%Mise a jour%' ORDER BY softwares.NAME";

//requete SQL sans filtre
//$sql= "SELECT DISTINCT softwares.NAME FROM softwares ORDER BY softwares.NAME";

$query=mysqli_query($_SESSION['OCS']["readServer"],$sql) or die ("erreur".mysqli_error($_SESSION['OCS']["readServer"]));
//echo '<option value=""></option>'; 

//remplit la liste deroulante
while($row=mysqli_fetch_array($query))
	{
	$name[$row['NAME']]=$row['NAME']; 
	}
echo 	show_modif($name,$input_name,2);
}

 
function creerTableau($var)  //$var est le $_post de mon script.php
{
	//connecOCS();
	echo "<br /><b><i>Vous avez choisi :<br />".$var."</i></b>";	
	$sql_version= "SELECT hardware.NAME AS 'hnom',hardware.IPADDR AS 'ip',hardware.WORKGROUP AS 'domaine', softwares.NAME AS 'snom', softwares.VERSION AS 'sversion',softwares.FOLDER as 'sfold' FROM hardware INNER JOIN softwares ON softwares.HARDWARE_ID =hardware.ID WHERE softwares.NAME='$var' ORDER BY softwares.VERSION";
	$query_version=mysqli_query($_SESSION['OCS']["readServer"],$sql_version);
	//echo "<style type='text/css'> table, th, tr, td, th {border:1px solid black;}td {padding-left: 3mm;padding-right: 3mm;} th {color:brown;}</style>";  //car pb de css avec l impression
	$html_data .="<table>\n";
	$html_data .="<tr><th>Nom du PC   </th><th>Nom du logiciel   </th><th>Version du logiciel </th><th>Repertoire</th><th>Adresse IP</th><th>Domaine</th></tr> ";
	while($row=mysqli_fetch_array($query_version,MYSQLI_ASSOC))
		{
		if($row['sfold']=="")
		{$row['sfold']="&nbsp";}
		if($row['sversion']=="")
		{$row['sversion']="&nbsp";}
		$html_data .="\n<tr><td style='color: blue'>".$row['hnom']." </td><td style='color : green'>".$row['snom']." </td><td style='color : red'> ".$row['sversion']."<td style='color : black'>".$row['sfold']."</td><td style='color : blue'>".$row['ip']."</td><td style='color: blue'>".$row['domaine']."</td></tr>";	
		}
	$html_data .="</table>";
	echo $html_data;
}





function csv($var)
{
//connecOCS();
$sql_version= "SELECT hardware.NAME AS 'hnom',softwares.NAME AS 'snom',softwares.VERSION AS 'sversion', softwares.FOLDER as 'sfold', hardware.IPADDR AS 'ip',hardware.WORKGROUP AS 'domaine' FROM hardware INNER JOIN softwares ON softwares.HARDWARE_ID =hardware.ID WHERE softwares.NAME='$var' ORDER BY softwares.VERSION";
$query_version=mysqli_query($_SESSION['OCS']["readServer"],$sql_version);
print "nom du PC;"."Nom du logiciel;"."Version du logiciel;"."Repertoire;"."Adresse IP;"."Domaine;"."\n\n\n";
while($row = mysqli_fetch_row($query_version))
	{
        print '"' . stripslashes(implode('";"',$row)) . "\"\n";
	}
exit;
}





?>
