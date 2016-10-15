<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */
require_once('require/function_search.php');
require_once('require/function_admininfo.php');
$form_name="lock_affect";

echo open_form($form_name);
echo "<div align=center>";
$list_id=multi_lot($form_name,$l->g(601));
if (isset($list_id) and $list_id != ''){
	//cas of TAG INFO
	if (isset($protectedPost['Valid_modif']) and $protectedPost['Valid_modif'] != ''){
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
			echo "<script language='javascript'> window.opener.document.show_all.submit();</script>";
		}
	}
	
	//CAS OF TELEDEPLOY
	if (isset($protectedPost['RAZ']) and $protectedPost['RAZ'] != "" and $protectedPost['pack_list'] != ""){
		$sql="select ID from download_enable 
				where fileid='%s'";
		$arg=$protectedPost['pack_list'];
		$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
		$item = mysqli_fetch_object($result);
		require_once('require/function_telediff.php');
		$nb_line_affected=desactive_packet($list_id,$item->ID);
		msg_success($nb_line_affected." ".$l->g(1026));	
	}
	//CAS OF WOL
	if(isset($protectedPost['WOL']) and $protectedPost['WOL'] != ''){
		require_once('require/function_wol.php');
		$wol = new Wol();
		$sql="select IPADDRESS,MACADDR from networks WHERE status='Up' and hardware_id in ";
		$arg=array();
		$tab_result=mysql2_prepare($sql,$arg,$list_id);
		$resultDetails = mysql2_query_secure($tab_result['SQL'], $_SESSION['OCS']["writeServer"],$tab_result['ARG']);
		$msg="";
		while($item = mysqli_fetch_object($resultDetails)){
			$wol->wake($item->MACADDR,$item->IPADDRESS);
			$msg.= "<br>".$wol->wol_send."=>".$item->MACADDR."/".$item->IPADDRESS;			
		}
		msg_info($msg);
		
		
	}
	
	//tab definition
	if ($_SESSION['OCS']['profile']->getConfigValue('CHANGE_ACCOUNTINFO') == "YES")
		$def_onglets['TAG']=$l->g(1022); 
	else
		$protectedPost['onglet']='SUP_PACK';
	$def_onglets['SUP_PACK']=$l->g(1021);
	
	if ($_SESSION['OCS']['profile']->getRestriction('WOL', 'NO')=="NO")
		$def_onglets['WOL']=$l->g(1280);
	
	if ($protectedPost['onglet'] == "")
		$protectedPost['onglet']="TAG";	
	//show onglet
	onglet($def_onglets,$form_name,"onglet",7);
	
	if (isset($protectedPost['CHOISE']) and $protectedPost['CHOISE'] != ""){
			if (!isset($protectedPost['onglet']) or $protectedPost['onglet']=="TAG"){	
				require_once('require/function_admininfo.php');
				$field_of_accountinfo=witch_field_more('COMPUTERS');
				$tab_typ_champ=array();
				$i=0;
				$dont_show_type=array(8,3);
				foreach ($field_of_accountinfo['LIST_FIELDS'] as $id=>$lbl){
					if (!in_array($field_of_accountinfo['LIST_TYPE'][$id],$dont_show_type)){
						if ($field_of_accountinfo['LIST_NAME'][$id] == "TAG"){
							$truename="TAG";	
						}else
							$truename="fields_" . $id;
						if ($field_of_accountinfo['LIST_TYPE'][$id] == 6){
							$tab_typ_champ[$i]['CONFIG']['MAXLENGTH']=10;
							$tab_typ_champ[$i]['CONFIG']['SIZE']=10;
							$tab_typ_champ[$i]['COMMENT_AFTER']=calendars($truename,"DDMMYYYY")."</a></td><td><input type='checkbox' name='check".$truename."' id='check".$truename."' ".(isset($protectedPost['check'.$truename])? " checked ": "").">";
						}elseif (in_array($field_of_accountinfo['LIST_TYPE'][$id],array(2,4,7))){
							$sql="select ivalue as ID,tvalue as NAME from config where name like 'ACCOUNT_VALUE_%s' order by 2";
							$arg= $field_of_accountinfo['LIST_NAME'][$id]."%";
							$result=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$arg);
							while ($val = mysqli_fetch_array( $result )){
								$tab_typ_champ[$i]['DEFAULT_VALUE'][$val['ID']]=$val['NAME'];
								
							}
							$tab_typ_champ[$i]['COMMENT_AFTER']="</td><td><input type='checkbox' name='check".$truename."' id='check".$truename."' ".(isset($protectedPost['check'.$truename])? " checked ": "").">";
							
							
						}else{
							$tab_typ_champ[$i]['COMMENT_AFTER']="</td><td><input type='checkbox' name='check".$truename."' id='check".$truename."' ".(isset($protectedPost['check'.$truename])? " checked ": "").">";
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
				}
				tab_modif_values($tab_name,$tab_typ_champ,array('TAG_MODIF'=>$protectedPost['MODIF'],'FIELD_FORMAT'=>$type_field[$protectedPost['MODIF']]),array(
					'title' => $l->g(895)
				));
				
			}elseif ($protectedPost['onglet']=="SUP_PACK"){
				echo "<div class='mvt_bordure'>";			
				$queryDetails = "select d_a.fileid,d_a.name 
									from download_available d_a, download_enable d_e 
									where d_e.FILEID=d_a.FILEID group by d_a.NAME  order by 1 desc";
				$resultDetails = mysql2_query_secure($queryDetails, $_SESSION['OCS']["readServer"]);
				while($val = mysqli_fetch_array($resultDetails)){
					$List[$val["fileid"]]=$val["name"];		
				}
				$select=show_modif($List,'pack_list',2,$form_name);
				echo  $l->g(970).": ".$select;
				if ($protectedPost['pack_list'] != ""){
					$sql ="select count(*) c, tvalue from download_enable d_e,devices d
							where d.name='DOWNLOAD' and d.IVALUE=d_e.ID and d_e.fileid='%s'
							and d.hardware_id in ";				
					$arg = array($protectedPost['pack_list']);
					$tab_result=mysql2_prepare($sql,$arg,$list_id);
					$sql= $tab_result['SQL'] . " group by tvalue";
					$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$tab_result['ARG'] );
					while ($item = mysqli_fetch_object($result)){
						if ($item->tvalue == "")
							$value=$l->g(482);
						else
							$value=$item->tvalue;
					echo "<br>".$item->c." ".$l->g(1023)." ".$value." ".$l->g(1024);
					}
					echo "<br><input type='submit' name='RAZ' value='".$l->g(1025)."'>";
				}
				
				
				
				echo "</div>";
			}elseif($protectedPost['onglet']=="WOL"){
				echo "<div class='mvt_bordure'>";
				echo "<br><input type='submit' name='WOL' value='".$l->g(13)."'>";
				echo "</div>";			
				
			}
	}
}
echo close_form();	
?>
