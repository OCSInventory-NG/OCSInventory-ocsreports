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
$form_name='pulgins_injector';
$table_name=$form_name;
$data_on['VIEW']=$l->g(1307);
$data_on['ADD']=$l->g(1308);
unset($ERROR);
echo "<br>";
echo open_form($form_name,'',"enctype='multipart/form-data'");
onglet($data_on,$form_name,"onglet",2);
echo '<div class="mlt_bordure" >';
require_once('require/function_compress.php');
if($_FILES['plug_php']['tmp_name']){
$toto=unzip($_FILES['plug_php']['tmp_name'],PLUGINS_GUI_DIR);
p($toto);
	/*$zip = zip_open($_FILES['plug_php']['tmp_name']);
		while ($zip_entry = zip_read($zip))
		{
			$file_name= zip_entry_name($zip_entry);
			$temp_exp_file_name=explode('.',$file_name);
			if (strtoupper(array_pop($temp_exp_file_name)) != 'PHP'){
				$ERROR="C'est pas des fichiers PHP";
			}
		}
	zip_close($zip);
	p($nom_fichier);*/
}
	
/*	
$zip = zip_open( $_FILES['plug_php']['tmp_name']);
do {
                $entry = zip_read($zip);
                p($entry);
            } while ($entry);
            zip_entry_open($zip, $entry, "r");
            
            zip_close($zip);
}*/
 /*   // writing uncompressed file
    $fp = fopen( $dstFileName, "w" );
    fwrite( $fp, $data );
    fclose( $fp );*/
if ($protectedPost['onglet'] == 'VIEW'){
	
	
	
	
	
}elseif ($protectedPost['onglet'] == 'ADD'){
	$name_field=array();
	$tab_name=array();
	$type_field=array();
	$value_field=array();
	$type_plugins=array('GUI'=>'Interface','DETAILS'=>'Détail de machine','AGENT'=>'Agent','MOTEUR'=>'Moteur');
	//if (isset($protectedGet['admin'])){
	array_push($name_field,"plug_type");
	array_push($tab_name,"Type :");
	array_push($type_field,2);
	array_push($value_field,$type_plugins);
		
	array_push($name_field,"plug_php");
	array_push($tab_name,"pages php (format zippé):");
	array_push($type_field,13);
	array_push($value_field,$protectedPost['plug_php']);	
	

	array_push($name_field,"plug_name");
	array_push($tab_name,"Nom du plugin:");
	array_push($type_field,0);
	array_push($value_field,$protectedPost['plug_php']);
	
	array_push($name_field,"plug_lbl");
	array_push($tab_name,"Libellé du plugin:");
	array_push($type_field,0);
	array_push($value_field,$protectedPost['plug_lbl']);


	$tab_typ_champ=show_field($name_field,$type_field,$value_field,$config);
/*	$tab_typ_champ[1]['CONFIG']['SIZE']=30;
	$tab_typ_champ[2]['CONFIG']['SIZE']=30;
	$tab_typ_champ[4]['COMMENT_BEHING']="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_adminvalues']."&head=1&tag=".$account_field."&form=".$form_name."\",\"".$account_field."\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")><img src=image/plus.png></a>";
*/	$tab_typ_champ[0]['RELOAD']=$form_name;/*
	$tab_typ_champ[3]['RELOAD']=$form_name;*/
	tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=true,$form_name='NO_FORM');
}
echo "</div>"; 
echo close_form();
?>