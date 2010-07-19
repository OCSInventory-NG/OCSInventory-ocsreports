<?php
require_once('require/function_config_generale.php');
function hidden($name_value_hidden,$name_field){
	foreach ($name_value_hidden as $key=>$value){
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






function list_status($allactif=1){
		$sql_service="select id,name,lbl,actif FROM downloadwk_statut_request";
		if ($allactif==1){
			$sql_service.=" WHERE (ACTIF is null or ACTIF=1)";
		}
		//echo $sql_id_STATUS;
		$resultSERV = mysql2_query_secure($sql_service, $_SESSION['OCS']["readServer"]);
		$List_stat[]='';
		while($item = mysql_fetch_object($resultSERV)){
			$id[]=$item->id;
			$List_stat[$item->id]=$item->name.'('.$item->lbl.')';
			$List_stat_bis[$item->id]=$item->lbl;
			if ($item->actif == ''){
				$act=1;
			}else
				$act=$item->actif;
			$actif[$item->id]=$act;
			$niv[$item->id]=$item->name;
			$niv_bis[$item->name]=$item->name.'('.$item->lbl.')';
			//$niv_ter[$item->id]=$item->name.'('.$item->lbl.')';
		}	
		return array('STAT'=>$List_stat,'ACTIF'=>$actif,'NIV'=>$niv,'NIV_BIS'=>$niv_bis,'STAT_BIS'=>$List_stat_bis,'ID_TAB'=>$id);
}


function define_lbl($LBL,$DEFAULT_FIELD){
	global $l;
	if ($DEFAULT_FIELD == 1)
		$lbl=$l->g($LBL);
	else
		$lbl=$LBL;
	return $lbl;
}

function find_dde_by_status($status){
	global $l;
	//echo $status;
	$status_array=list_status(false);
	if (in_array($status,$status_array['NIV'])){
		$status=array_search($status, $status_array['NIV']); 
		$id_status=find_id_field();
		$sql="select id from downloadwk_pack where fields_".$id_status['STATUS']->id."='".$status."'";
		$result = mysql_query($sql, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
		while ($item_values = mysql_fetch_object($result))
			$list_id_dde[]=$item_values->id;
			//echo $sql;
		return $list_id_dde;
	}elseif ($_SESSION['OCS']['DEBUG'])
	echo "<br><font color=red><b>" . $l->g(1076) . "</b></font><br>";
	
}


function info_dde($id_dde){
	if (is_array($id_dde)){
		$list_dde=implode("','",$id_dde);
		$sql="select * FROM downloadwk_pack where id in ('".$list_dde."')";
	//	echo $sql;
		$result = mysql_query($sql, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
		while ($item_values = mysql_fetch_object($result)){
		//	print_r($item_values);
			$tab_info_dde[$item_values->ID]=$item_values;
		}
		//	print_r($tab_info_dde);
		return 	$tab_info_dde;
	}	
	if (is_numeric($id_dde)){
		$sql="select * FROM downloadwk_pack where id='".$id_dde."'";
		$result = mysql_query($sql, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
		$item_values = mysql_fetch_object($result);
		return $item_values;	
	}else
		return false;
}

function find_id_field($name_field=array('STATUS'),$list_fields='id'){
		$sql_id_="select ".$list_fields.",FIELD from downloadwk_fields where FIELD in ('".implode("','",$name_field)."')";
		$result_id_ = mysql_query($sql_id_, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
		while ($item_id_ = mysql_fetch_object($result_id_))
		$return[$item_id_->FIELD]=$item_id_;
		return $return;
}


function dde_form($form_name){
	global $l,$protectedPost,$protectedGet,$pages_refs;
		
	//cas of dde modification
		if (isset($protectedPost['MODIF'])){
			$item_modif_values=info_dde($protectedPost['MODIF']);			
			if (is_object($item_modif_values)){
				foreach ($item_modif_values as $key=>$value){
					if (substr_count($key, 'fields_')){
						if (substr_count($value,'**&&&**')){
							$array_value=explode('**&&&**',$value);
							foreach ($array_value as $nb=>$value_tab){
								//if this field is readonly
								//we sow all values 
								$value_list[substr($key,7)].=$value_tab.". ";
								$protectedPost[substr($key,7).'_'.$nb]=$value_tab;
							}
						}elseif (substr_count($value,'**check&check**')){
							$array_value=explode('**check&check**',$value);
							foreach ($array_value as $nb=>$value_tab){
								//if this field is readonly
								//we sow all values 
								$value_list[substr($key,7)].=$value_tab.". ";
								$protectedPost[substr($key,7).'_'.$value_tab]='on';
							}
							
						}else
						$protectedPost[substr($key,7)]=$value;
					}elseif ($key == 'ID'){
						$protectedPost['OLD_MODIF']=$value;						
					}elseif ($key == 'STATUT'){
						$protectedPost['STATUS']=$value;		
					}elseif ($key == 'LOGIN_USER'){
						$protectedPost['LOGIN_USER']=$value;
					}
				}
				unset($protectedPost['MODIF']);
			}
			if ($protectedPost['LOGIN_USER'] != $_SESSION['OCS']['loggeduser'] and is_array($value_list)){
				foreach ($value_list as $key => $value){
					$protectedPost[$key]=$value;
				}
				
			}
			
		}
		//search all tab of this form 
		$sql_TAB="select VALUE,LBL,ID,DEFAULT_FIELD from downloadwk_tab_values where FIELD = 'TAB'";
		$result_TAB = mysql_query($sql_TAB, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
		while($item = mysql_fetch_object($result_TAB)){
			if (!isset($protectedPost['cat']) or $protectedPost['cat'] == ''){
				$protectedPost['cat']=$item->ID;
			}
			$lbl=define_lbl($item->LBL,$item->DEFAULT_FIELD);
			$cat_value[$item->ID]=$lbl;
		}
		
		//show all tab
		onglet($cat_value,$form_name,"cat",7);
		
		//search all fields of the form
		$sql_fields="select TAB,FIELD,TYPE,LBL,MUST_COMPLETED,ID,VALUE,DEFAULT_FIELD,RESTRICTED,LINK_STATUS from downloadwk_fields";
		$result_fields = mysql_query($sql_fields, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
		while($item = mysql_fetch_object($result_fields)){
			unset($value);
			if (($item->RESTRICTED == 1 and $_SESSION['OCS']['RESTRICTION']['TELEDIFF_WK_FIELDS'] == 'YES')
				 or (isset($protectedPost['OLD_MODIF']) 
				 		and $item->TYPE != 8 and $item->TYPE != 10 
				 		and $protectedPost['LOGIN_USER'] != $_SESSION['OCS']['loggeduser']
				 		)
				 ){
				 
				$grise=3;
				//cas of status
				if ($item->FIELD == "STATUS"){
					//It's the only field witch admin can modify when request is create by someone else
					if ($_SESSION['OCS']['RESTRICTION']['TELEDIFF_WK_FIELDS'] == 'YES'){
						$val_field=$item->VALUE;
						$sql_service="select id, lbl as value from downloadwk_statut_request where id='".$val_field."'";
						$resultSERV = mysql_query($sql_service, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
						$item_conf_values = mysql_fetch_object($resultSERV);	
						$value=$item_conf_values->value;
					}else
					$grise=$item->TYPE;
				}else{
					//cas of list and checkbox
					
					if (isset($protectedPost[$item->ID])){
						$list_id=explode('.',$protectedPost[$item->ID]);
						$p=0;
						$ok_num=true;
						while ($list_id[$p]){
							if (!is_numeric($list_id[$p]) and trim($list_id[$p]) != "")
							$ok_num=false;
							if (trim($list_id[$p]) == ""){
								unset($list_id[$p]);
							}
							$p++;
						}
						if ($ok_num and implode(',',$list_id)){
							$sql_service="select field, value from downloadwk_conf_values where id in (%s)";
							$arg_service=array(implode(',',$list_id));
							$resultSERV = mysql2_query_secure($sql_service, $_SESSION['OCS']["readServer"],$arg_service);
							$value="";
							while ($item_conf_values = mysql_fetch_object($resultSERV)){
								$value.=$item_conf_values->value.' ';
							}
						}
						//	echo $value;
					}
					if(!isset($value) or $value == "")
						$value=$protectedPost[$item->ID];
					if ($value == ""){
						$value=$value_list[$item->ID];	
					}
					
					
					/*$val_field=$item->ID;
					$sql_service="select field, value from downloadwk_conf_values where field='".$val_field."'";
					echo $sql_service."<br>";
					$resultSERV = mysql_query($sql_service, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
					$item_conf_values = mysql_fetch_object($resultSERV);
					if (isset($item_conf_values))	
					$value=$item_conf_values->value;*/
				}
			}//cas ou on ne peut pas ajouter des fichiers à une demande
			elseif(isset($protectedPost['OLD_MODIF']) and $item->TYPE == 8 
				and $protectedPost['LOGIN_USER'] != $_SESSION['OCS']['loggeduser']){				
				$grise=9;				
			}else
				$grise=$item->TYPE;
			
				
			//echo $grise."<br>";
			//	print_r($item);
			//si le champs n'est pas restraint, on l'affiche
			//$item->LINK_STATUS
			if (!(is_numeric($item->LINK_STATUS) and $protectedPost['STATUS']!= $item->LINK_STATUS and $item->LINK_STATUS != 0)){				
				$lbl=define_lbl($item->LBL,$item->DEFAULT_FIELD);
				$name_field[$item->TAB][$item->ID]=$item->ID;
				$oblig_field[$item->TAB][$item->ID]=$item->MUST_COMPLETED;
				$tab_name[$item->TAB][$item->ID]=$lbl;
				$type_field[$item->TAB][$item->ID]=$grise;
				$type_field_temp[$item->ID]=$grise;
				$restricted_field[$item->TAB][$item->ID]=$item->RESTRICTED;
				$link_status[$item->TAB][$item->ID]=$item->LINK_STATUS;
				//si une valeur par défaut est donnée au champ 
				//mais que l'on n'est dans un champ type multi lignes
				if (!isset($value)){
					if ($item->VALUE != '' and $grise != 6){
						if (isset($_SESSION['OCS'][$item->VALUE])){
							$value=$_SESSION['OCS'][$item->VALUE];
						}
						else
							$value=$item->VALUE;
					}else
						$value=$protectedPost[$item->ID];
				}
					//cas du champ multi ligne => on récupère le nbre de champ a afficher
				if ($grise == 6 ){
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
				//unset($value_field[$item->TAB][$item->ID]);
				if ($grise == 2 or $grise == 5){
					
					//echo $item->FIELD."<br>";
					//GESTION DU STATUT DE LA DEMANDE
					if ($item->FIELD == "STATUS"){
						$list_status=list_status();
						$sql_service="select id, lbl as value from downloadwk_statut_request where id in (".implode(',',$list_status['ID_TAB']).")";
					}else{
						$add_values_admin[$item->TAB][$item->ID]=$item->ID;
						$sql_service="select field,value,id,default_field FROM downloadwk_conf_values where field=".$item->ID;
					}
					//echo $item->FIELD."<br>";
					$resultSERV = mysql_query($sql_service, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
					while($item_conf_values = mysql_fetch_object($resultSERV)){					
							if (!isset($List[$item_conf_values->field]) and $grise == 2){
								$value_field[$item->TAB][$item->ID]['']='';
								
							}
							if ($grise == 5){
								$name_field[$item->TAB][$item->ID.'_'.$item_conf_values->id]=$item->ID.'_'.$item_conf_values->id;	
								
							}
							$value_field[$item->TAB][$item->ID][$item_conf_values->id]=$item_conf_values->value;
							//echo $value_field[$item->TAB][$item->ID][$item_conf_values->id]."<br>";
					}
				}
				/*elseif ($grise == 7){
					echo "toto";
					$value_field[$item->TAB][$item->ID]=8;
					
					}*/
				elseif ($grise == 9){
					$sql_files="select id,file_name from temp_files where fields_name='fields_".$item->ID."' and id_dde='".$protectedPost['OLD_MODIF']."'";
					$result_files= mysql_query($sql_files, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
					while($item_files = mysql_fetch_object($result_files)){			
					$value_field[$item->TAB][$item->ID][$item_files->id]=$item_files->file_name;
					}
				}
				elseif ($grise == 8){
					$nb_dde[$item->TAB][$item->ID]=$protectedPost['OLD_MODIF'];	
					
				}else{
					$value_field[$item->TAB][$item->ID]=$value;
					
				}
			}
		}	
		/*********************END initialisation***********************/
	//print_r($value_field);
	//echo "<br><hr><br>";
	//DDE POST
		if ($protectedPost['SUBMIT_FORM'] == "SUBMIT_FORM" ){
			foreach($oblig_field as $key=>$array_value){
								
				foreach($array_value as $id=>$value){
					//les champs en lecture seule ne sont pas à mettre à jour
					if ($type_field[$key][$id] != 3){
						//si on est à l'insertion de fichier dans la demande
						if ($type_field[$key][$id] == 8 or $type_field[$key][$id] == 9){
								
								//	//recherche des fichiers disponibles pour ce champ
									$sql="select id from temp_files where fields_name = 'fields_".$id."' 
											and AUTHOR='".$_SESSION['OCS']['loggeduser']."'
											and (ID_DDE is null or ID_DDE='".$protectedPost['OLD_MODIF']."')";
									$result = mysql_query($sql, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
									unset($id_files_value);
									while ($item_values = mysql_fetch_object($result)){
											$id_files_value[]=$item_values->id;
											$list_id_files_to_update[]=$item_values->id;
									}		
									//on ajoute les id des fichiers aux posts pour les insérer en base
									if (isset($id_files_value)){
										$protectedPost[$id]=implode(',',$id_files_value);									
									}
						}
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
						//si le champ est en lecture seule, on ne prend pas en compte la valeur
						//du post
						/*if ($type_field[$key][$id] == 3)
							unset($protectedPost[$id]);		*/		
					}	
				}		
			}	
		//	$msg_empty="STOP";
			//print_r($type_field_temp);
			if (isset($msg_empty)){
				echo "<script>alert('" . $l->g(684) . ":\\n" . $msg_empty . "');</script>";
				unset($protectedPost['VALID']);
			}else{
				
				//on récupère l'id du champ status
				$item_id_STATUS =find_id_field();
				if (isset($item_id_STATUS['STATUS']->id) and $item_id_STATUS['STATUS']->id != ''){
					foreach ($protectedPost as $key=>$value){						
							//cas of checkbox
							$checkbox=explode('_',$key);
							if ($type_field_temp[$key] != 3 
								and $type_field_temp[$checkbox[0]] != 3
								and $type_field_temp[$key] != 9){
								//echo $key."=>".$type_field_temp[$key]."<br>";
								if (is_numeric($checkbox[0]) and is_numeric($checkbox[1]) and !isset($value_list[$checkbox[0]])){
									$check_on[$checkbox[0]].=$checkbox[1]."**check&check**";					
								}elseif (isset($value_list[$checkbox[0]]) and $value_list[$checkbox[0]]!=''){
									$array_fields_form[]= "fields_".$checkbox[0];
									$array_value_form[]= $value_list[$checkbox[0]];
									$value_list[$checkbox[0]]='';
								}
								if (is_numeric($key)){
									$array_fields_form[]= "fields_".$key;
									//gestion du statut. La demande est faite => statut=2
									if (($item_id_STATUS['STATUS']->id == $key and $value == '') or($item_id_STATUS['STATUS']->id == $key and !is_numeric($value)))
									$array_value_form[]= "2";
									else
									$array_value_form[]= $value;
								}
							}
					}
					if (isset($check_on)){
						foreach ($check_on as $key=>$value){
							$array_fields_form[]= "fields_".$key;
							$array_value_form[]= $value;
							
						}
						
					}
					
					if (isset($protectedPost['OLD_MODIF']) and is_numeric($protectedPost['OLD_MODIF'])){
						//search old parameters
						$sql_old_param="select * from downloadwk_pack where id = '%s'";
						$arg=array($protectedPost['OLD_MODIF']);
						$result_old_param = mysql2_query_secure($sql_old_param, $_SESSION['OCS']["readServer"],$arg);
						$item_old_param = mysql_fetch_object($result_old_param);
						
						$sql_wk_dde="UPDATE downloadwk_pack set ";
						$i=0;
						$arg=array();
						while ($array_fields_form[$i]){
							$sql_wk_dde.=$array_fields_form[$i]."='%s', ";
							$arg_wk_dde[]=$array_value_form[$i];
							if ($item_old_param ->$array_fields_form[$i] != $array_value_form[$i]){
								$id_field=explode('_',$array_fields_form[$i]);
								$sql_fields_modif="select lbl from downloadwk_fields where id = '%s'";
								$arg_lbl=array($id_field[1]);
								$result_fields_modif = mysql2_query_secure($sql_fields_modif, $_SESSION['OCS']["readServer"],$arg_lbl);
								$item_fields_modif = mysql_fetch_object($result_fields_modif);
								if (is_numeric($item_fields_modif->lbl))
									$modif_field=$l->g($item_fields_modif->lbl);
								else
									$modif_field=$item_fields_modif->lbl;
								$list_fields_modif[]=$modif_field;
							}
							$i++;
						}
						$sql_wk_dde= substr($sql_wk_dde, 0, -2)." WHERE ID='%s'";
						$arg_wk_dde[]=$protectedPost['OLD_MODIF'];
						$subjet_mail=$l->g(1053) . ": " . $protectedPost['OLD_MODIF'];
						$body=$_SESSION['OCS']['loggeduser'] . $l->g(1090) . $protectedPost['OLD_MODIF'];
						
						//LOGS MODIF
						if (isset($list_fields_modif) and is_array($list_fields_modif)){
							$sql_log_modif="INSERT INTO downloadwk_history (ID_DDE,AUTHOR,DATE,ACTION) 
											VALUES ('%s','%s',sysdate(),'%s')";
							$arg=array($protectedPost['OLD_MODIF'],$_SESSION['OCS']['loggeduser'],implode(',',$list_fields_modif));
							mysql2_query_secure($sql_log_modif, $_SESSION['OCS']["writeServer"],$arg);
							$body.="Champs modifiés: <br>" . implode('<br>',$list_fields_modif);
						}
						$msg_popup=$l->g(1053);
					}else{
						$sql_wk_dde="INSERT INTO downloadwk_pack (LOGIN_USER,GROUP_USER,Q_DATE,";
						$sql_wk_dde.= implode(",",$array_fields_form);
						$sql_wk_dde.= ") VALUES ('%s','%s',UNIX_TIMESTAMP(),";
						$arg_wk_dde=array($_SESSION['OCS']['loggeduser'],$_SESSION['OCS']['user_group']);
						foreach ($array_value_form as $key_form=>$value_form){
							$sql_wk_dde.="'%s',";
							$arg_wk_dde[]=$value_form;
						}
						$sql_wk_dde= substr($sql_wk_dde, 0, -1).")";
						$msg_popup=$l->g(1054);
						
						$subjet_mail=$l->g(1091);
						$body=$_SESSION['OCS']['loggeduser'] . $l->g(1092);
					}
					/*echo "<br><hr><br>";
					echo $sql_wk_dde;
					echo "<br><hr><br>";*/
					//print_r($array_value_form);
					//$sql_insert= substr($sql_insert,0,-1);
					//$list_value= substr($list_value,0,-2);
					mysql2_query_secure($sql_wk_dde, $_SESSION['OCS']["writeServer"],$arg_wk_dde);	
					//mise à jour des blobs insérés		
					if (isset($list_id_files_to_update)){
						if (mysql_insert_id())
						$id_dde=mysql_insert_id();
						elseif (isset($protectedPost['OLD_MODIF']))
						$id_dde=$protectedPost['OLD_MODIF'];
										
						if (isset($id_dde)){
							$sql_up="update temp_files 
									set ID_DDE='%s' 
									where ID in (%s)";
							$arg=array($id_dde,implode(",",$list_id_files_to_update));
							mysql2_query_secure($sql_up, $_SESSION['OCS']["writeServer"],$arg);	
						}						
					}
					$tab=$protectedPost['cat'];
					unset($protectedPost);
					$protectedPost['cat']=$tab;
					echo "<script>alert('".$msg_popup."');</script>";
					
					//TODO: envoi de mail au group admin + soit à l'utilisateur soit à son groupe (voir la conf du profil)
					$mail_active=option_conf_activate('IT_SET_MAIL');
					if ($mail_active){
						//mail for admin of workflow
						$group_admin_mail=look_default_values(array('IT_SET_MAIL_ADMIN'));
						if (isset($group_admin_mail['ivalue']['IT_SET_MAIL_ADMIN'])){
							$sql_mail="select email 
										from operators 
										where user_group='%s'";
							$arg=array($group_admin_mail['ivalue']['IT_SET_MAIL_ADMIN']);
							$result_mail = mysql2_query_secure($sql_mail, $_SESSION['OCS']["readServer"],$arg);
							$mail_list=array();
							while($item_mail = mysql_fetch_object($result_mail)){
								if (VerifyMailadd($item_mail->email)){
									$mail_list[]=$item_mail->email;
								}
							}
						
						}
						//mail for other
						$sql_mail="select email,user_group 
								   from operators 
								   where ID='%s'";
						$arg=array($_SESSION['OCS']['loggeduser']);
						$result_mail = mysql2_query_secure($sql_mail, $_SESSION['OCS']["readServer"],$arg);
						$item_mail = mysql_fetch_object($result_mail);
						if (!VerifyMailadd($item_mail->email))
								echo "<script>alert('" . $l->g(1055)." ".$l->g(1056) . "');</script>";
						if ($_SESSION['OCS']['TELEDIFF_WK'] == 'LOGIN'){							
							if (VerifyMailadd($item_mail->email))
							$mail_list[]=$item_mail->email;
						}elseif ($_SESSION['OCS']['TELEDIFF_WK'] == 'USER_GROUP'){
							$sql_mail_group="select email from operators 
											 where USER_GROUP='%s'";
							$arg=array($item_mail->user_group);
							$result_mail_group = mysql2_query_secure($sql_mail_group, $_SESSION['OCS']["readServer"],$arg);
							while ($item_mail_group = mysql_fetch_object($result_mail_group)){
								if (VerifyMailadd($item_mail_group->email))
									$mail_list[]=$item_mail_group->email;
							}
						}
						
						if (isset($mail_list[0])){
							send_mail($mail_list,$subjet,$body);								
						}else
						 echo "<script>alert('" . $l->g(1058) . "');</script>";						
						
						
					}
				
					unset($_SESSION['OCS']['DATA_CACHE'],$_SESSION['OCS']['NUM_ROW']);
				}else{
					echo "<script>alert('" . $l->g(1093) . ".\\n " . $l->g(1094) . ".');</script>";
					unset($protectedPost['VALID']);
				}
				
			}
			
		}

		
		
		
		
		/***************BEGIN Show fields*************/

		if ($name_field[$protectedPost['cat']]){
			//print_r($type_field[$protectedPost['cat']]);			
			$tab_typ_champ=show_field($name_field[$protectedPost['cat']],$type_field[$protectedPost['cat']],$value_field[$protectedPost['cat']]);
			if (isset($nb_fields[$protectedPost['cat']])){
				foreach ($nb_fields[$protectedPost['cat']] as $key=>$value){
						$tab_typ_champ[$key]['CONFIG']['NB_FIELD']=$value;
				}
			}
			
			if (isset($nb_dde[$protectedPost['cat']])){
				foreach ($nb_dde[$protectedPost['cat']] as $key=>$value){
						$tab_typ_champ[$key]['CONFIG']['DDE']=$value;
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
						$tab_typ_champ[$value]['COMMENT_BEHING']="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_admin_management']."&head=1&value=".$value."\",\"admin_management\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")><img src=image/plus.png></a>";
				}				
			}	
		}else 
		echo "NOT DEFINE";
			/***************END Show fields*************/
		
		
		if (isset($msg))
		echo "<font color=green>".$msg."</font>";
		if (isset($tab_typ_champ)){
			//print_r($tab_typ_champ);
			$tab_hidden= hidden($protectedPost,$name_field[$protectedPost['cat']]);
			tab_modif_values($tab_name[$protectedPost['cat']],$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=false,'NO_FORM');
			
			if (isset($protectedPost['OLD_MODIF']))
			$lbl=$l->g(115);
			else
			$lbl=$l->g(114);
			echo "<input type=button name='VALID' id='VALID' value='".$lbl."' OnClick='pag(\"SUBMIT_FORM\",\"SUBMIT_FORM\",\"".$form_name."\");'>";
			echo "<input type='hidden' name='SUBMIT_FORM' id='SUBMIT_FORM' value=''>";
		}
}


function dde_conf($form_name){
	global $l,$protectedPost,$protectedGet,$pages_refs;
if ($_SESSION['OCS']['CONFIGURATION']['TELEDIFF_WK'] == 'YES'){
			if (!isset($protectedPost['conf']))
				$protectedPost['conf']='GENERAL';
			//sous onglets 
			$conf_value['GENERAL']=$l->g(107);
			$conf_value['GUI']=$l->g(84);
			$conf_value['STATUS']=$l->g(1095);;
			//$conf_value['ADMIN']='Administration';
			onglet($conf_value,$form_name,"conf",7);
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
					}else{
						$fields=array('type','field','lbl','must_completed','value','restricted','link_status');
						//si le type est TEXTAREA, il faut aussi changer le type de la colonne en longtext
						if ($protectedPost['type'] == 1){
							$type_modif="longtext";											
						}else
							$type_modif="varchar(255)";			
						
						$sql_modify_type='ALTER TABLE downloadwk_pack change 
									fields_%1$s  
									fields_%1$s '.$type_modif.' default null;';
						$arg=array($protectedPost['FIELDS']);
						mysql2_query_secure($sql_modify_type,$_SESSION['OCS']["writeServer"],$arg);	
						//echo $sql_modify_type;		
					}
					
					//création de la requête
					$sql_update='UPDATE downloadwk_fields 
										set ';
					$arg=array();
					foreach ($fields as $key=>$value){
						$sql_update.= $value."='%s' ,";	
						$arg[]=	$protectedPost[$value];				
					}
					$sql_update=substr($sql_update,0,-1)."where id='%s'";
					$arg[]=$protectedPost['FIELDS'];
					mysql2_query_secure($sql_update,$_SESSION['OCS']["writeServer"],$arg);
					//print_r
					
					//echo $sql_update;
				}
				
				
				
				$sql_service="select id,field,value,lbl,default_field 
							  FROM downloadwk_tab_values";
				$resultSERV = mysql2_query_secure($sql_service, $_SESSION['OCS']["readServer"]);
				$List_tab[]='';
				while($item = mysql_fetch_object($resultSERV)){
					$lbl=define_lbl($item->lbl,$item->default_field);
					$List_tab[$item->id]=$lbl;
				}
				$name_field= array("TAB");	
				//$oblig_field['INFO_VALID']=$name_field['INFO_VALID'];
				$tab_name= array($l->g(1097) . ":");
				$type_field= array(2);
				$value_field=array($List_tab);
				if (isset($protectedPost['TAB']) and $protectedPost['TAB'] != 0){
					$sql_service="select id,lbl,default_field 
								  FROM downloadwk_fields 
								  where TAB='%s'";
					$arg=array($protectedPost['TAB']);
					$resultSERV = mysql2_query_secure($sql_service, $_SESSION['OCS']["readServer"],$arg);
					$List_fields[]='';
					while($item = mysql_fetch_object($resultSERV)){
						$lbl=define_lbl($item->lbl,$item->default_field);
						$List_fields[$item->id]=$lbl;
						$default_field[$item->id]=$item->default_field;
					}
					array_push($name_field,"FIELDS");
					array_push($tab_name,$l->g(1096) . ":");
					array_push($type_field,2);
					array_push($value_field,$List_fields);
				}
				$tab_typ_champ=show_field($name_field,$type_field,$value_field);
				$tab_typ_champ[0]['COMMENT_BEHING']="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_admin_management']."&head=1&admin=tab&value=TAB\",\"admin_management\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")><img src=image/plus.png></a>";
				$tab_typ_champ[0]['RELOAD']=$form_name;
				$tab_typ_champ[1]['RELOAD']=$form_name;
				$tab_typ_champ[1]['COMMENT_BEHING']="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_admin_management']."&head=1&admin=fields&value=".$protectedPost['TAB']."\",\"admin_management\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=700,height=650\")><img src=image/plus.png></a>";
				
				tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=false,$form_name='NO_FORM');
				if (isset($protectedPost['FIELDS']) and $protectedPost['FIELDS'] != 0){
					echo "<br>";
					$sql_status="SELECT id,lbl FROM downloadwk_statut_request";
					$res_status = mysql2_query_secure( $sql_status, $_SESSION['OCS']["readServer"] );
					$status['0']= $l->g(454);
					while ($val_status = mysql_fetch_array( $res_status ))
					$status[$val_status['id']]=$val_status['lbl'];
					//print_r($status);
					$list_type=array('TEXT','TEXTAREA','SELECT',
									'SHOW DATA','PASSWORD',
									'CHECKBOX','LIST','HIDDEN',
									'BLOB (FILE)','LINK LIST','TABLE');
					$yes_no=array($l->g(454),$l->g(455));
					$sql_detailField="select type,field,lbl,must_completed,
										value,restricted,link_status 
									  FROM downloadwk_fields 
									  where id='%s'";	
					$arg=array($protectedPost['FIELDS']);		
					$result_detailField = mysql2_query_secure($sql_detailField, $_SESSION['OCS']["readServer"],$arg);
					$item_detailField = mysql_fetch_object($result_detailField);
					$protectedPost['type']=$item_detailField->type;
					$protectedPost['must_completed']=$item_detailField->must_completed;
					$protectedPost['restricted']=$item_detailField->restricted;
					$protectedPost['link_status']=$item_detailField->link_status;

					$name_field=array('type','field','lbl','must_completed','value','restricted','link_status');
					$tab_name= array($l->g(1071) . ':',$l->g(1098) . ':',$l->g(1063) . ':',$l->g(1064) . ':',$l->g(1099) . ':',$l->g(1065) . ':',$l->g(1066) . ':');					
					
					if ($default_field[$protectedPost['FIELDS']])	{
						$title= $l->g(1101);
						//$showbutton=false;
						$type_field= array(3,3,3,3,0,3,3,7);
						$value_field=array($list_type[$item_detailField->type],
										$item_detailField->field,
										$l->g($item_detailField->lbl),
										$yes_no[$item_detailField->must_completed],
										$item_detailField->value,
										$yes_no[$item_detailField->restricted],
										$status[$item_detailField->link_status],'value');
						if ($item_detailField->field == "STATUS"){
							$type_field[4]= 2;
							unset($status[0]);
							$value_field[4]=$status;
							$protectedPost['value']=$item_detailField->value;
						}
						$name_field[7]='DEFAULT_FIELD';
						$tab_name[7]='';
					}else{
						$title="";
						//$showbutton=true;
						$type_field= array(2,0,0,2,0,2,2);
						$value_field=array($list_type,
										$item_detailField->field,
										$item_detailField->lbl,
										$yes_no,
										$item_detailField->value,
										$yes_no,
										$status);	
					}
					
					
					
					
					$tab_typ_champ=show_field($name_field,$type_field,$value_field);
					tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title,$comment="",$name_button="fields",$showbutton=true,$form_name='NO_FORM');
				}
				
			}elseif($protectedPost['conf']=="STATUS"){
				//mise à jour des valeurs de statuts
				if ($protectedPost['Valid_fields_x'] != ''){
					if (trim ($protectedPost['lbl']) != ''){
							$sql_update="UPDATE downloadwk_statut_request
										set LBL='%s' ,  ACTIF='%s'
										where ID='%s'";
							$arg=array($protectedPost['lbl'],
										$protectedPost['actif'],
										$protectedPost['id']);
							mysql2_query_secure($sql_update,$_SESSION['OCS']["writeServer"],$arg);
												
					}else
						echo "<script>alert('" . $l->g(1061) . "');</script>";		
					
					
				}
				
				
				
				
				$infos_status=list_status(false);
				$name_field= array("STATUS");	
				$tab_name= array($l->g(1100) . ":");
				$type_field= array(2);
				$value_field=array($infos_status['STAT']);
				if (isset($protectedPost['STATUS']) and $protectedPost['STATUS'] != 0){
				/*	$status['0']= "NON";
					$status[$val_status['id']]=$val_status['lbl'];*/
					$yes_no=array($l->g(454),$l->g(455));
					$protectedPost['actif']=$infos_status['ACTIF'][$protectedPost['STATUS']];
					$protectedPost['id']=$protectedPost['STATUS'];
					$protectedPost['lbl']=$infos_status['STAT_BIS'][$protectedPost['STATUS']];
					$protectedPost['name']=$infos_status['NIV'][$protectedPost['STATUS']];
					array_push($name_field,'actif','id','lbl','name');
					array_push($tab_name,$l->g(1102) . ':',$l->g(1103) . ':',$l->g(1063) . ':',$l->g(1064) . ':');
					array_push($type_field,2,3,0,3);
					array_push($value_field,$yes_no,$protectedPost['id'],$protectedPost['lbl'],$protectedPost['name']);
					$showbutton=true;
					}else
					$showbutton=false;
					
					$tab_typ_champ=show_field($name_field,$type_field,$value_field);
					$tab_typ_champ[0]['RELOAD']=$form_name;
					tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title,$comment="",$name_button="fields",$showbutton,$form_name='NO_FORM');
								
			}
		}	
	
}

function dde_show($form_name){
		global $l,$protectedPost,$protectedGet,$pages_refs;
		
		//suppression d'une demande
		if(isset($protectedPost['SUP_PROF']) and is_numeric($protectedPost['SUP_PROF'])) {
				//on récupère l'id du champ status
				$item_id_STATUS =find_id_field();
				$sql="UPDATE downloadwk_pack 
										set FIELDS_%s='1'
										where ID='%s'";
				$arg=array($item_id_STATUS['STATUS']->id,$protectedPost['SUP_PROF']);
				mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
			
			
			$tab_options['CACHE']='RESET'; 
		}
		
		
		
		$table_name='LIST_DDE';
		
		//recherche des champs qui ont été créés
		$sql_fields="select lbl,id,type,field from downloadwk_fields ";
		$resultfields=mysql2_query_secure($sql_fields,$_SESSION['OCS']["readServer"]);
		//$resultfields = mysql_query($sql_fields, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
		$id_field1=0;
		$id_field2=0;
		$default_fields=array();
		while($item = mysql_fetch_object($resultfields)){
			$name_field[$item->id]=$item->field;
			$field='fields_'.$item->id;
			if ($item->field == "STATUS")
			$id_status=$field;
			if (($item->type == '2' or $item->type == '5') and $item->field != "STATUS"){
				$array_value_fields[$id_field1]=$field.".VALUE as ".$field;
				$array_fields[$id_field1]=$field;					
				$id_field1++;
			}else{
				$else_fields[$id_field2]="downloadwk_pack.".$field;
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
		$sql_statut="select id,lbl from downloadwk_statut_request";
		$resultfields = mysql2_query_secure($sql_statut,$_SESSION['OCS']["readServer"]);
		while($item = mysql_fetch_object($resultfields)){
			$statut[$item->id]=$item->lbl;		
		}

		$tab_options['REPLACE_VALUE'][$id_status]=$statut;
		$list_col_cant_del[$id_status]=$id_status;
		$default_fields[$id_status]=$id_status;
		$list_fields[$id_status]=$id_status;
		$list_col_cant_del['Q_DATE']='Q_DATE';
		$default_fields['Q_DATE']='Q_DATE';
		$list_fields['Q_DATE']='Q_DATE';
		$list_col_cant_del['SUP']='SUP';
		$default_fields['SUP']='SUP';
		$list_fields['SUP']='ID';
		$list_fields['MODIF']='ID';
		$default_fields['MODIF']='MODIF';
		$list_col_cant_del['MODIF']='MODIF';
		$tab_options['LBL']['Q_DATE']="Date dde";
		$sql="select downloadwk_pack.ID,FROM_UNIXTIME(Q_DATE) as Q_DATE,";
		
		if (isset($array_value_fields)){
			$sql.=implode(', ',$array_value_fields);
			$bool_select=true;
		}
	//	print_r($else_fields);
		if (isset($else_fields)){
			if ($bool_select)
				$sql.=", ";
			$sql.=implode(', ',$else_fields);	
			$bool_normal=true;
		}
		
		if (!$bool_normal and !$bool_select)
			$sql.=" * ";		
		$sql.=" from downloadwk_pack ";
	
		$i=0;
		while ($array_fields[$i]){
			$sql.= " left join downloadwk_conf_values ".$array_fields[$i]." on downloadwk_pack.".$array_fields[$i]."=".$array_fields[$i].".ID ";
			$i++;
		}
		$sql.=" WHERE ".$id_status."!= (Select id from downloadwk_statut_request where name='NIV0')";
		if ($_SESSION['OCS']['RESTRICTION']['TELEDIFF_WK'] == 'LOGIN')
			$sql.=" and LOGIN_USER='".$_SESSION['OCS']['loggeduser']."' ";
		elseif ($_SESSION['OCS']['RESTRICTION']['TELEDIFF_WK'] == 'USER_GROUP')
			$sql.=" and GROUP_USER='".$_SESSION['OCS']['user_group']."' ";
		tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql,$form_name,100,$tab_options);

}



?>