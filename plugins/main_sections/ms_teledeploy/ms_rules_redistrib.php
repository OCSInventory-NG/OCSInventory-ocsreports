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
if(AJAX){
	parse_str($protectedPost['ocs']['0'], $params);
	$protectedPost+=$params;
	ob_start();
	$ajax = true;
}
else{
	$ajax=false;
}

$tab_options=$protectedPost;
/*
 * Rules for redistribution servers
 */
if ($_SESSION['OCS']["use_redistribution"] == 1){
	require_once('require/function_rules.php');
	printEnTete($l->g(673));
	echo "<br />";
	//only for Super Admin
	//DEL RULE
	if ($protectedPost['SUP_PROF'] != ""){	
		delete_rule($protectedPost['SUP_PROF']);
		$tab_options['CACHE']='RESET';
	}
	//ADD new rule
	if ($protectedPost['ADD_RULE']){
		add_rule($protectedPost['RULE_NAME'],$protectedPost);
		$tab_options['CACHE']='RESET';
	}
	//modif rule
	if ($protectedPost['MODIF_RULE']){	
		$name_exist=verify_name($protectedPost['RULE_NAME'],"and rule != ".$protectedPost['OLD_MODIF']);
		if ($name_exist == 'NAME_NOT_EXIST'){
			delete_rule($protectedPost['OLD_MODIF']);
			add_rule($protectedPost['RULE_NAME'],$protectedPost,$protectedPost['OLD_MODIF']);
			echo msg_success($l->g(711));
			$tab_options['CACHE']='RESET';
		}
		else{
			msg_error($l->g(670));
		}
	}
	//form name
	$form_name = "rules";
	//show all rules
		echo open_form($form_name, '', '', 'form-horizontal');
				$list_fields= array('ID_RULE'=>'RULE',
									'RULE_NAME'=>'RULE_NAME',
									'SUP'=>'RULE',
									'MODIF'=>'RULE',
									);
				$table_name="DOWNLOAD_AFFECT_RULES";
				$default_fields= array('ID_RULE'=>'ID_RULE','RULE_NAME'=>'RULE_NAME','SUP'=>'SUP','MODIF'=>'MODIF');
				$list_col_cant_del=array('ID_RULE'=>'ID_RULE','SUP'=>'SUP','MODIF'=>'MODIF');
				$sql=prepare_sql_tab($list_fields,array('SUP'));
				
				$sql['SQL'] .= " from download_affect_rules ";
				$tab_options['ARG_SQL']=$sql['ARG'];

				$tab_options['form_name']=$form_name;
				$tab_options['table_name']=$table_name;
				$result_exist= ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
				echo "<br>";
		
	//Modif a rule => get this values 
	if ($protectedPost['MODIF'] != "" and $protectedPost['OLD_MODIF'] != $protectedPost['MODIF']){
		$sql="select priority,cfield,op,compto,rule_name 
				from download_affect_rules 
			 where rule='%s' 
				order by priority";
		$arg=$protectedPost['MODIF'];
		$res = mysql2_query_secure( $sql, $_SESSION['OCS']["readServer"],$arg);
		$i=1;
		while ($val = mysqli_fetch_array( $res )){
			$protectedPost['PRIORITE_'.$i]=$val['priority'];
			$protectedPost['CFIELD_'.$i]=$val['cfield'];
			$protectedPost['OP_'.$i]=$val['op'];
			$protectedPost['COMPTO_'.$i]=$val['compto'];
			$protectedPost['RULE_NAME']=$val['rule_name'];
			$i++;
		}
		$protectedPost['NUM_RULES']=$i-2;
	}
	
	//new rule
	if ($protectedPost['NEW_RULE'] or $protectedPost['NUM_RULES'] or $protectedPost['MODIF'] != ""){
		if ($protectedPost['MODIF'] != "")
		$modif=$protectedPost['MODIF'];
		else
		$modif=$protectedPost['OLD_MODIF'];
		$numero=$protectedPost['NUM_RULES']+1;
		$tab_nom=$l->g(674)." ".show_modif($protectedPost['RULE_NAME'],"RULE_NAME","0");
		$tab="<table align='center'>";
		$i=1;
		while($i<$numero+1){
			if ($i==1)
			$entete='YES';
			else
			$entete='NO';
		$tab.=fields_conditions_rules($i,$entete);
		$i++;
		}
		echo $tab_nom;
		echo $tab;
		echo "</tr></table>";
		echo "<a onclick='return pag(".$numero.",\"NUM_RULES\",\"rules\")'><font color=green>".$l->g(682)."</font></a>&nbsp<a onclick='return pag(\"RAZ\",\"RAZ\",\"rules\");'><font color=\"red\">".$l->g(113)."</font></a><br><br>";
		if ($protectedPost['MODIF'] != "" or $protectedPost['OLD_MODIF'] != "")
		echo "<input type='submit'  value='".$l->g(625)."' name='MODIF_RULE' onclick='return check();'>";	
		else
		echo "<input type='submit'  value='".$l->g(683)."' name='ADD_RULE' onclick='return check();'>";	
		echo "<input type='hidden' id='NUM_RULES' name='NUM_RULES' value=''>";
		echo "<input type='hidden' id='RAZ' name='RAZ' value=''>";
		echo "<input type='hidden' id='OLD_MODIF' name='OLD_MODIF' value='".$modif."'>";
	}else{	
	echo "<input type='submit' class='btn' value='".$l->g(685)."' name='NEW_RULE'>";
	}
	echo close_form();
}else{
	msg_info($l->g(1182));
}

if ($ajax){
	ob_end_clean();
	tab_req($list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$tab_options);
}
?>
