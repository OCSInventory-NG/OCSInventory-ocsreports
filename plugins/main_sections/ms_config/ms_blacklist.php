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

/*
 * this page makes it possible to seize the MAC addresses for blacklist
 */
require_once('require/function_blacklist.php');
$form_name="blacklist";

//printEnTete($l->g(703));
if ($protectedPost['onglet'] == "" or !isset($protectedPost['onglet']))
$protectedPost['onglet']=1;
 //d�finition des onglets
$data_on[1]=$l->g(95);
$data_on[2]=$l->g(36);
$data_on[3]=$l->g(2005);
$data_on[4]=$l->g(116);
if (isset($protectedPost['enre'])){
	if ($protectedPost['BLACK_CHOICE'] == 1){
		$ok=add_mac_add($protectedPost);	
	}
	if ($protectedPost['BLACK_CHOICE'] == 3){
		$ok=add_subnet_add($protectedPost);
		
	}	
	if ($protectedPost['BLACK_CHOICE'] == 2){
		$ok=add_serial_add($protectedPost);
	}
	if ($ok){
		msg_error($ok);
	}else
		unset($_SESSION['OCS']['DATA_CACHE'],$_SESSION['OCS']['NUM_ROW']);
}
echo open_form($form_name);
onglet($data_on,$form_name,"onglet",10);
echo '<div class="mlt_bordure" >';

if ($protectedPost['onglet'] == 1){
	$table_name="blacklist_macaddresses";
	$list_fields= array('ID'=>'ID',
						'MACADDRESS'=>'MACADDRESS',
						'SUP'=>'ID',
						//'MODIF'=>'ID',
						'CHECK'=>'ID');
	$list_col_cant_del=$list_fields;
	$default_fields=$list_fields; 
	$tab_options['FILTRE']=array('MACADDRESS'=>'MACADDRESS');
	$tab_options['LBL_POPUP']['SUP']='MACADDRESS';
	$tab_options['LBL']['MACADDRESS']=$l->g(95);
}elseif($protectedPost['onglet'] == 2){
	$table_name="blacklist_serials";
	$list_fields= array('ID'=>'ID',
						'SERIAL'=>'SERIAL',
						'SUP'=>'ID',
						//'MODIF'=>'ID',
						'CHECK'=>'ID');
	$list_col_cant_del=$list_fields;
	$default_fields=$list_fields; 
	$tab_options['FILTRE']=array('SERIAL'=>'SERIAL');
	$tab_options['LBL_POPUP']['SUP']='SERIAL';
	$tab_options['LBL']['SERIAL']=$l->g(36);
}elseif($protectedPost['onglet'] == 3){
	$table_name="blacklist_subnet";
	$list_fields= array('ID'=>'ID',
						'SUBNET'=>'SUBNET',
						'MASK'=>'MASK',
						'SUP'=>'ID',
						//'MODIF'=>'ID',
						'CHECK'=>'ID');
	$list_col_cant_del=$list_fields;
	$default_fields=$list_fields; 
	$tab_options['FILTRE']=array('SUBNET'=>'SUBNET','MASK'=>'MASK');
	$tab_options['LBL_POPUP']['SUP']='SUBNET';
	$tab_options['LBL']['SUBNET']=$l->g(2005);
}elseif ($protectedPost['onglet'] == 4){
	$list_action[1]=$l->g(95);
	$list_action[2]=$l->g(36);
	$list_action[3]=$l->g(2005);
	echo $l->g(700).": ".show_modif($list_action,"BLACK_CHOICE",2,$form_name)."<br>";
	if (isset($protectedPost['BLACK_CHOICE']) and $protectedPost['BLACK_CHOICE'] != ''){
		$aff="<table align=center><tr><td>";
		if ($protectedPost['BLACK_CHOICE'] == 1){
			$aff.=$l->g(654).": </td><td>";
			$aff=show_blacklist_fields($MACnb_field,$protectedPost,$MACfield_name,$MACnb_value_by_field,$MACsize,$MACseparat,$javascript_mac);
			
			
		}elseif ($protectedPost['BLACK_CHOICE'] == 3){
			$aff.=$l->g(1142).": </td><td>";
			$aff=show_blacklist_fields($SUBnb_field,$protectedPost,$SUBfield_name,$SUBnb_value_by_field,$SUBsize,$SUBseparat,$chiffres);
			$aff.=$l->g(1143).": </td><td>";
			$aff=show_blacklist_fields($MASKnb_field,$protectedPost,$MASKfield_name,$MASKnb_value_by_field,$MASKsize,$MASKseparat,$chiffres);
			
			
		}elseif ($protectedPost['BLACK_CHOICE'] == 2){
			$aff.=$l->g(702).": </td><td>";
			$aff=show_blacklist_fields($SERIALnb_field,$protectedPost,$SERIALfield_name,$SERIALnb_value_by_field,$SERIALsize,$SERIALseparat);
			//$aff.="</td></tr>";	
		}
		if (isset($aff)){
			$aff.="</td></tr></table>
				<input class='bouton' name='enre' type='submit' value=".$l->g(114).">";
				echo $aff;		
		}
	}
	


}
if (isset($list_fields)){
	//cas of delete mac address or serial
	if(isset($protectedPost["SUP_PROF"]) and is_numeric($protectedPost["SUP_PROF"])){
		$sql="delete from %s where id=%s";
		$arg=array($table_name,$protectedPost["SUP_PROF"]);
		mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg);
	}
	if (isset($protectedPost['del_check']) and $protectedPost['del_check'] != ''){
		$sql="delete from %s where id in (%s)";
		$arg=array($table_name,$protectedPost['del_check']);
		mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$arg);
		$tab_options['CACHE']='RESET';
	}
	$sql=prepare_sql_tab($list_fields,array('SUP','CHECK','MODIF'));
	$sql['SQL'].= " from ".$table_name;
	$tab_options['ARG_SQL']=$sql['ARG'];
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$form_name,100,$tab_options);
	del_selection($form_name);
}	
echo "</div>";
echo close_form();
?>
