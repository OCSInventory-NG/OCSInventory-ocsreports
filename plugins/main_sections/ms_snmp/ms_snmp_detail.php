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




require('require/function_opt_param.php');
require('require/function_graphic.php');
require_once('require/function_files.php');
require_once('require/function_snmp.php');
$form_name='SNMP_DETAILS';
//recherche des infos de la machine
$item=info_snmp($protectedGet['id']);
if (!is_array($item['data'])){
	msg_error($item);
	require_once(FOOTER_HTML);
	die();
}

$systemid=$item['data']['snmp']->ID;
// SNMP SUMMARY
$lbl_affich=array('NAME'=>$l->g(49),'UPTIME'=>$l->g(352),'MACADDR'=>$l->g(95),'IPADDR'=>$l->g(34),
					'CONTACT'=>$l->g(1227),'LOCATION'=>$l->g(295),'DOMAIN'=>$l->g(33),'TYPE'=>$l->g(66),
					'SNMPDEVICEID'=>$l->g(1297),'SERIALNUMBER'=>$l->g(36),'COUNTER'=>$l->g(55),
					'DESCRIPTION'=>$l->g(53),'LASTDATE'=>$l->g(46)
					);
$info['snmp']=$item['data']['snmp'];
					
$first_tab=bandeau($info,$lbl_affich);
unset($item['data']['snmp']);
$second_tab=bandeau($item['data'],$lbl_affich,$item['lbl'],'mvt_bordure');

if ($first_tab)
echo $first_tab;

if ($second_tab)
echo $second_tab;


//get plugins when exist
$Directory=PLUGINS_DIR."snmp_detail/";
$ms_cfg_file= $Directory."snmp_config.txt";
if (!isset($_SESSION['OCS']['DETAIL_SNMP'])){
	if (file_exists($ms_cfg_file)) {
		$search=array('ORDER'=>'MULTI2','LBL'=>'MULTI','ISAVAIL'=>'MULTI');
		$plugins_data=read_configuration($ms_cfg_file,$search);
		$_SESSION['OCS']['DETAIL_SNMP']['LIST_PLUGINS']=$plugins_data['ORDER'];
		$_SESSION['OCS']['DETAIL_SNMP']['LIST_LBL']=$plugins_data['LBL'];
		$_SESSION['OCS']['DETAIL_SNMP']['LIST_AVAIL']=$plugins_data['ISAVAIL'];
	}
}

$list_plugins=$_SESSION['OCS']['DETAIL_SNMP']['LIST_PLUGINS'];
$list_lbl=$_SESSION['OCS']['DETAIL_SNMP']['LIST_LBL'];
$list_avail=$_SESSION['OCS']['DETAIL_SNMP']['LIST_AVAIL'];


foreach ($list_avail as $key=>$value){
	$sql="select count(*) c from %s where SNMP_ID=%s";
	$arg=array($value,$systemid);
	$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
	$valavail = mysqli_fetch_array($result);
	if ($valavail['c'] == 0)
		unset($list_lbl[$key]);	
}
foreach ($list_lbl as $key=>$value){
	if (substr($value,0,2) == 'g('){
		unset($list_lbl[$key]);
		$list_lbl[$key]=$l->g(substr(substr($value,2),0,-1));
		
	}
	
	
}
//par défaut, on affiche les données admininfo
/*if (!isset($protectedGet['option'])){
	$protectedGet['option']="cd_admininfo";
}*/
echo "<br>";
echo open_form($form_name);
onglet($list_lbl,$form_name,"onglet_sd",10);
$msq_tab_error='<small>N/A</small>';
echo '<div class="mlt_bordure" >';
if (isset($list_lbl[$protectedPost['onglet_sd']])){
	
	if (file_exists($Directory."/".$protectedPost['onglet_sd']."/".$protectedPost['onglet_sd'].".php")){
	//	$protectedPost['computersectionrequest']=$protectedPost['onglet_sd'];
		include ($Directory."/".$protectedPost['onglet_sd']."/".$protectedPost['onglet_sd'].".php");
	}
}
echo "</div>";
echo close_form();



