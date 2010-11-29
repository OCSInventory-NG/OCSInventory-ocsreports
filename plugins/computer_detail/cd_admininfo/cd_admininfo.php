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

require_once('require/function_admininfo.php');
$form_name='admin_info_computer';
$table_name=$form_name;
	
//search all admininfo for this computer
$info_account_id=admininfo_computer($systemid);
if (isset($protectedPost['ADMIN']) and $protectedPost['ADMIN'] == 'ADMIN' and !isset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO']))
	$_SESSION['OCS']['ADMIN']['ACCOUNTINFO']=true;
elseif (isset($protectedPost['ADMIN']) and $protectedPost['ADMIN'] == 'ADMIN' and isset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO']))
	unset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO']);

if ($_SESSION['OCS']['CONFIGURATION']['ACCOUNTINFO'] == 'YES' and isset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO']))
	$admin_accountinfo=true;
	
$list_tab=find_all_account_tab('TAB_ACCOUNTAG','COMPUTERS',1);	
if ($list_tab != ''){
	if ($protectedPost['Valid_modif_x'] != ""){
		foreach ($protectedPost as $field=>$value){
			$temp_field=explode('_',$field);
			if (array_key_exists( $temp_field[0] . '_' . $temp_field[1],$info_account_id) or $temp_field[0] == 'TAG'){
				//cas of checkbox
				if (isset($temp_field[2])){
				$data_fields_account[$temp_field[0] . "_" . $temp_field[1]].=$temp_field[2] . "&&&";	
				}
				else
				$data_fields_account[$field]=$value;	
	
			}
		}
		updateinfo_computer($systemid,$data_fields_account);
		//search all admininfo for this computer
		$info_account_id=admininfo_computer($systemid);	
	}
		unset($action_updown);
		//UP/DOWN
		if ((isset($protectedPost['UP']) and $protectedPost['UP'] != ''))
			$action_updown='UP';
		if (isset($protectedPost['DOWN']) and $protectedPost['DOWN'] != '')
			$action_updown='DOWN';	
		
		if (isset($action_updown)){				
			$new_order=find_new_order($action_updown,$protectedPost[$action_updown],'COMPUTERS',$protectedPost['onglet']);
			if ($new_order){
			//	$array_info_account=find_info_accountinfo($new_order['NEW']);
				update_accountinfo_config($new_order['OLD'],array('SHOW_ORDER'=>$new_order['NEW_VALUE']));
				update_accountinfo_config($new_order['NEW'],array('SHOW_ORDER'=>$new_order['OLD_VALUE']));
			}
		}
	
	//print_r($info_account_id);
	if (!isset($protectedPost['onglet']) or $protectedPost['onglet'] =='' or !is_numeric($protectedPost['onglet']))
		 $protectedPost['onglet'] = $list_tab['FIRST'];
	unset($list_tab['FIRST']);
	
	echo "<br><form name='".$form_name."' id='".$form_name."' method='POST'>";
	onglet($list_tab,$form_name,"onglet",6);
	echo '<div class="mlt_bordure" >';
	if ($_SESSION['OCS']['CONFIGURATION']['ACCOUNTINFO'] == 'YES')
	$show_admin_button = "<a href=# OnClick='pag(\"ADMIN\",\"ADMIN\",\"".$form_name."\");'><img src=image/modif_tab.png></a>";
	else
	$show_admin_button='';
	$sql_admin_info="select ID,TYPE,NAME,COMMENT,NAME_ACCOUNTINFO,SHOW_ORDER from accountinfo_config where ID_TAB = %s and account_type='COMPUTERS'
						order by SHOW_ORDER ASC";
	$arg_admin_info=array($protectedPost['onglet']);
	$res_admin_info=mysql2_query_secure($sql_admin_info,$_SESSION['OCS']["readServer"],$arg_admin_info);
	$num_row=mysql_num_rows($res_admin_info);
	$name_field=array();
	$tab_name=array();
	$type_field=array();
	$value_field=array();
	$config['COMMENT_BEHING']=array();
	$config['SELECT_DEFAULT']=array();
	$config['JAVASCRIPT']=array();
	$config['SIZE']=array();
	$config['DDE']=array();
	
	$nb_row=1;
	while ($val_admin_info = mysql_fetch_array( $res_admin_info )){	
		array_push($config['DDE'],$systemid);	
		//if name_accountinfo is not null 
		//column name in accountinfo table is name_accountinfo 
		//functionnality for compatibily with older version of OCS
		//we can't change the name TAG in accountinfo table 
		if ($val_admin_info['NAME_ACCOUNTINFO'] != '')
			$name_accountinfo=trim($val_admin_info['NAME_ACCOUNTINFO']);
		else
			$name_accountinfo='fields_' . $val_admin_info['ID'];
		
		$up_png="";
			
		if ($nb_row!=1)
			$up_png.=updown($val_admin_info['ID'],'UP');
			
		if ($nb_row!=$num_row)
			$up_png.=updown($val_admin_info['ID'],'DOWN');	
		if ($val_admin_info['TYPE'] == 2 
				or $val_admin_info['TYPE'] == 4
				or $val_admin_info['TYPE'] == 7){
				array_push($config['JAVASCRIPT'],'');
				array_push($config['SIZE'],'');
				if ($admin_accountinfo)
					array_push($config['COMMENT_BEHING'],$up_png . "<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_adminvalues']."&head=1&tag=ACCOUNT_VALUE_" . $val_admin_info['NAME'] . "\",\"ACCOUNT_VALUE\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")><img src=image/plus.png></a>");
				else
					array_push($config['COMMENT_BEHING'],'');
				array_push($config['SELECT_DEFAULT'],'YES');
				$field_select_values=find_value_field("ACCOUNT_VALUE_".$val_admin_info['NAME']);
				array_push($value_field,$field_select_values);
				//cas of checkbox
				if ($val_admin_info['TYPE'] == 4){
				$temp_val=explode('&&&',$info_account_id[$name_accountinfo]);
				$i=0;
				while (isset($temp_val[$i])){
					$protectedPost[$name_accountinfo . '_' . $temp_val[$i]]='on';
					$i++;			
				}
				
				}else
				$protectedPost[$name_accountinfo]=$info_account_id[$name_accountinfo];		
	
			}elseif ($val_admin_info['TYPE'] == 6){	
				array_push($value_field,$info_account_id[$name_accountinfo]);
				if ($admin_accountinfo)
					array_push($config['COMMENT_BEHING'],$up_png . datePick($name_accountinfo));
				else
					array_push($config['COMMENT_BEHING'],datePick($name_accountinfo));
				array_push($config['JAVASCRIPT'],"READONLY ".dateOnClick($name_accountinfo));
				array_push($config['SELECT_DEFAULT'],'');
				array_push($config['SIZE'],'8');	
			}elseif ($val_admin_info['TYPE'] == 5){
				array_push($value_field,"accountinfo");
				if ($admin_accountinfo)
					array_push($config['COMMENT_BEHING'],$up_png);
				else
					array_push($config['COMMENT_BEHING'],"");
				array_push($config['SELECT_DEFAULT'],'');
				array_push($config['JAVASCRIPT'],'');
				array_push($config['SIZE'],'');
				
				
			}else{
				array_push($value_field,$info_account_id[$name_accountinfo]);
				if ($admin_accountinfo)
					array_push($config['COMMENT_BEHING'],$up_png);
				else
					array_push($config['COMMENT_BEHING'],"");
				array_push($config['SELECT_DEFAULT'],'');
				array_push($config['JAVASCRIPT'],'');
				array_push($config['SIZE'],'');
			}
			
			array_push($name_field,$name_accountinfo);
			array_push($tab_name,$val_admin_info['COMMENT']);
			if ($_SESSION['OCS']['CONFIGURATION']['CHANGE_ACCOUNTINFO'])
				array_push($type_field,$convert_type[$val_admin_info['TYPE']]);
			else
				array_push($type_field,3);
				
			
		$nb_row++;
	}	
	
		$tab_typ_champ=show_field($name_field,$type_field,$value_field,$config);
		if ($_SESSION['OCS']['CONFIGURATION']['ACCOUNTINFO'] == 'YES')
			$tab_hidden=array('ADMIN'=>'','UP'=>'','DOWN'=>'');
		//echo "<input type='hidden' name='ADMIN' id='ADMIN' value=''>";
		
		tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=true,$form_name='NO_FORM',$show_admin_button);
	
	echo "</div>"; 
	echo "</form>";
}
?>