<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Pierre LEMMET 2006
// Web: http://ocsinventory.sourceforge.net
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================
//Modified on $Date: 2008-02-27 12:34:12 $$Author: hunal $($Revision: 1.8 $)


debut_tab(array('CELLSPACING'=>'5',
					'WIDTH'=>'80%',
					'BORDER'=>'0',
					'ALIGN'=>'Center',
					'CELLPADDING'=>'0',
					'BGCOLOR'=>'#C7D9F5',
					'BORDERCOLOR'=>'#9894B5'));
$mode = 0;

if( $optvalueTvalue['IPDISCOVER'] && $optvalue['IPDISCOVER']==1 ) {
	$select_value=$optvalueTvalue['IPDISCOVER'];
	echo "<br><center><b>".$l->g(519).": ".$optvalueTvalue['IPDISCOVER']."</b></center>";
	$mode = 1;
}
else if( $optvalue['IPDISCOVER']==2 ) {
	$select_value=$optvalueTvalue['IPDISCOVER'];
	echo "<br><center><b>".$l->g(520).": ".$optvalueTvalue['IPDISCOVER']."</b></center>";
	$mode = 3;
}
else if( $optvalue['IPDISCOVER']==="0" ) {
	$select_value="OFF";
	echo "<br><center><b>".$l->g(521)."</b></center>";
	$mode = 2;	
}
elseif(isset($protectedGet['idchecked'])) {
	echo "<br><center><b>".$l->g(522)."</b></center>";		
}
elseif(!isset($protectedGet['idchecked'])){
	$mode = 2;	
}
if (isset($protectedGet['idchecked']) and is_numeric($protectedGet['idchecked'])){
	$sql="SELECT ipaddress FROM networks WHERE hardware_id=%s";
	$arg=$protectedGet['idchecked'];
	$resInt = mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$arg );
	while( $valInt = mysql_fetch_array( $resInt )){
		$sql="SELECT ipsubnet FROM networks WHERE ipaddress='%s' AND hardware_id=%s";
		$arg=array($valInt["ipaddress"],$protectedGet["idchecked"]);
		$res=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$arg );
		while( $val = mysql_fetch_array( $res ))
		$lesRez[$val["ipsubnet"]] = $val["ipsubnet"];
	}
}
	if( $mode==3 || $mode==2) 
	$lesRez['des']=$l->g(523);
	$lesRez['OFF']=$l->g(524);
ligne('IPDISCOVER',$l->g(518),'select',array('VALUE'=>$values['tvalue']['OCS_FILES_FORMAT'],'SELECT_VALUE'=>$lesRez,'VALUE'=>$select_value));
fin_tab($form_name);

?>

