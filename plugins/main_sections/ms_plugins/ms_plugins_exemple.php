<?php
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Erwan GOALOU 2013 (erwan(at)ocsinventory-ng(pt)org)
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
	$showit=true;
	$ajax = true;
}
else{
	$ajax=false;
}

$tab_options=$protectedPost;


//first include: get tab name
if (!$showit){
	$data_on['LICENCES']=$l->g(6001);
	$data_on['REPART_LICENCES']=$l->g(6002);
	$data_on['NB_BY_LICENCES']=$l->g(6003);
}else{ //second include
 
	if ($protectedPost["onglet"] == 'LICENCES'){
		//BEGIN SHOW ACCOUNTINFO
		require_once('require/function_admininfo.php');
		$accountinfo_value=interprete_accountinfo($list_fields,$tab_options);
		if (array($accountinfo_value['TAB_OPTIONS']))
			$tab_options=$accountinfo_value['TAB_OPTIONS'];
		if (array($accountinfo_value['DEFAULT_VALUE']))
			$default_fields=$accountinfo_value['DEFAULT_VALUE'];
		$list_fields=$accountinfo_value['LIST_FIELDS'];
		//END SHOW ACCOUNTINFO
		$list_fields2 = array ( $l->g(46) => "h.lastdate", 
							   'NAME'=>'h.name',
							   $l->g(949) => "h.ID",
							   $l->g(24) => "h.userid",
							   $l->g(25) => "h.osname",
							   $l->g(33) => "h.workgroup",
							   $l->g(275) => "h.osversion",
							   $l->g(286) => "h.oscomments",
							   $l->g(352) => "h.lastcome",
							   $l->g(53) => "h.description",
							   $l->g(355) => "h.wincompany",
							   $l->g(356) => "h.winowner",
							   $l->g(357) => "h.useragent",
							   $l->g(34) => "h.ipaddr",
							   $l->g(557) => "h.userdomain",
							   $l->g(277) => "n.officeversion",
							   $l->g(36) => "n.officekey");
 
		$list_fields=array_merge ($list_fields,$list_fields2);
		$tab_options['FILTRE']=array_flip($list_fields);
		$tab_options['FILTRE']['h.name']=$l->g(23);
		asort($tab_options['FILTRE']); 
		$list_col_cant_del=array('NAME'=>'NAME');
		$default_fields2= array($_SESSION['OCS']['TAG_LBL']['TAG']=>$_SESSION['OCS']['TAG_LBL'],$l->g(46)=>$l->g(46),'NAME'=>'NAME',
								$l->g(277)=>$l->g(277), $l->g(36) =>$l->g(36));
		$default_fields=array_merge ($default_fields,$default_fields2);
		$sql=prepare_sql_tab($list_fields);
		$tab_options['ARG_SQL']=$sql['ARG'];
		$queryDetails  = $sql['SQL']." from hardware h 
						RIGHT JOIN officepack n ON n.hardware_id=h.id 
						LEFT JOIN accountinfo a ON a.hardware_id=h.id 
						";
		if (isset($_SESSION['OCS']["mesmachines"]) and $_SESSION['OCS']["mesmachines"] != '')
			$queryDetails  .= "WHERE ".$_SESSION['OCS']["mesmachines"];
		$tab_options['form_name']=$form_name;
		$tab_options['table_name']=$table_name;
		$result_exist=ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	}
	elseif($protectedPost["onglet"] == 'REPART_LICENCES'){
 
		$list_fields=array($l->g(66) => 'officeversion',
							$l->g(55) => 'NB',
						   "PERCENT_BAR" => 'POURC');						   
		$list_col_cant_del=$list_fields;			
		$default_fields= $list_fields;
		$tab_options['LBL']['PERCENT_BAR']=$l->g(1125);
		$queryDetails  = "select count(id) NB,officeversion,round(count(id)*100/(select count(id) from officepack)) POURC from officepack group by officeversion";
		ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	}
	elseif($protectedPost["onglet"] == 'NB_BY_LICENCES'){
 
		if(isset($protectedPost["Valid_modif"]) and $protectedPost["Valid_modif"] != ''){
			if (isset($protectedPost["nb_total"]) and is_numeric($protectedPost["nb_total"])){
				$val=look_config_default_values('PLUGIN_MS_OFF%',1);
				$insert=true;	
				$i=0;
				if (isset($val['name'])){
					foreach ($val['name'] as $name){
						if($val['tvalue'][$name] == $protectedPost["NUM_OFF"]){
							$name_to_update=$name;
							$insert=false;					
						}						
						$explo_name=explode('_',$name);	
						if (isset($explo_name[3]) and $explo_name[3]>=$i)
							$i=$explo_name[3];		
					}
				}
				$i++;
				if ($insert){
					$sql="insert into config (NAME,IVALUE,TVALUE,COMMENTS) values ('%s',%s,'%s','%s')";
					$arg=array('PLUGIN_MS_OFF_'.$i,$protectedPost["nb_total"],$protectedPost["NUM_OFF"],'Max licences');
				}else{
					$sql="update config set IVALUE='%s' where name='%s'";
					$arg=array($protectedPost["nb_total"],$name_to_update);					
				}
				mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);		
			}			
		}
 
		if (isset($protectedPost["MODIF"]) and $protectedPost["MODIF"] != ''){
 
			$showbutton=true;
			$tab_hidden=array('NUM_OFF'=>$protectedPost["MODIF"]);
			$type_field= array(0);
			$name_field=array("nb_total");
			$tab_name=array($l->g(6004)." : ");
			$value_field=array($protectedPost['nb_total']);
			$config['JAVASCRIPT'][0]=$chiffres;
			$tab_typ_champ=show_field($name_field,$type_field,$value_field,$config);
			$tab_typ_champ[0]['CONFIG']['SIZE']=20;
			tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,array(
				'title' => $title,
				'show_button' => $showbutton
			));
 
 
		}else{
			$list_fields=array( $l->g(36) => 'officekey',
								$l->g(66) => 'officeversion',
								$l->g(55) => 'NB',
								$l->g(6004)=>'NB_TOTAL',
							   "PERCENT_BAR" => 'POURC',
								'MODIF'=>'officekey');						   
			$list_col_cant_del=$list_fields;			
			$default_fields= $list_fields;
			$tab_options['LBL']['PERCENT_BAR']=$l->g(1125);
			$queryDetails  = "select count(id) NB,
									  officekey,
									  officeversion,
									  ivalue NB_TOTAL,
									  round(count(id)*100/ivalue) POURC 
							from officepack left join config on tvalue=officekey
							group by officekey";
			ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
		}		
	}
}
if ($ajax){
	ob_end_clean();
	tab_req($list_fields,$default_fields,$list_col_cant_del,$queryDetails,$tab_options);
}

?>

