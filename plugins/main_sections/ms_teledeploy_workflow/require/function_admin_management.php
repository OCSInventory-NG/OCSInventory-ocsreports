<?php

/*
 * function to modify a list to array
 * or a numeric to array
 * $id=numeric or $id_tab=array() or $id_tab=list (1,2,3,6,...)
 * 
 */
function prepare_id($id){
	if (is_array($id))
		return $id;
	//if $id_tab is a list
	$temp_id=explode(',',$id);
	if (isset($temp_id[0]) and is_numeric($temp_id[0])){
		return $temp_id;		
	}
	return false;
	
}


/*
 * function to find all fields
 * for a tab
 * $id_tab=numeric or $id_tab=array() or $id_tab=list (1,2,3,6,...)
 * 
 */
function find_all_field_by_tab($id_tab){
	$list_fields=array('field','id','tab');
	$sql= prepare_sql_tab($list_fields);
	
	$id_tab=prepare_id($id_tab);
		
	if (is_array($id_tab)){		

		$sql['SQL'].=" from downloadwk_fields where tab in ";
		$sql_all_field=mysql2_prepare($sql['SQL'],$sql['ARG'],$id_tab); 		
		$result=mysql2_query_secure($sql_all_field['SQL'],$_SESSION['OCS']["readServer"],$sql_all_field['ARG']);
		
		while ($val = mysql_fetch_array( $result )){
			$array[$val['id']]=$val['id'];
		}
		return $array;		
	}	
	
	return false;	
}

/*
 * 
 * function to find all value
 * for a field
 * id_field=numeric or $id_tab=array() or $id_tab=list (1,2,3,6,...)* 
 * 
 * 
 */
function find_all_value_by_field($id_field){
	
	$id_field=prepare_id($id_field);
	
	if (is_array($id_field)){		
		$sql= "select id from downloadwk_conf_values where field in ";
		$sql_all_value=mysql2_prepare($sql,array(),$id_field); 	
		$result=mysql2_query_secure($sql_all_value['SQL'],$_SESSION['OCS']["readServer"],$sql_all_value['ARG']);			
		while ($val = mysql_fetch_array( $result )){
			$array[$val['id']]=$val['id'];
		}
		return $array;		
	}	
	
	return false;	
	
}




/*
 * 
 * function to delete a conf 
 * $id_conf=numeric or $id_tab=array() or $id_tab=list (1,2,3,6,...)
 * 
 */

function delete_conf($id_conf){	
	$id_conf=prepare_id($id_conf);
	if (is_array($id_conf)){	
		
		$sql_downloadwk_conf_values="DELETE FROM downloadwk_conf_values WHERE id in ";
		$downloadwk_conf_values=mysql2_prepare($sql_downloadwk_conf_values,array(),$id_conf); 	
		mysql2_query_secure($downloadwk_conf_values['SQL'],$_SESSION['OCS']["writeServer"],$downloadwk_conf_values['ARG']);
		addLog( 'DEL_WK_TELEDIFF','delete values of field (downloadwk_conf_values) => '.mysql_affected_rows().' values');	
		return true;
	}
	
	return false;	
}


/*
 * 
 * function to delete a field 
 * $id_field=numeric or $id_tab=array() or $id_tab=list (1,2,3,6,...)
 * 
 */
function delete_field($id_field){
	//delete all values of this field
	$id_field=prepare_id($id_field);
	if (is_array($id_field)){	
		$i=0;
		print_r($id_field);
		foreach($id_field as $id=>$value){
			$sql_downloadwk_pack="ALTER TABLE downloadwk_pack DROP COLUMN fields_%s";
			$arg_downloadwk_pack=$value;
			mysql2_query_secure($sql_downloadwk_pack,$_SESSION['OCS']["writeServer"],$arg_downloadwk_pack);
			$i++;
		}
		addLog( 'DEL_WK_TELEDIFF','delete fields on downloadwk_pack => '.$i.' column');
		
		$id_values=find_all_value_by_field($id_field);
		
		$result=delete_conf($id_values);
		if ($result){
			$sql_downloadwk_fields="DELETE FROM downloadwk_fields WHERE ID in ";
			$downloadwk_fields=mysql2_prepare($sql_downloadwk_fields,array(),$id_field); 	
			mysql2_query_secure($downloadwk_fields['SQL'],$_SESSION['OCS']["writeServer"],$downloadwk_fields['ARG']);
			addLog( 'DEL_WK_TELEDIFF','delete field (downloadwk_fields) => '.mysql_affected_rows().' values');
			return true;
		}
	}
	
	return false;	
}



/*
 * function to delete a tab
 * $id_tab=numeric or $id_tab=array() or $id_tab=list (1,2,3,6,...)
 * 
 */

function delete_tab($id_tab){
	$info_fields_into_tab=find_all_field_by_tab($id_tab);
	if (isset($info_fields_into_tab) and $info_fields_into_tab != '')
		$result=delete_field($info_fields_into_tab);
	else
		$result=true;
		
	if ($result){
		$id_tab=prepare_id($id_tab);
		if (is_array($id_tab)){	
			$sql_downloadwk_tab_values="DELETE FROM downloadwk_tab_values WHERE ID in ";
			$downloadwk_tab_values=mysql2_prepare($sql_downloadwk_tab_values,array(),$id_tab); 	
			mysql2_query_secure($downloadwk_tab_values['SQL'],$_SESSION['OCS']["writeServer"],$downloadwk_tab_values['ARG']);
			addLog( 'DEL_WK_TELEDIFF','delete TAB (downloadwk_tab_values) => '.mysql_affected_rows().' values');
		}		
		
	}
	
	
}



?>