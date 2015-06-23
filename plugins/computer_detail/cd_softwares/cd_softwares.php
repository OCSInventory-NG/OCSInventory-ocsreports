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
	unset($list_fields);
	print_item_header($l->g(20));
	$form_name="affich_soft";
	$table_name=$form_name;
	$tab_options=$protectedPost;
	$tab_options['form_name']=$form_name;
	$tab_options['table_name']=$table_name;
	echo open_form($form_name);
	$list_fields[$l->g(69)] = 'PUBLISHER';
	if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES']) 
		and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1){		
			$queryDetails  = "SELECT s.PUBLISHER,
									 s_name.NAME as NAME,
									 s_version.NAME as VERSION,
									 s.COMMENTS,s.FOLDER,s.FILENAME,s.FILESIZE,s.GUID,
									 s.LANGUAGE,s.INSTALLDATE,s.BITSWIDTH
							   FROM softwares s
								left join type_softwares_name s_name on s_name.id= s.name_id
								left join type_softwares_version s_version on s_version.id=s.version_id
								WHERE (hardware_id=$systemid)";		
			$list_fields[$l->g(49)] = 's_name.NAME';
	}else{
		$queryDetails  = "SELECT * FROM softwares 
								 WHERE (hardware_id=$systemid)";
		$list_fields[$l->g(49)] = 'NAME';
	}
	$list_fields[$l->g(277)] = 'VERSION';
	$list_fields[$l->g(51)]='COMMENTS';
	if($show_all_column)
		$list_col_cant_del=$list_fields;
	else
		$list_col_cant_del=array($l->g(49)=>$l->g(49));
		
	$default_fields= $list_fields;
	$list_fields[$l->g(1248)]='FOLDER';
	$list_fields[$l->g(446)]='FILENAME';	
	$list_fields[ucfirst(strtolower($l->g(953)))]='FILESIZE';
	
	$list_fields['GUID']='GUID';
	$list_fields[ucfirst(strtolower($l->g(1012)))]='LANGUAGE';	
	$list_fields[$l->g(1238)]='INSTALLDATE';
	$list_fields[$l->g(1247)]='BITSWIDTH';

	$tab_options['FILTRE']=array_flip($list_fields);//array('NAME'=>$l->g(49),'VERSION'=>$l->g(277),'PUBLISHER'=>$l->g(69));
	
	ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	echo close_form();
	if ($ajax){
		ob_end_clean();
		tab_req($list_fields,$default_fields,$list_col_cant_del,$queryDetails,$tab_options);
		ob_start();
	}
?>