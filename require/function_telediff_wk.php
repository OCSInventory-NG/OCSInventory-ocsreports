<?php
function hidden($protectedPost,$name_field){
	foreach ($protectedPost as $key=>$value){
			if ($key != 'cat' 
				and $key != 'old_cat' 
				and $key != 'onglet' 
				and $key != 'old_onglet'
				and !in_array($key ,$name_field) ){
				$tab_hidden[$key]=$value;
			}
		}
	return $tab_hidden;	
}

function define_lbl($LBL,$DEFAULT_FIELD){
	global $l;
	if ($DEFAULT_FIELD == 1)
		$lbl=$l->g($LBL);
	else
		$lbl=$LBL;
	return $lbl;
}

function dde_form($form_name){
	global $l,$protectedPost,$protectedGet,$pages_refs;
		//sous onglets 
		$sql_TAB="select VALUE,LBL,ID,DEFAULT_FIELD from itmgmt_tab_values where FIELD = 'TAB'";
		$result_TAB = mysql_query($sql_TAB, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
		while($item = mysql_fetch_object($result_TAB)){
			if (!isset($protectedPost['cat']) or $protectedPost['cat'] == ''){
				$protectedPost['cat']=$item->ID;
			}
			$lbl=define_lbl($item->LBL,$item->DEFAULT_FIELD);
			$cat_value[$item->ID]=$lbl;
		}
		onglet($cat_value,$form_name,"cat",7);
		$sql_fields="select TAB,FIELD,TYPE,LBL,MUST_COMPLETED,ID,VALUE,DEFAULT_FIELD from itmgmt_fields";
		$result_fields = mysql_query($sql_fields, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
		while($item = mysql_fetch_object($result_fields)){
			$lbl=define_lbl($item->LBL,$item->DEFAULT_FIELD);
			$name_field[$item->TAB][$item->ID]=$item->ID;
			$oblig_field[$item->TAB][$item->ID]=$item->MUST_COMPLETED;
			$tab_name[$item->TAB][$item->ID]=$lbl;
			$type_field[$item->TAB][$item->ID]=$item->TYPE;
			//si une valeur par défaut est donnée au champ 
			//mais que l'on n'est dans un champ type multi lignes
			if ($item->VALUE != '' and $item->TYPE != 6){
				if (isset($_SESSION['OCS'][$item->VALUE]))
					$value=$_SESSION['OCS'][$item->VALUE];
				else
					$value=$item->VALUE;
			}else
				$value=$protectedPost[$item->ID];

				//cas du champ multi ligne => on récupère le nbre de champ a afficher
			if ($item->TYPE == 6 ){
				if (!is_numeric($item->VALUE))
				$nb_fields[$item->TAB][$item->ID]=6;	
				else
				$nb_fields[$item->TAB][$item->ID]=$item->VALUE;	
				
				//ajout des champs a prendre en compte quand on passe d'onglet en onglet
				$k=0;
				while ($k<$nb_fields[$item->TAB][$item->ID]){
					$name_field[$item->TAB][$item->ID.'_'.$k]=$item->ID.'_'.$k;		
					$k++;
				}
			}
			$value_field[$item->TAB][$item->ID]=$value;
			if ($item->TYPE == 2 or $item->TYPE == 5){
				unset($value_field[$item->TAB][$item->ID]);
				$add_values_admin[$item->TAB][$item->ID]=$item->ID;
				//echo $item->TYPE."<br>";
				$sql_service="select field,value,id FROM itmgmt_conf_values where field=".$item->ID;
				//echo $sql_service."<br>";
				$resultSERV = mysql_query($sql_service, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
				while($item_conf_values = mysql_fetch_object($resultSERV)){
					if (!isset($List[$item_conf_values->field]) and $item->TYPE == 2)
						$value_field[$item->TAB][$item->ID]['']='';
					if ($item->TYPE == 5){
						$name_field[$item->TAB][$item->ID.'_'.$item_conf_values->id]=$item->ID.'_'.$item_conf_values->id;	

					}
					$value_field[$item->TAB][$item->ID][$item_conf_values->id]=$item_conf_values->value;

				}
			}
		}	
		/*********************END initialisation***********************/

		//DDE POST
		if ($protectedPost['SUBMIT_FORM'] == "SUBMIT_FORM" ){
			//print_r($protectedPost);
			foreach($oblig_field as $key=>$array_value){
								
				foreach($array_value as $id=>$value){
					//si le post n'existe pas ou qu'il est vide mais que ce champ est obligatoire
					if ((!isset($protectedPost[$id]) or $protectedPost[$id] == '')
						and $value == 1){
							
							//cas of checkbox
							unset($check);
							if ($type_field[$key][$id] == 5){ 			
								foreach($name_field[$key] as $id_check){
									if ($protectedPost[$id_check])
									$check=true;									
								}
							}
							//traitement des multi listes
							if ($type_field[$key][$id] == 6){
								foreach ($protectedPost as $key2=>$value2){
									if (strstr($key2,$id.'_') and $value2 != ''){
										$value_list[$id].=$value2.'**&&&**';
										$check=true;	
									}
								}
							}
									
							if (!isset($check)){
								$msg_empty.=mysql_real_escape_string($tab_name[$key][$id])."\\n"; 			
							}	
						}						
					
												
				}			
			}	
			//print_r($value_list);
			if (isset($msg_empty)){
				echo "<script>alert('Plusieurs champs doivent être complétés:\\n".$msg_empty."');</script>";
				unset($protectedPost['VALID']);
			}else{
				//print_r($protectedPost);
				//INSERT
				$sql_insert="INSERT INTO itmgmt_pack (STATUT,LOGIN_USER,GROUP_USER,Q_DATE,";
				$list_value="'";
				foreach ($protectedPost as $key=>$value){
					//cas of checkbox
					$checkbox=explode('_',$key);
					if (is_numeric($checkbox[0]) and is_numeric($checkbox[1]) and !isset($value_list[$checkbox[0]])){
						$check_on[$checkbox[0]].=$checkbox[1].";";					
					}elseif (isset($value_list[$checkbox[0]]) and $value_list[$checkbox[0]]!=''){
						$sql_insert.= "fields_".$checkbox[0].",";
						$list_value.= $value_list[$checkbox[0]]."','";
						$value_list[$checkbox[0]]='';
					}
					if (is_numeric($key)){
						$sql_insert.= "fields_".$key.",";
						$list_value.= $value."','";
					}
				}
				if (isset($check_on)){
					foreach ($check_on as $key=>$value){
						$sql_insert.= "fields_".$key.",";
						$list_value.= $value."','";
						
					}
					
				}
				$sql_insert= substr($sql_insert,0,-1);
				$list_value= substr($list_value,0,-2);
				$sql_insert.= ") VALUES (2,'".$_SESSION['OCS']['loggeduser']."','".$_SESSION['OCS']['user_group']."',UNIX_TIMESTAMP(),".$list_value.")";
				mysql_query($sql_insert, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));				
				$tab=$protectedPost['cat'];
				unset($protectedPost);
				$protectedPost['cat']=$tab;
				unset($_SESSION['OCS']['DATA_CACHE'],$_SESSION['OCS']['NUM_ROW']);
			}
			
		}

		
		
		
		
		/***************BEGIN Show fields*************/
		

		if ($name_field[$protectedPost['cat']]){			
			$tab_typ_champ=show_field($name_field[$protectedPost['cat']],$type_field[$protectedPost['cat']],$value_field[$protectedPost['cat']]);
			
			if (isset($nb_fields[$protectedPost['cat']])){
				foreach ($nb_fields[$protectedPost['cat']] as $key=>$value){
						$tab_typ_champ[$key]['CONFIG']['NB_FIELD']=$value;
				}
			}
			
			
			
			//add * before the lbl if this field must be completed
			foreach ($tab_name[$protectedPost['cat']] as $key=>$value){
				if ($oblig_field[$protectedPost['cat']][$key] == 1){
					$tab_name[$protectedPost['cat']][$key]='*'.$value;
				}
			}
			ksort($tab_name[$protectedPost['cat']]);
			//print_r($add_values[$protectedPost['cat']]);
			if ($_SESSION['OCS']['CONFIGURATION']['TELEDIFF_WK'] == 'YES'){
				if (isset($add_values_admin[$protectedPost['cat']])){
					foreach($add_values_admin[$protectedPost['cat']] as $key=>$value)
						$tab_typ_champ[$value]['COMMENT_BEHING']="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_admin_management']."&head=1&value=".$value."\",\"admin_management\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")>+++</a>";
				}				
			}	
		}else 
		echo "NOT DEFINE";
			/***************END Show fields*************/
		
		
		if (isset($msg))
		echo "<font color=green>".$msg."</font>";
		if (isset($tab_typ_champ)){
			$tab_hidden= hidden($protectedPost,$name_field[$protectedPost['cat']]);
			tab_modif_values($tab_name[$protectedPost['cat']],$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=false,'NO_FORM');
			echo "<input type=button name='VALID' id='VALID' value='Enregistrer' OnClick='pag(\"SUBMIT_FORM\",\"SUBMIT_FORM\",\"".$form_name."\");'>";
			echo "<input type='hidden' name='SUBMIT_FORM' id='SUBMIT_FORM' value=''>";
		}
}


function dde_conf($form_name){
	global $l,$protectedPost,$protectedGet,$pages_refs;
if ($_SESSION['OCS']['CONFIGURATION']['TELEDIFF_WK'] == 'YES'){
			if (!isset($protectedPost['conf']))
				$protectedPost['conf']='GENERAL';
			//sous onglets 
			$conf_value['GENERAL']='Générale';
			$conf_value['GUI']='Interface';
			//$conf_value['ADMIN']='Administration';
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
				pageTELEDIFF_WK($form_name);
			if ($protectedPost['conf']=="GUI"){
				//mise a jour des données demandée par l'utilisateur
				if( $protectedPost['Valid_fields_x'] != "" ) {
					//si la mise a jour est limitée à certain champs
					if (isset($protectedPost['DEFAULT_FIELD'])){
						$fields=explode(',',$protectedPost['DEFAULT_FIELD']);					
					}else
						$fields=array('type','field','lbl','must_completed','value');
					
					//création de la requête
					$sql_update='UPDATE itmgmt_fields 
										set ';
					foreach ($fields as $key=>$value){
						$sql_update.= $value."='".$protectedPost[$value]."' ,";						
					}
					$sql_update=substr($sql_update,0,-1)."where id='".$protectedPost['FIELDS']."'";
					mysql_query($sql_update,$_SESSION['OCS']["writeServer"]);
				}
				
				
				
				$sql_service="select id,field,value,lbl,default_field FROM itmgmt_tab_values";
				$resultSERV = mysql_query($sql_service, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
				$List_tab[]='';
				while($item = mysql_fetch_object($resultSERV)){
					$lbl=define_lbl($item->lbl,$item->default_field);
					$List_tab[$item->id]=$lbl;
				}
				$name_field= array("TAB");	
				//$oblig_field['INFO_VALID']=$name_field['INFO_VALID'];
				$tab_name= array("Liste des onglets:");
				$type_field= array(2);
				$value_field=array($List_tab);
				if (isset($protectedPost['TAB']) and $protectedPost['TAB'] != 0){
					$sql_service="select id,lbl,default_field FROM itmgmt_fields where TAB='".$protectedPost['TAB']."'";
					$resultSERV = mysql_query($sql_service, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
					$List_fields[]='';
					while($item = mysql_fetch_object($resultSERV)){
						$lbl=define_lbl($item->lbl,$item->default_field);
						$List_fields[$item->id]=$lbl;
						$default_field[$item->id]=$item->default_field;
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
					$list_type=array('TEXT','TEXTAREA','SELECT','Affiche la donnée','PASSWORD','CHECKBOX','LISTE');
					$yes_no=array('NON','OUI');
					$sql_detailField="select type,field,lbl,must_completed,value FROM itmgmt_fields where id='".$protectedPost['FIELDS']."'";			
					$result_detailField = mysql_query($sql_detailField, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
					$item_detailField = mysql_fetch_object($result_detailField);
					$protectedPost['type']=$item_detailField->type;
					$protectedPost['must_completed']=$item_detailField->must_completed;

					$name_field=array('type','field','lbl','must_completed','value');
					$tab_name= array('Type de champ:','Nom du champ:','Libellé:','Obligatoire:','Valeur par défaut:');					
					
					if ($default_field[$protectedPost['FIELDS']])	{
						$title= "Ce champ n'est pas modifiable";
						//$showbutton=false;
						$type_field= array(3,3,3,3,0,7);
						$value_field=array($list_type[$item_detailField->type],
										$item_detailField->field,
										$l->g($item_detailField->lbl),$yes_no[$item_detailField->must_completed],$item_detailField->value,'value');
						$name_field[5]='DEFAULT_FIELD';
						$tab_name[5]='';
					}else{
						$title="";
						//$showbutton=true;
						$type_field= array(2,0,0,2,0);
						$value_field=array($list_type,
										$item_detailField->field,
										$item_detailField->lbl,$yes_no,$item_detailField->value);	
					}
					
					
					
					
					$tab_typ_champ=show_field($name_field,$type_field,$value_field);
					tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title,$comment="",$name_button="fields",$showbutton=true,$form_name='NO_FORM');
				}
				
			}
		}	
	
}

function dde_show($form_name){
		global $l,$protectedPost,$protectedGet,$pages_refs;
		
		//suppression d'une demande
		if(isset($protectedPost['SUP_PROF'])) {
			mysql_query( "UPDATE itmgmt_pack 
										set STATUT='1'
										where ID='".$protectedPost['SUP_PROF']."'", $_SESSION['OCS']["writeServer"]  );
			
			
		}
		
		
		
		$table_name='LIST_DDE';
		
		//recherche des champs qui ont été créés
		$sql_fields="select lbl,id,type,field from itmgmt_fields";
		$resultfields = mysql_query($sql_fields, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
		$id_field1=0;
		$id_field2=0;
		$default_fields=array();
		while($item = mysql_fetch_object($resultfields)){
			$name_field[$item->id]=$item->field;
			$field='fields_'.$item->id;
			if ($item->type == '2' or $item->type == '5'){
				$array_value_fields[$id_field1]=$field.".VALUE as ".$field;
				$array_fields[$id_field1]=$field;					
				$id_field1++;
			}else{
				$else_fields[$id_field2]="itmgmt_pack.".$field;
				$id_field2++;
			}
			if (count($default_fields)<5)
			$default_fields[$field]=$field;
			if ($l->g($item->lbl))
			$tab_options['LBL'][$field]=$l->g($item->lbl);
			else
			$tab_options['LBL'][$field]=$item->lbl;			
			$list_fields[$field]=$field;
		}
		//recherche des valeurs des différents statuts
		$sql_statut="select id,lbl from itmgmt_statut_request";
		$resultfields = mysql_query($sql_statut, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
		while($item = mysql_fetch_object($resultfields)){
			$statut[$item->id]=$item->lbl;		
		}

		$tab_options['REPLACE_VALUE']['STATUT']=$statut;
		

		$list_col_cant_del['STATUT']='STATUT';
		$default_fields['STATUT']='STATUT';
		$list_fields['STATUT']='STATUT';
		$list_col_cant_del['Q_DATE']='Q_DATE';
		$default_fields['Q_DATE']='Q_DATE';
		$list_fields['Q_DATE']='Q_DATE';
		$list_col_cant_del['SUP']='SUP';
		$default_fields['SUP']='SUP';
		$list_fields['SUP']='ID';
		$tab_options['LBL']['Q_DATE']="Date dde";
		$sql="select itmgmt_pack.ID,STATUT,FROM_UNIXTIME(Q_DATE) as Q_DATE,";
		
		if (isset($array_value_fields)){
			$sql.=implode(', ',$array_value_fields);
			$bool_select=true;
		}
		print_r($else_fields);
		if (isset($else_fields)){
			if ($bool_select)
				$sql.=", ";
			$sql.=implode(', ',$else_fields);	
			$bool_normal=true;
		}
		
		if (!$bool_normal and !$bool_select)
			$sql.=" * ";		
		$sql.=" from itmgmt_pack ";
	
		$i=0;
		while ($array_fields[$i]){
			$sql.= " left join itmgmt_conf_values ".$array_fields[$i]." on itmgmt_pack.".$array_fields[$i]."=".$array_fields[$i].".ID ";
			$i++;
		}
		echo $_SESSION['OCS']['RESTRICTION']['TELEDIFF_WK'];
	//	if ($_SESSION['OCS']['RESTRICTION']['TELEDIFF_WK'] == 'LOGIN')
echo $sql;
//print_r($name_field);
		tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql,$form_name,100,$tab_options);

}



?>