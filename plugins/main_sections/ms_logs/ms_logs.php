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

 require_once('require/function_table_html.php');
 require_once('require/function_files.php');
 //d�finition des onglets
//$data_on['GUI_LOGS']="Logs de l'interface";
$protectedPost['onglet'] == "";
$form_name = "logs";
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
//onglet($data_on,$form_name,"onglet",2);
echo "<table cellspacing='5' width='80%' BORDER='0' ALIGN = 'Center' BGCOLOR='#C7D9F5' BORDERCOLOR='#9894B5'><tr><td colspan=10></td></tr>";
echo "<tr><td align=center>".$l->g(950)."</td><td align=center>".$l->g(951)."</td><td align=center>".$l->g(952)."</td><td align=center>".$l->g(953)."</td></tr>";
if ($protectedPost['onglet'] == 'GUI_LOGS' or $protectedPost['onglet'] == ""){
//	if ($_SESSION['OCS']['LOG_DIR'] == '')
//	$Directory="";
//	else
	$Directory=$_SESSION['OCS']['LOG_DIR']."/";
	$data=ScanDirectory($Directory,"csv");
	$i=0;
	while($data['name'][$i]){
		echo "<tr BGCOLOR='#f2f2f2'>";
		echo "<td align=center><a href='index.php?".PAG_INDEX."=".$pages_refs['ms_csv']."&no_header=1&log=".$data['name'][$i]."&rep=".$Directory."'>".$data['name'][$i]."</td>";
		echo "<td align=center>".$data['date_create'][$i]."</td>";
		echo "<td align=center>".$data['date_modif'][$i]."</td>";
		echo "<td align=center>".round($data['size'][$i]/1024,3)." ko</td>";
		echo "</tr>";		
	$i++;
	}
}
echo "</td></tr></table>";
echo "</tr></td></form>";



?>
