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

require_once('require/function_search.php');
require_once('require/function_computers.php');
PrintEnTete($l->g(985));
$form_name="del_affect";
echo open_form($form_name);
echo "<div align=center>";
$list_id=multi_lot($form_name,$l->g(601));
if ($protectedPost['SUP'] != '' and isset($protectedPost['SUP'])){
		$array_id=explode(',',$list_id);		
	//$i=0;
	foreach ($array_id as $key=>$hardware_id){
		deleteDid($hardware_id);
		//echo $hardware_id."<br>";
		
	}
}
if ($list_id){
	echo "<br><br><input type='submit' value=\"".$l->g(122)."\" name='SUP'>";
}
echo "</div>";//<input type=submit value='Supprimer TOUTES les machines?' name='delete'>
echo close_form();	
?>
