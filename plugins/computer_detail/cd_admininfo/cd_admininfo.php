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
if (!is_array($info_account_id))
	msg_error($info_account_id);
else{
	
		if (isset($protectedPost['ADMIN']) and $protectedPost['ADMIN'] == 'ADMIN' and !isset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO']))
			$_SESSION['OCS']['ADMIN']['ACCOUNTINFO']=true;
		elseif (isset($protectedPost['ADMIN']) and $protectedPost['ADMIN'] == 'ADMIN' and isset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO']))
			unset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO']);
		
		if ($_SESSION['OCS']['profile']->getConfigValue('ACCOUNTINFO') == 'YES' and isset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO']))
			$admin_accountinfo=true;
			
		$list_tab=find_all_account_tab('TAB_ACCOUNTAG','COMPUTERS',1);	
		if ($list_tab != ''){
			if ($protectedPost['Valid_modif'] != ""){
				
				if (!isset($protectedPost['onglet']) or $protectedPost['onglet'] == '' or ! is_numeric($protectedPost['onglet']))
					$protectedPost['onglet'] = $list_tab['FIRST'];
				
				$sql_admin_info = "select ID, NAME_ACCOUNTINFO from accountinfo_config where ID_TAB = %s and account_type='COMPUTERS' order by SHOW_ORDER ASC";
				$arg_admin_info = array($protectedPost['onglet']);
				
				$res_admin_info = mysql2_query_secure($sql_admin_info, $_SESSION['OCS']["readServer"], $arg_admin_info);
				
				while ( $val_admin_info = mysqli_fetch_array($res_admin_info) ) {
					if ($val_admin_info['NAME_ACCOUNTINFO']) {
						$data_fields_account[$val_admin_info['NAME_ACCOUNTINFO']] = "";
					} else {
						$data_fields_account["fields_" . $val_admin_info["ID"]] = "";
					}
				}

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
			if (!isset($protectedPost['onglet']) or $protectedPost['onglet'] =='' or !is_numeric($protectedPost['onglet']))
				 $protectedPost['onglet'] = $list_tab['FIRST'];
			unset($list_tab['FIRST']);
			
			echo open_form($form_name);
			if (!$show_all_column){
				onglet($list_tab,$form_name,"onglet",6);
				$sql_admin_info="select ID,TYPE,NAME,COMMENT,NAME_ACCOUNTINFO,SHOW_ORDER,DEFAULT_VALUE from accountinfo_config where ID_TAB = %s and account_type='COMPUTERS'
								order by SHOW_ORDER ASC";
				$arg_admin_info=array($protectedPost['onglet']);
			}else{
				$sql_admin_info="select ID,TYPE,NAME,COMMENT,NAME_ACCOUNTINFO,SHOW_ORDER,DEFAULT_VALUE from accountinfo_config where account_type='%s'
								order by SHOW_ORDER ASC";
				$arg_admin_info=array('COMPUTERS');		
			}
			echo '<div class="form-frame">';
			if ($_SESSION['OCS']['profile']->getConfigValue('ACCOUNTINFO') == 'YES' and !$show_all_column){
				$show_admin_button = "<a href=# OnClick='pag(\"ADMIN\",\"ADMIN\",\"".$form_name."\");'>";
				if (isset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO']))
					$show_admin_button .= "<img src=image/success.png></a>";
				else
					$show_admin_button .= "<img src=image/modif_tab.png></a>";
			
			}else
				$show_admin_button='';
				
			$res_admin_info=mysql2_query_secure($sql_admin_info,$_SESSION['OCS']["readServer"],$arg_admin_info);
			$num_row=mysqli_num_rows($res_admin_info);
			$name_field=array();
			$tab_name=array();
			$type_field=array();
			$value_field=array();
			$config['COMMENT_AFTER']=array();
			$config['SELECT_DEFAULT']=array();
			$config['JAVASCRIPT']=array();
			$config['SIZE']=array();
			$config['DDE']=array();
			
			$nb_row=1;
			while ($val_admin_info = mysqli_fetch_array( $res_admin_info )){	
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
						or $val_admin_info['TYPE'] == 7 ){
						array_push($config['JAVASCRIPT'],'');
						array_push($config['SIZE'],'');
						if ($admin_accountinfo)
							array_push($config['COMMENT_AFTER'],$up_png . "<a href=# href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_adminvalues']."&head=1&tag=ACCOUNT_VALUE_" . $val_admin_info['NAME'] . "\"><img src=image/plus.png></a>");
						else
							array_push($config['COMMENT_AFTER'],'');
						array_push($config['SELECT_DEFAULT'],'YES');
						$field_select_values=find_value_field("ACCOUNT_VALUE_".$val_admin_info['NAME']);
						
						//cas of checkbox
						if ($val_admin_info['TYPE'] == 4){
					
							$temp_val=explode('&&&',$info_account_id[$name_accountinfo]);
							$i=0;
							$tp_readonly="";
							while (isset($temp_val[$i]) and $temp_val[$i] != ''){
								if ($_SESSION['OCS']['profile']->getConfigValue('CHANGE_ACCOUNTINFO') == "YES"){			
									$protectedPost[$name_accountinfo . '_' . $temp_val[$i]]='on';
								}else{
									$tp_readonly .= $field_select_values[$temp_val[$i]].";";
								}
								$i++;			
							}
							
							if($tp_readonly != ''){
								array_push($value_field,substr($tp_readonly,0,-1));
							}else{
								array_push($value_field,$field_select_values);
							}
						}else{
							$protectedPost[$name_accountinfo]=$info_account_id[$name_accountinfo];
							if ($_SESSION['OCS']['profile']->getConfigValue('CHANGE_ACCOUNTINFO') == "YES")
								array_push($value_field,$field_select_values);
							else
								array_push($value_field,$field_select_values[$protectedPost[$name_accountinfo]]);
						}
							
					}elseif ($val_admin_info['TYPE'] == 6){	
						array_push($value_field,$info_account_id[$name_accountinfo]);
						if ($_SESSION['OCS']['profile']->getConfigValue('CHANGE_ACCOUNTINFO') == "YES"){
							if ($admin_accountinfo)
								array_push($config['COMMENT_AFTER'],$up_png . datePick($name_accountinfo));
							else
								array_push($config['COMMENT_AFTER'],datePick($name_accountinfo));
							array_push($config['JAVASCRIPT'],"READONLY ".dateOnClick($name_accountinfo));
							array_push($config['SELECT_DEFAULT'],'');
							array_push($config['SIZE'],'8');	
						}
					}elseif ($val_admin_info['TYPE'] == 5){
						array_push($value_field,"accountinfo");
						if ($admin_accountinfo)
							array_push($config['COMMENT_AFTER'],$up_png);
						else
							array_push($config['COMMENT_AFTER'],"");
						array_push($config['SELECT_DEFAULT'],'');
						array_push($config['JAVASCRIPT'],'');
						array_push($config['SIZE'],'');
					}elseif ($val_admin_info['TYPE'] == 8){ //QRCODE
						
						array_push($value_field,$info_account_id[$name_accountinfo]);
						if ($admin_accountinfo){
							
							array_push($config['COMMENT_AFTER'],$up_png);
						}else
							array_push($config['COMMENT_AFTER'],"");
						
						array_push($config['SELECT_DEFAULT'],"index.php?".PAG_INDEX."=".$pages_refs['ms_qrcode']."&no_header=1&default_value=".$val_admin_info['DEFAULT_VALUE']."&systemid=".$protectedGet['systemid']);
						array_push($config['JAVASCRIPT'],"onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_qrcode']."&no_header=1&default_value=".$val_admin_info['DEFAULT_VALUE']."&systemid=".$protectedGet['systemid']."\")");
						array_push($config['SIZE'],'width=80 height=80');
						
						}else{
						array_push($value_field,$info_account_id[$name_accountinfo]);
						if ($admin_accountinfo)
							array_push($config['COMMENT_AFTER'],$up_png);
						else
							array_push($config['COMMENT_AFTER'],"");
						array_push($config['SELECT_DEFAULT'],'');
						array_push($config['JAVASCRIPT'],'');
						array_push($config['SIZE'],'');
					}
					
					array_push($name_field,$name_accountinfo);
					array_push($tab_name,$val_admin_info['COMMENT']);
					if ($_SESSION['OCS']['profile']->getConfigValue('CHANGE_ACCOUNTINFO') == "YES")
						array_push($type_field,$convert_type[$val_admin_info['TYPE']]);
					else
						array_push($type_field,3);
						
					
				$nb_row++;
			}	
				$tab_typ_champ=show_field($name_field,$type_field,$value_field,$config);
				if ($_SESSION['OCS']['profile']->getConfigValue('ACCOUNTINFO') == 'YES')
					$tab_hidden=array('ADMIN'=>'','UP'=>'','DOWN'=>'');
				//echo "<input type='hidden' name='ADMIN' id='ADMIN' value=''>";
				if ($show_all_column or $admin_accountinfo)
					$showbutton=false;
				else
					$showbutton=true;
				
				if ($_SESSION['OCS']['profile']->getConfigValue('CHANGE_ACCOUNTINFO') != "YES")
					$showbutton=false;
				tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden, array(
					'show_button' => $showbutton,
					'form_name' => $form_name='NO_FORM',
					'top_action' => $show_admin_button,
					'show_frame' => false
				));
			
			echo '</div>';
			echo close_form();
		}
}
?>