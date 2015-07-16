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

require_once('require/function_opt_param.php');
require_once('require/function_config_generale.php');
require_once('require/function_search.php');

$form_name="param_affect";
echo open_form($form_name);
$list_id=multi_lot($form_name,$l->g(601));
	/*if ($protectedPost['onglet'] == "" or !isset($protectedPost['onglet']))
	$protectedPost['onglet'] = $l->g(499);*/
	
	$def_onglets['SERV']=$l->g(499); //Serveur
	$def_onglets['INV']=$l->g(728); //Inventaire
	$def_onglets['TELE']=$l->g(512); //Télédéploiement
	$def_onglets['RSX']=$l->g(1198); //ipdiscover
	

	//update values	
	if ($protectedPost['Valid']==$l->g(103)){
		if($list_id){
			//more then one value
			if (strstr($list_id,',') != ""){
				$tab_hadware_id=explode(",",$list_id);
				$add_lbl=" (".count($tab_hadware_id)." ".$l->g(652).")";
			}else
				$list_hardware_id=$list_id;
		}
		if (isset($list_hardware_id) or isset($tab_hadware_id)){
			 foreach ($protectedPost as $key => $value){
			 	if ($key != "systemid" and $key != "origine"){
				 	if ($value == "SERVER DEFAULT" or $value == "des" or trim($value) == "")
				 		erase($key);
				 	elseif ($value == "CUSTOM"){
				 		insert($key,$protectedPost[$key.'_edit']);	 	
				 	}
				 	elseif ($value == "ALWAYS"){
				 		insert($key,0);	 
				 	}
					elseif ($value == "NEVER"){
				 		insert($key,-1);	 
				 	} 
				 	elseif ($value == "ON"){
				 		insert($key,1);	 
				 	} 
				 	elseif ($value == "OFF"){
				 		insert($key,0);	 
				 	}elseif (($key == "IPDISCOVER" and $value != "des" and $value != "OFF") or ($key == "SNMP_NETWORK") ){
				 		insert($key,2,$value);	
				 	}
				 	
			 	}
		 	}
		 	$MAJ=$l->g(711);
		 	msg_success($MAJ.$add_lbl);
		 	if (isset($protectedGet['origine']) and $protectedGet['origine'] == 'machine'){
				$form_to_reload='config_mach';
		 	}elseif (isset($protectedGet['origine']) and $protectedGet['origine'] == 'group'){
		 		$form_to_reload='config_group';
		 	}
			if (isset($form_to_reload))
				echo "<script language='javascript'> window.opener.document.".$form_to_reload.".submit();</script>";
				
		 	
		 	
		}else
		echo "<script>alert('".$l->g(983)."')</script>";
	 }
	
	$default=look_config_default_values(array('DOWNLOAD','DOWNLOAD_CYCLE_LATENCY','DOWNLOAD_PERIOD_LENGTH',
											  'DOWNLOAD_FRAG_LATENCY','DOWNLOAD_PERIOD_LATENCY',	
											  'DOWNLOAD_TIMEOUT','PROLOG_FREQ'));
	$optdefault = $default["ivalue"];

	
	//not a sql query
	if (isset($protectedGet['origine']) and is_numeric($protectedGet['idchecked'])){
		//looking for value of systemid
		$sql_value_idhardware="select NAME,IVALUE,TVALUE from devices where name != 'DOWNLOAD' and hardware_id=%s";
		$arg_value_idhardware=$protectedGet['idchecked'];
		$result_value = mysql2_query_secure($sql_value_idhardware, $_SESSION['OCS']["readServer"],$arg_value_idhardware);
		while($value=mysqli_fetch_array($result_value)) {
			$optvalue[$value["NAME"] ] = $value["IVALUE"];
			$optvalueTvalue[$value["NAME"]]=$value["TVALUE"];
		}
		$champ_ignored=0;
	}elseif($list_id){
		$tab_hadware_id=explode(",",$list_id);
		$champ_ignored=1;
	}
	

	if ($list_id){
		onglet($def_onglets,$form_name,'onglet',7);
		echo '<div class="mlt_bordure" >';
		if ($protectedPost['onglet'] == 'INV'){
			include ('ms_custom_frequency.php');
		}
		if ($protectedPost['onglet'] == 'SERV'){
				include ('ms_custom_prolog.php');
		}
		if ($protectedPost['onglet'] == 'TELE'){
			include ('ms_custom_download.php');
		
		}
		if ($protectedPost['onglet'] == 'RSX'){
			include ('ms_custom_ipdiscover.php');
		
		}
		echo "</div>";
	}
	
echo close_form();
?>
