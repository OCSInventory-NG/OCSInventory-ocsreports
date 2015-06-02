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

require_once('require/function_files.php');
require_once('require/function_ipdiscover.php');


$form_name='ipdiscover_analyse';
$table_name=$form_name;
echo open_form($form_name);
$pas = $protectedGet['rzo'];
//$rez = $nomRez;
$values=look_config_default_values(array('IPDISCOVER_IPD_DIR'), '', array('IPDISCOVER_IPD_DIR'=>array('TVALUE'=>VARLIB_DIR)));
$fname=$values['tvalue']['IPDISCOVER_IPD_DIR'];
$file_name=$fname."/ipd/".$pas.".ipd";
//reset cache?
if (isset($protectedPost['reset']) and $protectedPost['reset'] != ''){
	unlink( $file_name );
	reloadform_closeme('',true);		
}else{	
	$fp = @fopen($file_name, "r");
   	 if (!$fp)
		runCommand("-cache -net=".$pas,$fname);
	@fclose($fp);
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
				$result_exist=tab_req($table_name,$tabBalises,$default_fields,$list_col_cant_del,$sql,$form_name,80,$tab_options); 		
				
			}
	echo "<br><input type='submit' name='reset' value='".$l->g(1261)."'>";
}
		
echo close_form();

?>