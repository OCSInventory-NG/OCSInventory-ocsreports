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

//you can delete all packets if status=NOTIFIED and date>3 mounths
if (isset($protectedGet['reset_notified']) and is_numeric($protectedGet['reset_notified'])){
	$sql=" delete from devices 
			where name='%s' 
				and tvalue = '%s' 
				and IVALUE='%s' 
				and hardware_id=%s"; 
	$arg=array("DOWNLOAD","NOTIFIED",$protectedGet['reset_notified'],$systemid);
	mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg);	
}



//affect again a packet
if ($protectedPost['Valid_modif_x']){
	if (trim($protectedPost['MOTIF'])){
		if ($protectedPost["ACTION"] == "again"){
			$sql=" update devices set TVALUE=%s
					where name='%s' and tvalue like '%s' and IVALUE='%s' and hardware_id=%s"; 
			$arg=array("null","DOWNLOAD","ERR_%",$protectedGet['affect_again'],$systemid);
		}elseif($protectedPost["ACTION"] == "reset"){
			$sql=" delete from devices 
			where name='%s' and tvalue like '%s' and IVALUE='%s' and hardware_id=%s"; 
			$arg=array("DOWNLOAD","ERR_%",$protectedGet['affect_again'],$systemid);
		}
		mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg);
		
		if (mysql_affected_rows() != 0){
			$sql="INSERT INTO itmgmt_comments (hardware_id,comments,user_insert,date_insert,action) 
					values ('%s','%s','%s',%s,'%s => %s')"; 
			$arg=array($systemid,$protectedPost['MOTIF'],$_SESSION['OCS']["loggeduser"],
						"sysdate()",$protectedPost["ACTION"],$protectedPost['NAME_PACK']);
			mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg);
		}
	}else
	echo "<script>alert(\"".$l->g(903)."\")</script>";	
}

if ($protectedPost['Reset_modif_x'])
unset($protectedGet['affect_again'],$protectedGet['affect_reset']);


if ($protectedGet['affect_again'] or $protectedGet['affect_reset']){
	if ($protectedGet['affect_again']){
		$id_pack_affect=$protectedGet['affect_again'];
		$hidden_action='again';
		$title_action=$l->g(904);		
		$lbl_action=$l->g(905);	
	}else{
		$id_pack_affect=$protectedGet['affect_reset'];
		$hidden_action='reset';
		$title_action=$l->g(906);
		$lbl_action=$l->g(907);
	}
	$sql="select da.name from devices d, 
						  download_enable de,
							download_available da
          where de.id='%s' and de.FILEID=da.FILEID
			and d.IVALUE=de.ID
			AND d.hardware_id='%s' AND d.name='%s'
			and tvalue like '%s'";
	$arg=array($id_pack_affect,$protectedGet['systemid'],"DOWNLOAD","ERR_%");		
	$res = mysql2_query_secure( $sql, $_SESSION['OCS']["readServer"],$arg );
		$val = mysql_fetch_array( $res ); 
	if (isset($val['name'])){		
		$tab_typ_champ[0]['INPUT_NAME']="MOTIF";
		$tab_typ_champ[0]['INPUT_TYPE']=1;
		$data_form[0]="<center>".$lbl_action."</center>";
		tab_modif_values($data_form,$tab_typ_champ,array('NAME_PACK'=>$val['name'],'ACTION'=>$hidden_action),$title_action.$val['name'],"");

	}
}
if( isset( $protectedGet["suppack"] ) &  $_SESSION['OCS']['CONFIGURATION']['TELEDIFF']=="YES" ) {
	
	if( $_SESSION['OCS']["justAdded"] == false ){
		
		$sql="DELETE FROM devices WHERE ivalue=%s AND hardware_id='%s' AND name='%s'";
		$arg=array($protectedGet["suppack"],$systemid,"DOWNLOAD");
		mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);	
		
	}else $_SESSION['OCS']["justAdded"] = false;
	addLog($l->g(512), $l->g(886)." ".$protectedGet["suppack"]." => ".$systemid );
}
else 
	$_SESSION['OCS']["justAdded"] = false;

