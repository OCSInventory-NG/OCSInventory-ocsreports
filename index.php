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
$Directory=$_SESSION['plugin_rep']."main_sections/";

if (file_exists($Directory."config.txt")) {
      $fd = fopen ($Directory."config.txt", "r");
      $capture='';
      while( !feof($fd) ) {

         $line = trim( fgets( $fd, 256 ) );

			if (substr($line,0,2) == "</")
            $capture='';

         if ($capture == 'OK_ORDER')
            $list_pluggins[]=$line;
         
			if ($capture == 'OK_LBL'){
            $tab_lbl=explode(":", $line);
            $list_lbl[$tab_lbl[0]]=$tab_lbl[1];
         }
         
			if ($capture == 'OK_ISAVAIL'){
            $tab_isavail=explode(":", $line);
            $list_avail[$tab_isavail[0]]=$tab_isavail[1];
         }
         
         if ($line{0} == "<"){
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
	
	echo $icons_list['all_computers'];
	echo $icons_list['repart_tag'];


//test for plugins
$i=0;
while ($list_pluggins[$i]){

	if (isset($list_avail[$list_pluggins[$i]])){
		echo $icons_list[$list_pluggins[$i]];
	}


	$i++;
}	


	//groups
	$sql_groups_4all="select workgroup from hardware where workgroup='GROUP_4_ALL' and deviceid='_SYSTEMGROUP_'";
	$res = mysql_query($sql_groups_4all, $_SESSION["readServer"]) or die(mysql_error($_SESSION["readServer"]));
	$item = mysql_fetch_object($res);
	if (isset($item->workgroup) or $_SESSION["lvluser"]==SADMIN or $_SESSION["lvluser"]==LADMIN)	
	
	echo $icons_list['groups'];
	echo $icons_list['all_soft'];
	echo $icons_list['multi_search'];
	
	echo "</tr></table></td><td>";	

	echo "<table BORDER='0' ALIGN = 'right' CELLPADDING='0' BGCOLOR='#FFFFFF' BORDERCOLOR='#9894B5'>";
	echo "<tr height=20px  bgcolor='white'>";
	
	flush();


	if($_SESSION["lvluser"]==SADMIN) {

			//Special code for teledploy
 			$name_menu="smenu1";
			$packAct = array($pages_refs['tele_package'],$pages_refs['tele_activate'],$pages_refs['rules_redistrib']);
			$nam_img="pack";
			$title=$l->g(512);
			$data_list_deploy[$pages_refs['tele_package']]=$l->g(513);
			$data_list_deploy[$pages_refs['tele_activate']]=$l->g(514);
			$data_list_deploy[$pages_refs['rules_redistrib']]=$l->g(662);
			menu_list($name_menu,$packAct,$nam_img,$title,$data_list_deploy);

			echo $icons_list['dict'];
			echo$icons_list['upload_file'];

			//Special code for config 
			$name_menu="smenu2";
			$packAct = array($pages_refs['configuration'],$pages_refs['blacklist']);
			$nam_img="configuration";
			$title=$l->g(107);
			$data_list_config[$pages_refs['configuration']]=$l->g(107);
			$data_list_config[$pages_refs['blacklist']]=$l->g(703);
			//$data_list_config[35]=$l->g(712);
			menu_list($name_menu,$packAct,$nam_img,$title,$data_list_config);	
			
			echo $icons_list['regconfig'];
			echo $icons_list['logs'];
			echo $icons_list['admininfo'];
        	        echo $icons_list['ipdiscover'];
	                echo $icons_list['doubles'];
			echo $icons_list['label'];
	                echo $icons_list['users'];
        	        echo $icons_list['local'];
			echo $icons_list['help'];

	}

	else {  //not clean for the moment...a second if will be better !! :) 
	
		//Icon for user profile	
		//echo $icons_list['admininfo'];
		echo $icons_list['ipdiscover'];
		echo $icons_list['doubles'];
		echo $icons_list['help'];
	}	
	
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
 		case $pages_refs['ipdiscover']: require ('ipdiscover_new.php');	break;
 		case $pages_refs['configuration']: require ('confiGale.php');	break;
 		case $pages_refs['regconfig']: require ('registre.php');	break;
 		case $pages_refs['doubles']: require ('doublons.php');	break;
 		case $pages_refs['upload_file']: require ('uploadfile.php');	break;
 		case $pages_refs['admininfo']: require ('donAdmini.php');	break;
 		case $pages_refs['label']: require ('label.php');	break;
		case $pages_refs['local']: require ('local.php');	break;
		case $pages_refs['dict']: require ('dico.php');	break;
		case $pages_refs['tele_package']: require ('tele_package.php'); break; 
		case $pages_refs['tele_activate']: require ('tele_activate.php'); break; 
		case $pages_refs['opt_param']: require ('opt_param.php'); break; 
		case $pages_refs['opt_ipdiscover']: require ('opt_ipdiscover.php'); break; 
		case $pages_refs['tele_stats']: require ('tele_stats.php'); break;
		case $pages_refs['tele_actives']: require ('tele_actives.php'); break;
		case $pages_refs['group_show']: require ('group_show.php'); break;
		case $pages_refs['tele_massaffect']: require ('tele_massaffect.php'); break; 
		case $pages_refs['admin_attrib']: require ('admin_attrib.php'); break; 
		case $pages_refs['blacklist']: require ('blacklist.php');break;
		case $pages_refs['rules_redistrib']: require ('rules_redistrib.php');break;
		case $pages_refs['all_soft']: require ('all_soft.php');break;
		case $pages_refs['groups']: require ('groups.php');break;
		case $pages_refs['show_detail']: require ('show_detail.php');break;
		case $pages_refs['logs']: require ('logs.php');break;
		case $pages_refs['multi_search']: require ('multi.php');break;
		case $pages_refs['all_computers']: require ('list_computors.php');break;
		case $pages_refs['repart_tag']: require ('repart_tag.php');break;
		case $pages_refs['users']: require ('admins.php');break;
		case $pages_refs['console']: require ('console.php');break;	
 		default: require ('console.php');		
 	}

if( !isset($protectedGet["popup"] ))
	require ($_SESSION['FOOTER_HTML']);
	
echo "<script language='javascript'>wait(0);</script>";

?>
