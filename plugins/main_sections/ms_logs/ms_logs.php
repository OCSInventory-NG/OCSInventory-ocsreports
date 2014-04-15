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
if ((array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')){  
		parse_str($protectedPost['ocs']['0'], $params);	
		$protectedPost+=$params; 
		
	ob_start();
	$ajax = true;
}
else{
	$ajax=false;
}



 require_once('require/function_table_html.php');
 require_once('require/function_files.php');
//$data_on['GUI_LOGS']="Logs de l'interface";
$protectedPost['onglet'] == "";
$tab_options=$protectedPost;

$Directory=$_SESSION['OCS']['LOG_DIR']."/";
$data=ScanDirectory($Directory,"csv");

if (is_array($data)){
	$form_name = "logs";
	echo open_form($form_name);
	$sql="";
	$arg=array();
	foreach($data['name'] as $id=>$value){
		if ($id == 0){
			$name='as name';
			$date_create='as date_create';
			$date_modif='as date_modif';
			$size='as size';	
		}else{
			$name='';
			$date_create='';
			$date_modif='';
			$size='';	
		}
		$sql.="select '%s' ".$name.",'%s' ".$date_create.",'%s' ".$date_modif.",'%s' ".$size." union ";
		array_push($arg,$value);
		array_push($arg,rtrim($data['date_create'][$id],"."));
		array_push($arg,rtrim($data['date_modif'][$id],"."));
		array_push($arg,round($data['size'][$id]/1024,3)." ".$l->g(516));
	}
	$sql=substr($sql,0,-6);
	
		$list_fields=array('name' => 'name',
						   $l->g(951) => 'date_create',
						   $l->g(952) => 'date_modif',
						   $l->g(953) => 'size'
						   );
		$list_col_cant_del=$list_fields;
		$default_fields= $list_fields;
	
	//	$sql= "select '%s' as function,%s from deploy";
		$tab_options['ARG_SQL']=$arg;
		$tab_options['LBL']['name']=$l->g(950);
		$tab_options['LIEN_LBL']['name']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_csv'].'&no_header=1&log=';
		$tab_options['LIEN_CHAMP']['name']='name';
		$tab_options['LIEN_TYPE']['name']='POPUP';
		$tab_options['POPUP_SIZE']['name']="width=900,height=600";
		printEntete($l->g(928));
		echo "<br>";
		$table_name = $form_name;
		$tab_options['form_name']=$form_name;
		$tab_options['table_name']=$table_name;
		ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	echo close_form();

}else
	msg_warning($l->g(766));

if ($ajax){
	ob_end_clean();
	tab_req($list_fields,$default_fields,$list_col_cant_del,$sql,$tab_options);
}
?>
