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

require_once('require/function_search.php');
require_once('require/function_admininfo.php');
$values=look_config_default_values(array('QRCODE'));
if(isset($values['ivalue']['QRCODE']) and $values['ivalue']['QRCODE'] == 1)
	$qrcode=true;
$form_name="lock_affect";
echo open_form($form_name);
echo "<div align=center>";
$list_id=multi_lot($form_name,$l->g(601));


if (isset($protectedPost['Valid_modif_x']) and $protectedPost['Valid_modif_x'] != ''){
	$info_account_id=admininfo_computer();

	foreach ($protectedPost as $field=>$value){
			
			if (substr($field, 0, 5) == "check"){
				$temp=substr($field, 5);
				if (array_key_exists($temp,$info_account_id)){
					//cas of checkbox
					foreach ($protectedPost as $field2=>$value2){
						$casofcheck=explode('_',$field2);
						if ($casofcheck[0] . '_' . $casofcheck[1] == $temp){
							if (isset($casofcheck[2]))
								$data_fields_account[$temp] .= $casofcheck[2] . "&&&";
							
						}						
					}
					if (!isset($data_fields_account[$temp]))
					$data_fields_account[$temp]=$protectedPost[$temp];	
		
				}			
			
			}
		}

	if (isset($data_fields_account)){	
		updateinfo_computer($list_id,$data_fields_account,'LIST');
		unset($_SESSION['OCS']['DATA_CACHE']['TAB_MULTICRITERE']);
		echo "<script language='javascript'> window.opener.document.multisearch.submit();</script>";
	}
}


