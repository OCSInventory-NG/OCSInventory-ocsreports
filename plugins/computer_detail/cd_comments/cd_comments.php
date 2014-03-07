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

	$lbl_log=$l->g(1128);
	$list_fields=array();
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	print_item_header($l->g(1128));
	$form_name="affich_notes";
	$table_name=$form_name;
	echo open_form($form_name);
	//delete a list of notes
	if ($protectedPost['del_check'] != ''){
		$arg_sql=array();
			$sql="update itmgmt_comments set visible=0 where id in ";
			$sql=mysql2_prepare($sql,$arg_sql,$protectedPost['del_check']);
			
			mysql2_query_secure($sql['SQL'], $_SESSION['OCS']["writeServer"],$sql['ARG'],'DEL_NOTES');
		 	//update table cache
			$tab_options['CACHE']='RESET';	 	
	 }	

	if ($protectedPost['SUP_PROF'] != '' and isset($protectedPost['SUP_PROF'])){
		$sql="update itmgmt_comments set visible=0 where id=%s";
		$arg=array($protectedPost['SUP_PROF']);
		mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg,'DEL_NOTE');
		//update table cache
		$tab_options['CACHE']='RESET';
	}
	
	if ($protectedPost['Valid_modif_x'] != '' and isset($protectedPost['Valid_modif_x'])){
		
		//ajout de note
		if (trim($protectedPost['NOTE']) != '' and isset($protectedPost['NOTE'])){
			$sql="insert into itmgmt_comments (HARDWARE_ID,DATE_INSERT,USER_INSERT,COMMENTS,ACTION) 
					value (%s,%s,'%s','%s','%s')";
			$arg=array($systemid,"sysdate()",$_SESSION['OCS']["loggeduser"],$protectedPost['NOTE'],"ADD_NOTE_BY_USER");
			
			mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg,'ADD_NOTE_BY_USER');
			//reg�n�ration du cache
			$tab_options['CACHE']='RESET';			
		}elseif (trim($protectedPost['NOTE_MODIF']) != '' and isset($protectedPost['NOTE_MODIF'])){
			$sql="update itmgmt_comments set COMMENTS='%s'";
			$arg=array($protectedPost['NOTE_MODIF']);
			if (!strstr($protectedPost['USER_INSERT'], $_SESSION['OCS']["loggeduser"])){
				$sql.=" , USER_INSERT = '%s/%s'";
				array_push($arg,$protectedPost['USER_INSERT'],$_SESSION['OCS']["loggeduser"]);
			}
			$sql.=" where id=%s";
			array_push($arg,$protectedPost['ID_MODIF']);
			$lbl_log.= "  Old Comments=".$protectedPost['OLD_COMMENTS'];
			mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg,'UPDATE_NOTE');
			//reg�n�ration du cache
			$tab_options['CACHE']='RESET';				

		}		
		
	}
	if ($protectedPost['ADD_NOTE']){
		$tab_name[1]=$l->g(1126).": ";
		$tab_name[2]=$l->g(1127).": ";
		$tab_name[3]=$l->g(1128).": ";
		$tab_typ_champ[1]['DEFAULT_VALUE']=date("d/m/Y");
		$tab_typ_champ[2]['DEFAULT_VALUE']=$_SESSION['OCS']["loggeduser"];
		$tab_typ_champ[1]['INPUT_TYPE']=3;
		$tab_typ_champ[2]['INPUT_TYPE']=3;
		$tab_typ_champ[3]['INPUT_NAME']='NOTE';
		$tab_typ_champ[3]['INPUT_TYPE']=1;
	    tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title,$comment);		
	}
	
	$queryDetails = "SELECT ID,DATE_INSERT,USER_INSERT,COMMENTS,ACTION FROM itmgmt_comments WHERE (visible is null or visible =1) and hardware_id=$systemid";
	$list_fields=array($l->g(1126) => 'DATE_INSERT',
					   $l->g(899) => 'USER_INSERT',
					   $l->g(51) => 'COMMENTS',
					   $l->g(443)=>'ACTION');
					   
	if (!$show_all_column){
		$list_fields['MODIF']='ID';
		$list_fields['SUP']='ID';
		$list_fields['CHECK']='ID';		
	}
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;

	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,80,$tab_options);
	if (!$show_all_column){
		echo "<br><input type='submit' name='ADD_NOTE' id='ADD_NOTE' value='" . $l->g(898) . "'>";
		del_selection($form_name);
	}
	
	if (isset($protectedPost['MODIF']) and $protectedPost['MODIF'] != ''){
		$queryDetails = "SELECT ID,DATE_INSERT,USER_INSERT,COMMENTS,ACTION FROM itmgmt_comments WHERE id=%s";
		$argDetail=array($protectedPost['MODIF']);
		$resultDetails = mysql2_query_secure($queryDetails, $_SESSION['OCS']["readServer"],$argDetail);
		$item=mysqli_fetch_array($resultDetails,MYSQL_ASSOC);
		$tab_name[1]= $l->g(1126) . ": ";
		$tab_name[2]= $l->g(1127) . ": ";
		$tab_name[3]= $l->g(1128) . ": ";
		$tab_typ_champ[1]['DEFAULT_VALUE']=$item['DATE_INSERT'];
		$tab_typ_champ[2]['DEFAULT_VALUE']=$item['USER_INSERT'];
		$tab_typ_champ[3]['DEFAULT_VALUE']=$item['COMMENTS'];
		$tab_typ_champ[1]['INPUT_TYPE']=3;
		$tab_typ_champ[2]['INPUT_TYPE']=3;
		$tab_typ_champ[3]['INPUT_NAME']='NOTE_MODIF';
		$tab_typ_champ[3]['INPUT_TYPE']=1;
		$tab_hidden['USER_INSERT']=$item['USER_INSERT'];
		$tab_hidden['ID_MODIF']=$protectedPost['MODIF'];
		$tab_hidden['OLD_COMMENTS']=$item['COMMENTS'];
	    tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title,$comment);
	
	}
	echo close_form();
?>