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
 * Administre your DATA for download workflow
 * 
 */
 
require_once ('require/function_admin_management.php');
if (!isset($protectedPost['onglet']) or $protectedPost['onglet']=='')
	 $protectedPost['onglet'] = 1;
$form_name='admin_telediff_wk';
$table_name=$form_name;
$data_on[1]=$l->g(1059);
$data_on[2]=$l->g(1060);
$yes_no=array($l->g(454),$l->g(455));
$multi_choice=array('TEXT','TEXTAREA','SELECT',
					$l->g(802),'PASSWORD','CHECKBOX',
					'LIST','HIDDEN','BLOB (FILE)','LIST LINK','TAB');

echo "<br><form name='".$form_name."' id='".$form_name."' method='POST'>";
onglet($data_on,$form_name,"onglet",2);
echo '<div class="mlt_bordure" >';

if ($protectedGet['admin'] == "tab"){
	$table="downloadwk_tab_values";
//	$array_fields=array('FIELD'=>'FIELD','Valeur'=>'VALUE','Libellé'=>'LBL');	
	$array_fields=array('FIELD'=>'FIELD',$l->g(224)=>'VALUE','Libellé'=>'LBL');	
	$array_values=array($protectedGet["value"],$protectedPost["newfield"],$protectedPost["newlbl"]);
	$field_search="field";	
}elseif ($protectedGet['admin'] == "fields"){
	$table="downloadwk_fields";
	$sql_status="SELECT id,lbl FROM downloadwk_statut_request";
	$res_status = mysql2_query_secure( $sql_status, $_SESSION['OCS']["readServer"] );
	$status['0']= $l->g(454);
	while ($val_status = mysql_fetch_array( $res_status ))
	$status[$val_status['id']]=$val_status['lbl'];
	
	$array_fields=array($l->g(1061)=>'TAB',
						$l->g(1062)=>'FIELD',
						$l->g(66)=>'TYPE',
						$l->g(1063)=>'LBL',
						$l->g(1064)=>'MUST_COMPLETED',
						$l->g(1065)=>'RESTRICTED',
						$l->g(1066)=>'LINK_STATUS');	
	$array_values=array($protectedGet["value"],$protectedPost["newfield"],$protectedPost["newtype"],$protectedPost["newlbl"],
						$protectedPost["must_completed"],
						$protectedPost["restricted"],$protectedPost["link_status"]);	
	$field_search="tab";
}else{
	$table="downloadwk_conf_values";
//	$array_fields=array('FIELD'=>'FIELD','Valeur'=>'VALUE');
	$array_fields=array('FIELD'=>'FIELD',$l->g(224)=>'VALUE');
	$array_values=array($protectedGet["value"],$protectedPost["newfield"]);
	$field_search="field";		
}
$fields=implode(',',$array_fields);
$values=implode("','",$array_values);

if ($protectedPost['onglet'] == 1){
	$tab_options['CACHE']='RESET';
	if (isset($protectedPost['del_check']) and $protectedPost['del_check'] != '') 
		$post_delete=$protectedPost['del_check'];
	if(isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != '') 
		$post_delete=$protectedPost['SUP_PROF'];
	if (isset($post_delete)){
		if ($table == "downloadwk_tab_values")
			delete_tab($post_delete);
		if ($table == "downloadwk_fields")	
			delete_field($post_delete);
		if ($table == "downloadwk_conf_values")	
			delete_conf($post_delete);
	}

	$queryDetails ="select ID,".$fields." from ".$table." where ".$field_search."='%s' 
					and (default_field is null or default_field=0) ";
	$argDetail=$protectedGet['value'];
	$resTypes = mysql2_query_secure( $queryDetails, $_SESSION['OCS']["readServer"],$argDetail);
	$valTypes = mysql_fetch_array( $resTypes );
	if (is_array($valTypes)){
		$tab_options['ARG_SQL']=$protectedGet['value'];
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
		$tab_options['REPLACE_VALUE'][$l->g(66)]=$multi_choice;
		$tab_options['REPLACE_VALUE'][$l->g(1064)]=$yes_no;
		$tab_options['REPLACE_VALUE'][$l->g(1065)]=$yes_no;
		$tab_options['REPLACE_VALUE'][$l->g(1066)]=$status;
		tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,100,$tab_options);
		//traitement par lot
		del_selection($form_name);
	
	
	}	
	
}elseif ($protectedPost['onglet'] == 2){
	if( $protectedPost['Valid_modif_x'] != "" ) {
		//Is this name already exist? 
		if ($table=="downloadwk_fields"){
			if (trim($protectedPost['newfield']) != ''){
				$sql_verif="SELECT count(*) c FROM ".$table." WHERE FIELD = '".$protectedPost['newfield']."'";
				$res_verif = mysql_query( $sql_verif, $_SESSION['OCS']["readServer"] );
				$val_verif = mysql_fetch_array( $res_verif );
				//this name is already exist
				if ($val_verif['c'] > 0)
					$ERROR=$l->g(1067);				
			}else
				//name can't be null
				$ERROR=$l->g(1068);		
		}
		
		if (!isset($ERROR)){		
			mysql_query( "INSERT INTO ".$table." (".$fields.") VALUES('".$values."')", $_SESSION['OCS']["writeServer"]) or mysql_error($_SESSION['OCS']["writeServer"]);
			//If we add a field, you must add a new colonm in downloadwk_pack table
			if ($table=="downloadwk_fields"){ 
				if ($protectedPost["newtype"] == 1)
					$type="LONGTEXT";
				elseif ($protectedPost["newtype"] == 8)
					$type="BLOB";
				else
					$type="VARCHAR(255)";
				$sql_add_column="ALTER TABLE downloadwk_pack ADD COLUMN fields_".mysql_insert_id()." ".$type." default NULL";
				mysql_query( $sql_add_column, $_SESSION['OCS']["writeServer"]  ) or mysql_error($_SESSION['OCS']["writeServer"]);		
			}
			msg_success($l->g(1069));
		}else
			msg_error($ERROR);
	}
	
	if( $protectedPost['Valid_modif_x'] != "" ) 
		unset($protectedPost['newfield'],$protectedPost['newlbl']);
	//NAME FIELD
	$name_field=array("newfield");
	$tab_name= array($l->g(1070).": ");
	$type_field= array(0);
	$value_field=array($protectedPost['newfield']);
	if (isset($protectedGet['admin'])){
		array_push($name_field,"newlbl");
		array_push($tab_name,$l->g(80)." :");
		array_push($type_field,0);
		array_push($value_field,$protectedPost['newlbl']);
		if ($protectedGet['admin'] == "fields"){
			
			array_push($name_field,"newtype");
			array_push($tab_name,$l->g(1071).":");
			array_push($type_field,2);
			array_push($value_field,$multi_choice);	
				
			array_push($name_field,"must_completed");
			array_push($tab_name,$l->g(1064).":");
			array_push($type_field,2);
			array_push($value_field,$yes_no);

			array_push($name_field,"restricted");
			array_push($tab_name,$l->g(1065).":");
			array_push($type_field,2);
			array_push($value_field,$yes_no);
			
			array_push($name_field,"link_status");
			array_push($tab_name,$l->g(1066).":");
			array_push($type_field,2);
			array_push($value_field,$status);
			
			
			
		}
	}

	$tab_typ_champ=show_field($name_field,$type_field,$value_field);
	$tab_typ_champ[0]['CONFIG']['SIZE']=20;
	$tab_typ_champ[1]['CONFIG']['SIZE']=20;
	tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=true,$form_name='NO_FORM');
}


echo "</div>"; 
echo "</form>";

?>