if( isset( $protectedGet["actgrp"] )) {	
		//this id is it a group?
		$reqGroups = "SELECT h.id id
					  FROM hardware h 
					  WHERE h.deviceid='_SYSTEMGROUP_' ";
		//If you hav'nt permission => see only visible groups
		if (!($_SESSION['OCS']['CONFIGURATION']['GROUPS']=="YES"))
			$reqGroups .= " and h.workgroup = 'GROUP_4_ALL'";
		$resGroups = mysql2_query_secure( $reqGroups, $_SESSION['OCS']["readServer"] );
		$valGroups = mysql_fetch_array( $resGroups ); 
		if (isset($valGroups['id'])){
			$reqDelete = "DELETE FROM groups_cache WHERE hardware_id=%s AND group_id=%s";
			
			if( $protectedGet["actgrp"] == 0 ) 
				$reqDelete .= " AND static<>0";
			$argDelete=array($systemid,$protectedGet["grp"]);
			$reqInsert = "INSERT INTO groups_cache(hardware_id, group_id, static) 
								VALUES (%s, %s, %s)";
			$argInsert=array($systemid,$protectedGet["grp"],$protectedGet["actgrp"]);
			mysql2_query_secure( $reqDelete, $_SESSION['OCS']["writeServer"],$argDelete );
			if( $protectedGet["actgrp"] != 0 )
				mysql2_query_secure( $reqInsert, $_SESSION['OCS']["writeServer"],$argInsert );
		}
}

