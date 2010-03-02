<?php
/*
 * Add tags for users
 * 
 */
 
if (!isset($protectedPost['onglet']) or $protectedPost['onglet']=='')
	 $protectedPost['onglet'] = 1;
$form_name='admin_telediff_wk';
$table_name=$form_name;
$data_on[1]="Données existantes";
$data_on[2]="Nouvelle donnée";
$yes_no=array('NON','OUI');
$multi_choice=array('TEXT','TEXTAREA','SELECT','Affiche la donnée','PASSWORD','CHECKBOX','LISTE');
echo "<br><form name='".$form_name."' id='".$form_name."' method='POST'>";
onglet($data_on,$form_name,"onglet",2);
echo '<div class="mlt_bordure" >';

if ($protectedGet['admin'] == "tab"){
	$table="itmgmt_tab_values";
	$array_fields=array('FIELD'=>'FIELD','Valeur'=>'VALUE','Libellé'=>'LBL');	
	$array_values=array($protectedGet["value"],$protectedPost["newfield"],$protectedPost["newlbl"]);
	$field_search="field";	
}elseif ($protectedGet['admin'] == "fields"){
	$table="itmgmt_fields";
	$array_fields=array('Onglet'=>'TAB','FIELD'=>'FIELD','Type'=>'TYPE','Libellé'=>'LBL','Champ obligatoire'=>'MUST_COMPLETED');	
	$array_values=array($protectedGet["value"],$protectedPost["newfield"],$protectedPost["newtype"],$protectedPost["newlbl"],$protectedPost["must_completed"]);	
	$field_search="tab";
}else{
	$table="itmgmt_conf_values";
	$array_fields=array('FIELD'=>'FIELD','Valeur'=>'VALUE');
	$array_values=array($protectedGet["value"],$protectedPost["newfield"]);
	$field_search="field";		
}
$fields=implode(',',$array_fields);
$values=implode("','",$array_values);


