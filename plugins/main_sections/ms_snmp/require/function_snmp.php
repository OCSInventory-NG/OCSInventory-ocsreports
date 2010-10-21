<?php
/*
 * Function for snmp details
 * 
 */
 
$snmp_tables=array('SNMP_BLADES','SNMP_CARDS','SNMP_CARTRIDGES',
				'SNMP_DRIVES','SNMP_FANS','SNMP_FIREWALLS',
				'SNMP_LOADBALANCERS','SNMP_NETWORKS','SNMP_POWERSUPPLIES',
				'SNMP_PRINTERS','SNMP_STORAGES','SNMP_SWITCHS','SNMP_TRAYS');


//is ID exist?
function info_snmp($snmp_id){
	global $l;
	if ($snmp_id == "" or !is_numeric($snmp_id)){
		return $l->g(837);	
	}
	$sql="select * from snmp where id = %s";
	$arg=$snmp_id;
	$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
	$item = mysql_fetch_object($result);
	if ( $item -> ID == ""){
		return $l->g(837);	
	}else{
		return $item;		
	}
	
	
}


function subnet_name($systemid){
	if (!is_numeric($systemid))
	return false;	
	$reqSub = "select NAME,NETID from subnet left join networks on networks.ipsubnet = subnet.netid 
				where  networks.status='Up' and hardware_id=".$systemid;
	$resSub = mysql_query($reqSub, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
	while($valSub = mysql_fetch_object( $resSub )){
		
		$returnVal[]=$valSub->NAME."  (".$valSub->NETID.")";
	}	
	return 	$returnVal;
}

function print_item_header($text)
{
	echo "<br><br><table align=\"center\"  width='100%'  cellpadding='4'>";
	echo "<tr>";
	echo "<td align='center' width='100%'><b><font color='blue'>".strtoupper($text)."</font></b></td>";
	echo "</tr>";
	echo "</table><br>";	
}

function bandeau($data,$lbl){
	$nb_col=2;
	echo "<table ALIGN = 'Center' class='mlt_bordure' ><tr><td align =center>";
	echo "		<table align=center border='0' width='95%'  ><tr>";
	$i=0;
	foreach ($data as $name=>$value){
		if (trim($value) != ''){
			if ($i == $nb_col){
				echo "</tr><tr>";
				$i=0;			
			}
			echo "<td >&nbsp;<b>".$lbl[$name]." :</b></td><td >".$value."</td>";
			$i++;
		}
	}
	echo "</tr></table></td></tr></table>";	
}

function deleteDid_snmp($id){
	global $snmp_tables;
	if (is_array($id))
		$id_snmp=explode(',',$id);
	else
		$id_snmp=$id;
	
	foreach ($snmp_tables as $key=>$values){
		$sql='delete from %s where snmp_id in ';
		$arg=$values;
		$del_sql=mysql2_prepare($sql,$arg,$id_snmp,$nocot=true);
		mysql2_query_secure($del_sql['SQL'],$_SESSION['OCS']["writeServer"],$del_sql['ARG'],true);		
	}	

	$sql='delete from SNMP where id in ';
	$del_sql=mysql2_prepare($sql,array(),$id_snmp,$nocot=true);
	mysql2_query_secure($del_sql['SQL'],$_SESSION['OCS']["writeServer"],$del_sql['ARG'],true);			
}

?>