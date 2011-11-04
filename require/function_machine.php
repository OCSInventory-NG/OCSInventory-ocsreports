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
			return $l->g(837);
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
			return $l->g(837);
	}
	
	
	//si le systemid de la machine existe
	if (isset($GET['systemid']) and !isset($systemid))
	$systemid = $GET['systemid'];
	//probl�me sur l'id
	//echo $systemid;
	if ($systemid == "" or !is_numeric($systemid))
		return $l->g(837);
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

function bandeau($data,$lbl,$link=array()){
	global $protectedGet,$pages_refs;
	$data=data_encode_utf8($data);
	$nb_col=2;
	echo "<table ALIGN = 'Center' class='mlt_bordure' border=0 width:100%><tr><td align =center>";
	echo "		<table align=center border='0' width='100%'  ><tr>";
	$i=0;
	foreach ($data as $name=>$value){
		if (trim($value) != ''){
			if ($i == $nb_col){
				echo "</tr><tr>";
				$i=0;			
			}
			if (!array_key_exists($name,$link)){
				//$value=htmlentities($value,ENT_COMPAT,'UTF-8');
				$value=strip_tags_array($value);
			}
			echo "<td>&nbsp;<b>".$lbl[$name].": </b></td><td >".$value."</td>";
			$i++;
		}
	}

	echo "</tr></table></td>";
	echo "</tr></table>";	
}

function show_packages($systemid){
	global $l,$pages_refs,$ii,$td3,$td2,$td4;
	$query="SELECT a.name, d.tvalue,d.ivalue,d.comments,e.fileid, e.pack_loc,h.name as name_server,h.id,a.comment
			FROM devices d left join download_enable e on e.id=d.ivalue
						LEFT JOIN download_available a ON e.fileid=a.fileid
						LEFT JOIN hardware h on h.id=e.server_id
			WHERE d.name='DOWNLOAD' and a.name != '' and pack_loc != ''   AND d.hardware_id=%s
			union
			SELECT '%s', d.tvalue,d.ivalue,d.comments,e.fileid, '%s',h.name,h.id,a.comment 
			FROM devices d left join download_enable e on e.id=d.ivalue
						LEFT JOIN download_available a ON e.fileid=a.fileid
						LEFT JOIN hardware h on h.id=e.server_id
			WHERE d.name='DOWNLOAD' and a.name is null and pack_loc is null  AND d.hardware_id=%s";
	$arg_query=array($systemid,$l->g(1129),$l->g(1129),$systemid);
	$resDeploy = mysql2_query_secure($query, $_SESSION['OCS']["readServer"],$arg_query); 
	if( mysql_num_rows( $resDeploy )>0 ) {
			
		while( $valDeploy = mysql_fetch_array( $resDeploy ) ) {
			$ii++; $td3 = $ii%2==0?$td2:$td4;
			if ((strpos($valDeploy["comment"], "[VISIBLE=1]") 
				or strpos($valDeploy["comment"], "[VISIBLE=]")
				or (!isset($_SESSION['OCS']['RESTRICTION']['TELEDIFF_VISIBLE'])
					and strpos($valDeploy["comment"], "[VISIBLE=0]"))
				or !strpos($valDeploy["comment"], "[VISIBLE"))
				or (isset($_SESSION['OCS']['RESTRICTION']['TELEDIFF_VISIBLE']) 
				and $_SESSION['OCS']['RESTRICTION']['TELEDIFF_VISIBLE'] == "NO"
				and preg_match("[VISIBLE=0]", $valDeploy["comment"]))){
					//echo $valDeploy["comment"];
					//	echo $_SESSION['OCS']['RESTRICTION']['TELEDIFF_VISIBLE'];			
					echo "<tr>";
					echo "<td bgcolor='white' align='center' valign='center'><img width='15px' src='image/red.png'></td>";
					echo $td3.$l->g(498)." <b>".$valDeploy["name"]."</b>";
					if (isset($valDeploy["fileid"]))
					echo "(<small>".$valDeploy["fileid"]."</small>)";
					
					if ($valDeploy["name_server"]!="")
						echo " (".$l->g(499)." redistrib: <a href='index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&systemid=".$valDeploy["id"]."' target='_blank'><b>".$valDeploy["name_server"]."</b></a>";
					else
					echo " (".$l->g(499).": ".$valDeploy["pack_loc"]." ";
					echo ")</td>";			
					echo $td3.$l->g(81).": ".($valDeploy["tvalue"]!=""?$valDeploy["tvalue"]:$l->g(482));
					echo ($valDeploy["comments"]!=""?" (".$valDeploy["comments"].")":"");
					echo "</td>";
					if( $_SESSION['OCS']['CONFIGURATION']['TELEDIFF']=="YES" )	{
						echo "$td3 <a href='index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&suppack=".$valDeploy["ivalue"]."&systemid=".
						urlencode($systemid)."&option=cd_configuration'>".$l->g(122)."</a></td>";
					}elseif (strstr($valDeploy["tvalue"], 'ERR_')){
						echo $td3."<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&affect_reset=".$valDeploy["ivalue"]."&systemid=".
							urlencode($systemid)."&option=cd_configuration'>".$l->g(113)."</a>";
						if ($valDeploy["name"] != "PAQUET SUPPRIME")
						echo $td3."<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&affect_again=".$valDeploy["ivalue"]."&systemid=".
							urlencode($systemid)."&option=cd_configuration'>".$l->g(1246)."</a></td>";				
					}elseif (strstr($valDeploy["tvalue"], 'NOTIFIED')){	
							if (isset($valDeploy["comments"]) and strtotime ($valDeploy["comments"])<strtotime ("-12 week")){
								echo $td3."<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&reset_notified=".$valDeploy["ivalue"]."&systemid=".
								urlencode($systemid)."&option=cd_configuration'><img src=image/supp.png></a>";
							}
					
					}
					echo "</tr>";
				}
		}
	}
	
}
?>