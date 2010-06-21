 <?php
 //====================================================================================
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================
 

include 'fonction.inc.php' ;
$form_name="search_soft";
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
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

//echo "\n".'<body>';
//echo "\n".'<div id="en-tete"><p><img src="intranet.gif" alt="intranet" id="intranet" /></p></div>';
echo "\n".'<i>'.$l->g(20).": ";
//echo "\n".'</i><select name="logiciel_select" >'."\n";

// voir fontion.php
remplirListe("logiciel_select");
//echo "\n".'</select>'."\n";
/*echo "\n".'</select><input id="show" type="submit" name="submit" value="Afficher" />';
echo "\n".'</form>';*/

// formulaire , bouton et code php pour l autocompletion
//echo "\n".'<form  method="post" name="form-test" id="form-test" >';
//echo "\n".'<input type="hidden" name="ms_options" id="ms_options" value="index.php?function=search_soft_option&no_header=1">';
echo "\n".'<br><br><input type="text" name="logiciel_text" value="'.$protectedPost['logiciel_text'].'" id="champ-texte"  size="15"/>';
echo "\n".'<br><input type="submit" id="bouton-submit" value="'.$l->g(965).'" name="bouton-submit">';
//echo "\n".'</form>';


/*if(!isset($protectedPost['logiciel']) || is_null($protectedPost['logiciel']) || (empty($protectedPost['logiciel'])))
	{
	//echo "\n".'<input type="hidden" name="export" id="export" value="export" ></form>';
	}
else
	{
//	echo "\n".'<form  method ="post" name="export" id="export" action="export.php">';
//	echo "\n".'<input type="hidden" name="logiciel" id="export" value="'.$_POST['logiciel'].'">';
//	echo "\n".'<input type="submit" name="export" id="export" value="Export" ></form>';
	}

echo "\n";'</p>';*/



// balise pour <div id="fr"> sert pour la fonction d'impression
echo "\n".'<div id="fr">';

// voir fonction.php
if ((isset($protectedPost['logiciel_select']) and $protectedPost['logiciel_select'] != '')
	 or (isset($protectedPost['logiciel_text']) and $protectedPost['logiciel_text'] != ''))                                   //logiciel du select name='logiciel'
	{
			echo "\n".'<input type="button" id="bouton-print" value="'.$l->g(214).'" onclick="imprime_zone(\'tableau\',\'fr\');">';
		
	if (isset($protectedPost['logiciel_select']) and $protectedPost['logiciel_select'] != '')
	$logiciel=$protectedPost['logiciel_select'];
	else
	$logiciel=$protectedPost['logiciel_text'];
	
	$table_name=$form_name;
	//echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	$list_fields=array('NAME' => 'h.NAME',
					   'ip' => 'h.IPADDR',
					   'domaine' => 'h.WORKGROUP',
					   'snom' => 'a.NAME',
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
				   WHERE a.HARDWARE_ID =h.ID and a.NAME='".$logiciel."'";
	
	$tab_options['LBL']['NAME']=$l->g(478);
	$tab_options['LBL']['ip']=$l->g(176);
	$tab_options['LBL']['domaine']=$l->g(680);
	$tab_options['LBL']['snom']=$l->g(847);
	$tab_options['LBL']['sversion']=$l->g(848);
	$tab_options['LBL']['sfold']=$l->g(849);
	
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,80,$tab_options);

	//creerTableau($logiciel);
	}

echo "\n".'</div>';

//echo "\n".'</div>';
echo "</form>";
//mysql_close();
?>	
