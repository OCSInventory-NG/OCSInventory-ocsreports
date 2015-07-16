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
	print_item_header($l->g(270));
	if (!isset($protectedPost['SHOW']))
	$protectedPost['SHOW'] = 'NOSHOW';
	$form_name="affich_modems";
	$table_name=$form_name;
	$tab_options=$protectedPost;
	$tab_options['form_name']=$form_name;
	$tab_options['table_name']=$table_name;
	
	echo open_form($form_name);
	$list_fields=array($l->g(49) => 'NAME',
					   $l->g(65) => 'MODEL',
					   $l->g(53) => 'DESCRIPTION',
					   $l->g(66) => 'TYPE');
	if($show_all_column)
		$list_col_cant_del=$list_fields;
	else
		$list_col_cant_del=array($l->g(49)=>$l->g(49),$l->g(66)=>$l->g(66));
		
	$default_fields= $list_fields;
	$queryDetails  = "SELECT * FROM modems WHERE (hardware_id=$systemid)";
	ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	echo close_form();
	if ($ajax){
		ob_end_clean();
		tab_req($list_fields,$default_fields,$list_col_cant_del,$queryDetails,$tab_options);
		ob_start();
	}


?>