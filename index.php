<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Pierre LEMMET 2005
// Web: http://ocsinventory.sourceforge.net
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================
//Modified on $Date: 2007/02/08 16:05:52 $$Author: plemmet $($Revision: 1.13 $)

error_reporting(E_ALL & ~E_NOTICE);
require("fichierConf.class.php");
@session_start();
require ('header.php');
require ('donnees.php');
require_once ('require/function_index.php');

$sleep=1;
$debut = getmicrotime();


//getting existing plugins by using tags in config.txt file
$Directory=$_SESSION['plugins_dir']."main_sections/";

switch( $_SESSION["lvluser"]) {		//Select config file depending on user profile
	case	SADMIN: $ms_cfg_file="sadmin_config.txt" ; break;
	case	ADMIN: $ms_cfg_file="admin_config.txt" ; break;
	case	LADMIN: $ms_cfg_file="ladmin_config.txt" ; break;
}

if (file_exists($Directory.$ms_cfg_file)) {
      $fd = fopen ($Directory.$ms_cfg_file, "r");
      $capture='';
      while( !feof($fd) ) {

         $line = trim( fgets( $fd, 256 ) );

			if (substr($line,0,2) == "</")
            $capture='';

         if ($capture == 'OK_ORDER')
            $list_plugins[]=$line;
         
			

			if ($capture == 'OK_LBL'){
            $tab_lbl=explode(":", $line);
            $list_lbl[$tab_lbl[0]]=$tab_lbl[1];
         }
         
			if ($capture == 'OK_ISAVAIL'){
            $tab_isavail=explode(":", $line);
            $list_avail[$tab_isavail[0]]=$tab_isavail[1];
         }
         
         if ($line{0} == "<"){ 	//Getting tag type for the next launch of the loop
            $capture = 'OK_'.substr(substr($line,1),0,-1);
         }
         
			flush();
      }
   fclose( $fd );
}



