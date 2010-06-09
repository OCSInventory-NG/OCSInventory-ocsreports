<?php
/*
 * Add groups for users
 * 
 */
 
if (!isset($protectedPost['onglet']) or $protectedPost['onglet']=='')
	 $protectedPost['onglet'] = 1;
$form_name='admin_users_groups';
$table_name=$form_name;
$data_on[1]="Données existantes";
$data_on[2]="Nouvelle donnée";
echo "<br><form name='".$form_name."' id='".$form_name."' method='POST'>";
onglet($data_on,$form_name,"onglet",2);
echo '<div class="mlt_bordure" >';
if ($protectedPost['onglet'] == 1){
	$tab_options['CACHE']='RESET';

	if (isset($protectedPost['del_check']) and $protectedPost['del_check'] != ''){		
		$list = $protectedPost['del_check'];
		$sql_delete="DELETE FROM config WHERE name like 'USER_GROUP_%' and ivalue in (".$list.")";
		mysql_query($sql_delete, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));				
	}
	
	if(isset($protectedPost['SUP_PROF'])) {
		@mysql_query( "DELETE FROM config WHERE name='USER_GROUP_".$protectedPost['SUP_PROF']."'", $_SESSION['OCS']["writeServer"]  );
	}	
	$queryDetails ="select IVALUE,TVALUE from config where name like 'USER_GROUP_%'";

	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	if (!(isset($protectedPost["pcparpage"])))
		 $protectedPost["pcparpage"]=5;

	$list_fields['GRP']='TVALUE';
	$list_fields['SUP']='IVALUE';
	$list_fields['CHECK']='IVALUE'; 
	$list_col_cant_del=$list_fields;
	$default_fields=$list_col_cant_del; 

	$are_result=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,100,$tab_options);
	//traitement par lot
	if ($are_result){
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
			if (trim($protectedPost['newfield']) != ''){
				$sql_verif="SELECT count(*) c FROM config WHERE TVALUE = '".$protectedPost['newfield']."' and NAME like 'USER_GROUP%'";
				//echo $sql_verif;
				$res_verif = mysql_query( $sql_verif, $_SESSION['OCS']["readServer"] );
				//echo $val_verif = mysql_fetch_array( $res_verif );
				$val_verif = mysql_fetch_array( $res_verif );
				if ($val_verif['c'] > 0)
					$ERROR="Ce nom de groupe est déjà utilisé";				
			}else
				$ERROR="Le nom du groupe ne peut pas être vide";			
		
		
		if (!isset($ERROR)){
			$sql_new_value="SELECT max(ivalue) max FROM config WHERE  NAME like 'USER_GROUP%'";
			$res_new_value = mysql_query( $sql_new_value, $_SESSION['OCS']["readServer"] );
			$val_new_value = mysql_fetch_array( $res_new_value );	
			if ($val_new_value['max'] == "")
			$val_new_value['max']=0;
			$val_new_value['max']++;
			mysql_query( "INSERT INTO config (NAME,TVALUE,IVALUE) VALUES('USER_GROUP_".$val_new_value['max']."','".$protectedPost['newfield']."','".$val_new_value['max']."')", $_SESSION['OCS']["writeServer"]) or mysql_error($_SESSION['OCS']["writeServer"]);
			//si on ajoute un champ, il faut créer la colonne dans la table downloadwk_pack
			echo "<font color=green><b>Ajout de la valeur effectuée</b></font>";
		}else
			echo "<font color=red><b>".$ERROR."</b></font>";
	}

	//NAME FIELD
	$name_field=array("newfield");
	$tab_name= array("Nouveau groupe: ");
	$type_field= array(0);
	$value_field=array($protectedPost['newfield']);
	$tab_typ_champ=show_field($name_field,$type_field,$value_field);
	$tab_typ_champ[0]['CONFIG']['SIZE']=20;
	tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=true,$form_name='NO_FORM');
}


echo "</div>"; 
echo "</form>";

?>

