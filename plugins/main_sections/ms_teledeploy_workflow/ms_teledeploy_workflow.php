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
 * workflow for Teledeploy 
 * 
 */
if ((array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')){
	parse_str($protectedPost['ocs']['0'], $params);
	$protectedPost+=$params;
	ob_start();
	$ajax = true;
}
else{
	$ajax=false;
}
require_once('require/function_telediff_wk.php');
//TELEDIFF_WK
$activate=option_conf_activate('TELEDIFF_WK');
if ($activate){
	if(isset($protectedPost['MODIF']) and $protectedPost['MODIF'] != null){
		$protectedPost['onglet'] = 2;		
	}
	//print_r($protectedPost);
	$infos_status=list_status();
	if ($infos_status['NIV_BIS'] == ""){
		msg_warning($l->g(1089));
	}else{
		 //define tab
		$data_on[1]=$l->g(1072);
		$data_on[2]=$l->g(1073);
	}
	
	if ($_SESSION['OCS']['CONFIGURATION']['TELEDIFF_WK'] == 'YES')
	$data_on[4]=$l->g(107);
	
	
	$form_name = "admins";
	echo open_form($form_name);
	if (isset($data_on)){
		onglet($data_on,$form_name,"onglet",4);
		$table_name=$form_name;	
		
		echo '<div class="mlt_bordure" >';	
		if ($protectedPost['onglet'] == 2){			
			dde_form($form_name);
		}elseif ($protectedPost['onglet'] == 4){
			dde_conf($form_name);
		}elseif ($protectedPost['onglet'] == 1){
			if ($ajax){
				ob_end_clean();
				$protectedPost['ajax_req']=true;
			}
			dde_show($form_name);
			if($ajax){
				ob_start();
			}
			
		}
		echo '</div>';	
	}else
		msg_info($l->g(1187));
	echo close_form();
}else
	msg_info($l->g(1075));

if ($ajax){
	ob_end_clean();
}
?>