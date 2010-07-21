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
 	global $protectedPost,$protectedGet,$list_hardware_id,$tab_hadware_id;
	// if it's for group or a machine
 	if( isset($list_hardware_id)) {
 		$sql="DELETE FROM devices WHERE name='%s' AND hardware_id='%s'";
 		$arg=array($NAME,$protectedGet["idchecked"]);
 		mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"] ,$arg);
	}
	else { //else : request 
		$sql="DELETE FROM devices WHERE name='%s' AND hardware_id in ";
		$arg_sql=array($NAME);
		$arg=mysql2_prepare($sql,$arg_sql,$tab_hadware_id);
		mysql2_query_secure($arg['SQL'], $_SESSION['OCS']["writeServer"] ,$arg['ARG']);
	}

}
 
 //function for insert param values
 function insert($NAME,$IVALUE,$TVALUE = ""){
 	global $list_hardware_id,$tab_hadware_id; 		
 	//delete old value before insert new 
 	erase($NAME);
 	// if it's for group or a machine
	if( isset($list_hardware_id)) {
		$arg=array($list_hardware_id,$NAME,$IVALUE);
		if ($TVALUE != ""){
			$sql="INSERT INTO devices(HARDWARE_ID,NAME,IVALUE,TVALUE) VALUES ('%s', '%s', '%s', '%s')";
			array_push($arg,$TVALUE);	
		}else
			$sql="INSERT INTO devices(HARDWARE_ID, NAME, IVALUE) VALUES('%s', '%s', '%s')";
			
		mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"] ,$arg);		
	}
	else {//else : request 
		$i=0;
		while( $tab_hadware_id[$i]) {
			$arg=array($tab_hadware_id[$i],$NAME,$IVALUE);
			if ($TVALUE != ""){
				$sql="INSERT INTO devices(HARDWARE_ID,NAME,IVALUE,TVALUE) VALUES ('%s', '%s', '%s', '%s')";
				array_push($arg,$TVALUE);	
			}else
				$sql="INSERT INTO devices(HARDWARE_ID, NAME, IVALUE) VALUES ('%s', '%s', '%s')";
		
			mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"] ,$arg);		
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
