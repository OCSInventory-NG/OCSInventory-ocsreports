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

if(AJAX){  
	parse_str($protectedPost['ocs']['0'], $params);	
	$protectedPost+=$params; 
	ob_start();
	$ajax = true;
}
else{
	$ajax=false;
}


require_once('require/function_config_generale.php');
$form_name = "console";
if ($protectedPost['RESET'] == 'FIRST'){
	unset($_SESSION['OCS']['COUNT_CONSOLE']);
}
if ($protectedPost['ADMIN'] == 'ADMIN' and !isset($_SESSION['OCS']['ADMIN_CONSOLE'])){
	$_SESSION['OCS']['ADMIN_CONSOLE']='ADMIN';	
}elseif ($protectedPost['ADMIN'] == 'ADMIN' and isset($_SESSION['OCS']['ADMIN_CONSOLE'])){
	unset($_SESSION['OCS']['ADMIN_CONSOLE']);
}

if (isset($protectedPost['VISIBLE']) and $protectedPost['VISIBLE'] != '' and isset($_SESSION['OCS']['ADMIN_CONSOLE'])){
	insert_update($protectedPost['VISIBLE'],1,'','IVALUE');
}

if (isset($protectedPost['NO_VISIBLE']) and $protectedPost['NO_VISIBLE'] != '' and isset($_SESSION['OCS']['ADMIN_CONSOLE'])){
		delete($protectedPost['NO_VISIBLE']);
}

if (isset($protectedPost['UPDATE_VALUE']) and $protectedPost['UPDATE_VALUE'] != '' and isset($_SESSION['OCS']['ADMIN_CONSOLE'])){
	$arg=look_config_default_values($protectedPost['UPDATE_VALUE']);
	insert_update($protectedPost['UPDATE_VALUE'],$protectedPost[$protectedPost['UPDATE_VALUE']],$arg['ivalue'][$protectedPost['UPDATE_VALUE']],'IVALUE');
}

require_once('require/function_console.php');


$data_on=define_tab();
$data_tab=show_active_tab($data_on);
echo open_form($form_name);
if (isset($protectedPost["onglet"]) and !isset($data_tab['DATA'][$protectedPost["onglet"]]))
	$protectedPost["onglet"]=$data_tab['DEFAULT'];
	
