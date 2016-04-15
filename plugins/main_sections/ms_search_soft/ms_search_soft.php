 <?php
 //====================================================================================
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



 

require_once('require/fonction.inc.php');
$form_name="search_soft";
$tab_options=$protectedPost;
$tab_options['form_name']=$form_name;
echo open_form($form_name);
//html
/*echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>\n";
echo "<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='fr'>"."\n";
echo "<head><title>Check Logiciel's Version</title><meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1' />"."\n";
echo '<link rel="stylesheet" media="screen" type="text/css" title="Design" href="design.css" />'."\n";
echo "\n".'<script type="text/javascript" src="print.js"></script>';
echo "\n".'<script type="text/javascript" src="autocomplete-3-2.js"></script>';
echo "\n".'<script type="text/javascript"> window.onload = function(){initAutoComplete(document.getElementById(\'form-test\'), document.getElementById(\'champ-texte\'), document.getElementById(\'bouton-submit\'))}</script>';
echo "\n".'</head>';

*/
$xml_file="index.php?".PAG_INDEX."=".$pages_refs['ms_options']."&no_header=1";
echo "\n".'<script type="text/javascript"> 
	window.onload = function(){initAutoComplete(document.getElementById(\''.$form_name.'\'), document.getElementById(\'champ-texte\'), document.getElementById(\'bouton-submit\'),\''.$xml_file.'\')}
	</script>';


echo "\n".'<i>'.$l->g(20).": ";

remplirListe("logiciel_select");

echo "\n".'<p><input type="text" name="logiciel_text" value="'.$protectedPost['logiciel_text'].'" id="champ-texte"  size="15"/></p>';
echo "\n".'<input type="submit" id="bouton-submit" value="'.$l->g(13).'" name="bouton-submit">';

echo "\n".'<div id="fr">';

// voir fonction.php
if ((isset($protectedPost['logiciel_select']) and $protectedPost['logiciel_select'] != '')
	 or (isset($protectedPost['logiciel_text']) and $protectedPost['logiciel_text'] != ''))                                   //logiciel du select name='logiciel'
	{		
	if (isset($protectedPost['logiciel_select']) and $protectedPost['logiciel_select'] != '')
	$logiciel=$protectedPost['logiciel_select'];
	else
	$logiciel=$protectedPost['logiciel_text'];
	
	$table_name=$form_name;

	$tab_options['table_name']=$table_name;
	$list_fields=array('NAME' => 'h.NAME',
					   'ip' => 'h.IPADDR',
					   'domaine' => 'h.WORKGROUP',
					   'snom' => 'a.NAME as softname',
					   'sversion'=> 'a.VERSION',
					   'sfold' => 'a.FOLDER');
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;
	$tab_options['AS']['a.NAME']='SNAME';
	//$queryDetails  = "SELECT * FROM monitors WHERE (hardware_id=$systemid)";
	$queryDetails= "SELECT h.ID,";
	foreach ($list_fields as $lbl=>$value){
		if ($value == 'a.NAME')
			$queryDetails .= $value." as ".$tab_options['AS']['a.NAME'].",";	
		else
			$queryDetails .= $value.",";		
	}
	$queryDetails  = substr($queryDetails,0,-1);
	
	$queryDetails.= " FROM hardware h ,softwares a
				   WHERE a.HARDWARE_ID =h.ID and a.NAME='".$logiciel."' group by name";
	
	$tab_options['LBL']['NAME']=$l->g(478);
	$tab_options['LBL']['ip']=$l->g(176);
	$tab_options['LBL']['domaine']=$l->g(680);
	$tab_options['LBL']['snom']=$l->g(847);
	$tab_options['LBL']['sversion']=$l->g(848);
	$tab_options['LBL']['sfold']=$l->g(849);
	
	ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);

	//creerTableau($logiciel);
	}

echo "\n".'</div>';

echo close_form();


if ($ajax){
	ob_end_clean();
	tab_req($list_fields,$default_fields,$list_col_cant_del,$queryDetails,$tab_options);
}
?>	