//Initiating icons
if( !isset($protectedGet["popup"] )) {
	//si la variable RESET existe
	//c'est que l'on a clique sur un icone d'un menu 
	if (isset($protectedPost['RESET'])){
		if ($_SESSION['DEBUG'] == 'ON')
			echo "<br><b><font color=red>".$l->g(5003)."</font></b><br>";
		unset($_SESSION['DATA_CACHE']);	
	}
	//formulaire pour detecter le clic sur un bouton du menu
	//permet de donner la fonctionnalite
	//de reset du cache des tableaux
	//si on reclic sur le meme icone
	echo "<form action='' name='ACTION_CLIC' id='ACTION_CLIC' method='POST'>";
	echo "<input type='hidden' name='RESET' id='RESET' value=''>";
	echo "</form>";
	
	
	echo "<table width='100%' border=0><tr><td>
	<table BORDER='0' ALIGN = 'left' CELLPADDING='0' BGCOLOR='#FFFFFF' BORDERCOLOR='#9894B5'><tr>";
	

//Using plugins sytem to show icons
$i=0;

while ($list_plugins[$i]){

	if (isset($list_avail[$list_plugins[$i]])) {
		echo $icons_list[$list_plugins[$i]];
	}
	
	if ($list_plugins[$i] == "-- TABLE_END --" ) {
		//echo "</tr></table></td><td><table BORDER='0' ALIGN = 'right' CELLPADDING='0' BGCOLOR='#FFFFFF' BORDERCOLOR='#9894B5'><tr height=20px bgcolor='white'>";
		echo "</tr></table></td><td>";	
		echo "<table BORDER='0' ALIGN = 'right' CELLPADDING='0' BGCOLOR='#FFFFFF' BORDERCOLOR='#9894B5'>";
		echo "<tr height=20px  bgcolor='white'>";
		flush();
		$end_table = 1 ;  //variable to say if the end of the table is set or not
	}

	if (isset($list_avail[$list_plugins[$i]]) && $list_plugins[$i]=="ms_teledeploy") {
			//Special code for teledeploy
 			$name_menu="teledeploy_smenu";
			$packAct = array($pages_refs['ms_tele_package'],$pages_refs['ms_tele_activate'],$pages_refs['ms_rules_redistrib']);
			$nam_img="pack";
			$title=$l->g(512);
			$data_list_deploy[$pages_refs['ms_tele_package']]=$l->g(513);
			$data_list_deploy[$pages_refs['ms_tele_activate']]=$l->g(514);
			$data_list_deploy[$pages_refs['ms_rules_redistrib']]=$l->g(662);
			menu_list($name_menu,$packAct,$nam_img,$title,$data_list_deploy);
	}

	if (isset($list_avail[$list_plugins[$i]]) && $list_plugins[$i]=="ms_config") {
			//Special code for config 
			$name_menu="config_smenu";
			$packAct = array($pages_refs['ms_config'],$pages_refs['blacklist']);
			$nam_img="configuration";
			$title=$l->g(107);
			$data_list_config[$pages_refs['ms_config']]=$l->g(107);
			$data_list_config[$pages_refs['ms_blacklist']]=$l->g(703);
			//$data_list_config[35]=$l->g(712);
			menu_list($name_menu,$packAct,$nam_img,$title,$data_list_config);	
	}

	$i++;
}	

if (!isset($end_table)) { // echo the end of table if not set in the plugin config file
	echo "</tr></table></td><td><table BORDER='0' ALIGN = 'right' CELLPADDING='0' BGCOLOR='#FFFFFF' BORDERCOLOR='#9894B5'><tr height=20px bgcolor='white'>";
	flush();
}



	//groups
	$sql_groups_4all="select workgroup from hardware where workgroup='GROUP_4_ALL' and deviceid='_SYSTEMGROUP_'";
	$res = mysql_query($sql_groups_4all, $_SESSION["readServer"]) or die(mysql_error($_SESSION["readServer"]));
	$item = mysql_fetch_object($res);
	if (isset($item->workgroup) or $_SESSION["lvluser"]==SADMIN or $_SESSION["lvluser"]==LADMIN)	
	
	//echo "</tr></table></td><td>";	
	//echo "<table BORDER='0' ALIGN = 'right' CELLPADDING='0' BGCOLOR='#FFFFFF' BORDERCOLOR='#9894B5'>";
	//echo "<tr height=20px  bgcolor='white'>";
	
		//Icon for user profile	
		//echo $icons_list['ipdiscover'];
		//echo $icons_list['doubles'];
		//echo $icons_list['help'];
	
	?>

	<script language='javascript'>montre();</script>

	<?php	

	echo "</tr></table>";
	echo "</td></tr></table>";
	flush();
}

