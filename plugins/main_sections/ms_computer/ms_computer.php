<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Pierre LEMMET 2005
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================
//Modified on $Date: 2010 $$Author: Erwan Goalou
@session_start();

require('require/function_opt_param.php');
require('require/function_graphic.php');
require_once('require/function_machine.php');
require_once('require/function_files.php');
//recherche des infos de la machine
$item=info($protectedGet,$protectedPost['systemid']);
if (!is_object($item)){
	msg_error($item);
	require_once(FOOTER_HTML);
	die();
}
//you can't view groups'detail by this way
if ( $item->DEVICEID == "_DOWNLOADGROUP_"
	or $item->DEVICEID == "_SYSTEMGROUP_"){
	die('FORBIDDEN');	
}
$systemid=$item -> ID;

// COMPUTER SUMMARY
$lbl_affich=array('NAME'=>$l->g(49),'WORKGROUP'=>$l->g(33),'USERDOMAIN'=>$l->g(557),'IPADDR'=>$l->g(34),
					'USERID'=>$l->g(24),'SWAP'=>$l->g(50),'OSNAME'=>$l->g(274),'OSVERSION'=>$l->g(275),
					'OSCOMMENTS'=>$l->g(286),'WINCOMPANY'=>$l->g(51),'WINOWNER'=>$l->g(348),
					'WINPRODID'=>$l->g(111),'WINPRODKEY'=>$l->g(553),'USERAGENT'=>$l->g(357),
					'MEMORY'=>$l->g(26),'LASTDATE'=>$l->g(46),'LASTCOME'=>$l->g(820),'DESCRIPTION'=>$l->g(53),
					'NAME_RZ'=>$l->g(304),'VMTYPE'=>$l->g(1267),'UUID'=>$l->g(1268),'ARCH'=>$l->g(1247));			
$values=look_config_default_values(array('EXPORT_OCS'));
if(!isset($_SESSION['OCS']['RESTRICTION']['EXPORT_XML']) or $_SESSION['OCS']['RESTRICTION']['EXPORT_XML'] == "NO")	
	$lbl_affich['EXPORT_OCS']=$l->g(1303);
foreach ($lbl_affich as $key=>$lbl){
	if ($key == "MEMORY"){
		$sqlMem = "SELECT SUM(capacity) AS 'capa' FROM memories WHERE hardware_id=%s";
		$argMem=$systemid;
		$resMem = mysql2_query_secure($sqlMem,$_SESSION['OCS']["readServer"],$argMem);		
		$valMem = mysql_fetch_array( $resMem );
		if( $valMem["capa"] > 0 )
			$memory = $valMem["capa"];
		else
			$memory = $item->$key;
		$data[$key]=$memory;
	}elseif ($key == "LASTDATE" or $key == "LASTCOME"){
		$data[$key]=dateTimeFromMysql($item->$key);
	}elseif ($key == "NAME_RZ"){
		$data[$key]="";
		$data_RZ=subnet_name($systemid);
		$nb_val=count($data_RZ);
		if ($nb_val == 1){
			$data[$key]=$data_RZ[0];
		}elseif(isset($data_RZ)){	
			foreach($data_RZ as $index=>$value){
				$data[$key].=$index." => ".$value."<br>";			
			}	
		}	
	}elseif($key == "VMTYPE" and $item->UUID != ''){
		$sqlVM = "select vm.hardware_id,vm.vmtype, h.name from virtualmachines vm left join hardware h on vm.hardware_id=h.id where vm.uuid='%s'";
		$argVM = $item->UUID;
		$resVM = mysql2_query_secure($sqlVM,$_SESSION['OCS']["readServer"],$argVM);		
		$valVM = mysql_fetch_array( $resVM );
		$data[$key]=$valVM['vmtype'];
		$link_vm="<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&systemid=".$valVM['hardware_id']."'  target='_blank'><font color=red>".$valVM['name']."</font></a>";
		$link[$key]=true;
		if ($data[$key] != '')
			msg_info($l->g(1266)."<br>".$l->g(1269).': '.$link_vm);
	}elseif($key == "EXPORT_OCS"){
		$data[$key] = "<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_export_ocs']."&no_header=1&systemid=".$protectedGet['systemid']."\")>".$l->g(1304)."</a>";			
		$link[$key]=true;
	}elseif($key == "IPADDR" 
			and (!isset($_SESSION['OCS']['RESTRICTION']['WOL']) or $_SESSION['OCS']['RESTRICTION']['WOL']=="NO")){
		$data[$key] = $item->$key." <a href=# OnClick='confirme(\"\",\"WOL\",\"bandeau\",\"WOL\",\"".$l->g(1283)."\");'><i>WOL</i></a>";
		$link[$key]=true;
	}elseif ($item->$key != '')
		$data[$key]=$item->$key;
}
echo open_form("bandeau");
//Wake On Lan function
if (isset($protectedPost["WOL"]) and $protectedPost["WOL"] == 'WOL'
	and (!isset($_SESSION['OCS']['RESTRICTION']['WOL']) or $_SESSION['OCS']['RESTRICTION']['WOL']=="NO")){
		require_once('require/function_wol.php');
		$wol = new Wol();
		$sql="select MACADDR,IPADDRESS from networks WHERE (hardware_id=%s) and status='Up'";
		$arg=array($systemid);
		$resultDetails = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
		$msg="";
		while ($item = mysql_fetch_object($resultDetails)){
			$wol->wake($item->MACADDR,$item->IPADDRESS);
			if ($wol->wol_send == $l->g(1282))
				msg_info($wol->wol_send."=>".$item->MACADDR."/".$item->IPADDRESS);		
			else
				msg_error($wol->wol_send."=>".$item->MACADDR."/".$item->IPADDRESS);
		}	
		
}
$bandeau=bandeau($data,$lbl_affich,$link);
echo "<input type='hidden' id='WOL' name='WOL' value=''>";
	echo close_form();
