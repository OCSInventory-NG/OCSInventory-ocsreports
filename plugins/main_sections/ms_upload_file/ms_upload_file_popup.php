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

$form_name="upload_file";
//verification if this field exist in the table and type like 'blob'
if (isset($protectedGet["tab"]) and $protectedGet["tab"]!=''){
	$table=$protectedGet["tab"];
}else{
	$table='downloadwk_pack';
}

$sql_show="show fields from %s
	where (field='%s' 
		or field='fields_%s')";
$var_show=array($table,$protectedGet["n"],$protectedGet["n"]);
$result = mysql2_query_secure($sql_show,$_SESSION['OCS']["readServer"],$var_show);	
$item = mysql_fetch_object($result);

$field=$item->Field;
if (isset($field) and $field != ''){
	echo "<script language='javascript'>
			function verif()
			 {
				var msg='';
				if (document.getElementById(\"file_upload\").value == ''){
					 document.getElementById(\"file_upload\").style.backgroundColor = 'RED';
					 var msg='1';
				}
	
				
	
				if (msg != ''){
				alert ('".mysql_real_escape_string($l->g(920))."');
				return false;
				}else
				return true;			
			}
		</script>";
	if ($protectedPost['GO']){
		$filename = $_FILES['file_upload']['tmp_name'];
		$fd = fopen($filename, "r");
		$contents = fread($fd, filesize ($filename));
		fclose($fd);
		//$binary = addslashes($contents);
		$sql_insert="insert into temp_files (TABLE_NAME,FIELDS_NAME,FILE,AUTHOR,FILE_NAME,FILE_TYPE,FILE_SIZE,ID_DDE)
			values ('%s','%s','%s','%s','%s','%s','%s','%s')";
		$var_insert=array($table,$field,$contents,
						$_SESSION['OCS']['loggeduser'],
						$_FILES['file_upload']['name'],
						$_FILES['file_upload']['type'],
						$_FILES['file_upload']['size'],
						$protectedGet["dde"]);
		mysql2_query_secure($sql_insert,$_SESSION['OCS']["writeServer"],$var_insert);	
	//	mysql_query($sql_insert, $_SESSION['OCS']["writeServer"],$_FILES['file_upload']['name'],$_FILES['file_upload']['type'],$_FILES['file_upload']['size']);
		$tab_options['CACHE']='RESET';
		
	}
	
	if (isset($protectedPost['SUP_PROF']) and is_numeric($protectedPost['SUP_PROF'])){
		$sql_delete="delete from temp_files where id ='%s'";
		$var_delete=array($protectedPost['SUP_PROF']);
		mysql2_query_secure($sql_delete,$_SESSION['OCS']["writeServer"],$var_delete);	
		$tab_options['CACHE']='RESET';	
	}
	
	//ouverture du formulaire
	echo open_form($form_name,'',"enctype='multipart/form-data'");
	echo $l->g(1048).": <input id='file_upload' name='file_upload' type='file' accept=''>";
	echo "<br><br><input name='GO' id='GO' type='submit' value='".$l->g(13)."' OnClick='return verif();window.close();'>&nbsp;&nbsp;<input type=button value='".$l->g(113)."' Onclick='window.close();'>";
	echo close_form();
	echo "<br>";
	
	
	
	//print_item_header($l->g(92));
			if (!isset($protectedPost['SHOW']))
			$protectedPost['SHOW'] = 'NOSHOW';
		$form_name2="affich_files";
		$table_name=$form_name2;
		echo open_form($form_name2);
		$list_fields=array('id'=>'id','Fichier'=>'file_name','Type'=>'file_type','Poids'=>'file_size','SUP'=>'id');
		$list_col_cant_del=array('Fichier'=>'Fichier','SUP'=>'SUP');
		$default_fields= $list_fields;
		$queryDetails  = "SELECT ";
		foreach ($list_fields as $key=>$value){
			if ($key != 'SUP'){
				$queryDetails .= $value.",";			
			}
		}
		$queryDetails=substr($queryDetails,0,-1);
		$queryDetails .= " FROM temp_files where fields_name = '".$field."' 
							and (id_dde is null or id_dde='".$protectedGet["dde"]."')";
		$tab_options['LIEN_LBL']['Fichier']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_view_file'].'&prov=dde_wk&no_header=1&value=';
		$tab_options['LIEN_CHAMP']['Fichier']='id';
		$tab_options['LIEN_TYPE']['Fichier']='POPUP';
		$tab_options['POPUP_SIZE']['Fichier']="width=900,height=600";
		
		
		tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name2,80,$tab_options);
		echo close_form();
}else
	msg_error($l->g(1049));

?>