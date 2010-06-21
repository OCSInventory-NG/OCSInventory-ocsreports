<?php
/*
 * Add value in config table
 * 
 */
//$protectedGet['tag']= USER_GROUP
//faire la vérif sur le tag en get

if (!isset($protectedPost['onglet']) or $protectedPost['onglet']=='')
	 $protectedPost['onglet'] = 1;
$form_name='admin_values_config'.$protectedGet['tag'];
$table_name=$form_name;
$data_on[1]=$l->g(1059);
$data_on[2]=$l->g(1060);
echo "<br><form name='".$form_name."' id='".$form_name."' method='POST'>";
onglet($data_on,$form_name,"onglet",2);
echo '<div class="mlt_bordure" >';
if ($protectedPost['onglet'] == 1){
	$tab_options['CACHE']='RESET';

	if (isset($protectedPost['del_check']) and $protectedPost['del_check'] != ''){		
		$list = $protectedPost['del_check'];
		$sql_delete="DELETE FROM config WHERE name like '".$protectedGet['tag']."_%' and ivalue in (".$list.")";
		mysql_query($sql_delete, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));				
	}
	
	if(isset($protectedPost['SUP_PROF'])) {
		mysql_query( "DELETE FROM config WHERE name='".$protectedGet['tag']."_".$protectedPost['SUP_PROF']."'", $_SESSION['OCS']["writeServer"]  );
	}	
	$queryDetails ="select IVALUE,TVALUE from config where name like '".$protectedGet['tag']."_%'";

	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	if (!(isset($protectedPost["pcparpage"])))
		 $protectedPost["pcparpage"]=5;

	$list_fields[$l->g(224)]='TVALUE';
	$list_fields['SUP']='IVALUE';
	$list_fields['CHECK']='IVALUE'; 
	$list_col_cant_del=$list_fields;
	$default_fields=$list_col_cant_del; 
	$tab_options['LBL']['SUP']=$l->g(122);
	$are_result=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,100,$tab_options);
	//traitement par lot
	if ($are_result){
		del_selection($form_name);
	}
	
	}elseif ($protectedPost['onglet'] == 2){
	if( $protectedPost['Valid_modif_x'] != "" ) {
		//vérification que le nom du champ n'existe pas pour les nouveaux champs
			if (trim($protectedPost['newfield']) != ''){
				$sql_verif="SELECT count(*) c FROM config WHERE TVALUE = '".$protectedPost['newfield']."' and NAME like '".$protectedGet['tag']."_%'";
				//echo $sql_verif;
				$res_verif = mysql_query( $sql_verif, $_SESSION['OCS']["readServer"] );
				//echo $val_verif = mysql_fetch_array( $res_verif );
				$val_verif = mysql_fetch_array( $res_verif );
				if ($val_verif['c'] > 0)
				$ERROR=$l->g(656);
			}else
				$ERROR=$l->g(1068);
		
		
		if (!isset($ERROR)){
			$sql_new_value="SELECT max(ivalue) max FROM config WHERE  NAME like '".$protectedGet['tag']."_%'";
			$res_new_value = mysql_query( $sql_new_value, $_SESSION['OCS']["readServer"] );
			$val_new_value = mysql_fetch_array( $res_new_value );	
			if ($val_new_value['max'] == "")
			$val_new_value['max']=0;
			$val_new_value['max']++;
			mysql_query( "INSERT INTO config (NAME,TVALUE,IVALUE) VALUES('".$protectedGet['tag']."_".$val_new_value['max']."','".$protectedPost['newfield']."','".$val_new_value['max']."')", $_SESSION['OCS']["writeServer"]) or mysql_error($_SESSION['OCS']["writeServer"]);
			//si on ajoute un champ, il faut créer la colonne dans la table downloadwk_pack
			echo "<font color=green><b>".$l->g(1069)."</b></font>";
		}else
			echo "<font color=red><b>".$ERROR."</b></font>";
	}

	//NAME FIELD
	$name_field=array("newfield");
	$tab_name[0]=$l->g(80);
	$type_field= array(0);
	$value_field=array($protectedPost['newfield']);
	$tab_typ_champ=show_field($name_field,$type_field,$value_field);
	$tab_typ_champ[0]['CONFIG']['SIZE']=20;
	tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=true,$form_name='NO_FORM');
}


echo "</div>"; 
echo "</form>";

?>

