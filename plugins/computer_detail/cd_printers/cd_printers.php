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
/*SERVERNAME = Nom du serveur de partage de l'imprimante
SHARENAME = Nom de partage de l'imprimante sur le serveur
RESOLUTION = Resolution au format horizontal x vertical
COMMENT = commentaire
SHARED = 1 si partagée, 0 sinon
NETWORK = 1 si impirmante sur le réseau, 0 si imprimante connectée localement
1323 Serveur de partage imprimante
1324 Partage imprimante sur serveur
1325 Résolution format horizontal/vertical
*/

	if ((array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')){
		parse_str($protectedPost['ocs']['0'], $params);
		$protectedPost+=$params;
		ob_start();
		$ajax = true;
	}
	else{
		$ajax=false;
	}
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	print_item_header($l->g(79));
	$form_name="affich_printers";
	$table_name=$form_name;
	echo open_form($form_name);
	$tab_options=$protectedPost;
	$tab_options['form_name']=$form_name;
	$tab_options['table_name']=$table_name;
	$list_fields=array($l->g(49) => 'NAME',
					   $l->g(278) => 'DRIVER',
					   $l->g(279) => 'PORT',
					   $l->g(53) =>'DESCRIPTION',
					   $l->g(1323) =>'SERVERNAME',
					   $l->g(1324) =>'SHARENAME',
					   $l->g(1325) =>'RESOLUTION',
					   $l->g(51) =>'COMMENT',
					   $l->g(1326) =>'SHARED',
					   $l->g(1327) =>'NETWORK');
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;
	$tab_options['FILTRE'] = array_flip($list_fields);
	$queryDetails  = "SELECT * FROM printers WHERE (hardware_id=$systemid)";
	ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	echo close_form();
	if ($ajax){
		ob_end_clean();
		tab_req($list_fields,$default_fields,$list_col_cant_del,$queryDetails,$tab_options);
		ob_start();
	}
?>