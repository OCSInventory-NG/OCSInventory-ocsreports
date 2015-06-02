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

if ($_SESSION['OCS']['RESTRICTION']['TELEDIFF_ACTIVATE'] == 'NO')
	$cant_active=false;
else
	$cant_active=true;
	
if (!$cant_active){
	if ($protectedPost['DEL_ALL'] != ''){
		$sql_listIDdel="select distinct ID from download_enable where FILEID=%s";
		$arg_listIDdel=$protectedPost['DEL_ALL'];
		$res_listIDdel = mysql2_query_secure( $sql_listIDdel, $_SESSION['OCS']["readServer"], $arg_listIDdel);
		while( $val_listIDdel = mysql_fetch_array( $res_listIDdel ) ) {
				$listIDdel[]=$val_listIDdel['ID'];
		}	
		if ($listIDdel != ''){
			$reqSupp = "DELETE FROM devices WHERE name='DOWNLOAD' AND ivalue in ";
			$sql=mysql2_prepare($reqSupp,'',$listIDdel);
			mysql2_query_secure($sql['SQL'], $_SESSION['OCS']["writeServer"], $sql['ARG']);	
		}
		mysql2_query_secure("DELETE FROM download_enable WHERE FILEID=%s", $_SESSION['OCS']["writeServer"],$protectedPost['DEL_ALL']);		
		echo "<script>window.opener.document.packlist.submit(); self.close();</script>";	
	}
	if ($protectedPost['SUP_PROF'] != ''){
		$reqSupp = "DELETE FROM devices WHERE name='DOWNLOAD' AND ivalue = %s";
		
		mysql2_query_secure($reqSupp, $_SESSION['OCS']["writeServer"],$protectedPost['SUP_PROF']);	
			
		mysql2_query_secure("DELETE FROM download_enable WHERE ID=%s", $_SESSION['OCS']["writeServer"],$protectedPost['SUP_PROF']);		
	}
}
$sql_details="select distinct priority,fragments,size from download_available where fileid=%s";
$res_details = mysql2_query_secure( $sql_details, $_SESSION['OCS']["readServer"],$protectedGet['timestamp'] );
$val_details = mysql_fetch_array( $res_details ) ;
$tps="<br>".$l->g(992)." : <b><font color=red>".tps_estimated($val_details)."</font></b>";
PrintEnTete( $l->g(481).$tps);	
echo "<br>";
$form_name="tele_actives";
//ouverture du formulaire	
echo open_form($form_name);
$list_fields= array($l->g(460)=>'e.ID',
							'Timestamp'=>'e.FILEID',
							$l->g(470)=>'e.INFO_LOC',
							$l->g(471)=>'e.PACK_LOC',
							$l->g(49)=>'a.NAME',
							$l->g(440)=>'a.PRIORITY',
							$l->g(480)=>'a.FRAGMENTS',
							$l->g(462)=>'a.SIZE',
							$l->g(25)=>'a.OSNAME');
if (!$cant_active){
	$list_fields['SUP']='e.ID';
}	
$table_name="LIST_ACTIVES";
$default_fields= $list_fields;
$list_col_cant_del=array($l->g(460)=>$l->g(460),'SUP'=>'SUP');
$querypack = 'SELECT distinct ';
foreach ($list_fields as $key=>$value){
		if( $key != 'SUP')
		$querypack .= $value.',';		
} 
$querypack=substr($querypack,0,-1);
$querypack .= " from download_enable e RIGHT JOIN download_available a ON a.fileid = e.fileid
				where e.FILEID=".$protectedGet['timestamp'];
$result_exist=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$querypack,$form_name,95,$tab_options); 
if ($result_exist != "" and !$cant_active)
echo "<a href=# OnClick='confirme(\"\",\"".$protectedGet['timestamp']."\",\"".$form_name."\",\"DEL_ALL\",\"".$l->g(900)."\");'><img src='image/sup_search.png' title='Supprimer' ></a>";
echo "<input type='hidden' id='DEL_ALL' name='DEL_ALL' value=''>";
echo close_form();
echo "<center>".$l->g(552)."</center>";
?>