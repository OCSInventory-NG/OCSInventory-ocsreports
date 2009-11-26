<?php
/*
 * Add tags for users
 * 
 */
 
//require ('fichierConf.class.php');
$form_name='admin_itsetmanagement';
$table_name=$form_name;
$data_on[1]="Données existantes";
$data_on[2]="Nouvelle donnée";
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

if (!isset($protectedPost['onglet']) or $protectedPost['onglet'] == 1){
	$tab_options['CACHE']='RESET';
	//suppression d'une liste de type
	if (isset($protectedPost['del_check']) and $protectedPost['del_check'] != ''){
		$list = "'".implode("','", explode(",",$protectedPost['del_check']))."'";
		$sql_delete="DELETE FROM ".$table." WHERE value in (".$list.")";
		mysql_query($sql_delete, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));				
	}
	
	if(isset($protectedPost['SUP_PROF'])) {
		//$tbd = $protectedGet["supptag"];
		@mysql_query( "DELETE FROM ".$table." WHERE ID='".$protectedPost['SUP_PROF']."'", $_SESSION['OCS']["writeServer"]  );
	}	
	$reqTypes ="select ID,".$fields." from ".$table." where ".$field_search."='".$protectedGet['value']."'";
	$resTypes = mysql_query( $reqTypes, $_SESSION['OCS']["readServer"] );
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
	$queryDetails = 'SELECT ';
	//print_r($list_fields);
	foreach ($list_fields as $key=>$value){
		if($key != 'SUP' and $key != 'CHECK')
		$queryDetails .= $value.',';		
	} 
	$queryDetails=substr($queryDetails,0,-1);
	$queryDetails .= " FROM ".$table." where ".$field_search."='".$protectedGet['value']."'";
	//$tab_options['FILTRE']=$fields;

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
		mysql_query( "INSERT INTO ".$table." (".$fields.") VALUES('".$values."')", $_SESSION['OCS']["writeServer"]  );
		echo "<font color=green><b>Ajout de la valeur effectuée</b></font>";
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
				array_push($value_field,array('NON','OUI'));
								
				array_push($name_field,"newtype");
				array_push($tab_name,"Type de champ:");
				array_push($type_field,2);
				array_push($value_field,array('TEXT','TEXTAREA','SELECT','Affiche la donnée','PASSWORD','CHECKBOX'));				
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

