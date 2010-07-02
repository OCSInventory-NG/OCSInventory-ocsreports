<script language=javascript>

function recharge(modif,origine){
	document.getElementById('systemid').value=modif;
	document.getElementById('origine').value=origine;
	document.getElementById('modif_param').submit();	
}

</script>
<?php
 

 
 //function for erase param values 
 function erase($NAME){
 	global $protectedPost,$list_hardware_id,$tab_hadware_id;
	// if it's for group or a machine
 	if( isset($list_hardware_id)) {
 		if( ! @mysql_query( "DELETE FROM devices WHERE name='".$NAME."' AND hardware_id='".$protectedPost["systemid"]."'", $_SESSION['OCS']["writeServer"] )) {
 				echo "<br><center><font color=red><b>ERROR: MySql connection problem<br>".mysql_error($_SESSION['OCS']["writeServer"])."</b></font></center>";
				return false;
			}
	}
	else { //else : request 
		if( ! @mysql_query( "DELETE FROM devices WHERE name='".$NAME."' AND hardware_id in (".implode(',',$tab_hadware_id).")", $_SESSION['OCS']["writeServer"] )) {
				echo "<br><center><font color=red><b>ERROR: MySql connection problem<br>".mysql_error($_SESSION['OCS']["writeServer"])."</b></font></center>";
				return false;
			}
			

	}

}
 
 //function for insert param values
 function insert($NAME,$IVALUE,$TVALUE = ""){
 	global $list_hardware_id,$tab_hadware_id; 		
 	//delete old value before insert new 
 	
 	erase($NAME);
 	// if it's for group or a machine
	if( isset($list_hardware_id)) {
			if ($TVALUE != "")
				$sql="INSERT INTO devices(HARDWARE_ID,NAME,IVALUE,TVALUE) VALUES ('".$list_hardware_id."', '".$NAME."', '".$IVALUE."', '".$TVALUE."')";
			else
				$sql="INSERT INTO devices(HARDWARE_ID, NAME, IVALUE) VALUES('".$list_hardware_id."', '".$NAME."', '".$IVALUE."')";
			if( ! @mysql_query( $sql, $_SESSION['OCS']["writeServer"] )) {
				echo "<br><center><font color=red><b>ERROR: MySql connection problem<br>".mysql_error($_SESSION['OCS']["writeServer"])."</b></font></center>";
				return false;
			}
	}
	else {//else : request 
		$i=0;
		while( $tab_hadware_id[$i]) {
			if ($TVALUE != "")
				$sql="INSERT INTO devices(HARDWARE_ID,NAME,IVALUE,TVALUE) VALUES ('".$tab_hadware_id[$i]."', '".$NAME."', '".$IVALUE."', '".$TVALUE."')";
			else
				$sql="INSERT INTO devices(HARDWARE_ID, NAME, IVALUE) VALUES (".$tab_hadware_id[$i].", '".$NAME."', $IVALUE)";
		
			if( ! @mysql_query( $sql, $_SESSION['OCS']["writeServer"] )) {
					echo "<br><center><font color=red><b>ERROR: MySql connection problem<br>".mysql_error($_SESSION['OCS']["writeServer"])."</b></font></center>";
					return false;
				}
				$i++;
		}
	}
	
 }
 
 function optperso($lbl,$lblPerso,$optPerso,$group=0,$default_value='',$end = ''){
	global $l,$td3,$systemid;
	echo "<tr><td bgcolor='white' align='center' valign='center'>".(isset($optPerso[$lbl])?"<img width='15px' src='image/red.png'>":"&nbsp;")."</td>";
	echo $td3.$lblPerso."</td>";
	if( isset( $optPerso[$lbl] )) {
		if( isset($optPerso[$lbl]["IVALUE"]) ) echo $td3.$optPerso[$lbl]["IVALUE"]." ".$end."</td>";
		
	}
	else {
	    if($end !=''){
		echo $td3.$l->g(488)." (".$default_value." ".$end.")</td>";
	    }
	    else {
		echo $td3.$l->g(488)." (".$default_value.")</td>";
	    }
	}
	echo "</tr>";
}
?>
