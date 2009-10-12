<?php
require_once('require/function_search.php');
PrintEnTete($l->g(985));
$form_name="del_affect";
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''><div align=center>";
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
echo "</div></form>";//<input type=submit value='Supprimer TOUTES les machines?' name='delete'>

?>
