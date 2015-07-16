<?php

if(AJAX){
	parse_str($protectedPost['ocs']['0'], $params);
	$protectedPost+=$params;
	ob_start();
	$ajax = true;
}
else{
	$ajax=false;
}

printEnTete($l->g(6000));

if (!function_exists('rrmdir')) {
	require 'functions_delete.php';
}

if ($protectedPost['SUP_PROF'] != ''){
	delete_plugin($protectedPost['SUP_PROF']);
	$tab_options['CACHE']='RESET';
}

if (isset($protectedPost['del_check']) and $protectedPost['del_check'] != ''){
	
	$delarray = explode(",", $protectedPost['del_check']);
	
	foreach ($delarray as $value){
		delete_plugin($value);
	}
	$tab_options['CACHE']='RESET';
}

$form_name="show_all_plugins";
$table_name=$form_name;
$tab_options=$protectedPost;
$tab_options['form_name']=$form_name;
$tab_options['table_name']=$table_name;

echo open_form($form_name);
$list_fields=array('ID'=>'id',
				   $l->g(7002)=>'name',
				   $l->g(7003)=>'version',
				   $l->g(7004)=>'licence',
				   $l->g(7005)=>'author',
				   'Required OCS ver.'=>'verminocs',
				   'Activated'=>'activated',
				   $l->g(7006) =>'reg_date'
				);			

$tab_options['FILTRE']=array_flip($list_fields);
$tab_options['FILTRE']['NAME']=$l->g(49);
asort($tab_options['FILTRE']); 
$list_fields['SUP']='ID';
$list_fields['CHECK']='ID';

$list_col_cant_del=array('SUP'=>'SUP','CHECK'=>'CHECK');
$default_fields= array($l->g(7002)=>$l->g(7002),$l->g(7003)=>$l->g(7003),$l->g(7004)=>$l->g(7005),$l->g(7006),$l->g(7006));
$sql=prepare_sql_tab($list_fields,$list_col_cant_del);
$tab_options['ARG_SQL']=$sql['ARG'];
$queryDetails  = $sql['SQL'].",ID from plugins";
$tab_options['LBL_POPUP']['SUP']=$l->g(7007)." ";
$tab_options['LBL']['SUP']=$l->g(122);

$tab_options['LIEN_LBL']['NAME']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_pluginsmanager'].'&head=1&id=';
$tab_options['LIEN_CHAMP']['NAME']='ID';
$tab_options['LBL']['NAME']=$l->g(49);

ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
$img['image/delete.png']=$l->g(162);
del_selection($form_name);
echo close_form();

if ($ajax){
	ob_end_clean();
	tab_req($list_fields,$default_fields,$list_col_cant_del,$queryDetails,$tab_options);
}
?>

