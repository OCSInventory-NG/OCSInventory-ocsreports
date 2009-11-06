<?php 
	$list_fields=array();
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';


	$form_name="affich_tag";
	$table_name=$form_name;
	if (isset($protectedPost['Valid_modif_x'])){
		if ($protectedPost['TAG_MODIF'] == $_SESSION['TAG_LBL'])
		$lbl_champ='TAG';
		else
		$lbl_champ=$protectedPost['TAG_MODIF'];
		$sql=" update accountinfo set ".$lbl_champ."='";
		if ($protectedPost['FIELD_FORMAT'] == "date")
		$sql.= dateToMysql($protectedPost['NEW_VALUE'])."'";
		else
		$sql.= $protectedPost['NEW_VALUE']."'";
		$sql.=" where hardware_id=".$systemid; 
		mysql_query($sql, $_SESSION["writeServer"]);
		//reg�n�ration du cache
		$tab_options['CACHE']='RESET';
	}
	
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";

	$queryDetails = "SELECT * FROM accountinfo WHERE hardware_id=$systemid";
	$resultDetails = mysql_query($queryDetails, $_SESSION["readServer"]) or die(mysql_error($_SESSION["readServer"]));
	$item=mysql_fetch_array($resultDetails,MYSQL_ASSOC);
	$i=0;
	$queryDetails = "";
	while (@mysql_field_name($resultDetails,$i)){
		if(mysql_field_type($resultDetails,$i)=="date"){
			//echo dateFromMysql($item[mysql_field_name($resultDetails,$i)])." => ".mysql_field_name($resultDetails,$i);
			$value = "'".dateFromMysql($item[mysql_field_name($resultDetails,$i)])."'";
		}else
			$value = mysql_field_name($resultDetails,$i);
		$lbl=mysql_field_name($resultDetails,$i);	
		if ($lbl != 'HARDWARE_ID'){
			if ($lbl == 'TAG')
			$lbl=$_SESSION['TAG_LBL'];
			$queryDetails .= "SELECT hardware_id as ID,'".$lbl."' as libelle, ".$value." as valeur FROM accountinfo WHERE hardware_id=".$systemid." UNION ";
		}
		$type_field[$lbl]=mysql_field_type($resultDetails,$i);
		$i++;
	}
	$queryDetails=substr($queryDetails,0,-6);
	$list_fields['Information']='libelle';
	$list_fields['Valeur']='valeur';
	//$list_fields['SUP']= 'ID';
	$list_fields['MODIF']= 'libelle';
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;

	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,80,$tab_options);
	//print_r($type_field);
	if (isset($protectedPost['MODIF']) and $protectedPost['MODIF'] != ''){
		switch ($type_field[$protectedPost['MODIF']]){
			case "int" : $java = $chiffres;
							break;
			case "string"  : $java = $majuscule;
							break;
			case "date"  : $java = "READONLY ".dateOnClick('NEW_VALUE');
							break;
			default : $java;
		}
		
		$truename=$protectedPost['MODIF'];
		if ($protectedPost['MODIF'] == $_SESSION['TAG_LBL'])
			$truename='TAG';			
		if ($type_field[$protectedPost['MODIF']]=="date"){
		$tab_typ_champ[0]['COMMENT_BEHING'] =datePick('NEW_VALUE');
		$tab_typ_champ[0]['DEFAULT_VALUE']=dateFromMysql($item[$truename]);
		}else
		$tab_typ_champ[0]['DEFAULT_VALUE']=$item[$truename];
		$tab_typ_champ[0]['INPUT_NAME']="NEW_VALUE";
		$tab_typ_champ[0]['INPUT_TYPE']=0;
		$tab_typ_champ[0]['CONFIG']['JAVASCRIPT']=$java;
		$tab_typ_champ[0]['CONFIG']['MAXLENGTH']=100;
		$tab_typ_champ[0]['CONFIG']['SIZE']=40;
		$data_form[0]=$protectedPost['MODIF'];
		tab_modif_values($data_form,$tab_typ_champ,array('TAG_MODIF'=>$protectedPost['MODIF'],'FIELD_FORMAT'=>$type_field[$protectedPost['MODIF']]),$l->g(895),"");
		
	}
	echo "</form>";
?>