echo "<br><center><span id='wait' class='warn'><font color=red>".$l->g(332)."</font></span></center><br>";
	flush();

	if( ! isset( $_SESSION["mac"] ) ) {
		loadMac();
	}
	
	if( $protectedGet[PAG_INDEX] != $pages_refs['ipdiscover'] )
		unset( $_SESSION["forcedRequest"] );

	//GROUP CREATION
	if( $_SESSION["lvluser"] == SADMIN ) {
		// New classic group
		if( ! empty( $protectedPost["cg"] ) ) {
			if( createGroup( $protectedPost["cg"], $protectedPost["desc"] ) ) {
				unset( $protectedPost );
			}
		}
		//New static group, with checked computers in cache
		else if( ! empty( $protectedPost["cgs"] ) ) {
			if( createGroup( $protectedPost["cgs"], $protectedPost["desc"], true ) ) {
				$mess=addComputersToGroup( $protectedPost["cgs"], $protectedPost );
				echo "<div align=center><font color=green><big><B>".$mess." ".$l->g(819)."</B></big></font></div>";
				unset( $protectedPost );
			}
		}
		// Overwrite a classic group
		else if( isset( $protectedPost["eg"] ) && $protectedPost["eg"] != "_nothing_" ) {
			createGroup( $protectedPost["eg"], $protectedPost["desc"], false, true );
			unset( $protectedPost );
		}
	}
		// Add checked computers to existing group
	 if( isset( $protectedPost["asg"] ) && $protectedPost["asg"] != "_nothing_" ) {
			$mess=addComputersToGroup( $protectedPost["asg"], $protectedPost );
			echo "<div align=center><font color=green><big><B>".$mess." ".$l->g(819)."</B></big></font></div>";
			unset( $protectedPost );
		
	}

	switch($protectedGet[PAG_INDEX]) {
 		case $pages_refs['ms_ipdiscover']: require ("$Directory/ms_ipdiscover/ms_ipdiscover_new.php");	break;
 		case $pages_refs['ms_config']: require ("$Directory/ms_config/config.php");	break;
 		case $pages_refs['ms_regconfig']: require ("$Directory/ms_regconfig/ms_regconfig.php");	break;
 		case $pages_refs['ms_doubles']: require ("$Directory/ms_doubles/ms_doubles.php");	break;
 		case $pages_refs['ms_upload_file']: require ("$Directory/ms_uploadfile/ms_uploadfile.php");	break;
 		case $pages_refs['ms_admininfo']: require ("$Directory/ms_admininfo/ms_admininfo.php");	break;
 		case $pages_refs['ms_label']: require ("$Directory/ms_label/ms_label.php");	break;
		case $pages_refs['ms_local']: require ("$Directory/ms_local/ms_local.php");	break;
		case $pages_refs['ms_dict']: require ("$Directory/ms_dict/ms_dict.php");	break;
		case $pages_refs['ms_tele_package']: require ("$Directory/ms_teledeploy/ms_tele_package.php"); break; 
		case $pages_refs['ms_tele_activate']: require ("$Directory/ms_teledeploy/ms_tele_activate.php"); break; 
		case $pages_refs['ms_opt_param']: require ("$Directory/ms_config/ms_custom_param.php"); break; 
		case $pages_refs['ms_opt_ipdiscover']: require ("$Directory/ms_config/ms_custom_ipdiscover.php"); break; 
		case $pages_refs['ms_tele_stats']: require ("$Directory/ms_teledeploy/ms_tele_stats.php"); break;
		case $pages_refs['ms_tele_actives']: require ("$Directory/ms_teledeploy/ms_tele_actives.php"); break;
		case $pages_refs['ms_group_show']: require ("$Directory/ms_groups/ms_group_show.php"); break;
		case $pages_refs['ms_tele_massaffect']: require ("$Directory/ms_teledeploy/tele_massaffect.php"); break; 
		case $pages_refs['ms_admin_attrib']: require ('admin_attrib.php'); break; //don't seems to be used anymore 
		case $pages_refs['ms_blacklist']: require ("$Directory/ms_config/ms_blacklist.php");break;
		case $pages_refs['ms_rules_redistrib']: require ("$Directory/ms_teledeploy/ms_rules_redistrib.php");break;
		case $pages_refs['ms_all_soft']: require ("$Directory/ms_all_soft/ms_all_soft.php");break;
		case $pages_refs['ms_groups']: require ("$Directory/ms_groups/ms_groups.php");break;
		case $pages_refs['ms_show_detail']: require ('show_detail.php');break; //don't seels to be sused anymore
		case $pages_refs['ms_logs']: require ("$Directory/ms_logs/ms_logs.php");break;
		case $pages_refs['ms_multi_search']: require ("$Directory/ms_multi_search/ms_multi_search.php");break;
		case $pages_refs['ms_all_computers']: require ("$Directory/ms_all_computers/ms_all_computers.php");break;
		case $pages_refs['ms_repart_tag']: require ("$Directory/ms_repart_tag/ms_repart_tag.php");break;
		case $pages_refs['ms_users']: require ("$Directory/ms_users/ms_users.php");break;
		case $pages_refs['ms_console']: require ("$Directory/ms_console/ms_console.php");break;	
 		default: require ("$Directory/ms_console/ms_console.php");		
 	}

if( !isset($protectedGet["popup"] ))
	require ($_SESSION['FOOTER_HTML']);
	
echo "<script language='javascript'>wait(0);</script>";

?>
