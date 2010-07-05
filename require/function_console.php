<?php
//function for only count before show result
 function query_on_table_count($name,$lbl_data,$tablename="hardware"){
 	global $exlu_group,$list_on_hardware,$form_name,$data,$data_detail,$titre,$list_on_else,$list_no_show;
 	if (!isset($list_no_show[$name])){
	 	$sql_on_hardware="select count(".$name.") c
						from ".$tablename." h ";
		if ($tablename=="hardware"){
			if ($list_on_hardware == "")
				$sql_on_hardware.=" where ".$exlu_group;
			else
			    $sql_on_hardware.=$list_on_hardware." and ".$exlu_group;
		}
		else
		$sql_on_hardware.=$list_on_else;		
		$sql_on_hardware.="	group by ".$name;
		$sql_on_hardware.=" having c != 0 ";
	 	$result_on_hardware = mysql_query( $sql_on_hardware, $_SESSION['OCS']["readServer"]);
	 	$num_rows = mysql_num_rows($result_on_hardware);
		$data['nb_'.$name]['count']=$num_rows;
		$data['nb_'.$name]['data']="<a OnClick='garde_valeur_console(\"".$form_name."\",\"".$name."\",\"detail\",\"".$tablename."\",\"tablename\")'>".$data['nb_'.$name]['count']."</a>";
		$data['nb_'.$name]['lbl']=$lbl_data;
 	}
 }
 
//function for show all result  
 function query_on_table($name,$lbl_data,$lbl_data_detail,$tablename="hardware"){
 	global $protectedPost,$exlu_group,$list_on_hardware,$form_name,$data,$data_detail,$titre,$list_on_else,$list_no_show,$limit;
 	if (!isset($list_no_show[$name])){
 		if ($protectedPost['tri2'] == ""){
 			$protectedPost['tri2']=1;
 			$protectedPost['sens']='DESC';
 			
 		}
 		
	 	$sql_on_hardware="select count(".$name.") c, ".$name." NAME
						from ".$tablename." h ";
		if ($tablename=="hardware"){
			if ($list_on_hardware == "")
				$sql_on_hardware.=" where ".$exlu_group;
			else
			    $sql_on_hardware.=$list_on_hardware." and ".$exlu_group;
		}else
		$sql_on_hardware.=$list_on_else;
		$sql_on_hardware.="	group by ".$name;
		$_SESSION['OCS']["forcedRequest"]=$sql_on_hardware;
		$sql_on_hardware.="	order by ".$protectedPost['tri2']." ".$protectedPost['sens']." limit ".$limit['BEGIN'].",".$limit['END'];
	 	$result_on_hardware = mysql_query( $sql_on_hardware, $_SESSION['OCS']["readServer"]);
		$nb_lign=0;
		while($item_on_hardware = mysql_fetch_object($result_on_hardware)){
			if ($item_on_hardware -> c != 0){
			$data_detail[$name][$nb_lign]['lbl']=$item_on_hardware ->NAME;
			$data_detail[$name][$nb_lign]['data']= $item_on_hardware -> c;
		 	$nb_lign++;
			}
		}
		$titre[$name]=$lbl_data_detail;
 	}
 }
 //function for count result
 function query_with_condition($wherecondition,$lbl_data,$name_data,$tablename="hardware",$link=""){
 	global $exlu_group,$data,$titre,$list_hardware_id,$list_id,$list_no_show,$form_name;
 	
 	if (!isset($list_no_show[$name_data])){
	 	$sql_count="select count(*) c from ".$tablename." h ";
	 	$sql_SESSION=$sql_count;
	 	if ($tablename=="hardware"){
	 		$sql_count.=$wherecondition." ".$list_hardware_id." and ".$exlu_group;
	 		$sql_SESSION.=$wherecondition." ".$list_hardware_id." and ".$exlu_group;
	 	}else{
	 		$sql_SESSION.= ",hardware h1 ".$wherecondition." and h1.id=h.hardware_id ".$list_id;
	 		$sql_count.=$wherecondition." ".$list_id;
	 	}
	 	$result_count = mysql_query( $sql_count, $_SESSION['OCS']["readServer"]);
		$item_count = mysql_fetch_object($result_count);
		
		if ($link != "" and $item_count -> c != 0 and $item_count -> c != ""){
			$a_behing="<a href='".$link."' target='_blank'>";
 			$a_end="</a>";
 			$_SESSION['OCS']['SQL'][$name_data]= $sql_SESSION;		
 		}elseif($item_count -> c != 0 and $item_count -> c != ""){
 			$a_behing="<a OnClick='garde_valeur_console(\"".$form_name."\",\"".$name_data."\",\"detail\",\"ELSE\",\"tablename\")'>";
 			$a_end="</a>";
 			$_SESSION['OCS']['SQL'][$name_data]= $sql_SESSION;	
 			
		}
		$data[$name_data]['data']= $a_behing.$item_count -> c.$a_end;
	 	$data[$name_data]['lbl']=$lbl_data;
 	}

 }


?>