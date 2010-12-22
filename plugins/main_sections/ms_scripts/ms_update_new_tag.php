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
 * 
 * if your version of ocs < 2.0, your tag are in this table but not in accountinfo_config
 * so we have to add them.
 * 
 */

//show all columns in accountinfo table
$sql="show columns from accountinfo";
$res=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"]);
while ($value = mysql_fetch_object($res)){
	if ($value->Field != 'HARDWARE_ID' and $value->Field != 'TAG')
		$list_field[$value->Field]=$value->Field;
		$type_field[$value->Field]=$value->Type;
}

$fields_table=array('ID','NAME_ACCOUNTINFO','TYPE,NAME','ID_TAB','COMMENT','SHOW_ORDER','ACCOUNT_TYPE');
$sql=prepare_sql_tab($fields_table);
$sql['SQL'] .="from accountinfo_config where ACCOUNT_TYPE='COMPUTERS'";
$res=mysql2_query_secure($sql['SQL'],$_SESSION['OCS']["readServer"],$sql['ARG']);
while ($value = mysql_fetch_object($res)){
	if (!isset($max_order) or $value->SHOW_ORDER > $max_order)
		$max_order=$value->SHOW_ORDER;
	if ($value->NAME_ACCOUNTINFO != 'TAG'){
		//this column does'nt exist in accountinfo_config
		if (!$list_field['fields_'.$value->ID]){
			//add this column in accountinfo_config
			$sql_column_account="ALTER TABLE accountinfo ADD COLUMN %s VARCHAR(255) default NULL";
			$arg="fields_".$value->ID;
			if (isset($protectedPost['EXE']) and $protectedPost['EXE']!= ''){
				mysql2_query_secure($sql_column_account,$_SESSION['OCS']["writeServer"],$arg);			
				addLog( 'SCRIPT_ADD_COLUMN_ACCOUNTINFO',$arg);	
			}else{
				$add_colum_accountinfo[]=$arg;				
			}
		}
		$name_accountinfo["fields_".$value->ID]	= "fields_".$value->ID;
	}
}
//for each column we are going to verify that this field exist in accountinfo_config
if (is_array($list_field)){
foreach ($list_field as $name){
	//if this name does'nt exist in accuontinfo_config
	if (!isset($name_accountinfo[$name]))
	{
		//echo $name_accountinfo[$name]."=>".$name."<br>";
		unset($fields_table,$values);
		$fields_table=array('TYPE','NAME','ID_TAB','COMMENT','SHOW_ORDER','ACCOUNT_TYPE');
		$max_order++;
		
		if ($type_field[$name] == "varchar(10)")
			$type=6;
		elseif ($type_field[$name] == "blob")
			$type=5;
		elseif ($type_field[$name] == "varchar(255)")
			$type=0;
		else
			$type=0;
		$sql="insert into accountinfo_config ";
		$arg='';
		$sql=mysql2_prepare($sql,$arg,$fields_table,true);
		$values=array($type,$name,1,$name. " (" . $l->g(2101) . ")",$max_order,'COMPUTERS');
		$sql=mysql2_prepare($sql['SQL']." VALUES ",$sql['ARG'],$values);
		
		if (isset($protectedPost['EXE']) and $protectedPost['EXE']!= '')
			mysql2_query_secure($sql['SQL'],$_SESSION['OCS']["writeServer"],$sql['ARG']);
		else
			$add_lign_accountinfo_config[]=$sql['ARG'];
		$sql_alter="ALTER TABLE accountinfo CHANGE %s  %s %s";
		$arg=array($name,"fields_".mysql_insert_id(),$type_field[$name]);
		
		if (isset($protectedPost['EXE']) and $protectedPost['EXE']!= ''){
			mysql2_query_secure($sql_alter,$_SESSION['OCS']["writeServer"],$arg);			
			addLog( 'SCRIPT_ADD_DATA_ACCOUNTINFO_CONFIG',$name);	
		}else
			$rename_col_accountinfo[]=$arg;
	}
	
}
}

if (isset($add_colum_accountinfo) or isset($add_lign_accountinfo_config) or isset($rename_col_accountinfo)){
		
		
		$form_name = "console";
		echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
		echo "<b>This script is going to help you to update your old admin info<br>";
		echo "to the new version 2.0 <br><br></b> ";
		echo "<input type=submit id='EXE' name='EXE'>";
		echo "</form>";
		echo "<br>";
		echo '<div class="mlt_bordure" >';
		echo "<font size=4><i>Summary of actions to be undertaken</i></font><br><br>";
		if (isset($add_colum_accountinfo)){
			echo "<b><font color=red>add column in accountinfo table<br>
					(orphans found in accountinfo_config table (Inconsistency))<br><br></font></b>";
			foreach($add_colum_accountinfo as $key=>$values){
				echo $values."<br>";		
			}	
		}
		
		
		if (isset($add_lign_accountinfo_config)){
			echo "<b><font color=blue>add lignes in accountinfo_config table<br>
					(orphans found in accountinfo table (=> 2.0))<br><br></font></b>";
			foreach($add_lign_accountinfo_config as $key=>$values){
				$i=0;
				while (isset($values[$i])){
					echo $values[$i];
					echo "&nbsp;";
					if ($i == 5)
						echo "<br>";
					$i++;	
				}
				echo "<br><br><br>";		
			}	
			echo "<br>";
		}
		if (isset($rename_col_accountinfo)){
			echo "<b><font color=blue>Renaming of old columns in accountinfo table
					<br>(=> 2.0)<br><br><br></font></b>";
			foreach($rename_col_accountinfo as $key=>$values){
				echo $values[0]."<br>";
			}	
			
		}
		echo "</div>";
}else
echo "<font size=4 color=blue>YOUR BASE IS A DAY.<br>NO ACTION TAKEN</font>";
/*ALTER TABLE accountinfo CHANGE fields_86 lettre_commande  varchar(255);
ALTER TABLE accountinfo CHANGE fields_87 date_commande  varchar(10);
ALTER TABLE accountinfo CHANGE fields_88 fichier_commande  blob;
ALTER TABLE accountinfo CHANGE fields_89 fichier_commande_bis  blob;*/

?>