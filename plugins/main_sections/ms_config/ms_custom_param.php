<?php
/*
 * Created on 25 janv. 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once('require/function_opt_param.php');
require_once('require/function_config_generale.php');
require_once('require/function_search.php');

$form_name="param_affect";
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
$list_id=multi_lot($form_name,$l->g(601));
	//print_r($list_id);
	if ($protectedPost['onglet'] == "" or !isset($protectedPost['onglet']))
	$protectedPost['onglet'] = $l->g(499);
	
	$def_onglets[$l->g(499)]=$l->g(499); //Serveur
	$def_onglets[$l->g(728)]=$l->g(728); //Inventaire
	$def_onglets[$l->g(512)]=$l->g(512); //T�l�d�ploiement
	$def_onglets[$l->g(312)]=$l->g(312); //ipdiscover
	

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
//print_r($tab_hadware_id);
		if (isset($list_hardware_id) or isset($tab_hadware_id)){

			 foreach ($protectedPost as $key => $value){
			 	if ($key != "systemid" and $key != "origine"){
				 	if ($value == "SERVER DEFAULT" or $value == "des")
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
				 	}elseif ($key == "IPDISCOVER" and $value != "des" and $value != "OFF"){
				 		insert($key,2,$value);	
				 	}
				 	
			 	}
		 	}
		 	$MAJ=$l->g(711);
		 	echo "<font color=green><center><b>".$MAJ.$add_lbl."</b></center></font>";
		}else
		echo "<script>alert('".$l->g(983)."')</script>";
	 }
	/*if ($protectedPost['origine'] == "machine"){
	$direction=	"index.php?".PAG_INDEX."=".$pages_refs['ms_computor']."&head=1&option=cd_configuration&systemid=".$protectedPost["systemid"];	
	}elseif ($protectedPost['origine'] == "group")
	$direction=	"index.php?".PAG_INDEX."=".$pages_refs['ms_group_show']."&popup=1&systemid=".$protectedPost["systemid"];
	else*/
//	$direction="index.php?redo=1".$_SESSION['OCS']["queryString"];	
	
	$sql_default_value="select NAME,IVALUE from config where NAME	in ('DOWNLOAD',
																'DOWNLOAD_CYCLE_LATENCY',
																'DOWNLOAD_PERIOD_LENGTH',
																'DOWNLOAD_FRAG_LATENCY',
																'DOWNLOAD_PERIOD_LATENCY',	
																'DOWNLOAD_TIMEOUT',
																'PROLOG_FREQ')";
	$result_default_value = mysql_query($sql_default_value, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
	while($default=mysql_fetch_array($result_default_value)) {
		$optdefault[$default["NAME"] ] = $default["IVALUE"];
	}	
	
	//not a sql query
	if (isset($protectedGet['origine']) and is_numeric($protectedGet['idchecked'])){
		//looking for value of systemid
		$sql_value_idhardware="select * from devices where name != 'DOWNLOAD' and hardware_id=".$protectedGet['idchecked'];
		$result_value = mysql_query($sql_value_idhardware, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
		while($value=mysql_fetch_array($result_value)) {
			$optvalue[$value["NAME"] ] = $value["IVALUE"];
			$optvalueTvalue[$value["NAME"]]=$value["TVALUE"];
		}
		$champ_ignored=0;
	}elseif($list_id){
		$tab_hadware_id=explode(",",$list_id);
		$champ_ignored=1;
	}
	
	
	/*if(isset($direction)){
	//link for return 
		echo "<br><center><a href='#' OnClick=\"window.location='".$direction."';\"><= ".$l->g(188)."</a></center>";
		
	}*/
	if ($list_id){
		onglet($def_onglets,$form_name,'onglet',7);
			echo "<table ALIGN = 'Center' class='onglet'><tr><td align =center><tr><td>";
		if ($protectedPost['onglet'] == $l->g(728)){
			include ('ms_custom_frequency.php');
		}
		if ($protectedPost['onglet'] == $l->g(499)){
				include ('ms_custom_prolog.php');
		}
		if ($protectedPost['onglet'] == $l->g(512)){
			include ('ms_custom_download.php');
		
		}
		if ($protectedPost['onglet'] == $l->g(312)){
			include ('ms_custom_ipdiscover.php');
		
		}
		/*if (isset($protectedPost['origine'])){
		echo "<input type='hidden' id='systemid' name='systemid' value='".$protectedPost['systemid']."'>";
			echo "<input type='hidden' id='origine' name='origine' value='".$protectedPost['origine']."'>";
		} */
		echo "</td></tr></table>";
	}
 echo "</form>";
?>
