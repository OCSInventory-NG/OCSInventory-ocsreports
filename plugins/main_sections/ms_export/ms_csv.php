<?php
/*
 * Created on 17 juin 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
$values=look_config_default_values(array('EXPORT_SEP'));
if (isset($values['tvalue']['EXPORT_SEP']) and $values['tvalue']['EXPORT_SEP'] != '')
	$separator=$values['tvalue']['EXPORT_SEP'];
else
	$separator=';';
$link=$_SESSION['OCS']["readServer"];	
$toBeWritten = "";
if (isset($protectedGet['log'])){
	
	if (file_exists($protectedGet['rep'].$protectedGet['log'])){
		$tab = file($protectedGet['rep'].$protectedGet['log']);
		while(list($cle,$val) = each($tab)) {
 		  $toBeWritten  .= $val."\r\n";
		}
		$filename=$protectedGet['log'];
	}
}//gestion par valeur en cache (LIMITE A 200)
elseif (!$_SESSION['OCS']['DATA_CACHE'][$protectedGet['tablename']][199]){
	$filename="cache.csv";
	//print_r($_SESSION['OCS']['col_tab'][$protectedGet['tablename']]);
	//gestion des entetes
	if (is_array($_SESSION['OCS']['col_tab'][$protectedGet['tablename']]))
	foreach ($_SESSION['OCS']['col_tab'][$protectedGet['tablename']] as $name){
		if ($name != 'SUP' and $name != 'CHECK' and $name != 'NAME' and $name != $l->g(23)){
		//	echo "<br>".$_SESSION['OCS']['list_fields'][$name]." => ".$_SESSION['OCS']['list_fields'][$name]{1};
			if ($_SESSION['OCS']['list_fields'][$name]{1} == ".")
			$lbl=substr(strrchr($_SESSION['OCS']['list_fields'][$name], "."), 1);
			else
			$lbl=$_SESSION['OCS']['list_fields'][$name];
			$col[$lbl]=$name;
			//echo "toto".substr(strrchr($_SESSION['OCS']['list_fields'][$name], "."), 1);
			$toBeWritten .=$name.";";
		}elseif($name == 'NAME' or $name == $l->g(23)){
			$col['name_of_machine']="name_of_machine";
			$toBeWritten .="machine".$separator;
		}		
	}
	$i=0;
	//print_r($_SESSION['OCS']['DATA_CACHE'][$protectedGet['tablename']]);
	while ($_SESSION['OCS']['DATA_CACHE'][$protectedGet['tablename']][$i]){
		$toBeWritten .="\r\n";
		foreach ($col as $lbl => $name){
			if ($lbl == "name_of_machine" and !isset($_SESSION['OCS']['DATA_CACHE'][$protectedGet['tablename']][0])){
				$lbl='name';
			}
		//	echo $lbl."<br>";
			if ($_SESSION['OCS']['DATA_CACHE'][$protectedGet['tablename']][$i][$lbl])
			$toBeWritten .=$_SESSION['OCS']['DATA_CACHE'][$protectedGet['tablename']][$i][$lbl].$separator;
			
		}
		$i++;
	}
	//$toBeWritten = "toto";
}elseif (isset($_SESSION['OCS']['csv']['SQL'][$protectedGet['tablename']])){
	$toBeWritten="";
	//gestion des entetes
	foreach ($_SESSION['OCS']['col_tab'][$protectedGet['tablename']] as $name){
		if ($name != 'SUP' and $name != 'CHECK' and $name != 'NAME'){
			if ($_SESSION['OCS']['list_fields'][$name]{1} == ".")
			$lbl=substr(strrchr($_SESSION['OCS']['list_fields'][$name], "."), 1);
			else
			$lbl=$_SESSION['OCS']['list_fields'][$name];
			$col[$lbl]=$name;
			//echo "toto".substr(strrchr($_SESSION['OCS']['list_fields'][$name], "."), 1);
			$toBeWritten .=$name.";";
		}elseif($name == 'NAME' or $name == $l->g(23)){
			//echo $name;
			$col['name_of_machine']="name_of_machine";
			$toBeWritten .="machine".$separator;
		}		
	}
	
	//gestion des donnees fixes
	if (isset($_SESSION['OCS']['SQL_DATA_FIXE'][$protectedGet['tablename']])){
		$i=0;
		
		while($_SESSION['OCS']['SQL_DATA_FIXE'][$protectedGet['tablename']][$i]){
			$result=mysql_query($_SESSION['OCS']['SQL_DATA_FIXE'][$protectedGet['tablename']][$i], $link) or die(mysql_error($link));
			while( $cont = mysql_fetch_array($result,MYSQL_ASSOC) ) {
				//print_r($cont);
				foreach ($col as $field => $lbl){
					if (array_key_exists($lbl,$cont)){
					
						$data_fixe[$cont['HARDWARE_ID']][$field]=$cont[$lbl];
					}
				}
			}
			$i++;	
		}
		
	}
	//print_r($data_fixe);
	//gestion de la requete de r�sultat
	if ($_SESSION['OCS']['csv']['ARG'][$protectedGet['tablename']])
		$arg=$_SESSION['OCS']['csv']['ARG'][$protectedGet['tablename']];
	else
		$arg='';	
	$result=mysql2_query_secure($_SESSION['OCS']['csv']['SQL'][$protectedGet['tablename']], $link,$arg);
	$i=0;
	while( $cont = mysql_fetch_array($result,MYSQL_ASSOC) ) {
		foreach ($col as $field => $lbl){
			if ($lbl == "name_of_machine" and !isset($cont[$field])){
				$field='name';
			}
//			print_r($cont);
//			echo $cont[$field];
			if (isset($cont[$field])){
			$data[$i][$lbl]=$cont[$field];			
			}elseif (isset($data_fixe[$cont['ID']][$field]))
			$data[$i][$lbl]=$data_fixe[$cont['ID']][$field];	
//			elseif (isset($_SESSION['OCS']['list_fields'][$field]))
//			$data[$i][$lbl]=$cont[$field];	
		}
		$i++;
	}
//	
//	$_SESSION['OCS']['list_fields']
//	if (isset($_SESSION['OCS']['SQL_DATA_FIXE'][$protectedGet['tablename']])){
//		
//		
//	}
	$i=0;
	while ($data[$i]){
		//print_r($data[$i]);
		//echo "<br>";
		$toBeWritten .="\r\n";
		foreach ($data[$i] as $field_name=>$donnee){
		$toBeWritten .=$donnee.$separator;		
		}
		$i++;
	}

	$filename="export.csv";
}
	
if ($toBeWritten != ""){
	// iexplorer problem
	if( ini_get("zlib.output-compression"))
		ini_set("zlib.output-compression","Off");
		
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-control: private", false);
	header("Content-type: application/force-download");
	header("Content-Disposition: attachment; filename=\"".$filename."\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".strlen($toBeWritten));
	echo $toBeWritten;
	die();
}else
	msg_error($l->g(920));
?>
