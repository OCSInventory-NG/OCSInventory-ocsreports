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
		//echo "<table ALIGN = 'Center' class='onglet'><tr><td align =center>";
	
	
	if ($protectedPost['onglet'] == 2){	
		//sous onglets 
		if (!isset($protectedPost['cat']) or $protectedPost['cat'] == ''){
			$protectedPost['cat']="INFO_DEM";
		}
		$cat_value['INFO_DEM']='Inf. demandeur';
		$cat_value['INFO_PAQUET']='Inf. générales paquet';
		$cat_value['INFO_TECHNIQUE']='Inf. techniques paquet';
		$cat_value['INFO_VALID']='Inf. validation';
		//$cat_value['INFO_ECH']='Inf. échéance';
		onglet($cat_value,$form_name,"cat",7);
		
		$sql_service="select field,value FROM itmgmt_conf_values ";
		$resultSERV = mysql_query($sql_service, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
		while($item = mysql_fetch_object($resultSERV)){
			if (!isset($List[$item->field]) and $item->field!= "PERIM")
				$List[$item->field]['']='';
			$List[$item->field][$item->value]=$item->value;
		}
		/*********************BEGIN initialisation**********************/
		//NAME FIELD
		$name_field['INFO_DEM']=array("DEMANDEUR","SERVICE","USER_CREAT","INFO_DDE");
		//MUST COMPLETED
		$oblig_field['INFO_DEM']=array(1,2);
		//LBL FIELD
		$tab_name['INFO_DEM']= array("Demandeur: ","Service demandeur: ","Créateur du paquet","info complémentaires: ");
		//TYPE FIELD (0=>select;1=>textarea;2=>text;3=>text readonly;5=>chekbox)
		$type_field['INFO_DEM']= array(3,2,2,1);
		//DEFAULT VALUES
		$value_field['INFO_DEM']=array($_SESSION['OCS']["loggeduser"],$List['SERVICE'],$List['USER_CREAT'],$protectedPost['INFO_DDE']);
		//ADMIN CAN ADD VALUE TO THIS FIELD
		$add_values['INFO_DEM']=array(1,2);
		
		$name_field['INFO_PAQUET']= array("TYPE","NAME_TELEDEPLOY","PERIM","INFO_PACK","PERMANANT");	
		//$oblig_field['INFO_PAQUET']= array(1,2,3);;
		$tab_name['INFO_PAQUET']= array("Type de paquet: ","Nom: ","Périmètre touché: ","Description du paquet: ","Paquet Permanent? ");
		$type_field['INFO_PAQUET']= array(2,0,5,1,2);
		$value_field['INFO_PAQUET']=array($List['TYPE'],$protectedPost['NAME_TELEDEPLOY'],$List['PERIM'],$protectedPost['INFO_PACK'],$List['PERMANANT']);
		$add_values['INFO_PAQUET']=array(0,2,4);
		
		$name_field['INFO_TECHNIQUE']= array("PRIORITY","PDS","NB_FRAG","NOTIF_USER","REPORT_USER","REBOOT","EFFECTS");	
		//$oblig_field['INFO_TECHNIQUE']= $name_field['INFO_TECHNIQUE'];
		$tab_name['INFO_TECHNIQUE']= array("Priorité: ","Poids: ","Nombre fragment: ","Utilisateur notifié: ","Utilisateur peut reporter: ",
										   "Entraine un reboot","Effets attendu du paquet");
		$type_field['INFO_TECHNIQUE']= array(2,2,0,2,2,2,1);
		$value_field['INFO_TECHNIQUE']=array($List["PRIORITY"],$List["PDS"],$protectedPost['NB_FRAG'],$List["NOTIF_USER"],$List["REPORT_USER"],$List["REBOOT"],$protectedPost['EFFECTS']);
		$add_values['INFO_TECHNIQUE']=array(0,1,3,4,5);
		
		$name_field['INFO_VALID']= array("VALID_INSTALL","FONCT_INSTALL");	
		//$oblig_field['INFO_VALID']=$name_field['INFO_VALID'];
		$tab_name['INFO_VALID']= array("Actions de contrôle pour valider l'installation:","Points supplémentaires de vérification de bon fonctionnement:");
		$type_field['INFO_VALID']= array(1,1);
		$value_field['INFO_VALID']=array($protectedPost['VALID_INSTALL'],$protectedPost['FONCT_INSTALL']);
		
		/*********************END initialisation***********************/
		//traitement checkbox
		foreach ($type_field as $name_list=>$array_value){
			foreach ($array_value as $key=>$value){
				$name_of_field=$name_field[$name_list][$key];
				//checkbox
				if ($value == 5){
					foreach ($List[$name_of_field] as $name_check)
					array_push($name_field[$name_list],$name_of_field.'_'.$name_check);					
				}				
			}			
		}		
		
		//DDE POST
		if ($protectedPost['VALID']){
			foreach($name_field as $key=>$array_value){
				foreach($array_value as $id=>$value){
					if ($type_field[$key][$id] == 5){
						foreach ($List[$value] as $name_list){
							if (isset($protectedPost[$value.'_'.$name_list]))
								$tab_data[$value][$name_list]=$name_list;									
						}								
					}
					//value must be completed
					if (!isset($oblig_field[$key]) or in_array($id,$oblig_field[$key])){
						if((!isset($protectedPost[$value]) or $protectedPost[$value] == '')
					 	and !isset($tab_data[$value]) and $type_field[$key][$id] != 3 and isset($tab_name[$key][$id])){							
						$msg_empty.=mysql_real_escape_string($tab_name[$key][$id])."\\n";
						}					
					}									
				}			
			}	
			if (isset($msg_empty)){
				echo "<script>alert('Plusieurs champs doivent être complétés:\\n".$msg_empty."');</script>";
				unset($protectedPost['VALID']);
			}else{
				//INSERT
				
				
				
				
				
			}
			
		}

		
		
		
		
		/***************BEGIN Show fields*************/
		

		if ($name_field[$protectedPost['cat']]){			
			$tab_typ_champ=show_field($name_field[$protectedPost['cat']],$type_field[$protectedPost['cat']],$value_field[$protectedPost['cat']]);
			//add * before the lbl if this field must be completed
			foreach ($tab_name[$protectedPost['cat']] as $key=>$value){
				if (isset($oblig_field[$protectedPost['cat']])){
					if (in_array($key,$oblig_field[$protectedPost['cat']])){
						unset($tab_name[$protectedPost['cat']][$key]);
						$tab_name[$protectedPost['cat']][$key]='*'.$value;
					}						
				}else{
					unset($tab_name[$protectedPost['cat']][$key]);
					$tab_name[$protectedPost['cat']][$key]='*'.$value;
				}				
			}
			ksort($tab_name[$protectedPost['cat']]);
			
			if ($_SESSION['OCS']['CONFIGURATION']['ITSETMANAGEMENT'] == 'YES'){
				if (isset($add_values[$protectedPost['cat']])){
					foreach($add_values[$protectedPost['cat']] as $key=>$value)
						$tab_typ_champ[$value]['COMMENT_BEHING']="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_admin_management']."&head=1&value=".$name_field[$protectedPost['cat']][$value]."\",\"admin_management\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")>+++</a>";
				}				
			}			
		}else 
		echo "ERROR";
			/***************END Show fields*************/
		
		
		if (isset($msg))
		echo "<font color=green>".$msg."</font>";
		if (isset($tab_typ_champ)){
			$tab_hidden= hidden($protectedPost,$name_field[$protectedPost['cat']]);
			tab_modif_values($tab_name[$protectedPost['cat']],$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=false,$form_name='NO_FORM');
			echo "<input type=submit name='VALID' value='Enregistrer'>";
		}
	}elseif ($protectedPost['onglet'] == 4){
		if ($_SESSION['OCS']['CONFIGURATION']['ITSETMANAGEMENT'] == 'YES'){
			if (!isset($protectedPost['conf']))
				$protectedPost['conf']='GENERAL';
			//sous onglets 
			$conf_value['GENERAL']='Générale';
			$conf_value['GUI']='Interface';
//			$cat_value['INFO_TECHNIQUE']='Inf. techniques paquet';
//			$cat_value['INFO_VALID']='Inf. validation';
			//$cat_value['INFO_ECH']='Inf. échéance';
			onglet($conf_value,$form_name,"conf",7);
		
			require_once('require/function_config_generale.php');
			if ($protectedPost['Valid'] == $l->g(103)){
				$etat=verif_champ();
				if ($etat == "")
				$MAJ=update_default_value($protectedPost); //function in function_config_generale.php
				else{
					$msg="";
					foreach ($etat as $name=>$value){
						$msg.=$name." ".$l->g(759)." ".$value."<br>";
					}
				echo "<font color=RED ><center><b>".$msg."</b></center></font>";
					
				}	
			}
			if (!isset($protectedPost['conf']) or $protectedPost['conf']=="GENERAL")
				pageitsetmanagement($form_name);
			if ($protectedPost['conf']=="GUI"){
				$sql_service="select id,field,value,lbl FROM itmgmt_tab_values";
				$resultSERV = mysql_query($sql_service, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
				$List_tab[]='';
				while($item = mysql_fetch_object($resultSERV)){
					$List_tab[$item->id]=$item->lbl;
				}
				$name_field= array("TAB");	
				//$oblig_field['INFO_VALID']=$name_field['INFO_VALID'];
				$tab_name= array("Liste des onglets:");
				$type_field= array(2);
				$value_field=array($List_tab);
				if (isset($protectedPost['TAB']) and $protectedPost['TAB'] != 0){
					$sql_service="select id,lbl FROM itmgmt_fields where TAB='".$protectedPost['TAB']."'";
					$resultSERV = mysql_query($sql_service, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
					$List_fields[]='';
					while($item = mysql_fetch_object($resultSERV)){
						$List_fields[$item->id]=$item->lbl;
					}
					array_push($name_field,"FIELDS");
					array_push($tab_name,"Liste des champs:");
					array_push($type_field,2);
					array_push($value_field,$List_fields);
				}
				$tab_typ_champ=show_field($name_field,$type_field,$value_field);
				$tab_typ_champ[0]['COMMENT_BEHING']="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_admin_management']."&head=1&admin=tab&value=TAB\",\"admin_management\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")>+++</a>";
				$tab_typ_champ[0]['RELOAD']=$form_name;
				$tab_typ_champ[1]['RELOAD']=$form_name;
				$tab_typ_champ[1]['COMMENT_BEHING']="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_admin_management']."&head=1&admin=fields&value=".$protectedPost['TAB']."\",\"admin_management\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")>+++</a>";
				
				tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=false,$form_name='NO_FORM');
				if (isset($protectedPost['FIELDS']) and $protectedPost['FIELDS'] != 0){
					echo "<br>";	
					$sql_detailField="select type,field,lbl,must_completed FROM itmgmt_fields where id='".$protectedPost['FIELDS']."'";			
					$result_detailField = mysql_query($sql_detailField, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
					$item_detailField = mysql_fetch_object($result_detailField);
					$name_field=array('type','field','lbl','must_completed');
					$tab_name= array('Type de champ:','Nom du champ:','Libellé:','Obligatoire');
					$type_field= array(2,3,0,2);
					$value_field=array(array('TEXT','TEXTAREA','SELECT','Affiche la donnée','PASSWORD','CHECKBOX'),
										$item_detailField->field,
										$item_detailField->lbl,array('NON','OUI'));
					$tab_typ_champ=show_field($name_field,$type_field,$value_field);
					tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=true,$form_name='NO_FORM');
				}
			}
		}
	}	
	echo '</div>';	
	echo "</form>";
}else
	echo "<b><font color=red>La fonctionnalité 'IT_SET_MANAGEMENT' n'est pas activée. <br>Veuillez l'activer pour l'utiliser </font></b>";


?>