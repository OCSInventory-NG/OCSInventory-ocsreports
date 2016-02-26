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
	if(AJAX){
		parse_str($protectedPost['ocs']['0'], $params);
		$protectedPost+=$params;
		ob_start();
		$ajax = true;
	}
	else{
		$ajax=false;
	}
	print_item_header($l->g(54));
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	$form_name="affich_processors";
	$table_name=$form_name;
	$tab_options=$protectedPost;
	$tab_options['form_name']=$form_name;
	$tab_options['table_name']=$table_name;
	
	echo open_form($form_name);
	$sql="select id from cpus where hardware_id=%s";
	$arg=$systemid;
	$res =mysql2_query_secure( $sql, $_SESSION['OCS']["readServer"],$arg);
	$val = mysqli_fetch_array( $res );
	if (isset($val['id'])){
		$list_fields=array($l->g(64) => 'MANUFACTURER',
						   $l->g(66) => 'TYPE',
						   $l->g(36) => 'SERIALNUMBER',
						   $l->g(429) => 'SPEED',
						   $l->g(1317) => 'CORES',
						   $l->g(1318) => 'L2CACHESIZE',
						   $l->g(1247) => 'CPUARCH',
						   $l->g(1312) => 'DATA_WIDTH',
						   $l->g(1313) => 'CURRENT_ADDRESS_WIDTH',
						   $l->g(1314) => 'LOGICAL_CPUS',
						   $l->g(1319) => 'VOLTAGE',
						   $l->g(1315) => 'CURRENT_SPEED',
						   $l->g(1316) => 'SOCKET');
		$list_col_cant_del=$list_fields;
		$default_fields= $list_fields;
		$queryDetails  = "SELECT * FROM cpus WHERE (hardware_id=$systemid)";
		
		
	}else{
	
		$list_fields=array($l->g(66) => 'PROCESSORT',
						   $l->g(377) => 'PROCESSORS',
						   $l->g(55) => 'PROCESSORN');
		$list_col_cant_del=$list_fields;
		$default_fields= $list_fields;
	//	$tab_options['FILTRE']=array('NAME'=>$l->g(49),'MANUFACTURER'=>$l->g(64),'TYPE'=>$l->g(66));
		$queryDetails  = "SELECT * FROM hardware WHERE (id=$systemid)";
	}
	ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	echo close_form();
	if ($ajax){
		ob_end_clean();
		tab_req($list_fields,$default_fields,$list_col_cant_del,$queryDetails,$tab_options);
		ob_start();
	}
?>