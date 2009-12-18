<?php
/*
 * formulaire de demande de création de paquet
 * 
 */
require_once('require/function_it_set_management.php');
//IT SET MANAGEMENT
$sql_It_set="select IVALUE from config where name='IT_SET_MANAGEMENT'";
$result_It_set = mysql_query($sql_It_set, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
while($value=mysql_fetch_array($result_It_set)){
	if ($value['IVALUE'] == 1)
		$activate=1;
	else
		$activate=0;	
}

if ($activate){
	 //d�finition des onglets
	$data_on[1]="Suivi des demandes";
	$data_on[2]="Faire une demande";
	$data_on[3]="Traiter une demande";
	if ($_SESSION['OCS']['CONFIGURATION']['ITSETMANAGEMENT'] == 'YES')
	$data_on[4]="Configuration";
	
	
	$form_name = "admins";
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	onglet($data_on,$form_name,"onglet",4);
	$table_name=$form_name;	
	
	echo '<div class="mlt_bordure" >';	
	if ($protectedPost['onglet'] == 2){	
		dde_form($form_name);
	}elseif ($protectedPost['onglet'] == 4){
		dde_conf($form_name);
		
	}elseif ($protectedPost['onglet'] == 1 or !isset($protectedPost['onglet'])){
		dde_show($form_name);
	}
	echo '</div>';	
	echo "</form>";
}else
	echo "<b><font color=red>La fonctionnalité 'IT_SET_MANAGEMENT' n'est pas activée. <br>Veuillez l'activer pour l'utiliser </font></b>";


?>