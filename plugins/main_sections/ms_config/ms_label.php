<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Pierre LEMMET 2005
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================
//Modified on $Date: 2010 $$Author: Erwan Goalou

//UPDATE/DELETE
if ($protectedPost['Valid_modif_x']){
	$sql="DELETE FROM deploy WHERE name='%s'";
	$arg="label";
	$msg=$l->g(261);
	
	if (trim ($protectedPost['lbl']) != ""){
		$protectedPost["lbl"] = str_replace(array("\t","\n","\r"), array("","",""), $protectedPost["lbl"] );
		mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
		$sql="INSERT INTO deploy VALUES('%s','%s')";
		$arg=array('label',$protectedPost["lbl"]);
		$msg=$l->g(260);
	}
	
	mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
	msg_success($msg);
}
//Looking for the label
$reqL="SELECT content FROM deploy WHERE name='%s'";
$arg="label";
$resL=mysql2_query_secure($reqL,$_SESSION['OCS']["readServer"],$arg);
$val = mysqli_fetch_object($resL);
printEntete($l->g(263));
$form_name='admin_info';
echo "<br>";
echo open_form($form_name);

$name_field=array("lbl");
$tab_name= array($l->g(262).": ");
$type_field= array(1);		
$value_field=array($val->content);

$tab_typ_champ=show_field($name_field,$type_field,$value_field);
//$tab_typ_champ[0]['CONFIG']['SIZE']=100;
tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=true,$form_name='NO_FORM');

echo close_form();
	?>