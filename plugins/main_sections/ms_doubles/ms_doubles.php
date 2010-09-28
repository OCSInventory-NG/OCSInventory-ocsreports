<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Pierre LEMMET 2005
// Web: http://ocsinventory.sourceforge.net
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================
//Modified on $Date: 2007/01/26 17:05:42 $$Author: plemmet $($Revision: 1.10 $)
require_once('require/function_computers.php');
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
			$res = mysql_query("SELECT deviceid,id,lastcome FROM hardware WHERE id=".$list_id_fusion[$i], $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));		
			$afus[] = mysql_fetch_array($res,MYSQL_ASSOC);	
			$i++;
		}	
		if (isset($afus))
		fusionne($afus);		
	}
			
	
}

//gestion des restrictions par profils
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
$sql_doublon['macaddress']="select MACADDR val from networks,hardware h where h.id=networks.hardware_id and  MACADDR not in (select macaddress from blacklist_macaddresses) ";
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
	while( $val = mysql_fetch_object( $res ) ){		
		$doublon[$name][] = $val->val;
	}
}



//recherche des id des machines en doublons s�rial number
	$sql_id_doublon['ssn']=" select distinct hardware_id id,SSN info1 from bios,hardware h where h.id=bios.hardware_id and SSN in ";
	$arg_id_doublon['ssn']=array();
	$sql=mysql2_prepare($sql_id_doublon['ssn'],$arg_id_doublon['ssn'],$doublon['ssn']);
	$arg_id_doublon['ssn']=$sql['ARG'];
	$sql_id_doublon['ssn']=$sql['SQL'];

//recherche des id des machines en doublons macaddresses

$sql_id_doublon['macaddress']=" select distinct hardware_id id,MACADDR info1 
								from networks,hardware h 
								where h.id=networks.hardware_id and MACADDR in ";
$arg_id_doublon['macaddress']=array();
$sql=mysql2_prepare($sql_id_doublon['macaddress'],$arg_id_doublon['macaddress'],$doublon['macaddress']);
$arg_id_doublon['macaddress']=$sql['ARG'];
$sql_id_doublon['macaddress']=$sql['SQL'];	

//recherche des id des machines en doublons hostname

$sql_id_doublon['hostname']=" select id, NAME info1 from hardware h,accountinfo a where a.hardware_id=h.id and NAME in ";
$arg_id_doublon['hostname']=array();
$sql=mysql2_prepare($sql_id_doublon['hostname'],$arg_id_doublon['hostname'],$doublon['hostname']);
$arg_id_doublon['hostname']=$sql['ARG'];
$sql_id_doublon['hostname']=$sql['SQL'];	
	
//doublon hostname + serial number
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

//doublon hostname + mac address
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
	$res = mysql2_query_secure($sql_value, $_SESSION['OCS']["readServer"],$arg_id_doublon[$name]);
	$count_id[$name] = 0;
	while( $val = mysql_fetch_object( $res ) ) {
		//on ne compte que les machines appartenant au profil connect�
		//si on est admin, on compte toutes les machines
			if (is_array($tab_id_mes_machines) and in_array ($val->id,$tab_id_mes_machines)){
				$list_id[$name][$val->id]=$val->id;
				$count_id[$name]++;
			}elseif ($tab_id_mes_machines == ""){
				$list_id[$name][$val->id]=$val->id;
				$count_id[$name]++;
			}

		
	}
}
$form_name='doublon';
$table_name='DOUBLON';
echo "<form name='".$form_name."' id='".$form_name."' method='post'>";
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

//affichage des d�tails
if ($protectedPost['detail'] != ''){
	//liste des champs du tableau des doublons
	$list_fields= array($_SESSION['OCS']['TAG_LBL']['TAG']=>'a.TAG',
						$l->g(95)=>'n.macaddr',
						$l->g(36)=>'b.ssn',
						$l->g(23).": id"=>'h.ID',
						$l->g(23).": ".$l->g(46)=>'h.LASTDATE',
						$l->g(35)=>'h.NAME',
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
	$list_fields['CHECK']='h.ID';
	
	$list_col_cant_del=array('NAME'=>'NAME','CHECK'=>'CHECK');
	$default_fields=$list_fields;

	//on modifie le type de champs en num�ric de certain champs
	//pour que le tri se fasse correctement
	$sql=prepare_sql_tab($list_fields,array('SUP','CHECK'));
	$sql['SQL'] .= " from hardware h left join accountinfo a on h.id=a.hardware_id ";
	$sql['SQL'] .= ",bios b, ";

	$sql['SQL'] .= " networks n where  h.id=n.hardware_id ";
	$sql['SQL'] .= " and h.id=b.hardware_id and  h.id in ";
	$sql=mysql2_prepare($sql['SQL'],$sql['ARG'],$list_id[$protectedPost['detail']]);
 	$sql['SQL'] .= " group by h.id ";
	$tab_options['ARG_SQL']=$sql['ARG'];
	$tab_options['FILTRE']=array('NAME'=>$l->g(35),'b.ssn'=>$l->g(36),'n.macaddr'=>$l->g(95));

	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$form_name,'95',$tab_options);
	echo "<br><input type='submit' value='".$l->g(177)."' name='FUSION'>";
	echo "<input type=hidden name=old_detail id=old_detail value='".$protectedPost['detail']."'>";
}



echo "</form>";	


?>
