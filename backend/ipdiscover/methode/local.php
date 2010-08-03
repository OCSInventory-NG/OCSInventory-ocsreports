<?php

//nom de la page
$name="local.php";
connexion_local_read();
mysql_select_db($db_ocs,$link_ocs);
/*
 * if you want to blacklist some subnet  
 * add in $subnet_to_balcklist
 * like  $subnet_to_balcklist=array('128.128','192.168','128.42','128.105');
 */
$subnet_to_balcklist=array();
		
$req="select distinct ipsubnet,s.name,s.id 
			from networks n left join subnet s on s.netid=n.ipsubnet
			,accountinfo a
		where a.hardware_id=n.HARDWARE_ID 
			and n.status='Up'";
if (isset($_SESSION['OCS']["mesmachines"]) and $_SESSION['OCS']["mesmachines"] != '')
		$req.="	and ".$_SESSION['OCS']["mesmachines"]." order by ipsubnet";
$res=mysql_query($req, $link_ocs) or die(mysql_error($link_ocs));
while ($row=mysql_fetch_object($res)){
	unset($id);
	foreach ($subnet_to_balcklist as $value){
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
	}
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
