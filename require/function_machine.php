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

/*
 * Page de fonction communes aux d�tails d'une machine 
 * 
 */
 
//fonction de traitement de l'ID envoy�
function info($GET,$post_systemid){
	global $l,$protectedPost;
	//send post
	if ($post_systemid != '')
		$systemid = $protectedPost['systemid'];
	//you can see computer's detail by deviceid
	if (isset($GET['deviceid']) and !isset($systemid)){
		$querydeviceid = "SELECT ID FROM hardware WHERE deviceid='%s'";
		$argdevicedid=strtoupper ($GET['deviceid']);
		$resultdeviceid = mysql2_query_secure($querydeviceid, $_SESSION['OCS']["readServer"],$argdevicedid);
		$item = mysql_fetch_object($resultdeviceid);	
		$GET['systemid']=$item -> ID;
		//echo $GET['systemid'];
		if ($GET['systemid'] == "")
			return "Please Supply A Device ID";
	}
	
	//you can see computer's detail by md5(deviceid)
	if (isset($GET['crypt'])){
		$querydeviceid = "SELECT ID FROM hardware WHERE md5(deviceid)='%s'";
		$argdevicedid=($GET['crypt']);
		$resultdeviceid = mysql2_query_secure($querydeviceid, $_SESSION['OCS']["readServer"],$argdevicedid);
		$item = mysql_fetch_object($resultdeviceid);	
		$GET['systemid']=$item -> ID;
		//echo $GET['systemid'];
		if ($GET['systemid'] == "")
			return "Please Supply A Device ID";
	}
	
	
	//si le systemid de la machine existe
	if (isset($GET['systemid']) and !isset($systemid))
	$systemid = $GET['systemid'];
	//probl�me sur l'id
	//echo $systemid;
	if ($systemid == "" or !is_numeric($systemid))
		return "Please Supply A System ID";
		//recherche des infos de la machine
		$querydeviceid = "SELECT * FROM hardware h left join accountinfo a on a.hardware_id=h.id
						 WHERE h.id=".$systemid." ";
		if ($_SESSION['OCS']['RESTRICTION']['GUI'] == "YES" 
			and isset($_SESSION['OCS']['mesmachines']) 
			and $_SESSION['OCS']['mesmachines'] != ''
			and !isset($GET['crypt']))			 
				$querydeviceid .= " and (".$_SESSION['OCS']['mesmachines']." or a.tag is null or a.tag='')";
		$resultdeviceid = mysql_query($querydeviceid, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
		$item = mysql_fetch_object($resultdeviceid);
		if ( $item -> ID == ""){
			return $l->g(837);	
		}
		return $item;
	
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
	$data=data_encode_utf8($data);
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
?>