if ($data_tab['DATA'] != array()){
	show_tabs($data_tab['DATA'],$form_name,"onglet",8);
	
	echo '<div class="right-content"><div class="mlt_bordure" >';
	
	if ($_SESSION['OCS']['profile']->getConfigValue('CONSOLE') == 'YES'){
		echo "<table align='right' border='0'><tr><td colspan=10 align='right'><a href=# OnClick='pag(\"ADMIN\",\"ADMIN\",\"".$form_name."\");'>";
		if (isset($_SESSION['OCS']['ADMIN_CONSOLE']) and $_SESSION['OCS']['ADMIN_CONSOLE'] == 'ADMIN')
			echo "<img src='image/success.png'>";
		else
			echo "<img src='image/modif_tab.png'>";
		echo "</a></td></tr></table>";
	}
	if ($data_on['DATA'][$protectedPost['onglet']]){
		$fields=list_field($protectedPost['onglet']);
		show_console_field($fields,$form_name);
	}else{
		$array_group="";
		$sql_group="select id,name from hardware where deviceid='_SYSTEMGROUP_'";
		$res=mysql2_query_secure($sql_group,$_SESSION['OCS']["readServer"]);
		while ($value = mysqli_fetch_object($res)){
			$array_group[$value->id]=$value->name;			
		}
		if (is_array($array_group)){
			
			if (isset($protectedPost["SUP_PROF"]) and $protectedPost["SUP_PROF"]!=''){
				delete($protectedPost['SUP_PROF']);	
			}
			
			if (isset($protectedPost["Valid_modif"]) and $protectedPost["Valid_modif"] != '') {
				$sql_msg="select name from config where name like '%s'";
				$arg="GUI_REPORT_MSG%";
				$result_msg = mysql2_query_secure($sql_msg,$_SESSION['OCS']["readServer"],$arg);
				while($item_msg = mysqli_fetch_object($result_msg)){
					$list_name_msg[]=substr($item_msg ->name,14);		
				}
				if (isset($list_name_msg)){
					$i=1;
					foreach ($list_name_msg as $k=>$v){
						if ($v == $i)
						$i++;			
					}
				} else {
					$i=1;
				}
				
				$tab_options=$protectedPost;
				
				if (trim($protectedPost['GROUP']) != "" and is_numeric($protectedPost['GROUP']) and trim($protectedPost['MESSAGE'])!=""){
					$sql="insert into config (NAME,IVALUE,TVALUE) values ('%s',%s,'%s')";
					$arg=array("GUI_REPORT_MSG".$i,$protectedPost['GROUP'],$protectedPost['MESSAGE']);
					mysql2_query_secure( $sql, $_SESSION['OCS']["writeServer"],$arg );
					$tab_options['CACHE']='RESET';
				} else {
					msg_error($l->g(239));
				}
			}else{
				$tab_options=$protectedPost;
			}
		
			$table_name=$protectedPost['onglet'];
			$tab_options['table_name']=$table_name;
			$tab_options['form_name']=$form_name;	
			$list_fields=array('GROUP_NAME'=>'h.NAME', 
							   $l->g(915) => 'tvalue',
							   'SUP'=>'CNAME');

			$sql=prepare_sql_tab($list_fields,array('SUP'));
			$list_col_cant_del=$list_fields;
			$default_fields= $list_fields;
			$sql['SQL']  = $sql['SQL'].",c.name as CNAME,ID FROM %s WHERE (c.name like '%s')";
			$sql['ARG'][]='config c left join hardware h on c.ivalue=h.id';
			$sql['ARG'][]='GUI_REPORT_MSG%';
			$tab_options['ARG_SQL']=$sql['ARG'];
			$tab_options['LBL_POPUP']['SUP']=$l->g(919);
			$tab_options['LBL']['GROUP_NAME']=$l->g(49);
			ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);	
			echo "<input type='submit' name='NEW' id='NEW' value='".$l->g(617)."' align=center>";
			if ($protectedPost["NEW"]){
					$name_field=array("GROUP","MESSAGE");
					$tab_name=array($l->g(577).": ",$l->g(449).":");
					$type_field= array(2,1);	
					$value_field=array($array_group,'');
					$tab_typ_champ=show_field($name_field,$type_field,$value_field);
					foreach ($tab_typ_champ as $id=>$values){
						$tab_typ_champ[$id]['CONFIG']['SIZE']=20;
					}
					if (isset($tab_typ_champ)){
						tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden);
					}	
						
			}
			
		}

	}
	
	echo "</div></div>";
	echo "<input type=hidden name='ADMIN' value='' id='ADMIN'>";
	echo "<input type=hidden name='VISIBLE' value='' id='VISIBLE'>";
	echo "<input type=hidden name='NO_VISIBLE' value='' id='NO_VISIBLE'>";
	echo "<input type=hidden name='VALID_MODIF' value='' id='VALID_MODIF'>";
	echo "<input type=hidden name='SHOW_ME' value='' id='SHOW_ME'>";
	echo "<input type=hidden name='UPDATE_VALUE' value='' id='UPDATE_VALUE'>";
}else
	echo "<table align=center><tr><td align=center><img src='image/fond.png'></td></tr></table>";
	
if (isset($protectedPost["onglet"]) and isset($protectedPost["old_onglet"]) 
	and $protectedPost["onglet"]!=$protectedPost["old_onglet"])
	unset($protectedPost["SHOW_ME"],$protectedPost["SHOW_ME_SAUV"]);

if (isset($protectedPost["SHOW_ME"]) and $protectedPost["SHOW_ME"] != '' 
	and $protectedPost["SHOW_ME_SAUV"] != ""
	and $protectedPost["SHOW_ME"] != $protectedPost["SHOW_ME_SAUV"]){
		unset($protectedPost["SHOW_ME_SAUV"]);
}

if (isset($protectedPost["SHOW_ME_SAUV"]) and $protectedPost["SHOW_ME_SAUV"] != '' 
	and $protectedPost["SHOW_ME"] == "")
	$protectedPost["SHOW_ME"]=$protectedPost["SHOW_ME_SAUV"];

	
