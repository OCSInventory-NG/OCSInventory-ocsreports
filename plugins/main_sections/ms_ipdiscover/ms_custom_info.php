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
$form_name='info_ipdiscover';
$tab_options=$protectedPost;

//$ban_head='no';
//$no_error='YES';
//recherche de la personne connectée
if (isset($_SESSION['OCS']['TRUE_USER']))
$user=$_SESSION['OCS']['TRUE_USER'];
else
$user=$_SESSION['OCS']['loggeduser'];

//suppression d'une adresse mac
if(isset($protectedPost['SUP_PROF'])){
    
        //check if we are deleting an identified peripherials ?
        if ($protectedGet['prov'] == "ident"){
            //dismiss manufacturer name and mac to be able to remove it properly.
            $exploded_data = explode(' ',$protectedPost['SUP_PROF']);
            //var_dump($exploded_data);
            $protectedPost['SUP_PROF'] = $exploded_data[0];
        } 
    
	$sql="DELETE FROM netmap WHERE mac='%s'";
	mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$protectedPost['SUP_PROF']);
	$sql="DELETE FROM network_devices WHERE macaddr='%s'";
	mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"],$protectedPost['SUP_PROF']);
	unset($_SESSION['OCS']['DATA_CACHE']['IPDISCOVER_'.$protectedGet['prov']]);	
}
//identification d'une adresse mac
if (isset($protectedPost['Valid_modif'])){
	if (trim($protectedPost['COMMENT']) == "")
	$ERROR= $l->g(942);
	if (trim($protectedPost['TYPE']) == "")
	$ERROR= $l->g(943);
	if (isset($ERROR) and $protectedPost['MODIF_ID'] != '')
	$protectedPost['USER']=$protectedPost['USER_ENTER'];

	if (!isset($ERROR)){
		//$post=xml_escape_string($protectedPost);
		if ($protectedPost['USER_ENTER'] != ''){
			$sql="update network_devices 
					set DESCRIPTION = '%s',
					TYPE = '%s',
					MACADDR = '%s',
					USER = '%s' where MACADDR='%s'";		
			$arg=array($protectedPost['COMMENT'],$protectedPost['TYPE'],$protectedPost['mac'],$user,$protectedPost['MODIF_ID']);
		}else{		
			$sql="insert into network_devices (DESCRIPTION,TYPE,MACADDR,USER)
			  VALUES('%s','%s','%s','%s')";
			$arg=array($protectedPost['COMMENT'],$protectedPost['TYPE'],$protectedPost['mac'],$user);
		}
		mysql2_query_secure( $sql , $_SESSION['OCS']["writeServer"],$arg);
		//suppression du cache pour prendre en compte la modif
		unset($_SESSION['OCS']['DATA_CACHE']['IPDISCOVER_'.$protectedGet['prov']]);
	}else{		
		$protectedPost['MODIF']=$protectedPost['mac'];
	}	
}

