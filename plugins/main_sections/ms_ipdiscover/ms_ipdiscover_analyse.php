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
$tab_options=$protectedPost;

require_once('require/function_files.php');
require_once('require/function_ipdiscover.php');


$form_name='ipdiscover_analyse';
$table_name=$form_name;
$tab_options['form_name']=$form_name;
$tab_options['table_name']=$table_name;
echo open_form($form_name);
$pas = $protectedGet['rzo'];
//$rez = $nomRez;
$values=look_config_default_values(array('IPDISCOVER_IPD_DIR'));
$fname=$values['tvalue']['IPDISCOVER_IPD_DIR'];
$file_name=$fname."/ipd/".$pas.".ipd";
//reset cache?
if (isset($protectedPost['reset']) and $protectedPost['reset'] != ''){
	unlink( $file_name );
	reloadform_closeme('',true);		
}else{	
   	 if (!is_readable($file_name))
		runCommand("-cache -net=".$pas,$fname);
	$tabBalises = Array($l->g(34) => "IP",
					$l->g(95) => "MAC",
					$l->g(49) => "NAME",
					$l->g(232) => "DATE",
					$l->g(66) => "TYPE");
	$ret=array();
	$ret= parse_xml_file($file_name,$tabBalises,"HOST");
	if ($ret != array()){
		$sql="select ";
		$i=0;
		//var_dump($ret);
		while ($ret[$i]){
				foreach ($ret[$i] as $key=>$value){
						$sql.= "'".$value. "' as ".$key.",";	
				}
					$sql=substr($sql,0,-1)." union select ";
					$i++; 
				}
				$sql=substr($sql,0,-13);
				$default_fields=$tabBalises;
				$list_col_cant_del=$default_fields;
				$tab_options['NO_NAME']['NAME']=1;
				$result_exist=ajaxtab_entete_fixe($tabBalises,$default_fields,$tab_options,$list_col_cant_del);		
				
			}
	echo "<p><input type='submit' name='reset' value='".$l->g(1261)."'></p>";
}
		
echo close_form();

if ($ajax){
	ob_end_clean();
	tab_req($tabBalises,$default_fields,$list_col_cant_del,$sql,$tab_options);
}
?>