$Directory=PLUGINS_DIR."computer_detail/";
$ms_cfg_file= $Directory."cd_config.txt";
if (!isset($_SESSION['OCS']['DETAIL_COMPUTER'])){
	//get plugins when exist	
	if (file_exists($ms_cfg_file)) {
		$search=array('ORDER'=>'MULTI2','LBL'=>'MULTI','ISAVAIL'=>'MULTI');
		$plugins_data=read_configuration($ms_cfg_file,$search);
		$_SESSION['OCS']['DETAIL_COMPUTER']['LIST_PLUGINS']=$plugins_data['ORDER'];
		$_SESSION['OCS']['DETAIL_COMPUTER']['LIST_LBL']=$plugins_data['LBL'];
		$_SESSION['OCS']['DETAIL_COMPUTER']['LIST_AVAIL']=$plugins_data['ISAVAIL'];
	}
}
$list_plugins=$_SESSION['OCS']['DETAIL_COMPUTER']['LIST_PLUGINS'];
$list_lbl=$_SESSION['OCS']['DETAIL_COMPUTER']['LIST_LBL'];
$list_avail=$_SESSION['OCS']['DETAIL_COMPUTER']['LIST_AVAIL'];

//par d�faut, on affiche les donn�es admininfo
if (!isset($protectedGet['option'])){
	$protectedGet['option']="cd_admininfo";
}
$i=0;
echo "<br><br><table width='90%' border=0 align='center'><tr align=center>";
$nb_col=array(10,20,13);
$j=0;
$index_tab=0;
//intitialisation du tableau de plugins
$show_all=array();
while ($list_plugins[$i]){
	unset($valavail);
	//v�rification de l'existance des donn�es
	if (isset($list_avail[$list_plugins[$i]])){
		if (stripos($list_avail[$list_plugins[$i]], 'select') !== false)
			$sql_avail=$list_avail[$list_plugins[$i]];
		else
			$sql_avail="select count(*) from ".$list_avail[$list_plugins[$i]] . " where 1=1 ";
		$sql_avail .= " and hardware_id=".$systemid;
		$resavail = mysql_query( $sql_avail, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
		$valavail = mysql_fetch_array($resavail);
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
		$llink="index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&systemid=".$systemid."&option=".$list_plugins[$i];
		$href = "<a onclick='clic(\"".$llink."\",1);'>";
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
echo "</tr></table><br><br>";
if ($protectedGet['all'] == 1){
	$protectedPost["pcparpage"]=1000000;
	$protectedPost['SHOW'] = 'NEVER_SHOW';
	$show_all_column=true;
	$tab_options['SAVE_CACHE']=true;
	$list_plugins_4_all=0;
	while (isset($show_all[$list_plugins_4_all])){
		include ($Directory."/".$show_all[$list_plugins_4_all]."/".$show_all[$list_plugins_4_all].".php");	
		$list_plugins_4_all++;
	}
	
}else{
	if (file_exists($Directory."/".$protectedGet['option']."/".$protectedGet['option'].".php"))
		include ($Directory."/".$protectedGet['option']."/".$protectedGet['option'].".php");
}

//echo "<br><table align='center'> <tr><td width =50%>";
//echo "<a style=\"text-decoration:underline\" onClick=print()><img src='image/print.png' title='".$l->g(214)."'></a></td>";


if(!isset($protectedGet["all"]))
		echo"<td width=50%>
			<a style=\"text-decoration:underline\" href='index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&systemid=".urlencode(stripslashes($systemid))."&all=1\'>
			<img width='60px' src='image/aff_all.png' title='".$l->g(215)."'></a></td>";
		
//echo "</tr></table>";


?>