$td1	  = "<td height=20px id='color' align='center'><FONT FACE='tahoma' SIZE=2 color=blue><b>";
$td2      = "<td height=20px bgcolor='white' align='center'>";
$td3      = $td2;
$td4      = "<td height=20px bgcolor='#F0F0F0' align='center'>";
$i=0;
	$queryDetails = "SELECT * FROM devices WHERE hardware_id=%s";
	$argDetail=$systemid;
	$resultDetails = mysql2_query_secure($queryDetails, $_SESSION['OCS']["readServer"],$argDetail);
	$form_name='config_mach';
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	echo "<table BORDER='1' WIDTH = '95%' ALIGN = 'Center' CELLPADDING='0' BGCOLOR='#C7D9F5' BORDERCOLOR='#9894B5'>";
	
	
	while($item=mysql_fetch_array($resultDetails,MYSQL_ASSOC)) {
		$optPerso[ $item["NAME"] ][ "IVALUE" ] = $item["IVALUE"];
		$optPerso[ $item["NAME"] ][ "TVALUE" ] = $item["TVALUE"];
	}	
	
	$ii++; $td3 = $ii%2==0?$td2:$td4;
	$field_name=array('DOWNLOAD','DOWNLOAD_CYCLE_LATENCY','DOWNLOAD_PERIOD_LENGTH','DOWNLOAD_FRAG_LATENCY',
	    			  'DOWNLOAD_PERIOD_LATENCY','DOWNLOAD_TIMEOUT','PROLOG_FREQ','SNMP');
	$optdefault=look_config_default_values($field_name);

	
	
	//IPDISCOVER
	echo "<tr><td bgcolor='white' align='center' valign='center'>".(isset($optPerso["IPDISCOVER"])&&$optPerso["IPDISCOVER"]["IVALUE"]!=1?"<img width='15px' src='image/red.png'>":"&nbsp;")."</td>&nbsp;</td>";
	echo $td3.$l->g(489)."</td>";	
	if( isset( $optPerso["IPDISCOVER"] )) {		
		if( $optPerso["IPDISCOVER"]["IVALUE"]==0 ) echo $td3.$l->g(490)."</td>";	
		else if( $optPerso["IPDISCOVER"]["IVALUE"]==2 ) echo $td3.$l->g(491)." ".$optPerso["IPDISCOVER"]["TVALUE"]."</td>";
		else if( $optPerso["IPDISCOVER"]["IVALUE"]==1 ) echo $td3.$l->g(492)." ".$optPerso["IPDISCOVER"]["TVALUE"]."</td>";
	}
	else {
		echo $td3.$l->g(493)."</td>";
	}
	//Can you modify configuration of this computer?
	if( $_SESSION['OCS']['CONFIGURATION']['CONFIG']=="YES" ){
		echo "<td align=center rowspan=8><a href=# Onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_custom_param']."&head=1&idchecked=".$systemid."&origine=machine\",\"rollo\",\"location=0,status=0,scrollbars=1,menubar=0,resizable=0,width=800,height=500\");>
		<img src='image/modif_a.png' title='".$l->g(285)."'></a></td></tr>";
	}
	$ii++; $td3 = $ii%2==0?$td2:$td4;
	//FREQUENCY
	echo "<tr><td bgcolor='white' align='center' valign='center'>".(isset($optPerso["FREQUENCY"])?"<img width='15px' src='image/red.png'>":"&nbsp;")."</td>";
	echo $td3.$l->g(494)."</td>";
	if( isset( $optPerso["FREQUENCY"] )) {
		if( $optPerso["FREQUENCY"]["IVALUE"]==0 ) echo $td3.$l->g(485)."</td>";
		else if( $optPerso["FREQUENCY"]["IVALUE"]==-1 ) echo $td3.$l->g(486)."</td>";
		else echo $td3.$l->g(495)." ".$optPerso["FREQUENCY"]["IVALUE"]." ".$l->g(496)."</td>";
	}
	else {
		echo $td3.$l->g(497)."</td>";
	}	
	echo "</tr>";
	
	//DOWNLOAD_SWITCH
	echo "<tr><td bgcolor='white' align='center' valign='center'>".(isset($optPerso["DOWNLOAD_SWITCH"])?"<img width='15px' src='image/red.png'>":"&nbsp;")."</td>";
	echo $td3.$l->g(417)." <font color=green size=1><i>DOWNLOAD</i></font></td>";
	if( isset( $optPerso["DOWNLOAD_SWITCH"] )) {
		if( $optPerso["DOWNLOAD_SWITCH"]["IVALUE"]==0 ) echo $td3.$l->g(733)."</td>";
		else if( $optPerso["DOWNLOAD_SWITCH"]["IVALUE"]==1 ) echo $td3.$l->g(205)."</td>";
		else echo $td3."</td>";
	}
	else {
		echo $td3.$l->g(488)." (";
		if ($optdefault['ivalue']["DOWNLOAD"] == 1) echo $l->g(205); else echo $l->g(733);
		echo ")</td>";
	}	
	echo "</tr>";
	
	//DOWNLOAD_CYCLE_LATENCY
	optperso("DOWNLOAD_CYCLE_LATENCY",$l->g(720)." <font color=green size=1><i>DOWNLOAD_CYCLE_LATENCY</i></font>",$optPerso,0,$optdefault['ivalue']["DOWNLOAD_CYCLE_LATENCY"],$l->g(511));
	
	//DOWNLOAD_FRAG_LATENCY
	optperso("DOWNLOAD_FRAG_LATENCY",$l->g(721)." <font color=green size=1><i>DOWNLOAD_FRAG_LATENCY</i></font>",$optPerso,0,$optdefault['ivalue']["DOWNLOAD_FRAG_LATENCY"],$l->g(511));

	
	//DOWNLOAD_PERIOD_LATENCY
	optperso("DOWNLOAD_PERIOD_LATENCY",$l->g(722)." <font color=green size=1><i>DOWNLOAD_PERIOD_LATENCY</i></font>",$optPerso,0,$optdefault['ivalue']["DOWNLOAD_PERIOD_LATENCY"],$l->g(511));
	
	//DOWNLOAD_PERIOD_LENGTH
	optperso("DOWNLOAD_PERIOD_LENGTH",$l->g(723)." <font color=green size=1><i>DOWNLOAD_PERIOD_LENGTH</i></font>",$optPerso,0,$optdefault['ivalue']["DOWNLOAD_PERIOD_LENGTH"]);

	//PROLOG_FREQ
	optperso("PROLOG_FREQ",$l->g(724)." <font color=green size=1><i>PROLOG_FREQ</i></font>",$optPerso,0,$optdefault['ivalue']["PROLOG_FREQ"],$l->g(730));
	
	//PROLOG_FREQ
	optperso("DOWNLOAD_TIMEOUT",$l->g(424)." <font color=green size=1><i>DOWNLOAD_TIMEOUT</i></font>",$optPerso,0,$optdefault['ivalue']["DOWNLOAD_TIMEOUT"],$l->g(496));

	//PROLOG_FREQ
	//optperso("SNMP_SWITCH",$l->g(1197)." <font color=green size=1><i>SNMP_SWITCH</i></font>",$optPerso,0,$optdefault["SNMP_SWITCH"],$l->g(496));
	//DOWNLOAD_SWITCH
	echo "<tr><td bgcolor='white' align='center' valign='center'>".(isset($optPerso["SNMP_SWITCH"])?"<img width='15px' src='image/red.png'>":"&nbsp;")."</td>";
	echo $td3.$l->g(1197)." <font color=green size=1><i>SNMP_SWITCH</i></font></td>";
	if( isset( $optPerso["SNMP_SWITCH"] )) {
		if( $optPerso["SNMP_SWITCH"]["IVALUE"]==0 ) echo $td3.$l->g(733)."</td>";
		else if( $optPerso["SNMP_SWITCH"]["IVALUE"]==1 ) echo $td3.$l->g(205)."</td>";
		else echo $td3."</td>";
	}
	else {
		echo $td3.$l->g(488)." (";
		if ($optdefault['ivalue']["SNMP"] == 1) echo $l->g(205); else echo $l->g(733);
		echo ")</td>";
	}	
	echo "</tr>";
	
	
	
	//GROUPS
	$sql_groups="SELECT static, name, group_id,workgroup  
				FROM groups_cache g, hardware h WHERE g.hardware_id=%s AND h.id=g.group_id";
	$arg_groups=$systemid;
	$resGroups = mysql2_query_secure($sql_groups, $_SESSION['OCS']["readServer"],$arg_groups); 
	echo "<tr><td colspan=100></td></tr>";
	if( mysql_num_rows( $resGroups )>0 ) {
		while( $valGroups = mysql_fetch_array( $resGroups ) ) {
			$ii++; $td3 = $ii%2==0?$td2:$td4;
			echo "<tr>";
			echo "<td bgcolor='white' align='center' valign='center'>&nbsp;</td>";
			echo $td3.$l->g(607)." ";		
			if( $_SESSION['OCS']['CONFIGURATION']['GROUPS']=="YES" || $valGroups["workgroup"]=="GROUP_4_ALL")
				echo "<a href='index.php?".PAG_INDEX."=".$pages_refs['group_show']."&popup=1&systemid=".$valGroups["group_id"]."' target='_blank'>".$valGroups["name"]."</td>";
			else
				echo "<b>".$valGroups["name"]."</b></td>";			
				
			echo $td3.$l->g(81).": ";
			switch( $valGroups["static"] ) {
				case 0: echo "<font color='green'>".$l->g(596)."</font></td>"; break;
				case 1: echo "<font color='blue'>".$l->g(610)."</font></td>"; break;
				case 2: echo "<font color='red'>".$l->g(597)."</font></td>"; break;
			}
			
			if( $_SESSION['OCS']['CONFIGURATION']['GROUPS']=="YES" || $valGroups["workgroup"]=="GROUP_4_ALL") {
				$hrefBase = "index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&systemid=".urlencode($systemid)."&option=cd_configuration&grp=".$valGroups["group_id"];
				switch( $valGroups["static"] ) {
					case 0: echo $td3."<a href='$hrefBase&actgrp=1'>".$l->g(598)."</a>&nbsp; &nbsp; &nbsp;<a href='$hrefBase&actgrp=2'>".$l->g(600)."</a></td>"; break;
					case 1: echo $td3."<a href='$hrefBase&actgrp=0'>".$l->g(818)."</a></td>"; break;
					case 2: echo $td3."<a href='$hrefBase&actgrp=1'>".$l->g(598)."</a>&nbsp; &nbsp; &nbsp;<a href='$hrefBase&actgrp=0'>".$l->g(41)."</a></td>"; break;
				}
			}			
			echo "</td>";
			echo "</tr>";			
		}
	}
	echo "<tr><td colspan=100></td></tr>";
	//TELEDEPLOY
	$query="SELECT a.name, d.tvalue,d.ivalue,d.comments,e.fileid, e.pack_loc,h.name as name_server,h.id
			FROM devices d left join download_enable e on e.id=d.ivalue
						LEFT JOIN download_available a ON e.fileid=a.fileid
						LEFT JOIN hardware h on h.id=e.server_id
			WHERE d.name='DOWNLOAD' and a.name != '' and pack_loc != ''   AND d.hardware_id=%s
			union
			SELECT '" . $l->g(1129) . "', d.tvalue,d.ivalue,d.comments,e.fileid, '" . $l->g(1129) . "',h.name,h.id 
			FROM devices d left join download_enable e on e.id=d.ivalue
						LEFT JOIN download_available a ON e.fileid=a.fileid
						LEFT JOIN hardware h on h.id=e.server_id
			WHERE d.name='DOWNLOAD' and a.name is null and pack_loc is null  AND d.hardware_id=%s";
	$arg_query=array($systemid,$systemid);
	$resDeploy = mysql2_query_secure($query, $_SESSION['OCS']["readServer"],$arg_query); 
	if( mysql_num_rows( $resDeploy )>0 ) {
			
		while( $valDeploy = mysql_fetch_array( $resDeploy ) ) {
			$ii++; $td3 = $ii%2==0?$td2:$td4;
			echo "<tr>";
			echo "<td bgcolor='white' align='center' valign='center'><img width='15px' src='image/red.png'></td>";
			echo $td3.$l->g(498)." <b>".$valDeploy["name"]."</b>";
			if (isset($valDeploy["fileid"]))
			echo "(<small>".$valDeploy["fileid"]."</small>)";
			
			if ($valDeploy["name_server"]!="")
				echo " (".$l->g(499)." redistrib: <a href='index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&systemid=".$valDeploy["id"]."' target='_blank'><b>".$valDeploy["name_server"]."</b></a>";
			else
			echo " (".$l->g(499).": ".$valDeploy["pack_loc"]." ";
			//echo ($valDeploy["name_server"]!=""?"<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&systemid=".$valDeploy["id"]."' target='_blank'><b>".$valDeploy["name_server"]."</b></a>":"");
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
						$possible_desafect='YES';
					//	echo $td3."<a href=# OnClick='confirme(\"\",\"".$value_of_field."\",\"".$form_name."\",\"SUP_PROF\",\"".$l->g(640)." ".$value_of_field."\");'><img src=image/supp.png></a>";
						echo $td3."<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&reset_notified=".$valDeploy["ivalue"]."&systemid=".
						urlencode($systemid)."&option=cd_configuration'><img src=image/supp.png></a>";
	//					$actuel_mount=date("M");
	//					$actuel_year=date("Y");
	//					echo "$td3 <a href='machine.php?suppack=".$valDeploy["ivalue"]."&systemid=".
	//				urlencode($systemid)."&option=cd_configuration'>".$year."   ".$mount."</a></td>";
					}
			
				
//			if (strstr($valDeploy["tvalue"], 'ERR_')){
//				echo "$td3<a href='machine.php?affect_again=".$valDeploy["ivalue"]."&systemid=".
//					urlencode($systemid)."&option=cd_configuration'>R�-".$l->g(433)."</a></td>";				
			}
			echo "</tr>";
		}
	}

		$hrefBase = "index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&systemid=".urlencode($systemid)."&option=cd_configuration";
		
		echo "<tr><td colspan='10' align='right'>";
		if( $_SESSION['OCS']['CONFIGURATION']['TELEDIFF']=="YES" ) 
			echo "<a href=# Onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_custom_pack']."&head=1&idchecked=".$systemid."&origine=mach\",\"rollo\",\"location=0,status=0,scrollbars=1,menubar=0,resizable=0,width=800,height=500\");>".$l->g(501)."</a> ";
		
	
		$reqGroups = "SELECT h.name,h.id,h.workgroup 
					  FROM hardware h,groups g 
					  WHERE  g.hardware_id=h.id  and h.deviceid='_SYSTEMGROUP_'";
		if( !($_SESSION['OCS']['CONFIGURATION']['GROUPS']=="YES") )
			$reqGroups .= " and workgroup = 'GROUP_4_ALL'";
		$resGroups =mysql2_query_secure( $reqGroups, $_SESSION['OCS']["readServer"] );
		$first = true;
		while( $valGroups = mysql_fetch_array( $resGroups ) ) {
			if( $first ) {
				echo $l->g(386)." <a href=# OnClick=window.location='$hrefBase&actgrp=1&grp='+document.getElementById(\"groupcombo\").options[document.getElementById(\"groupcombo\").selectedIndex].value>".
				$l->g(589)."</a>";
				echo " <select id='groupcombo'>";
				$first = false;
			}
			echo "<option value='".$valGroups["id"]."'>".$valGroups["name"]."</option>";
		}
		
		if( ! $first )
			echo "</select>";
			
		echo "</td></tr>";		
	//}
	echo "</table><br>";
	echo "</form>";
?>