if (isset($protectedPost['RAZ']) and $protectedPost['RAZ'] != "" and $protectedPost['pack_list'] != ""){
	$sql="select ID from download_enable 
			where fileid='%s'";
	$arg=$protectedPost['pack_list'];
	$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
	while($item = mysql_fetch_object($result)){	
		$list_download_id[]=$item->ID;
	}

	$sql="delete from devices 
			where IVALUE in ";	
	$arg=array();
	$tab_result=mysql2_prepare($sql,$arg,$list_download_id);
	$sql=$tab_result['SQL'];
	$arg=$tab_result['ARG'];
	$sql .= "and NAME='DOWNLOAD' 
			 and hardware_id in ";
	$tab_result=mysql2_prepare($sql,$arg,$list_id);
	mysql2_query_secure($tab_result['SQL'], $_SESSION['OCS']["writeServer"],$tab_result['ARG']);	
	msg_success(mysql_affected_rows()." ".$l->g(1026));	
}
		if ($_SESSION['OCS']['CONFIGURATION']['CHANGE_ACCOUNTINFO'] == "YES")
			$def_onglets['TAG']=$l->g(1022); 
		else
			$protectedPost['onglet']='SUP_PACK';
		
		$def_onglets['SUP_PACK']=$l->g(1021);
		 
		if (isset($qrcode))
			$def_onglets['QRCODE']=$l->g(1298);
		
		if ($protectedPost['onglet'] == "")
			$protectedPost['onglet']="TAG";	
		//show onglet
		onglet($def_onglets,$form_name,"onglet",7);
	
	
	//print_r($protectedPost);
	if (isset($protectedPost['CHOISE']) and $protectedPost['CHOISE'] != ""){
		if ($protectedPost['onglet']=="TAG" or !isset($protectedPost['onglet'])){	
			require_once('require/function_admininfo.php');
			$field_of_accountinfo=witch_field_more('COMPUTERS');
			$tab_typ_champ=array();
			$i=0;
			foreach ($field_of_accountinfo['LIST_FIELDS'] as $id=>$lbl){
				if ($field_of_accountinfo['LIST_NAME'][$id] == "TAG"){
					$truename="TAG";	
				//	$delfault_tag="Accinf: ".$lbl;
				}else
					$truename="fields_" . $id;
				//	echo $field_of_accountinfo['LIST_TYPE'][$id];
			if ($field_of_accountinfo['LIST_TYPE'][$id] == 6){
				$tab_typ_champ[$i]['CONFIG']['MAXLENGTH']=10;
				$tab_typ_champ[$i]['CONFIG']['SIZE']=10;
				$tab_typ_champ[$i]['COMMENT_BEHING']=calendars($truename,"DDMMYYYY")."</a></td><td><input type='checkbox' name='check".$truename."' id='check".$truename."' ".(isset($protectedPost['check'.$truename])? " checked ": "").">";
			}elseif (in_array($field_of_accountinfo['LIST_TYPE'][$id],array(2,4,7))){
				$sql="select ivalue as ID,tvalue as NAME from config where name like 'ACCOUNT_VALUE_%s' order by 2";
				$arg= $field_of_accountinfo['LIST_NAME'][$id]."%";
				$result=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$arg);
				while ($val = mysql_fetch_array( $result )){
					$tab_typ_champ[$i]['DEFAULT_VALUE'][$val['ID']]=$val['NAME'];
					
				}
				$tab_typ_champ[$i]['COMMENT_BEHING']="</td><td><input type='checkbox' name='check".$truename."' id='check".$truename."' ".(isset($protectedPost['check'.$truename])? " checked ": "").">";
				
				
			}else{
				$tab_typ_champ[$i]['COMMENT_BEHING']="</td><td><input type='checkbox' name='check".$truename."' id='check".$truename."' ".(isset($protectedPost['check'.$truename])? " checked ": "").">";
				$tab_typ_champ[$i]['CONFIG']['MAXLENGTH']=100;
				$tab_typ_champ[$i]['CONFIG']['SIZE']=30;
				
			}
				$tab_typ_champ[$i]['INPUT_NAME']=$truename;
				$tab_typ_champ[$i]['INPUT_TYPE']=$convert_type[$field_of_accountinfo['LIST_TYPE'][$id]];
				$tab_typ_champ[$i]['CONFIG']['JAVASCRIPT']=$java." onclick='document.getElementById(\"check".$truename."\").checked = true' ";
				
				//$tab_typ_champ[$i]['DEFAULT_VALUE']=$protectedPost[$truename];
				$tab_name[$i]=$lbl;
				$i++;
			}
			tab_modif_values($tab_name,$tab_typ_champ,array('TAG_MODIF'=>$protectedPost['MODIF'],'FIELD_FORMAT'=>$type_field[$protectedPost['MODIF']]),$l->g(895),"");
			
		}elseif ($protectedPost['onglet']=="SUP_PACK"){
			echo "<table cellspacing='5' width='80%' BORDER='0' ALIGN = 'Center' CELLPADDING='0' BGCOLOR='#C7D9F5' BORDERCOLOR='#9894B5'><tr><td>";
			
			$queryDetails = "select d_a.fileid,d_a.name 
								from download_available d_a, download_enable d_e 
								where d_e.FILEID=d_a.FILEID group by d_a.NAME  order by 1 desc";
			$resultDetails = mysql2_query_secure($queryDetails, $_SESSION['OCS']["readServer"]);
			while($val = mysql_fetch_array($resultDetails)){
				$List[$val["fileid"]]=$val["name"];		
			}
			$select=show_modif($List,'pack_list',2,$form_name);
			echo  "<tr><td align=center>".$l->g(970).": ".$select."</td></tr>";
			if ($protectedPost['pack_list'] != ""){
				$sql ="select count(*) c, tvalue from download_enable d_e,devices d
						where d.name='DOWNLOAD' and d.IVALUE=d_e.ID and d_e.fileid='%s'
						and d.hardware_id in ";				
				$arg = array($protectedPost['pack_list']);
				$tab_result=mysql2_prepare($sql,$arg,$list_id);
				$sql= $tab_result['SQL'] . " group by tvalue";
				$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$tab_result['ARG'] );
				while ($item = mysql_fetch_object($result)){
					if ($item->tvalue == "")
						$value=$l->g(482);
					else
						$value=$item->tvalue;
				echo "<tr><td colspan=10 align=center>".$item->c." ".$l->g(1023)." ".$value." ".$l->g(1024)."</td></tr>";
				}
			}
			echo "<tr><td colspan=10 align=center><input type='submit' name='RAZ' value='".$l->g(1025)."'></td></tr>";
			
			
			echo "</table>";
		}
	}
	
echo close_form();	
?>
