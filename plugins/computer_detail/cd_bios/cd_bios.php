<?php
	print_item_header($l->g(273));
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	if ($protectedPost['OTHER_BIS'] != ''){
		$sql="INSERT INTO blacklist_serials (SERIAL) value ('%s')";
		$arg=array($protectedPost['OTHER_BIS']);
		mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
	}
	if ($protectedPost['OTHER'] != ''){
		$sql="DELETE FROM blacklist_serials WHERE SERIAL='%s'";
		$arg=array($protectedPost['OTHER']);
		mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
	}

	$form_name="affich_bios";
	$table_name=$form_name;
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	$list_fields=array($l->g(36) => 'SSN',
					   $l->g(64) => 'SMANUFACTURER',
					   $l->g(65) => 'SMODEL',
					   $l->g(66) => 'TYPE',
					   $l->g(284) => 'BMANUFACTURER',
					   $l->g(209) => 'BVERSION',
					   $l->g(210) => 'BDATE',
					   'ASSETTAG' =>'ASSETTAG');
	$sql="select SSN from bios WHERE (hardware_id=%s)";
	$arg=array($systemid);
	$resultDetails = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
	$item = mysql_fetch_object($resultDetails);	
	$sql="select ID from blacklist_serials where SERIAL='%s'";		
	$arg=array($item->SSN);
	$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
	if ($_SESSION['OCS']['ADMIN_BLACKLIST']['SERIAL']=='YES'){
		if ( mysql_num_rows($result) == 1 ){	
			$tab_options['OTHER'][$l->g(36)][$item->SSN]=$item->SSN;
			$tab_options['OTHER']['IMG']='image/red.png';	   
		}else{
			$tab_options['OTHER_BIS'][$l->g(36)][$item->SSN]=$item->SSN;
			$tab_options['OTHER_BIS']['IMG']='image/green.png';
		}
	}
	
	//$list_fields['SUP']= 'ID';
	$list_col_cant_del[$l->g(36)]=$l->g(36);
	$default_fields= $list_fields;
	$queryDetails  = "SELECT ";
	foreach ($list_fields as $lbl=>$value){
			$queryDetails .= $value.",";		
	}
	$queryDetails  = substr($queryDetails,0,-1)." FROM bios WHERE (hardware_id=$systemid)";
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,80,$tab_options);
	echo "</form>";

?>