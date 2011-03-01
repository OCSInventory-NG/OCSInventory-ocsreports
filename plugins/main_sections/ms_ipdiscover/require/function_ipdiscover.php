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


function find_info_subnet($netid){
	$sql="select NETID,NAME,ID,MASK from subnet where netid='%s'";
	$arg=$netid;
	$res=mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
	$row=mysql_fetch_object($res);
	return $row;
	
}

function find_info_type($name='',$id='',$update=''){	
	if ($name != ''){	
		$sql="select ID,NAME from devicetype where NAME = '%s'";
		$arg=array($name);
	}elseif ($id != ''){
		$sql="select ID,NAME from devicetype where ID = '%s'";
		$arg=array($id);
	}
	if ($update != ''){
		$sql.= " AND ID != '%s'";
		$arg[]=$update;
	}
	$res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
	$row=mysql_fetch_object($res);	
	return $row;
}


function form_add_subnet($title='',$default_value,$form){
	global $l,$pages_refs;
	
	$name_field=array("RSX_NAME","ID_NAME","ADD_IP","ADD_SX_RSX");
		if (isset($_SESSION['OCS']["ipdiscover_id"]))
			$lbl_id= $_SESSION['OCS']["ipdiscover_id"];
		else
			$lbl_id= $l->g(305).":";
			
		$tab_name=array($l->g(304).": ",$lbl_id,$l->g(34).": ",$l->g(208).": ");
		if ($title == $l->g(931))
			$type_field=array(0,2,3,0);
		else
			$type_field=array(0,2,0,0);
			
		$value_field=array($default_value['RSX_NAME'],$default_value['ID_NAME'],$default_value['ADD_IP'],$default_value['ADD_SX_RSX']);
		
		$tab_typ_champ=show_field($name_field,$type_field,$value_field);
		foreach ($tab_typ_champ as $id=>$values){
			$tab_typ_champ[$id]['CONFIG']['SIZE']=30;
		}

			$tab_typ_champ[1]["CONFIG"]['DEFAULT']="NO";
			$tab_typ_champ[1]['COMMENT_BEHING']="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_adminvalues']."&head=1&tag=ID_IPDISCOVER&form=".$form."\",\"admin_id_ipdiscover\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")><img src=image/plus.png></a>";

		tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title,$comment="");
	
}



function verif_base_methode($base){
		global $l;
	if ($_SESSION['OCS']['ipdiscover_methode'] != $base){
		return $l->g(929)."<br>".$l->g(930);
	}else
		return false;	
}

function add_subnet($add_ip,$sub_name,$id_name,$add_sub){
	global $l;
	
		if (trim($add_ip) == '')
			return $l->g(932);	
		if (trim($sub_name) == '')
			return $l->g(933);	
		if (trim($id_name) == '' or $id_name == '0')
			return $l->g(934);
		if (trim($add_sub) == '')
			return $l->g(935);
		$row_verif=find_info_subnet($add_ip);
		if (isset($row_verif->NETID)){
			$sql="update subnet set name='%s', id='%s', MASK='%s'
				where netid = '%s'";			
			$arg=array($sub_name,$id_name,$add_sub,$add_ip);
		}else{	
			$sql="insert into subnet (netid,name,id,mask) VALUES ('%s','%s',
					'%s','%s')";
			$arg=array($add_ip,$sub_name,$id_name,$add_sub);
		}
		mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg);
		return false;
}


function add_type($name,$update=''){
	global $l;
	
	if (trim($name) == ''){
		return $l->g(936);		
	}else{
		$row=find_info_type($name,'',$update);
		if (isset($row->ID))
			return $l->g(937);	
	}
	if ($update != ''){
		$sql="update devicetype set NAME = '%s' where ID = '%s' ";
		$arg=array($name,$update);
	}else{
		$sql="insert into devicetype (NAME) VALUES ('%s')";
		$arg=$name;
	}
	mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg);
	return false;		
}

 function delete_type($id_type){
	$sql="delete from devicetype where id='%s'";
	$arg=$id_type;
	mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg);	
 }
 
 function delete_subnet($netid){
 	$sql="delete from subnet where netid='%s'";
	$arg=$netid;
	mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg);	 	
 }
 
 
 /**
  * Loads the whole mac file in memory
  */
function loadMac() {
	if( $file=@fopen(MAC_FILE,"r") ) {			
		while (!feof($file)) {				 
			$line  = fgets($file, 4096);
			if( preg_match("/^((?:[a-fA-F0-9]{2}-){2}[a-fA-F0-9]{2})\s+\(.+\)\s+(.+)\s*$/", $line, $result ) ) {
				$_SESSION['OCS']["mac"][strtoupper(str_replace("-",":",$result[1]))] = $result[2];
			}				
		}
		fclose($file);			
	}
}

