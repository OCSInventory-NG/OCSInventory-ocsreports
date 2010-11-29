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
$lesRez['des']=$l->g(523);
	$lesRez['OFF']=$l->g(524);
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
//	if( $mode==3 || $mode==2) 
	
ligne('IPDISCOVER',$l->g(518),'select',array('VALUE'=>$values['tvalue']['OCS_FILES_FORMAT'],'SELECT_VALUE'=>$lesRez,'VALUE'=>$select_value));

if(!isset($optvalue['SNMP_SWITCH']))
$optvalueselected='SERVER DEFAULT';
elseif($optvalue['SNMP_SWITCH'] == 0)
$optvalueselected='OFF';
elseif($optvalue['SNMP_SWITCH'] == 1)
$optvalueselected='ON';
$champ_value['VALUE']=$optvalueselected;
$champ_value['ON']='ON';
$champ_value['OFF']='OFF';
$champ_value['SERVER DEFAULT']=$l->g(488);
if (!isset($protectedGet['origine'])){	
	$champ_value['IGNORED']=$l->g(718);
	$champ_value['VALUE']='IGNORED';
}
ligne("SNMP_SWITCH",$l->g(1197),'radio',$champ_value);
unset($champ_value);

fin_tab($form_name);

?>