if ($protectedPost['onglet'] == 1){
	$tab_options['CACHE']='RESET';
		//vérification si champ par défaut.
		//Peut-on supprimer ce champ?
//	if ($protectedPost['del_check'] != '' or $protectedPost['SUP_PROF'] != ''){			
//		$sql="select id from ".$table." where id in (".$protectedPost['del_check'].$protectedPost['SUP_PROF'].") and (default_field!=1 or default_field is null)";
//		$res = mysql_query( $sql,$_SESSION['OCS']["readServer"]);
//		$val = mysql_fetch_object($res);
//		if (!isset($val->id)){
//
//			echo "<script>alert('Vous ne pouvez supprimer ce champ car c\'est un champ par défaut');</script>";
//			unset($protectedPost['del_check'],$protectedPost['SUP_PROF']);		
//		}
//	}
//	unset($protectedPost['del_check'],$protectedPost['SUP_PROF']);		
	//suppression d'une liste de type
	if (isset($protectedPost['del_check']) and $protectedPost['del_check'] != ''){		
		$list = $protectedPost['del_check'];
		//suppression des valeurs déjà entrées
		if ($table=="itmgmt_fields"){ 
			$tab_values=explode(',',$list);
			$i=0;
			while($tab_values[$i]){
				$sql_drop_column="ALTER TABLE itmgmt_pack DROP COLUMN fields_".$tab_values[$i];
				mysql_query( $sql_drop_column, $_SESSION['OCS']["writeServer"]  ) or mysql_error($_SESSION['OCS']["writeServer"]);		
				$i++;				
			}
			$sql_delete="DELETE FROM itmgmt_conf_values WHERE field in (".$list.")";
			mysql_query($sql_delete, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));				
		}
		$sql_delete="DELETE FROM ".$table." WHERE id in (".$list.")";
		mysql_query($sql_delete, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));				
	}
	
	if(isset($protectedPost['SUP_PROF'])) {
		@mysql_query( "DELETE FROM ".$table." WHERE ID='".$protectedPost['SUP_PROF']."'", $_SESSION['OCS']["writeServer"]  );
	//si on supprime un champ, il faut supprimer la colonne dans la table itmgmt_pack
		if ($table=="itmgmt_fields"){ 
			$sql_delete="DELETE FROM itmgmt_conf_values WHERE field ='".$protectedPost['SUP_PROF']."'";
			mysql_query($sql_delete, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));				
			
			$sql_drop_column="ALTER TABLE itmgmt_pack DROP COLUMN fields_".$protectedPost['SUP_PROF'];
			mysql_query( $sql_drop_column, $_SESSION['OCS']["writeServer"]  ) or mysql_error($_SESSION['OCS']["writeServer"]);		
		}
	}	
	$queryDetails ="select ID,".$fields." from ".$table." where ".$field_search."='".$protectedGet['value']."' and default_field is null";
	$resTypes = mysql_query( $queryDetails, $_SESSION['OCS']["readServer"] );
	$valTypes = mysql_fetch_array( $resTypes );
	if (is_array($valTypes)){
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	if (!(isset($protectedPost["pcparpage"])))
		 $protectedPost["pcparpage"]=5;
	array_shift($array_fields);
	$list_fields= $array_fields;

	$list_fields['SUP']='ID';
	$list_fields['CHECK']='ID'; 
	$list_col_cant_del=$list_fields;
	$default_fields=$list_col_cant_del; 
//	$queryDetails = 'SELECT ID,';
//	//print_r($list_fields);
//	foreach ($list_fields as $key=>$value){
//		if($key != 'SUP' and $key != 'CHECK')
//		$queryDetails .= $value.',';		
//	} 
//	$queryDetails=substr($queryDetails,0,-1);
//	$queryDetails .= " FROM ".$table." where ".$field_search."='".$protectedGet['value']."'";
	//$tab_options['FILTRE']=$fields;
	$tab_options['REPLACE_VALUE']['Type']=$multi_choice;
	$tab_options['REPLACE_VALUE']['Champ obligatoire']=$yes_no;
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,100,$tab_options);
	//traitement par lot
	$img['image/sup_search.png']=$l->g(162);
	echo "<script language=javascript>
			function garde_check(image,id)
			 {
				var idchecked = '';
				for(i=0; i<document.".$form_name.".elements.length; i++)
				{
					if(document.".$form_name.".elements[i].name.substring(0,5) == 'check'){
				        if (document.".$form_name.".elements[i].checked)
							idchecked = idchecked + document.".$form_name.".elements[i].name.substring(5) + ',';
					}
				}
				idchecked = idchecked.substr(0,(idchecked.length -1));
				confirme('',idchecked,\"".$form_name."\",\"del_check\",\"".$l->g(900)."\");
			}
		</script>";
		echo "<table align='center' width='30%' border='0'>";
		echo "<tr><td>";
		//foreach ($img as $key=>$value){
			echo "<td align=center><a href=# onclick=garde_check(\"image/sup_search.png\",\"\")><img src='image/sup_search.png' title='".$l->g(162)."' ></a></td>";
		//}
	 echo "</tr></tr></table>";
	 echo "<input type='hidden' id='del_check' name='del_check' value=''>";
	
	
	}	
	
}elseif ($protectedPost['onglet'] == 2){
	if( $protectedPost['Valid_modif_x'] != "" ) {
		//vérification que le nom du champ n'existe pas pour les nouveaux champs
		if ($table=="itmgmt_fields"){
			if (trim($protectedPost['newfield']) != ''){
				$sql_verif="SELECT count(*) FROM ".$table." WHERE FIELD = '".$protectedPost['newfield']."'";
				$res_verif = mysql_query( $sql_verif, $_SESSION['OCS']["readServer"] );
				if ($val_verif = mysql_fetch_array( $res_verif ) > 0)
					$ERROR="Ce nom de champ est déjà utilisé";				
			}else
				$ERROR="Le nom du champ ne peut pas être vide";			
		}
		
		if (!isset($ERROR)){		
			mysql_query( "INSERT INTO ".$table." (".$fields.") VALUES('".$values."')", $_SESSION['OCS']["writeServer"]) or mysql_error($_SESSION['OCS']["writeServer"]);
			//si on ajoute un champ, il faut créer la colonne dans la table itmgmt_pack
			if ($table=="itmgmt_fields"){ 
				if ($protectedPost["newtype"] == 1)
					$type="LONGTEXT";
				else
					$type="VARCHAR(255)";
				$sql_add_column="ALTER TABLE itmgmt_pack ADD COLUMN fields_".mysql_insert_id()." ".$type." default NULL";
				mysql_query( $sql_add_column, $_SESSION['OCS']["writeServer"]  ) or mysql_error($_SESSION['OCS']["writeServer"]);		
			}
			echo "<font color=green><b>Ajout de la valeur effectuée</b></font>";
		}else
			echo "<font color=red><b>".$ERROR."</b></font>";
	}
	
	if( $protectedPost['Valid_modif_x'] != "" ) 
		unset($protectedPost['newfield'],$protectedPost['newlbl']);
	//NAME FIELD
	$name_field=array("newfield");
	$tab_name= array("Nouveau Champ: ");
	$type_field= array(0);
	$value_field=array($protectedPost['newfield']);
	if (isset($protectedGet['admin'])){
		array_push($name_field,"newlbl");
		array_push($tab_name,$l->g(80)." :");
		array_push($type_field,0);
		array_push($value_field,$protectedPost['newlbl']);
		if ($protectedGet['admin'] == "fields"){
				
			array_push($name_field,"must_completed");
			array_push($tab_name,"Champ obligatoire:");
			array_push($type_field,2);
			array_push($value_field,$yes_no);
							
			array_push($name_field,"newtype");
			array_push($tab_name,"Type de champ:");
			array_push($type_field,2);
			array_push($value_field,$multi_choice);				
		}
	}

	$tab_typ_champ=show_field($name_field,$type_field,$value_field);
	$tab_typ_champ[0]['CONFIG']['SIZE']=30;
	$tab_typ_champ[1]['CONFIG']['SIZE']=30;
	tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=true,$form_name='NO_FORM');
}


echo "</div>"; 
echo "</form>";

?>