if ($ajax){
	ob_end_clean();
}
/*$i=0;
echo "<br><br><table width='90%' border=0 align='center'><tr align=center>";
$nb_col=array(10,13,13);
$j=0;
$index_tab=0;
//intitialisation du tableau de plugins
$show_all=array();
while ($list_plugins[$i]){
	unset($valavail);
	//vérification de l'existance des données
	if (isset($list_avail[$list_plugins[$i]])){
		$sql_avail="select count(*) from ".$list_avail[$list_plugins[$i]]." where SNMP_ID=".$systemid;
		$resavail = mysqli_query( $sql_avail, $_SESSION['OCS']["readServer"]) or die(mysqli_error($_SESSION['OCS']["readServer"]));
		$valavail = mysqli_fetch_array($resavail);
	}
	if ($j == $nb_col[$index_tab]){
		echo "</tr></table><table width='90%' border=0 align='center'><tr align=center>";
		$index_tab++;
		$j=0;
	}
	//echo substr(substr($list_lbl[$list_plugins[$i]],2),0,-1);
	echo "<td align=center>";
	if (!isset($valavail[0]) or $valavail[0] != 0){
		//liste de toutes les infos de la machine
		$show_all[]=$list_plugins[$i];
		$href = "<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&systemid=".$systemid."&option=".$list_plugins[$i]."'>";
		$fhref = "</a>";
	}else{
		$href = "";
		$fhref = "";
	}
	echo $href."<img title=\"";
	if (substr($list_lbl[$list_plugins[$i]],0,2) == 'g(')
	echo $l->g(substr(substr($list_lbl[$list_plugins[$i]],2),0,-1));
	else
	echo $list_lbl[$i];
	echo "\" src='plugins/computer_detail/img/";
	$list_plugins[$i];
	if (isset($valavail[0]) and $valavail[0] == 0){
		if (file_exists($Directory."/img/".$list_plugins[$i]."_d.png"))
			echo $list_plugins[$i]."_d.png";
		else
			echo "cd_default_d.png";
	}
	elseif ($protectedGet['option'] == $list_plugins[$i]){
		if (file_exists($Directory."/img/".$list_plugins[$i]."_a.png"))
			echo $list_plugins[$i]."_a.png";
		else
			echo "cd_default_a.png";		
	}
	else{
		if (file_exists($Directory."/img/".$list_plugins[$i].".png"))
			echo $list_plugins[$i].".png";
		else
			echo "cd_default.png";
		
	}
	echo "'/>".$fhref."</td>";
	$j++;
 	$i++;	
}
echo "</tr></table><br><br>";*/
/*if ($protectedGet['tout'] == 1){
	$list_plugins_4_all=0;
	while (isset($show_all[$list_plugins_4_all])){
		include ($Directory."/".$show_all[$list_plugins_4_all]."/".$show_all[$list_plugins_4_all].".php");	
		$list_plugins_4_all++;
	}
	
}else{
	if (file_exists($Directory."/".$protectedGet['option']."/".$protectedGet['option'].".php"))
		include ($Directory."/".$protectedGet['option']."/".$protectedGet['option'].".php");
}
*/
/*echo "<br><table align='center'> <tr><td width =50%>";
echo "<a style=\"text-decoration:underline\" onClick=print()><img src='image/print.png' title='".$l->g(214)."'></a></td>";


//if(!isset($protectedGet["tout"]))
		echo"<td width=50%>
			<a style=\"text-decoration:underline\" href='index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&systemid=".urlencode(stripslashes($systemid))."&tout=1\'>
			<img width='60px' src='image/aff_all.png' title='".$l->g(215)."'></a></td>";
		
echo "</tr></table>";*/


?>
