<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Pierre LEMMET 2006
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================
//Modified on $Date: 2010 $$Author: Erwan Goalou
require_once('require/function_telediff.php');
require_once('require/function_computers.php');

$form_name='packlist';
//show or not stats on the table
$show_stats=true;
	
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
PrintEnTete($l->g(465));

if ($_SESSION['OCS']['RESTRICTION']['TELEDIFF_ACTIVATE'] == 'NO')
	$cant_active=false;
else
	$cant_active=true;

if ($_SESSION['OCS']['RESTRICTION']['GUI'] == 'YES'){
	$restrict_computers=computer_list_by_tag('','ARRAY');
	if ($restrict_computers == "ERROR"){
		msg_error($l->g(893));
		require_once(FOOTER_HTML);
		die();
	}
}
//only for profils who can activate packet
if (!$cant_active){	
	if($protectedPost["SUP_PROF"] != "") {
		del_pack($protectedPost["SUP_PROF"]);
		$tab_options['CACHE']='RESET';
	}
	//delete more than one packet
	if ($protectedPost['del_check'] != ''){
		 foreach (explode(",", $protectedPost['del_check']) as $key){
		 	del_pack($key);
			$tab_options['CACHE']='RESET';	 	
		 }	
	}
}

if (!$protectedPost['SHOW_SELECT']){
$protectedPost['SHOW_SELECT']='download';

}
echo "<BR>".show_modif(array('download'=>$l->g(990),'server'=>$l->g(991)),'SHOW_SELECT',2,$form_name)."<BR><BR>";

//only for profils who can activate packet
if (!$cant_active){	
	//where packets are created?
	if ($protectedPost['SHOW_SELECT'] == 'download'){
			$config_document_root="DOWNLOAD_PACK_DIR";
	}else
			$config_document_root="DOWNLOAD_REP_CREAT";
	$info_document_root=look_config_default_values($config_document_root);
	$document_root = $info_document_root["tvalue"][$config_document_root];
	//if no directory in base, take $_SERVER["DOCUMENT_ROOT"]
	if (!isset($document_root)){
			$document_root = DOCUMENT_ROOT."download";
			if ($protectedPost['SHOW_SELECT'] == "server")
				$document_root .="server/";
			
	}else{
	//can we have the zip?	
		$document_root .= "/download";
	}
	$dir = @opendir($document_root);
	if ($dir){		
		while($f = readdir($dir)){
			if (is_numeric ($f))
			$tab_options['SHOW_ONLY']['ZIP'][$f]=$f;
		}
		if (!$tab_options['SHOW_ONLY']['ZIP'])
		$tab_options['SHOW_ONLY']['ZIP']='NULL';
	}else
	$tab_options['SHOW_ONLY']['ZIP']='NULL';
}else
	$tab_options['SHOW_ONLY']['ZIP']='NULL';

