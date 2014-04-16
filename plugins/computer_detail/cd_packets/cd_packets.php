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


/*
 * 
 * NO USE?
 * 
 * 
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
	print_item_header($l->g(512));
	$form_name="affich_packets";
	$table_name=$form_name;
	$tab_options=$protectedPost;
	$tab_options['form_name']=$form_name;
	$tab_options['table_name']=$table_name;
	echo open_form($form_name);
	$list_fields=array($l->g(475) => 'PKG_ID',
					   $l->g(49) => 'NAME',
					   $l->g(440)=>'PRIORITY',
					   $l->g(464)=>'FRAGMENTS',
					   $l->g(462)." Ko"=>'SIZE',
					   $l->g(25)=>'OSNAME',
					   'COMMENT'=>'COMMENT');
	$list_col_cant_del=array($l->g(475)=>$l->g(475),$l->g(49)=>$l->g(49));
	$default_fields= $list_col_cant_del;
	$pack_sup="<b><font color=red>".$l->g(561)."</font></b>";
	$queryDetails  = "SELECT PKG_ID,NAME,PRIORITY,FRAGMENTS,round(SIZE/1024,2) as SIZE,OSNAME,COMMENT
						FROM download_history h LEFT JOIN download_available a ON h.pkg_id=a.fileid 
						where hardware_id=%s and name is not null";
	$arg=array($systemid);
	if (isset($_SESSION['OCS']['RESTRICTION']['TELEDIFF_VISIBLE']) and $_SESSION['OCS']['RESTRICTION']['TELEDIFF_VISIBLE'] == "YES"){
			$queryDetails  .= " and a.comment not like '%s'";
			array_push($arg,'%[VISIBLE=0]%');
	}
	$queryDetails  .= "	union SELECT PKG_ID,'%s','%s','%s','%s','%s','%s'
						FROM download_history h LEFT JOIN download_available a ON h.pkg_id=a.fileid where hardware_id=%s and name is null";
	$i=0;
	while ($i<6){
		array_push($arg,$pack_sup);
		$i++;
	}
	array_push($arg,$systemid);
	$tab_options['ARG_SQL']=$arg;
	ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	
	echo close_form();
	if ($ajax){
		ob_end_clean();
		tab_req($list_fields,$default_fields,$list_col_cant_del,$queryDetails,$tab_options);
		ob_start();
	}
?>