if ((isset($protectedPost["SHOW_ME"]) and $protectedPost["SHOW_ME"] != "")){
	$array_fields=array_values($table_field[$protectedPost["SHOW_ME"]]);
	echo "<input type=hidden name='SHOW_ME_SAUV' value='".$protectedPost["SHOW_ME"]."' id='SHOW_ME_SAUV'>";
	$table_name=$protectedPost["SHOW_ME"];
	$tab_options=$protectedPost;
	$tab_options['form_name']=$form_name;
	$tab_options['table_name']=$table_name;	
	if (!isset($sql_field[$protectedPost["SHOW_ME"]]['SQL']))
		$sql_field[$protectedPost["SHOW_ME"]]['SQL']="select %s from %s %s";
	
	$list_fields=$table_field[$protectedPost["SHOW_ME"]];
	$list_fields[$l->g(1120)]='c';
	foreach ($table_field[$protectedPost["SHOW_ME"]] as $lbl=>$value){
		$recup_list_add_field[]=$value;
	}
	
	$sql_field[$protectedPost["SHOW_ME"]]['ARG'][0]="count(*) c,".implode(',',$recup_list_add_field);
	if (!preg_match("/where/i", $sql_field[$protectedPost["SHOW_ME"]]['ARG'][2]))
		$sql_field[$protectedPost["SHOW_ME"]]['ARG'][2].=" where ";
	else
		$sql_field[$protectedPost["SHOW_ME"]]['ARG'][2].=" and ";
		
	//restriction on computer id
	if (isset($myids)){
		if (mb_strtoupper($sql_field[$protectedPost["SHOW_ME"]]['ARG'][1]) == "HARDWARE")
			$sql_field[$protectedPost["SHOW_ME"]]['ARG'][2].="id ";
		else
			$sql_field[$protectedPost["SHOW_ME"]]['ARG'][2].="hardware_id";
		$sql_field[$protectedPost["SHOW_ME"]]['ARG'][2].=" in (".implode(',',$myids['ARG']).") and ";
	}
	$sql_field[$protectedPost["SHOW_ME"]]['ARG'][2].= $recup_list_add_field[0]." is not null group by ".implode(',',$recup_list_add_field);
	
	$tab_options['SQL_COUNT']="select %s from %s %s";
	$tab_options['ARG_SQL_COUNT']=array("count(distinct ".implode(',',$recup_list_add_field).") count_nb_ligne",
										$sql_field[$protectedPost["SHOW_ME"]]['ARG'][1],$sql_field[$protectedPost["SHOW_ME"]]['ARG'][2]);
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;
	$tab_options['ARG_SQL']=$sql_field[$protectedPost["SHOW_ME"]]['ARG'];
	if (isset($multi_search[$protectedPost["SHOW_ME"]])){
		$tab_options['LIEN_LBL'][$l->g(1120)]='index.php?'.PAG_INDEX.'='.$pages_refs['ms_multi_search'].'&fields=';
		$tab_options['LIEN_LBL'][$l->g(1120)].=$multi_search[$protectedPost["SHOW_ME"]]['FIELD']."&comp=".$multi_search[$protectedPost["SHOW_ME"]]['COMP']."&values=";
		$tab_options['LIEN_CHAMP'][$l->g(1120)]=$array_fields[0];		
	}
	ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
}
echo close_form();
//show messages
if ($_SESSION['OCS']['profile']->getRestriction('GUI') == "YES"){
	$info_msg=look_config_default_values('GUI_REPORT_MSG%','LIKE');
	if (is_array($info_msg['ivalue']))
		$list_id_groups=implode(',',$info_msg['ivalue']);
	
	if ($list_id_groups != ""){
		$sql_my_msg="select distinct g_c.group_id groups 
					from accountinfo a ,groups_cache g_c
					where g_c.HARDWARE_ID=a.HARDWARE_ID
						and	g_c.GROUP_ID in (".$list_id_groups.")";
		if (isset($_SESSION['OCS']['mesmachines']) and $_SESSION['OCS']['mesmachines'] != "")
			$sql_my_msg.= " and ".$_SESSION['OCS']['mesmachines'];
		$result_my_msg = mysqli_query( $_SESSION['OCS']["readServer"],$sql_my_msg);
		while($item_my_msg = mysqli_fetch_object($result_my_msg)){
			foreach ($info_msg['ivalue'] as $key=>$value){
				if ($value == $item_my_msg ->groups){
					$msg_group[$key]=$info_msg['tvalue'][$key];
				}				
			}
		}	
		
		if (isset($msg_group) and $msg_group !=''){
			msg_warning(implode('<br>',$msg_group));
		}
	}
}


if ($ajax){
	ob_end_clean();
	
	if (isset($sql_field[$protectedPost["SHOW_ME"]]['SQL'])){
		tab_req($list_fields,$default_fields,$list_col_cant_del,$sql_field[$protectedPost["SHOW_ME"]]['SQL'],$tab_options);
	}
	else{
		tab_req($list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$tab_options);
	}
	
}
?>


