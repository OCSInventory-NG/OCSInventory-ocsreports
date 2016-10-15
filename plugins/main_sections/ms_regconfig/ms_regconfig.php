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
require_once('require/function_regconfig.php');
$tab_hidden=array();
$tab['VIEW']=$l->g(1059);
$tab['ADD']=$l->g(1060);
$form_name="registry";
$table_name="registry";
echo open_form($form_name, '', '', 'form-horizontal');

if (isset($protectedPost['MODIF']) and $protectedPost['MODIF'] != ''){	
		$protectedPost['tab'] = 'ADD';
		$sql="select NAME,REGTREE,REGKEY,REGVALUE,ID from regconfig where id = '%s'";
		$arg=$protectedPost['MODIF'];
		//$sql="select NAME,ID,MASK from subnet where netid='".$netid."'";
		$res=mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
		$row=mysqli_fetch_object($res);
		$protectedPost['NAME']=$row->NAME;
		$protectedPost['REGTREE']=$row->REGTREE;
		$protectedPost['REGKEY']=$row->REGKEY;
		$protectedPost['REGVALUE']=$row->REGVALUE;
		$tab_hidden['id']=$row->ID;	
}
$tab_options=$protectedPost;
show_tabs($tab,$form_name,"tab",4);
echo '<div class="col col-md-10" >';
if ($ajax){
	if (isset($protectedPost['REGKEY'])){
		$protectedPost['tab']="VIEW";
	}
}
if ($protectedPost['tab'] == 'VIEW'){
	
	// delete register key
	if (isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != '') {
		// delete one row
		delkey($protectedPost['SUP_PROF']);
		$tab_options['CACHE'] = 'RESET';
	} else if (isset($protectedPost['del_check']) and $protectedPost['del_check'] != '') {
		// delete multiple selected rows
		$ids = explode(',', $protectedPost['del_check']);
		foreach ($ids as $id) {
			delkey($id);
		}
		$tab_options['CACHE'] = 'RESET';
	}
	
	$list_fields = array ( 'ID'   => "id", 
						   $l->g(49) => "name", 
						   'REGTREE' => "REGTREE",
						   'REGKEY' => "REGKEY",
						   'REGVALUE' => "REGVALUE",
						   'SUP' => "id",
						   'MODIF' => "id",
						   'CHECK'=>'id');	
	$list_col_cant_del=array('SUP'=>'SUP','MODIF' => 'MODIF',$l->g(49)=> $l->g(49),'CHECK'=>'CHECK');
	$default_fields= array($l->g(49) => "name", 
						   'REGTREE' => "REGTREE",
						   'REGKEY' => "REGKEY",
						   'REGVALUE' => "REGVALUE",
						   'SUP' => "id",
						   'MODIF' => "id",
						   'CHECK'=>"id");
	$sql=prepare_sql_tab($list_fields,array('SUP','MODIF','CHECK'));
	$tab_options['ARG_SQL']=$sql['ARG'];
	$sql['SQL'] .= " from regconfig ";
	$tab_options['FILTRE']['name']=$l->g(49);
	$tab_options['FILTRE']['REGKEY']='REGKEY';
	$tab_options['FILTRE']['REGVALUE']='REGVALUE';
	$tab_options['REPLACE_VALUE']['REGTREE']=$list_registry_key;
	$tab_options['LBL_POPUP']['SUP']='name';
	$tab_options['form_name']=$form_name;
	$tab_options['table_name']=$table_name;
	ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);		
	$img['image/delete.png']=$l->g(162);
	del_selection($form_name);	
}elseif ($protectedPost['tab'] == 'ADD'){

	if (isset($protectedPost['Valid_modif'])){
		$form_values=array("NAME"=>$protectedPost["NAME"],
						   "REGTREE"=>$protectedPost["REGTREE"],
						   "REGKEY"=>$protectedPost["REGKEY"],
						   "REGVALUE"=>$protectedPost["REGVALUE"]);
		if (isset($protectedPost['id']) and $protectedPost['id'] != '')
			$udpate=$protectedPost['id'];
		else
			$udpate=FALSE;
		$result=add_update_key($form_values,$udpate);
		if ($result){
			unset($_SESSION['OCS']['DATA_CACHE'][$table_name]);
			unset($_SESSION['OCS']['NUM_ROW'][$table_name]);		
		}		
	}	
	
	$tab_typ_champ[0]['DEFAULT_VALUE']=$protectedPost['NAME'];
	$tab_typ_champ[0]['INPUT_NAME']="NAME";
	$tab_typ_champ[0]['CONFIG']['SIZE']=60;
	$tab_typ_champ[0]['CONFIG']['MAXLENGTH']=255;
	$tab_typ_champ[0]['INPUT_TYPE']=0;
	$tab_name[0]=$l->g(252).": ";
	$tab_typ_champ[1]['DEFAULT_VALUE']=$list_registry_key;
	$tab_typ_champ[1]['INPUT_NAME']="REGTREE";
	$tab_typ_champ[1]['INPUT_TYPE']=2;
	$tab_name[1]=$l->g(253).":";
	$tab_typ_champ[2]['DEFAULT_VALUE']=$protectedPost['REGKEY'];
	$tab_typ_champ[2]['INPUT_NAME']="REGKEY";
	$tab_typ_champ[2]['CONFIG']['SIZE']=60;
	$tab_typ_champ[2]['CONFIG']['MAXLENGTH']=255;
	$tab_name[2]=$l->g(254).": ";
	$tab_typ_champ[3]['DEFAULT_VALUE']=$protectedPost['REGVALUE'];
	$tab_typ_champ[3]['INPUT_NAME']="REGVALUE";
	$tab_typ_champ[3]['CONFIG']['SIZE']=60;
	$tab_typ_champ[3]['CONFIG']['MAXLENGTH']=255;
	$tab_typ_champ[3]['INPUT_TYPE']=0;
	$tab_name[3]=$l->g(255).": ";
	tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden, array(
		'show_frame' => false
	));
		
}
echo "</div>";
echo close_form();


if ($ajax){
	ob_end_clean();
	if(is_array($sql)){
		tab_req($list_fields,$default_fields,$list_col_cant_del,$sql['SQL'],$tab_options);
	}else{
		tab_req($list_fields,$default_fields,$list_col_cant_del,$sql,$tab_options);
	}
}

?>