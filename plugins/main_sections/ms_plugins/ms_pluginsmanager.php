<?php
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Gilles DUBOIS 2015
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

require_once 'require/function_commun.php';

if (!class_exists('plugins')) {
	require 'plugins.class.php';
}

if (!function_exists('rrmdir')) {
	require 'functions_delete.php';
}

if (!function_exists('exec_plugin_soap_client')) {
	require 'functions_webservices.php';
}

if (!function_exists('install')) {
	require 'functions_check.php';
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

// Plugins Install menu.

printEnTete($l->g(7008));

echo "<table align='center'><th>";
echo open_form("PluginInstall");

$availablePlugins = scan_downloaded_plugins();

if (!empty($availablePlugins)){

	echo "<select name='plugin'>";
	foreach ($availablePlugins as $key => $value){
		$name = explode(".", $value);
		$info = new SplFileInfo(PLUGINS_DL_DIR."/".$value);
		
		if($info->getExtension() == "zip"){
			echo "<option value=$value >$name[0]</option>";
		} 
	}
	echo "</select>";
	echo "<input type='submit' value='Install'>";
}
else{
	msg_warning($l->g(7014));
}

echo close_form();
echo "</th></table>";

if (isset($protectedPost['plugin'])){
	
	$pluginArchive = $protectedPost['plugin'];
	
	$bool = install($pluginArchive);
	
	if($bool){
		$pluginame = explode(".", $pluginArchive);
		
		$plugintab = array("name" => $pluginame[0]);
		
		$isok = check($plugintab);
		
		mv_computer_detail($pluginame[0]);
		$result = mv_server_side($pluginame[0]);
		
		if($result){
			exec_plugin_soap_client($pluginame[0], 1);
		}
		
		if ($isok){
			$msg = $l->g(6003)." ".$pluginame[0]." ".$l->g(7013) ;
			msg_success($msg);
		}else{
			$msg = $l->g(2001)." ".$pluginame[0]." ".$l->g(7011)."<br>".$l->g(7012);
			msg_error($msg);
		}
		
		
	}else{
		msg_error($l->g(7010));
	}
	
}

// Plugins Tab

printEnTete($l->g(7009));

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

