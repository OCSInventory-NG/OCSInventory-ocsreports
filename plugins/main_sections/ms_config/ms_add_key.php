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
		
//$type_cert=array('MYKEY'=>$l->g(1280),'AC'=>$l->g(1281));
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
		$sql_name="select file_name from ssl_store where id=%s";
		$arg_name=$protectedPost['SUP_PROF'];
		$result=mysql2_query_secure($sql_name,$_SESSION['OCS']["readServer"],$arg_name);
		$file = mysql_fetch_array( $result );
			
		$sql="delete from ssl_store where file_name='%s'";
		$arg=$file['file_name'];
		mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);	
		$tab_options['CACHE']='RESET';
		msg_success($l->g(171));
	}
	$form_name="view_cert";
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	$list_fields=array($l->g(49)=>'FILE_NAME',
					   $l->g(369)=>'AUTHOR','SUP'=>'ID');	
	$list_col_cant_del=array($l->g(49)=>$l->g(49),'SUP'=>'SUP');
	$sql=prepare_sql_tab($list_fields,array('SUP'=>'SUP'));
	$default_fields= array($l->g(49)=>$l->g(49),$l->g(369)=>$l->g(369),$l->g(53)=>$l->g(53),'SUP'=>'SUP');
	$sql['SQL'].=",ID from ssl_store group by file_name";
	$tab_options['LBL_POPUP']['SUP']='FILE_NAME';
	$tab_options['ARG_SQL']=$sql['ARG'];
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$form_name,80,$tab_options);
	//echo "<input type=hidden name='onglet' id='onglet' value='".$protectedPost['onglet']."'>";
	echo "</form>";
	
}elseif ($protectedPost['onglet'] == 'add'){
if ($protectedPost['GO'] 
			and isset($_FILES['file_upload']['tmp_name']) 
			and $_FILES['file_upload']['tmp_name'] != ''){				
				
			$filename = $_FILES['file_upload']['tmp_name'];
			$fd = fopen($filename, "r");
			$contents = fread($fd, filesize ($filename));
			fclose($fd);
				
		$cert=parse_cert($contents,$protectedPost['PASSWORD']);
		if (is_array($cert)){
			//delete all CERT_SUPPORT before insert a new one
			$sql_del="delete from ssl_store where file_type='CERT_SUPPORT'";
			mysql2_query_secure($sql_del,$_SESSION['OCS']["writeServer"]);
			
			//prepare the request to insert new certificat
			$sql_insert="insert into ssl_store (FILE_NAME,FILE,AUTHOR,FILE_TYPE,DESCRIPTION)
				values ('%s','%s','%s','%s','%s')";
			foreach ($cert as $key=>$values){
				if (is_array($values)){
					foreach ($values as $k=>$v){
						$var_insert=array($_FILES['file_upload']['name'],
							$v,
							$_SESSION['OCS']['loggeduser'],
							'CERT_SUPPORT',
							$key."-".$k);
						mysql2_query_secure($sql_insert,$_SESSION['OCS']["writeServer"],$var_insert);
					}					
				}else{
					$var_insert=array($_FILES['file_upload']['name'],
							$values,
							$_SESSION['OCS']['loggeduser'],
							'CERT_SUPPORT',
							$key);
					mysql2_query_secure($sql_insert,$_SESSION['OCS']["writeServer"],$var_insert);
				}
				
			}
			msg_success($l->g(1184));
					
		}		
	}
	$sql_verif="select file_name from ssl_store where file_type='CERT_SUPPORT' group by file_name";
	$result=mysql2_query_secure($sql_verif,$_SESSION['OCS']["readServer"]);
	$file = mysql_fetch_array( $result );
	if (isset($file['file_name'])){
		msg_warning($l->g(1280));	
	}
		echo "<form name='upload_cert' id='upload_cert' method='POST' action='' enctype='multipart/form-data'>";
		echo $l->g(1048).": <input id='file_upload' name='file_upload' type='file' accept=''>";
		echo "<br><br>".$l->g(217).": ".show_modif('','PASSWORD',4)."<br>";
			
		echo "<br><input name='GO' id='GO' type='submit' value='".$l->g(13)."'>&nbsp;&nbsp;<input type=button value='".$l->g(113)."' Onclick='window.close();'>";
		echo "<input type=hidden name='onglet' id='onglet' value='".$protectedPost['onglet']."'>";
	
		echo "</form>";
}

		echo "</div>";




?>