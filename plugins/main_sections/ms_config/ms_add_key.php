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
		
$type_cert=array('MYKEY'=>$l->g(1280),'AC'=>$l->g(1281));
	$data_on['view']=$l->g(1059);
$data_on['add']=$l->g(1060);
$form_name="tab";
$table_name='support_certificat';
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
onglet($data_on,$form_name,"onglet",2);
echo '<div class="mlt_bordure" >';
echo "</form>";

if ($protectedPost['onglet'] == 'view'){
	if(isset($protectedPost['SUP_PROF']) and is_numeric($protectedPost['SUP_PROF'])){
		$sql="delete from ssl_store where id=%s";
		$arg=$protectedPost['SUP_PROF'];
		mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);	
		$tab_options['CACHE']='RESET';
		msg_success($l->g(171));
	}
	$form_name="view_cert";
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	$list_fields=array('ID'=>'ID',$l->g(49)=>'FILE_NAME',
					   $l->g(369)=>'AUTHOR',$l->g(66)=>'FILE_TYPE',
					   ucfirst(strtolower($l->g(953)))=>'FILE_SIZE',
					   $l->g(53)=>'DESCRIPTION','SUP'=>'ID');	
	$list_col_cant_del=array($l->g(49)=>$l->g(49),'SUP'=>'SUP');
	$sql=prepare_sql_tab($list_fields,array('SUP'=>'SUP'));
	$default_fields= array($l->g(49)=>$l->g(49),$l->g(369)=>$l->g(369),$l->g(53)=>$l->g(53),'SUP'=>'SUP');
	$sql['SQL'].=" from ssl_store ";
	$tab_options['LIEN_LBL'][$l->g(49)]='index.php?'.PAG_INDEX.'='.$pages_refs['ms_view_file'].'&prov=ssl&no_header=1&value=';
	$tab_options['LIEN_CHAMP'][$l->g(49)]='ID';
	$tab_options['LIEN_TYPE'][$l->g(49)]='POPUP';
	$tab_options['POPUP_SIZE'][$l->g(49)]="width=900,height=600";
	$tab_options['LBL_POPUP']['SUP']='FILE_NAME';
	$tab_options['ARG_SQL']=$sql['ARG'];
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$form_name,80,$tab_options);
	//echo "<input type=hidden name='onglet' id='onglet' value='".$protectedPost['onglet']."'>";
	echo "</form>";
	
}elseif ($protectedPost['onglet'] == 'add'){
if ($protectedPost['GO'] 
			and isset($_FILES['file_upload']['tmp_name']) 
			and $_FILES['file_upload']['tmp_name'] != ''){
		$sql="select count(*) c from ssl_store where description='%s'";
		$arg=$protectedPost['DESCR'];
		$result=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$arg);
		$count_cert = mysql_fetch_array( $result );
		if ($count_cert['c'] == 0){
			$filename = $_FILES['file_upload']['tmp_name'];
			$fd = fopen($filename, "r");
			$contents = fread($fd, filesize ($filename));
			fclose($fd);
			//$binary = addslashes($contents);
			$sql_insert="insert into ssl_store (FILE_NAME,FILE,AUTHOR,FILE_TYPE,FILE_SIZE,DESCRIPTION)
				values ('%s','%s','%s','%s','%s','%s')";
			$var_insert=array($_FILES['file_upload']['name'],
							$contents,
							$_SESSION['OCS']['loggeduser'],
							$_FILES['file_upload']['type'],
							$_FILES['file_upload']['size'],
							$protectedPost['DESCR']);
			mysql2_query_secure($sql_insert,$_SESSION['OCS']["writeServer"],$var_insert);	
			unset($_SESSION['OCS']['DATA_CACHE'][$table_name]);
			unset($_SESSION['OCS']['NUM_ROW'][$table_name]);	
			msg_success($l->g(1184));
		//	mysql_query($sql_insert, $_SESSION['OCS']["writeServer"],$_FILES['file_upload']['name'],$_FILES['file_upload']['type'],$_FILES['file_upload']['size']);
		}else
			msg_error($l->g(1282)." ".$type_cert[$protectedPost['DESCR']]);
		
	}
		echo "<form name='upload_cert' id='upload_cert' method='POST' action='' enctype='multipart/form-data'>";
		echo $l->g(1048).": <input id='file_upload' name='file_upload' type='file' accept=''>";
		echo "<br>".$l->g(66).": ".show_modif($type_cert,'DESCR',2,"",array('DEFAULT'=>"NO"))."<br>";
		
	echo "<br><input name='GO' id='GO' type='submit' value='".$l->g(13)."'>&nbsp;&nbsp;<input type=button value='".$l->g(113)."' Onclick='window.close();'>";
	echo "<input type=hidden name='onglet' id='onglet' value='".$protectedPost['onglet']."'>";

	echo "</form>";
}

		echo "</div>";




?>