function form_add_community($title='',$default_value,$form){
	global $l,$pages_refs,$protectedPost;
	
		$name_field=array("NAME","VERSION");					
		$tab_name=array($l->g(49).": ",$l->g(1199).": ");
		$type_field=array(0,2);
		$value_field=array($default_value['NAME'],$default_value['VERSION']);
		
		if ($protectedPost['VERSION'] == '3a'){
			array_push($name_field,"USERNAME","AUTHKEY","AUTHPASSWD");
			array_push($tab_name,"USERNAME : ","AUTHKEY : ","AUTHPASSWD :");
			array_push($type_field,0,0);
			array_push($value_field,$default_value['USERNAME'],$default_value['AUTHKEY'],$default_value['AUTHPASSWD']);
		}
						   			
		$tab_typ_champ=show_field($name_field,$type_field,$value_field);
		foreach ($tab_typ_champ as $id=>$values){
			$tab_typ_champ[$id]['CONFIG']['SIZE']=30;
		}

		$tab_typ_champ[1]['RELOAD']=$form;
		if (is_numeric($protectedPost['MODIF'])){
			$tab_hidden['MODIF']=$protectedPost['MODIF'];	
		}
		$tab_hidden['ADD_COMM']=$protectedPost['ADD_COMM'];
		$tab_hidden['ID']=$protectedPost['ID'];
		tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title,$comment="");
	
}


function add_community($snmp_value,$new_ms_cfg_file=''){
	//$new_ms_cfg_file_end = "\n";
	$snmp_file_begin = "<CONTENT>\n";
	$snmp_file_end = "</CONTENT>\n";
	$snmp_file='';
//	p($new_ms_cfg_file);
	if 	(is_array($new_ms_cfg_file)){
		$i=0;
		
		while ($new_ms_cfg_file[$i]){
			$snmp_file.="\t<COMMUNITY>\n";
			foreach ($new_ms_cfg_file[$i] as $key=>$value){
				$snmp_file .= "\t\t<".$key.">";
				if ($value != "")
				$snmp_file .= $value;
				$snmp_file .= "</".$key.">\n";
			}		
			$snmp_file .="\t</COMMUNITY>\n";				
			$i++;	
		}
		
	}
	if (is_array($snmp_value)){
		$snmp_file .="\t<COMMUNITY>\n";
		foreach ($snmp_value as $key=>$value){
			$snmp_file .= "\t\t<".$key.">";
			if ($value != "")
			$snmp_file .= $value;
			$snmp_file .= "</".$key.">\n";
		}		
		$snmp_file .="\t</COMMUNITY>\n";
	}
	$new_ms_cfg_file =$snmp_file_begin.$snmp_file.$snmp_file_end;
	return $new_ms_cfg_file;
}


function format_value_community($value){
	$snmp_value['ID']=(isset($value['ID']) ? $value['ID'] : '0');
	if ($value['VERSION'] != '2c')
		$snmp_value['VERSION']=$value['VERSION']{0};
	else
		$snmp_value['VERSION']=$value['VERSION'];
		
	$snmp_value['NAME']=(isset($value['NAME']) ? $value['NAME'] : '');
	$snmp_value['USERNAME']=(isset($value['USERNAME']) ? $value['USERNAME'] : '');
	$snmp_value['AUTHKEY']=(isset($value['AUTHKEY']) ? $value['AUTHKEY'] : '');
	$snmp_value['AUTHPASSWD']=(isset($value['AUTHPASSWD']) ? $value['AUTHPASSWD'] : '');	
	return $snmp_value;
}

function del_community($id_community,$ms_cfg_file,$search){
		$field_com=parse_xml_file($ms_cfg_file,$search,"COMMUNITY");
		$i=0;
		while ($field_com[$i]){
			if (!in_array($field_com[$i]['ID'],$id_community))	
				$new_ms_cfg_file[]=$field_com[$i];
			//	unset($field_com[$i]);	
			$i++;		
		}
		$communities=add_community('',$new_ms_cfg_file);
		$file=fopen($ms_cfg_file,"w+");
		fwrite($file,$communities);	
		fclose( $file );
}
 

function find_community_info($id,$ms_cfg_file,$search){
		$field_com=parse_xml_file($ms_cfg_file,$search,"COMMUNITY");
		$i=0;
		while ($field_com[$i]){
			if ($field_com[$i]['ID'] == $id)
				return $field_com[$i];
			$i++;		
		}
		return false;
	
}

function runCommand($command="",$fname) {
	global $l;
	$command = "perl ipdiscover-util.pl $command -xml -h=".SERVER_READ." -u=".COMPTE_BASE." -p=".PSWD_BASE." -d=".DB_NAME." -path=".$fname;
 	exec($command);	
}

?>
