<?php
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Erwan GOALOU 2014 (erwan(at)ocsinventory-ng(pt)org)
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================

if (!isset($debut))
	die('FORBIDDEN');
error_reporting(E_ALL & ~E_NOTICE);
if (isset($_GET['debug']))
 $_SESSION['OCS']['DEBUG'] = 'ON';
require_once('require/fichierConf.class.php');
require_once('require/function_commun.php');
require_once('require/function_files.php');
require_once('var.php');
html_header(true);
if (!isset($_SESSION['OCS']['LANGUAGE']) or !isset($_SESSION['OCS']["LANGUAGE_FILE"])){
	if (isset($_COOKIE['LANG']))
		$_SESSION['OCS']['LANGUAGE']=$_COOKIE['LANG'];
	if (!isset($_COOKIE['LANG']))
		$_SESSION['OCS']['LANGUAGE']=DEFAULT_LANGUAGE;
	$_SESSION['OCS']["LANGUAGE_FILE"]=new language($_SESSION['OCS']['LANGUAGE']);
}


$l = $_SESSION['OCS']["LANGUAGE_FILE"];
$version_database=$_SESSION['OCS']['SQL_BASE_VERS'];
$form_name='form_update';
$rep_maj='files/update/';

//search all sql files for update
$list_fichier=ScanDirectory($rep_maj,"sql");
echo "<form name='".$form_name."' id='".$form_name."' method='POST'>";
msg_info($l->g(2057));

if (GUI_VER < $_SESSION['OCS']['SQL_BASE_VERS']){
	msg_error($l->g(2107)."<br>".$l->g(2108)."<br>".$l->g(2109).":".$version_database."=>".$l->g(2110).":".GUI_VER);
	echo "</form>";
	require_once('require/footer.php');
	die();
}


$msg_info=$l->g(2109).":".$version_database."=>".$l->g(2110).":".GUI_VER;
msg_info($msg_info);


echo "<br><input type=submit name='update' value='".$l->g(2111)."'>";

if (isset($_POST['update'])){
	while ($version_database < GUI_VER){
		
		$version_database++;
		if (in_array($version_database.".sql", $list_fichier['name'])){
			if ( $_SESSION['OCS']['DEBUG'] == 'ON')
				msg_success("Mise à jour effectuée: ".$version_database.".sql");
			exec_fichier_sql($rep_maj.'/'.$version_database.".sql");
			$sql="update config set tvalue='%s' where name='GUI_VERSION'";
			$arg=$version_database;
			$res_column=mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
			$_SESSION['OCS']['SQL_BASE_VERS']=$version_database;
		}
		else{
			msg_error($l->g(2114)." ".$version_database);
			die();
		}
		
	}
	msg_success($l->g(1121));
	echo "<br><br><br><b><a href='index.php'>".$l->g(2051)."</a></b>";
}
echo "</form>";
if (isset($_GET['debug']))
 unset($_SESSION['OCS']['DEBUG']);
require_once('require/footer.php');
/*
 * function to execute sql file
*
*/
function exec_fichier_sql($fichier){
	$db_file=$fichier;
	$dbf_handle = @fopen($db_file, "r");
		
	if (!$dbf_handle){
		msg_error($l->g(2112)." : ".$fichier);
		return true;
	}else{
		if (filesize($db_file) > 0){			
			$sql_query = fread($dbf_handle, filesize($db_file));
			fclose($dbf_handle);
			$data_sql=explode(";", $sql_query);
			foreach ($data_sql as $k=>$v){
				if (trim($v) != "")
					mysql2_query_secure($v,$_SESSION['OCS']["writeServer"]);
			}
			return false;
		}
		return true;
	}
}
?>