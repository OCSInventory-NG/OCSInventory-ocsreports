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
if ((array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')){  
		parse_str($protectedPost['ocs']['0'], $params);	
		$protectedPost+=$params; 
		
	ob_start();
	$ajax = true;
}
else{
	$ajax=false;
}


echo "test";
require_once('require/function_computers.php');
if ($protectedPost['DEL_ALL'] != ''){
	foreach ($protectedPost as $key=>$value){
		$checkbox=explode('check',$key);
		if(isset($checkbox[1])){
			deleteDid($checkbox[1]);			
		}
	}
}
if ($protectedPost['SUP_PROF'] != '' and is_numeric($protectedPost['SUP_PROF'])){
	deleteDid($protectedPost['SUP_PROF']);	
}


if ($protectedPost['FUSION']){
	foreach ($protectedPost as $name=>$value){
		if (substr($name,0,5) == "check"){
			$list_id_fusion[]= substr($name,5);			
		}		
	}
	if (count($list_id_fusion)<2){
			echo "<script>alert('".$l->g(922)."');</script>";
	}else{
		$afus=array();
		$i=0;
		while (isset($list_id_fusion[$i])){
			$res = mysqli_query($_SESSION['OCS']["readServer"],"SELECT deviceid,id,lastcome FROM hardware WHERE id=".$list_id_fusion[$i]) or die(mysqli_error($_SESSION['OCS']["readServer"]));		
			$afus[] = mysqli_fetch_array($res,MYSQL_ASSOC);	
			$i++;
		}	
		if (isset($afus))
		fusionne($afus);		
	}	
}

//restriction for profils?
if ($_SESSION['OCS']['mesmachines']){
	$tab_id_mes_machines=computer_list_by_tag('','ARRAY');
	if ($tab_id_mes_machines=="ERROR"){
		echo $l->g(923);
		break;
	}
}else{
	$tab_id_mes_machines="";
}

	
printEnTete($l->g(199));

/************************  hostname double ***************************************/
$sql_doublon['hostname'] = "select NAME val from hardware ";
$arg_doublon['hostname'] = array();

if (isset($tab_id_mes_machines) and $tab_id_mes_machines != ""){
	$sql=mysql2_prepare($sql_doublon['hostname'].' where id in ',$arg_doublon['hostname'],$tab_id_mes_machines);
	$sql_doublon['hostname']=$sql['SQL'];
	$arg_doublon['hostname']=$sql['ARG'];
}
$sql_doublon['hostname'] .= "  group by NAME having count(NAME)>1";

/************************  serial number double ***************************************/
$sql_doublon['ssn']="select SSN val from bios,hardware h where h.id=bios.hardware_id and SSN not in (select serial from blacklist_serials) ";
$arg_doublon['ssn'] = array();
if (isset($tab_id_mes_machines) and $tab_id_mes_machines != ""){
	$sql=mysql2_prepare($sql_doublon['ssn'].' and hardware_id in ',$arg_doublon['ssn'],$tab_id_mes_machines);
	$sql_doublon['ssn']=$sql['SQL'];
	$arg_doublon['ssn']=$sql['ARG'];
}
$sql_doublon['ssn'].=" group by SSN having count(SSN)>1";

/************************  macaddress double ***************************************/
$sql_doublon['macaddress']="select h.id, MACADDR val 
							from (select hardware_id,MACADDR from networks group by hardware_id,MACADDR) networks,hardware h 
							where h.id=networks.hardware_id 
									and  MACADDR not in (select macaddress from blacklist_macaddresses)";
$arg_doublon['macaddress']=array();
if (isset($tab_id_mes_machines) and $tab_id_mes_machines != ""){
	$sql=mysql2_prepare($sql_doublon['macaddress'].' and hardware_id in ',$arg_doublon['macaddress'],$tab_id_mes_machines);
	$sql_doublon['macaddress']=$sql['SQL'];
	$arg_doublon['macaddress']=$sql['ARG'];
}

/*****************************request execution*****************************************/
$sql_doublon['macaddress'].=" group by MACADDR having count(MACADDR)>1";
foreach($sql_doublon as $name=>$sql_value){
	$res = mysql2_query_secure($sql_value, $_SESSION['OCS']["readServer"],$arg_doublon[$name]);
	while( $val = mysqli_fetch_object( $res ) ){		
		$doublon[$name][] = $val->val;
	}
}



//search id of computers => serial number
if (isset($doublon['ssn'])){
	$sql_id_doublon['ssn']=" select distinct hardware_id id,SSN info1 from bios,hardware h where h.id=bios.hardware_id and SSN in ";
	$arg_id_doublon['ssn']=array();
	$sql=mysql2_prepare($sql_id_doublon['ssn'],$arg_id_doublon['ssn'],$doublon['ssn']);
	$arg_id_doublon['ssn']=$sql['ARG'];
	$sql_id_doublon['ssn']=$sql['SQL'];
}else
	$count_id['ssn']=0;
////search id of computers => macaddresses
if(isset($doublon['macaddress'])){
	$sql_id_doublon['macaddress']=" select distinct hardware_id id,MACADDR info1 
									from networks,hardware h 
									where h.id=networks.hardware_id and MACADDR in ";
	$arg_id_doublon['macaddress']=array();
	$sql=mysql2_prepare($sql_id_doublon['macaddress'],$arg_id_doublon['macaddress'],$doublon['macaddress']);
	$arg_id_doublon['macaddress']=$sql['ARG'];
	$sql_id_doublon['macaddress']=$sql['SQL'];	
}else
	$count_id['macaddress']=0;
//search id of computers => hostname
if(isset($doublon['hostname'])){
	$sql_id_doublon['hostname']=" select id, NAME info1 from hardware h,accountinfo a where a.hardware_id=h.id and NAME in ";
	$arg_id_doublon['hostname']=array();
	$sql=mysql2_prepare($sql_id_doublon['hostname'],$arg_id_doublon['hostname'],$doublon['hostname']);
	$arg_id_doublon['hostname']=$sql['ARG'];
	$sql_id_doublon['hostname']=$sql['SQL'];	
}else
	$count_id['hostname']=0;
//search id of computers => hostname + serial number
$sql_id_doublon['hostname_serial']="SELECT DISTINCT h.id,h.name info1,b.ssn info2
						FROM hardware h 
						LEFT JOIN bios b ON b.hardware_id = h.id 
						LEFT JOIN hardware h2 on h.name=h2.name
						LEFT JOIN  bios b2 on b2.ssn = b.ssn
						WHERE  b2.hardware_id = h2.id 
						AND h.id <> h2.id and b.ssn not in (select serial from blacklist_serials) ";
$arg_id_doublon['hostname_serial']=array();
if (isset($tab_id_mes_machines) and $tab_id_mes_machines != ""){
	$sql=mysql2_prepare($sql_id_doublon['hostname_serial'].' and h.id in ',$arg_id_doublon['hostname_serial'],$tab_id_mes_machines);
	$sql_id_doublon['hostname_serial']=$sql['SQL'];
	$arg_id_doublon['hostname_serial']=$sql['ARG'];
}

//search id of computers => hostname + mac address
$sql_id_doublon['hostname_macaddress']="SELECT DISTINCT h.id,h.name info1,n.macaddr info2
						FROM hardware h 
						LEFT JOIN networks n ON n.hardware_id = h.id 
						LEFT JOIN hardware h2 on h.name=h2.name
						LEFT JOIN  networks n2 on n2.MACADDR = n.MACADDR
						WHERE  n2.hardware_id = h2.id 
						AND h.id <> h2.id and n.MACADDR not in (select macaddress from blacklist_macaddresses)";
$arg_id_doublon['hostname_macaddress']=array();
if (isset($tab_id_mes_machines) and $tab_id_mes_machines != ""){
	$sql=mysql2_prepare($sql_id_doublon['hostname_macaddress'].' and h.id in ',$arg_id_doublon['hostname_macaddress'],$tab_id_mes_machines);
	$sql_id_doublon['hostname_macaddress']=$sql['SQL'];
	$arg_id_doublon['hostname_macaddress']=$sql['ARG'];
}


$sql_id_doublon['macaddress_serial']="SELECT DISTINCT h.id, n1.macaddr info1, b.ssn info2 
									  FROM hardware h 
										LEFT JOIN bios b ON b.hardware_id = h.id 
										LEFT JOIN networks n1 on b.hardware_id=n1.hardware_id
										LEFT JOIN networks n2 on n1.macaddr = n2.macaddr
										LEFT JOIN bios b2 on b2.ssn = b.ssn
									  WHERE n1.hardware_id = h.id 
										AND b2.hardware_id = n2.hardware_id 
										AND b2.hardware_id <> b.hardware_id 
										AND b.ssn not in (select serial from blacklist_serials)
										AND n1.macaddr not in (select macaddress from blacklist_macaddresses)";
$arg_id_doublon['macaddress_serial']=array();
if (isset($tab_id_mes_machines) and $tab_id_mes_machines != ""){
	$sql=mysql2_prepare($sql_id_doublon['macaddress_serial'].' and h.id in ',$arg_id_doublon['macaddress_serial'],$tab_id_mes_machines);
	$sql_id_doublon['macaddress_serial']=$sql['SQL'];
	$arg_id_doublon['macaddress_serial']=$sql['ARG'];
}
foreach($sql_id_doublon as $name=>$sql_value){
	$sql_value.=" group by id";
	$res = mysql2_query_secure($sql_value, $_SESSION['OCS']["readServer"],$arg_id_doublon[$name]);
	$count_id[$name] = 0;
	while( $val = mysqli_fetch_object( $res ) ) {
		//if restriction => count only computers of profil
		//else, all computers
			if (is_array($tab_id_mes_machines) and in_array ($val->id,$tab_id_mes_machines)){
				$list_id[$name][$val->id]=$val->id;
				$count_id[$name]++;
			}elseif ($tab_id_mes_machines == ""){
				$list_id[$name][$val->id]=$val->id;
				$count_id[$name]++;
			}		
			$list_info[$name][]=$val->info1;
	}
}
$form_name='doublon';
$table_name='DOUBLON';
$tab_options=$protectedPost;
$tab_options['form_name']=$form_name;
$tab_options['table_name']=$table_name;
echo open_form($form_name);
echo "<br><table BORDER='0' WIDTH = '25%' ALIGN = 'Center' CELLPADDING='0' BGCOLOR='#C7D9F5' BORDERCOLOR='#9894B5'>";
foreach ($count_id as $lbl=>$count_value){
	echo "<tr><td align='center'>";
	switch($lbl) {
		case "hostname_serial": echo $l->g(193); break ;
		case "hostname_macaddress": echo $l->g(194); break ;
		case "macaddress_serial": echo $l->g(195); break ;
		case "hostname": echo $l->g(196); break ;
		case "ssn": echo $l->g(197); break ;
		case "macaddress": echo $l->g(198); break ;
	}
	echo  ":&nbsp;<b>";
	if ($count_value != 0)
	echo "<a href=# onclick='pag(\"".$lbl."\",\"detail\",\"".$form_name."\");' alt='".$l->g(41)."'>";
	echo $count_value;
	if ($count_value != 0)
	echo "</a>";
	echo "</b></td></tr>";
	if ($protectedPost['detail'] == $lbl and $count_value == 0)
	unset($protectedPost['detail']);
}
echo "</table><br>";
echo "<input type=hidden name=detail id=detail value='".$protectedPost['detail']."'>";

//show details
if ($protectedPost['detail'] != ''){
	//BEGIN SHOW ACCOUNTINFO
	require_once('require/function_admininfo.php');
	$accountinfo_value=interprete_accountinfo($list_fields,$tab_options);
	if (array($accountinfo_value['TAB_OPTIONS']))
		$tab_options=$accountinfo_value['TAB_OPTIONS'];
	if (array($accountinfo_value['DEFAULT_VALUE']))
		$default_fields=$accountinfo_value['DEFAULT_VALUE'];
	$list_fields=$accountinfo_value['LIST_FIELDS'];
	//END SHOW ACCOUNTINFO
	$list_fields2= array($l->g(95)=>'n.macaddr',
						$l->g(36)=>'b.ssn',
						$l->g(23).": id"=>'h.ID',
						$l->g(23).": ".$l->g(46)=>'h.LASTDATE',
						'NAME'=>'h.NAME',
						$l->g(82).": ".$l->g(33)=>'h.WORKGROUP',
						$l->g(23).": ".$l->g(25)=>'h.OSNAME',
						$l->g(23).": ".$l->g(24)=>'h.USERID',
						$l->g(23).": ".$l->g(26)=>'h.MEMORY',
						$l->g(23).": ".$l->g(569)=>'h.PROCESSORS',
						$l->g(23).": ".$l->g(34)=>'h.IPADDR',
						$l->g(23).": ".$l->g(53)=>'h.DESCRIPTION',
						$l->g(23).": ".$l->g(354)=>'h.FIDELITY',
						$l->g(23).": ".$l->g(820)=>'h.LASTCOME',
						$l->g(23).": ".$l->g(351)=>'h.PROCESSORN',
						$l->g(23).": ".$l->g(350)=>'h.PROCESSORT',
						$l->g(23).": ".$l->g(357)=>'h.USERAGENT',
						$l->g(23).": ".$l->g(50)=>'h.SWAP',
						$l->g(23).": ".$l->g(111)=>'h.WINPRODKEY',
						$l->g(23).": ".$l->g(553)=>'h.WINPRODID');
	$list_fields=array_merge ($list_fields,$list_fields2);
	$list_fields['CHECK']='h.ID';	
	$list_col_cant_del=array('NAME'=>'NAME','CHECK'=>'CHECK',$l->g(35));
	$default_fields2=array($l->g(95)=>$l->g(95),$l->g(36)=>$l->g(36),
						   $l->g(23).": ".$l->g(46)=>$l->g(23).": ".$l->g(46),
						   $l->g(23).": ".$l->g(34)=>$l->g(23).": ".$l->g(34));
	$default_fields=array_merge ($default_fields,$default_fields2);
	if ($_SESSION['OCS']['CONFIGURATION']['DELETE_COMPUTERS'] == "YES"){
		$list_fields['SUP']='h.ID';
		$list_col_cant_del['SUP']='SUP';
	}
	$sql=prepare_sql_tab($list_fields,array('SUP','CHECK'));
	$sql['SQL'] .= " from hardware h left join accountinfo a on h.id=a.hardware_id ";
	$sql['SQL'] .= ",bios b, ";

	$sql['SQL'] .= " networks n where  h.id=n.hardware_id ";
	$sql['SQL'] .= " and h.id=b.hardware_id and  h.id in ";
	$sql=mysql2_prepare($sql['SQL'],$sql['ARG'],$list_id[$protectedPost['detail']]);
	if (($protectedPost['detail'] == "macaddress" or $protectedPost['detail'] == "macaddress_serial")
			 and count($list_info)>0){
		$sql['SQL'] .= " and n.macaddr in ";
		$sql=mysql2_prepare($sql['SQL'],$sql['ARG'],$list_info[$protectedPost['detail']]);
		
	}
 	$sql['SQL'] .= " group by h.id ";
	$tab_options['ARG_SQL']=$sql['ARG'];
	$tab_options['FILTRE']=array('NAME'=>$l->g(35),'b.ssn'=>$l->g(36),'n.macaddr'=>$l->g(95));
	$tab_options['LBL_POPUP']['SUP']='NAME';
	$tab_options['LBL']['SUP']=$l->g(122);
	$result_exist=ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	if ($result_exist != "" and $_SESSION['OCS']['CONFIGURATION']['DELETE_COMPUTERS'] == "YES"){
		echo "<a href=# OnClick='confirme(\"\",\"DEL_SEL\",\"".$form_name."\",\"DEL_ALL\",\"".$l->g(900)."\");'><img src='image/sup_search.png' title='Supprimer' ></a>";
		echo "<input type='hidden' id='DEL_ALL' name='DEL_ALL' value=''>";
	}
	echo "<br><input type='submit' value='".$l->g(177)."' name='FUSION'>";
	echo "<input type=hidden name=old_detail id=old_detail value='".$protectedPost['detail']."'>";
}
echo close_form();

if ($ajax){
	ob_end_clean();
	tab_req($list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$tab_options);
}
?>
