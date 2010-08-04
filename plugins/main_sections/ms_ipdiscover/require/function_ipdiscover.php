<?php
/*
 * Created on 7 mai 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

function find_info_subnet($netid){
	$sql="select NETID,NAME,ID,MASK from subnet where netid='%s'";
	$arg=$netid;
	$res=mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
	$row=mysql_fetch_object($res);
	return $row;
	
}

function find_info_type($name='',$id=''){	
	if ($name != ''){	
		$sql="select ID,NAME from devicetype where NAME = '%s'";
		$arg=$name;
	}elseif ($id != ''){
		$sql="select ID,NAME from devicetype where ID = '%s'";
		$arg=$id;
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

			$tab_typ_champ[1]["CONFIG"]['DEFAULT']="YES";
			$tab_typ_champ[1]['COMMENT_BEHING']="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_adminvalues']."&head=1&tag=ID_IPDISCOVER&form=".$form."\",\"admin_id_ipdiscover\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")><img src=image/plus.png></a>";

		tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title,$comment="");
	
}



function verif_base_methode($base){
		global $l;
		
	if ($_SESSION['OCS']['ipdiscover_methode'] != $base){
		return "<font color=red><b>".$l->g(929)."<br>".$l->g(930)."</b></font><br><br>";
	}else
		return false;	
}

function add_subnet($add_ip,$sub_name,$id_name,$add_sub){
	global $l;
	
		if (trim($add_ip) == '')
			return $l->g(932);	
		if (trim($sub_name) == '')
			return $l->g(933);	
		if (trim($id_name) == '')
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


function add_type($name){
	global $l;
	
	if (trim($name) == ''){
		return $l->g(936);		
	}else{
		$row=find_info_type($name);
		if (isset($row->ID))
			return $l->g(937);	
	}
	$sql="insert into devicetype (NAME) VALUES ('%s')";
	$arg=$name;
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
 
?>
