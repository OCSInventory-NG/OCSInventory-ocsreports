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

$snmp_tables_type=array($l->g(1215)=>'SNMP_BLADES',$l->g(1216)=>'SNMP_FIREWALLS',$l->g(1217)=>'SNMP_LOADBALANCERS',
					    $l->g(79)=>'SNMP_PRINTERS',$l->g(1218)=>'SNMP_SWITCHINFOS',$l->g(729)=>'SNMP_COMPUTERS'); 

					    
$snmp_tables=array('SNMP_ACCOUNTINFO','SNMP_CARDS','SNMP_CARTRIDGES','SNMP_CPUS','SNMP_DRIVES',
				'SNMP_FANS','SNMP_INPUTS','SNMP_LOCALPRINTERS','SNMP_MEMORIES',
				'SNMP_MODEMS','SNMP_NETWORKS','SNMP_PORTS','SNMP_POWERSUPPLIES','SNMP_SOFTWARES',
				'SNMP_SOUNDS','SNMP_STORAGES','SNMP_SWITCHS','SNMP_TRAYS','SNMP_VIDEOS','SNMP_VIRTUALMACHINES');

$all_snmp_table=array_merge($snmp_tables_type,$snmp_tables);
//is ID exist?
function info_snmp($snmp_id){
	global $l,$snmp_tables_type;
	if ($snmp_id == "" or !is_numeric($snmp_id)){
		return $l->g(837);	
	}
	
	//$arg=array();
	$sql="select * from snmp where id=%s";
	$arg=$snmp_id;
	$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
	$array['data']['snmp'] = mysqli_fetch_object($result);
	if ( $array['data']['snmp']->ID == ""){
		return $l->g(837);	
	}else{
		foreach($snmp_tables_type as $lbl=>$table){
			$sql="select * from %s where snmp_id=%s";
			$arg=array(strtolower($table),$snmp_id);
			$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
			$array['data'][$table] = mysqli_fetch_object($result);
			if ($array['data'][$table] != '')
				$array['lbl']=$lbl;
		}
		return $array;		
	}

	/*foreach($snmp_tables_type as $id=>$table){
		$table_alias[]=$var.'.*';
		$sql.= " left join %s %s on sn.id=%s.snmp_id ";
		array_push($arg,strtolower($table));
		array_push($arg,$var);
		array_push($arg,$var);
		$var++;
	}
	
	$sql= "select ".implode(',',$table_alias).$sql." where sn.id = %s";
	array_push($arg,$snmp_id);*/
	
	
	
}


function subnet_name($systemid){
	if (!is_numeric($systemid))
	return false;	
	$reqSub = "select NAME,NETID from subnet left join networks on networks.ipsubnet = subnet.netid 
				where  networks.status='Up' and hardware_id=".$systemid;
	$resSub = mysqli_query($_SESSION['OCS']["readServer"],$reqSub) or die(mysqli_error($_SESSION['OCS']["readServer"]));
	while($valSub = mysqli_fetch_object( $resSub )){
		
		$returnVal[]=$valSub->NAME."  (".$valSub->NETID.")";
	}	
	return 	$returnVal;
}

function print_item_header($text)
{
	echo "<table align=\"center\"  width='100%'  cellpadding='4'>";
	echo "<tr>";
	echo "<td align='center' width='100%'><b><font color='blue'>".mb_strtoupper($text)."</font></b></td>";
	echo "</tr>";
	echo "</table>";	
}

function bandeau($data,$lbl_affich,$title='',$class='mlt_bordure'){
	$nb_col=2;
	$data_exist=false;
	$show_table = "<table ALIGN = 'Center' class='".$class."' ><tr><td align =center colspan=20>";
	if ($title != '')
	$show_table .= "<i><b><big>".$title."</big></b><br><br></i></td></tr><tr><td align =center>";
	$show_table .= "		<table align=center border='0' width='100%'  ><tr>";
	$i=0;
	foreach ($data as $table=>$values){
		if (is_object($values)){
			foreach ($values as $field=>$field_value){
				$data_exist=true;
				if (trim($field_value) != '' 
						and $field != 'ID' 
						and $field != 'SNMP_ID'){
					if ($i == $nb_col){
						$show_table .= "</tr><tr>";
						$i=0;			
					}
					$show_table.= "<td>&nbsp;<b>";
					if (isset($lbl_affich[$field]))
						$show_table.= $lbl_affich[$field];
					else
						$show_table.= $field;
					$show_table.= ": </b></td><td>".$field_value."</td>";
					$i++;
				}
			}
		}
		
		
	}		
	$show_table.= "</tr></table></td></tr></table><br>";	
	
	if ($data_exist)
		return $show_table;
	return false;
	
}

function deleteDid_snmp($id){
	global $all_snmp_table;
	if (is_array($id))
		$id_snmp=explode(',',$id);
	else
		$id_snmp=$id;
	//p($all_snmp_table);
	foreach ($all_snmp_table as $key=>$values){
		$sql='delete from %s where snmp_id in ';
		$arg=array(strtolower($values));
		$del_sql=mysql2_prepare($sql,$arg,$id_snmp,$nocot=true);
		mysql2_query_secure($del_sql['SQL'],$_SESSION['OCS']["writeServer"],$del_sql['ARG'],true);		
	}	

	$sql='delete from snmp where id in ';
	$del_sql=mysql2_prepare($sql,array(),$id_snmp,$nocot=true);
	mysql2_query_secure($del_sql['SQL'],$_SESSION['OCS']["writeServer"],$del_sql['ARG'],true);			
}


/*
 * 
 *Find all accountinfo for  
 * snmp data
 * 
 */
function admininfo_snmp($id = ""){
	global $l;
	if (!is_numeric($id) and $id != "")
		return $l->g(400);		
	$arg_account_data=array();	
	$sql_account_data="SELECT * FROM snmp_accountinfo ";
	if (is_numeric($id)){
		$sql_account_data.= " WHERE snmp_id=%s";
		$arg_account_data=array($id);
	}else
		$sql_account_data.= " LIMIT 1 ";
	
	$res_account_data=mysql2_query_secure($sql_account_data,$_SESSION['OCS']["readServer"],$arg_account_data);
	$val_account_data = mysqli_fetch_array( $res_account_data );
	return $val_account_data;	
}

function updateinfo_snmp($id,$values,$list=''){
	global $l;
	if (!is_numeric($id) and $list == '')
		return $l->g(400);		
	$arg_account_data=array();	
	$sql_account_data="UPDATE snmp_accountinfo SET ";
	foreach ($values as $field=>$val){
		$sql_account_data .= " %s='%s', ";
		array_push($arg_account_data,$field);
		array_push($arg_account_data,$val);		
	}
	$sql_account_data = substr($sql_account_data,0,-2);
	if (is_numeric($id) and $list == '')
	$sql_account_data.=" WHERE snmp_id=%s";
	if ($list != '')
	$sql_account_data.=" WHERE snmp_id in (%s)";
	
	array_push($arg_account_data,$id);	
	mysql2_query_secure($sql_account_data,$_SESSION['OCS']["readServer"],$arg_account_data);
	return $l->g(1121);	
}


?>