//formulaire de saisie de l'identification de l'adresse mac
if (isset($protectedPost['MODIF']) and $protectedPost['MODIF'] != ''){
	
	//cas d'une modification de la donnée déjà saisie
	if ($protectedGet['prov'] == "ident" and !isset($protectedPost['COMMENT'])){
		$sql="select DESCRIPTION,TYPE,MACADDR,USER from network_devices where id ='%s'";
		$arg=$protectedPost['MODIF'];
		$res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg );
		$val = mysqli_fetch_array( $res );
		$protectedPost['COMMENT']=$val['DESCRIPTION'];
		$protectedPost['MODIF']=$val['MACADDR'];
		$protectedPost['TYPE']=$val['TYPE'];
		$protectedPost['USER']=	$val['USER'];
		$protectedPost['MODIF_ID']=$protectedPost['MODIF'];
	}
	$tab_hidden['USER_ENTER']=$protectedPost['USER'];	
	$tab_hidden['MODIF_ID']=$protectedPost['MODIF_ID'];	
	//si on est dans le cas d'une modif, on affiche le login qui a saisi la donnée
	if ($protectedPost['MODIF_ID'] != ''){
		$tab_typ_champ[3]['DEFAULT_VALUE']=$protectedPost['USER'];
		$tab_typ_champ[3]['INPUT_NAME']="USER";
		$tab_typ_champ[3]['INPUT_TYPE']=3;
		$tab_name[3]=$l->g(944).": ";
		
		$title=$l->g(945);		
	}else{
		$title=$l->g(946);	
	}
	
	$tab_typ_champ[0]['DEFAULT_VALUE']=$protectedPost['MODIF'];
	$tab_typ_champ[0]['INPUT_NAME']="MAC";
	$tab_typ_champ[0]['INPUT_TYPE']=3;
	$tab_name[0]=$l->g(95).": ";
	
	$tab_typ_champ[1]['DEFAULT_VALUE']=$protectedPost['COMMENT'];
	$tab_typ_champ[1]['INPUT_NAME']="COMMENT";
	$tab_typ_champ[1]['INPUT_TYPE']=0;
	$tab_typ_champ[1]['CONFIG']['SIZE']=60;
	$tab_typ_champ[1]['CONFIG']['MAXLENGTH']=255;
	$tab_name[1]=$l->g(53).": ";
	
	$sql="select distinct NAME from devicetype ";
	$res=mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
	while ($row=mysqli_fetch_object($res)){
		$list_type[$row->NAME]=$row->NAME;
	}
	$tab_typ_champ[2]['DEFAULT_VALUE']=$list_type;
	$tab_typ_champ[2]['INPUT_NAME']="TYPE";
	$tab_typ_champ[2]['INPUT_TYPE']=2;
	$tab_name[2]=$l->g(66).": ";

	$tab_hidden['mac']=$protectedPost['MODIF'];	
	if (isset($ERROR))
		msg_error($ERROR);
	tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,array(
		'title' => $title
	));	
}
else{ //affichage des périphériques
	if (!(isset($protectedPost["pcparpage"])))
		 $protectedPost["pcparpage"]=5;
	if (isset($protectedGet['value'])){
		if ($protectedGet['prov'] == "no_inv"){
			$title=$l->g(947);
			$sql="SELECT ip, mac, mask, date, name FROM netmap n
                            LEFT JOIN networks ns ON ns.macaddr=n.mac
                            WHERE n.netid='%s'
                            AND (ns.macaddr IS NULL)
                            AND mac NOT IN (SELECT DISTINCT(macaddr) FROM network_devices)";
			$tab_options['ARG_SQL']=array($protectedGet['value']);
			$list_fields= array($l->g(34) => 'ip','MAC'=>'mac',
								$l->g(208)=>'mask',
								$l->g(232)=>'date',
								$l->g(318)=>'name');
			$tab_options['FILTRE']=array_flip($list_fields);
			$tab_options['ARG_SQL_COUNT']=array($protectedGet['value']);
			$list_fields['SUP']='mac';
			$list_fields['MODIF']='mac';
			$tab_options['MODIF']['IMG']="image/prec16.png";
			$tab_options['LBL']['MODIF']=$l->g(114);
			$default_fields= $list_fields;
		}elseif($protectedGet['prov'] == "ident"){
			$title=$l->g(948);
			$sql="select n.ID,n.TYPE,n.DESCRIPTION,a.IP,a.MAC,a.MASK,a.NETID,a.NAME,a.date,n.USER
				 from network_devices n LEFT JOIN netmap a ON a.mac=n.macaddr
				 where netid='%s'";
			$tab_options['ARG_SQL']=array($protectedGet['value']);
				 $list_fields= array($l->g(66) => 'TYPE',$l->g(53)=>'DESCRIPTION',
								$l->g(34)=>'IP',
								'MAC'=>'MAC',
								$l->g(208)=>'MASK',
								$l->g(316)=>'NETID',
								$l->g(318)=>'NAME',
								$l->g(232)=>'date',
								$l->g(369)=>'USER');
				$tab_options['FILTRE']=array_flip($list_fields);
				$tab_options['ARG_SQL_COUNT']=array($protectedGet['value']);
				$list_fields['SUP']='MAC';
				$list_fields['MODIF']='ID';
				$default_fields= array($l->g(34)=>$l->g(34),$l->g(66)=>$l->g(66),$l->g(53)=>$l->g(53),
									'MAC'=>'MAC',$l->g(232)=>$l->g(232),$l->g(369)=>$l->g(369),'SUP'=>'SUP','MODIF'=>'MODIF');

		}elseif($protectedGet['prov'] == "inv" or $protectedGet['prov'] == "ipdiscover"){
			
			//BEGIN SHOW ACCOUNTINFO
			require_once('require/function_admininfo.php');
			$accountinfo_value=interprete_accountinfo($list_fields,$tab_options);
			if (array($accountinfo_value['TAB_OPTIONS']))
				$tab_options=$accountinfo_value['TAB_OPTIONS'];
			if (array($accountinfo_value['DEFAULT_VALUE']))
				$default_fields=$accountinfo_value['DEFAULT_VALUE'];				
			$list_fields=$accountinfo_value['LIST_FIELDS'];
			$tab_options['FILTRE']=array_flip($list_fields);
			//END SHOW ACCOUNTINFO
			$list_fields2 = array ( $l->g(46) => "h.lastdate", 
						   'NAME'=>'h.name',
						   $l->g(24) => "h.userid",
						   $l->g(25) => "h.osname",
						   $l->g(33) => "h.workgroup",
						   $l->g(275) => "h.osversion",
						   $l->g(34) => "h.ipaddr",
                                                   $l->g(95) => 'n.macaddr',
						   $l->g(557) => "h.userdomain");
			
			$tab_options["replace_query_arg"]['MD5_DEVICEID']=" md5(deviceid) ";
			$list_fields=array_merge ($list_fields,$list_fields2);
			$sql=prepare_sql_tab($list_fields);
			$list_fields=array_merge($list_fields,array('MD5_DEVICEID' => "MD5_DEVICEID"));
			$tab_options['ARG_SQL']=$sql['ARG'];
			if($protectedGet['prov'] == "inv"){
				$title=$l->g(1271);
				$sql=$sql['SQL'].",md5(deviceid) as MD5_DEVICEID from accountinfo a,hardware h LEFT JOIN networks n ON n.hardware_id=h.id";
				$sql.=" where ipsubnet='%s' and status='Up' and a.hardware_id=h.id ";
			}else{
				$title=$l->g(492);
				$sql=$sql['SQL']." from accountinfo a,hardware h left join devices d on d.hardware_id=h.id";
				$sql.=" where a.hardware_id=h.id and (d.ivalue=1 or d.ivalue=2) and d.name='IPDISCOVER' and d.tvalue='%s'";
			}
				
			array_push($tab_options['ARG_SQL'],$protectedGet['value']);
			$default_fields['NAME']='NAME';
			$default_fields[$l->g(34)]=$l->g(34);
			$default_fields[$l->g(24)]=$l->g(24);
			$default_fields[$l->g(25)]=$l->g(25);
			$default_fields[$l->g(275)]=$l->g(275);
			$tab_options['ARG_SQL_COUNT']=array($protectedGet['value']);
			$tab_options['FILTRE']['h.name']=$l->g(49);
			$tab_options['FILTRE']['h.userid']=$l->g(24);
			$tab_options['FILTRE']['h.osname']=$l->g(25);
			$tab_options['FILTRE']['h.ipaddr']=$l->g(34);
		}
		printEnTete($title);
		echo "<br><br>";	
		
		$tab_options['LBL']['MAC']=$l->g(95);		
		
		$list_col_cant_del=array($l->g(66)=>$l->g(66),'SUP'=>'SUP','MODIF'=>'MODIF');
		$table_name="IPDISCOVER_".$protectedGet['prov'];
		$tab_options['table_name']=$table_name;
		$form_name=$table_name;
		$tab_options['form_name']=$form_name;
		echo open_form($form_name);		
		$result_exist=ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
			$fipdisc = "ipdiscover-util.pl" ;
		$values=look_config_default_values(array('IPDISCOVER_IPD_DIR'), '', array('IPDISCOVER_IPD_DIR'=>array('TVALUE'=>VARLIB_DIR)));
		$IPD_DIR=$values['tvalue']['IPDISCOVER_IPD_DIR']."/ipd";
		if( $scriptPresent = @stat($fipdisc) ) {
			$filePresent = true;
			if( ! is_executable($fipdisc) ) {
				$msg_info=$fipdisc." ".$l->g(341);
			}
			else if( ! is_writable($IPD_DIR) ) {
				$msg_info=$l->g(342)." ".$fipdisc." (".$IPD_DIR.")";
			}	
			if (!isset($msg_info)){
				echo "<p><input type='button' onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_ipdiscover_analyse']."&head=1&rzo=".$protectedGet['value']."\",\"analyse\",\"location=0,status=0,scrollbars=1,menubar=0,resizable=0,width=800,height=650\") name='analyse' value='".$l->g(317)."'></p>";
				
			}else
				msg_info($msg_info);
			
		}
		echo close_form();
	}
}

if ($ajax){
	ob_end_clean();
	tab_req($list_fields,$default_fields,$list_col_cant_del,$sql,$tab_options);
}
?>