//only for profils who can activate packet
if (!$cant_active){		
	//javascript for manual activate
	echo "<script language='javascript'>
			function manualActive()
			 {
				var msg = '';
				var lien = '';
				if( isNaN(document.getElementById('manualActive').value) || document.getElementById('manualActive').value=='' )
					msg = '".$l->g(473)."';
				if( document.getElementById('manualActive').value.length != 10 )
					msg = '".$l->g(474)."';
				if (msg != ''){
					document.getElementById('manualActive').style.backgroundColor = 'RED';
					alert (msg);
					return false;
				}else{
					lien='index.php?".PAG_INDEX."=".$pages_refs['ms_tele_popup_active']."&head=1&active='+ document.getElementById('manualActive').value;
	 				window.open(lien,\"active\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=350\");
						
				}	
		}
		</script>";
}

//$list_fields= array('Timestamp'=>'FILEID',
$list_fields= array($l->g(475)=>'FILEID',
							$l->g(593)=>'from_unixtime(FILEID)',
							'SHOWACTIVE'=>'NAME',
							$l->g(440)=>'PRIORITY',
							$l->g(464)=>'FRAGMENTS',
							$l->g(462)." KB"=>'round(SIZE/1024,2)',
							$l->g(25)=>'OSNAME',
							$l->g(53)=>'COMMENT');
if ($show_stats){
	$list_fields['NO_NOTIF']='NO_NOTIF';
	$list_fields['NOTI']='NOTI';
	$list_fields['SUCC']='SUCC';
	$list_fields['ERR_']='ERR_';
	$list_fields['EXIT_CODE']='EXIT_CODE';
	//can't sort on this cols
	$tab_options['NO_TRI']['NOTI']=1;
	$tab_options['NO_TRI']['NO_NOTIF']=1;
	$tab_options['NO_TRI']['SUCC']=1;
	$tab_options['NO_TRI']['ERR_']=1;	
	$tab_options['NO_TRI']['EXIT_CODE']=1;	
}
//only for profils who can activate packet
if (!$cant_active){					
	$list_fields['ZIP']='FILEID';			
	$list_fields['ACTIVE']='FILEID';
	$list_fields['SUP']='FILEID';
	$list_fields['CHECK']='FILEID';
	$tab_options['LBL_POPUP']['SUP']='NAME';
}
$list_fields['STAT']='FILEID';

$table_name="LIST_PACK";
$default_fields= array('Timestamp'=>'Timestamp',
					   $l->g(593)=>$l->g(593),
					   'SHOWACTIVE'=>'SHOWACTIVE',
					   'CHECK'=>'CHECK','NOTI'=>'NOTI','SUCC'=>'SUCC',
					   'ERR_'=>'ERR_','SUP'=>'SUP','ACTIVE'=>'ACTIVE','STAT'=>'STAT','ZIP'=>'ZIP');
/*if ($show_stats){
	$default_fields['NOTI']='NOTI';
	$default_fields['SUCC']='SUCC';
	$default_fields['ERR_']='ERR_';
}*/
$list_col_cant_del=array('SHOWACTIVE'=>'SHOWACTIVE','SUP'=>'SUP','ACTIVE'=>'ACTIVE','STAT'=>'STAT','ZIP'=>'ZIP','CHECK'=>'CHECK');
$querypack=prepare_sql_tab($list_fields,array('SELECT','ZIP','STAT','ACTIVE','SUP','CHECK','NO_NOTIF','NOTI','SUCC','ERR_','EXIT_CODE'));



$querypack['SQL'] .= " from download_available ";
if ($protectedPost['SHOW_SELECT'] == 'download')
	$querypack['SQL'] .= " where (comment not like '%s' or comment is null or comment = '')";
else
	$querypack['SQL'] .= " where comment like '%s'";
array_push($querypack['ARG'],"[PACK REDISTRIBUTION%");
$arg_count=array("[PACK REDISTRIBUTION%");
if (isset($_SESSION['OCS']['RESTRICTION']['TELEDIFF_VISIBLE']) 
		and $_SESSION['OCS']['RESTRICTION']['TELEDIFF_VISIBLE'] == "YES" ){
		$querypack['SQL'] .= " and comment not like '%s'";
	array_push($querypack['ARG'],"%[VISIBLE=0]%");	
	array_push($arg_count,"%[VISIBLE=0]%");	
}
	
$tab_options['ARG_SQL']=$querypack['ARG'];
$tab_options['ARG_SQL_COUNT']=$arg_count;
//echo $querypack;
$tab_options['LBL']=array('ZIP'=>"Archives",
							  'STAT'=>$l->g(574),
						      'ACTIVE'=>$l->g(431),
							  'SHOWACTIVE'=>$l->g(49),
							  'NO_NOTIF'=>$l->g(432),
							  'NOTI'=>$l->g(1000),
							  'SUCC'=>$l->g(572),
							  'ERR_'=>$l->g(344));
$tab_options['REQUEST']['STAT']='select distinct fileid AS FIRST from devices d,download_enable de where d.IVALUE=de.ID ';
if ($restrict_computers){	
	$tab_options['REQUEST']['STAT'].= 'and d.hardware_id in ';
	$temp=mysql2_prepare($tab_options['REQUEST']['STAT'],array(),$restrict_computers);
	$tab_options['ARG']['STAT']=$temp['ARG'];
	$tab_options['REQUEST']['STAT']=$temp['SQL'];
	unset($temp);
}
$tab_options['FIELD']['STAT']='FILEID';
$tab_options['REQUEST']['SHOWACTIVE']='select distinct fileid AS FIRST from download_enable';
$tab_options['FIELD']['SHOWACTIVE']='FILEID';
//on force le tri desc pour l'ordre des paquets
if (!$protectedPost['sens_'.$table_name])
	$protectedPost['sens_'.$table_name]='DESC';

if ($show_stats){
	$sql_data_fixe="select count(*) as %s,de.FILEID
				from devices d,download_enable de 
				where d.IVALUE=de.ID  and d.name='DOWNLOAD' 
				and d.tvalue %s '%s' ";
	$sql_data_fixe_bis="select count(*) as %s,de.FILEID
				from devices d,download_enable de 
				where d.IVALUE=de.ID  and d.name='DOWNLOAD' 
				and d.tvalue %s  ";
	
	$_SESSION['OCS']['ARG_DATA_FIXE'][$table_name]['ERR_']=array('ERR_','LIKE','ERR_%');
	$_SESSION['OCS']['ARG_DATA_FIXE'][$table_name]['SUCC']=array('SUCC','LIKE','SUCCESS%');
	$_SESSION['OCS']['ARG_DATA_FIXE'][$table_name]['NOTI']=array('NOTI','LIKE','NOTI%');
	$_SESSION['OCS']['ARG_DATA_FIXE'][$table_name]['NO_NOTIF']=array('NO_NOTIF','IS NULL');
	$_SESSION['OCS']['ARG_DATA_FIXE'][$table_name]['EXIT_CODE']=array('EXIT_CODE','LIKE','EXIT_CODE%');
	
	if ($restrict_computers){
		$sql_data_fixe.=" and d.hardware_id in ";
		$sql_data_fixe_bis.=" and d.hardware_id in ";
		$temp=mysql2_prepare($sql_data_fixe,array(),$restrict_computers);
		$temp_bis=mysql2_prepare($sql_data_fixe_bis,array(),$restrict_computers);
	}
	foreach($_SESSION['OCS']['ARG_DATA_FIXE'][$table_name] as $key=>$value){
		if ($restrict_computers){
			if ($key != 'NO_NOTIF'){
				$_SESSION['OCS']['ARG_DATA_FIXE'][$table_name][$key] = array_merge($_SESSION['OCS']['ARG_DATA_FIXE'][$table_name][$key], $temp['ARG']);
				$_SESSION['OCS']['SQL_DATA_FIXE'][$table_name][$key] = $temp['SQL']." group by FILEID";	
			}else{
				$_SESSION['OCS']['ARG_DATA_FIXE'][$table_name][$key] = array_merge($_SESSION['OCS']['ARG_DATA_FIXE'][$table_name][$key], $temp_bis['ARG']);
				$_SESSION['OCS']['SQL_DATA_FIXE'][$table_name][$key] = $temp_bis['SQL']." group by FILEID";				
			}
		}else{
			if ($key != 'NO_NOTIF')
				$_SESSION['OCS']['SQL_DATA_FIXE'][$table_name][$key]=$sql_data_fixe." group by FILEID";	
			else
				$_SESSION['OCS']['SQL_DATA_FIXE'][$table_name][$key]=$sql_data_fixe_bis." group by FILEID";	
		}
	}
}
			
$tab_options['COLOR']['ERR_']='RED';	
$tab_options['COLOR']['SUCC']='GREEN';	
$tab_options['COLOR']['NOTI']='GREY';	
$tab_options['COLOR']['NO_NOTIF']='BLACK';	
$tab_options['COLOR']['EXIT_CODE']='BLUE';
$tab_options['FILTRE']=array('FILEID'=>'Timestamp','NAME'=>$l->g(49));
$tab_options['TYPE']['ZIP']=$protectedPost['SHOW_SELECT'];
$tab_options['FIELD_REPLACE_VALUE_ALL_TIME']='FILEID';
$result_exist=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$querypack['SQL'],$form_name,95,$tab_options); 
//only for profils who can activate packet
if (!$cant_active){		
	del_selection($form_name);
	if ($protectedPost['SHOW_SELECT'] == 'download'){
		$config_input=array('MAXLENGTH'=>10,'SIZE'=>15);
		$activ_manuel=show_modif($protectedPost['manualActive'],'manualActive',0,'',$config_input);
		echo "<b>".$l->g(476)."</b>&nbsp;&nbsp;&nbsp;".$l->g(475).": ".$activ_manuel."";
		echo "<a href='#' OnClick='manualActive();'><img src='image/activer.png'></a>";
	}
}
echo "</form>";




?>
