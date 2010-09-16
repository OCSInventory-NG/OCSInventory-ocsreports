<?php

//if your script not use ocsbase
//$base = 'OTHER';

$base="OCS";
connexion_local_read();
mysql_select_db($db_ocs,$link_ocs);

$sql_black="select SUBNET,MASK from blacklist_subnet";
$res_black=mysql2_query_secure($sql_black, $link_ocs);
while ($row=mysql_fetch_object($res_black)){
	$subnet_to_balcklist[$row->SUBNET]=$row->MASK;
	
}		
$req="select distinct ipsubnet,s.name,s.id 
			from networks n left join subnet s on s.netid=n.ipsubnet
			,accountinfo a
		where a.hardware_id=n.HARDWARE_ID 
			and n.status='Up'";
if (isset($_SESSION['OCS']["mesmachines"]) and $_SESSION['OCS']["mesmachines"] != '')
		$req.="	and ".$_SESSION['OCS']["mesmachines"]." order by ipsubnet";
else
		$req.=" union select netid,name,id from subnet";
$res=mysql2_query_secure($req, $link_ocs) or die(mysql_error($link_ocs));
while ($row=mysql_fetch_object($res)){
	unset($id);
	if (is_array($subnet_to_balcklist)){
		foreach ($subnet_to_balcklist as $key=>$value){
			if ($key == $row -> ipsubnet)
				$id='--'.$l->g(703).'--';
		}
	}
	/*foreach ($subnet_to_balcklist as $key=>$value){
		$black=explode('.',$value);
		$nb=count($black);
		$origine=explode('.',$row->ipsubnet);
		$nb--;
		unset($verif);
		while ($black[$nb]){
			if ($black[$nb] != $origine[$nb]){
				$verif=true;
			}
			$nb--;			
		}
		if (!isset($verif)){
			$id='--'.$l->g(703).'--';
		}
	}*/
	//this subnet was identify
	if ($row->id != null and !isset($id)){
		$list_ip[$row->id][$row->ipsubnet]=$row->name;
		$list_ip['---'.$l->g(1138).'---'][$row->ipsubnet]=$row->name;
	}elseif(!isset($id)){
		$no_name='---'.$l->g(885).'---';
		$list_ip[$no_name][$row->ipsubnet]=$no_name;
	}else{
		$list_ip[$id][$row->ipsubnet]=$id;
	}

}
$id_subnet="ID";
if (!isset($list_ip))
$INFO="NO_IPDICOVER";

?>
