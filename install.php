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
//Modified on $Date: 2008-03-04 17:07:55 $$Author: dliroulet $($Revision: 1.19 $)

@set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
?>
<html>
<head>
<TITLE>OCS Inventory Installation</TITLE>
<LINK REL='StyleSheet' TYPE='text/css' HREF='css/ocsreports.css'>
</head><body>

<?php
require_once('fichierConf.class.php');
if (!isset($_SESSION['OCS']['LANGUAGE']) or !isset($_SESSION['OCS']["LANGUAGE_FILE"])){
    if (isset($_COOKIE['LANG']))
        $_SESSION['OCS']['LANGUAGE']=$_COOKIE['LANG'];
    if (!isset($_COOKIE['LANG']))
        $_SESSION['OCS']['LANGUAGE']=DEFAULT_LANGUAGE;
    $_SESSION['OCS']["LANGUAGE_FILE"]=new language($_SESSION['OCS']['LANGUAGE']);
}

$l = $_SESSION['OCS']["LANGUAGE_FILE"];

printEnTeteInstall($l->g(2030));

if( isset($fromAuto) && $fromAuto==true)
   echo "<center><br><font color='green'><b>" . $l->g(2031) . " " .
   											    $valUpd["tvalue"] . 
   											    " " . $l->g(2032) . 
   											    " (" . GUI_VER . ") " . 
   											    $l->g(2033) . "</b></red><br></center>";

if( isset($fromdbconfig_out) && $fromdbconfig_out==true)
   echo "<center><br><font color='green'><b>" . $l->g(2034) . "</b></red><br></center>";

/*
if(!isset($_POST["name"])) {
	if( $hnd = @fopen("dbconfig.inc.php", "r") ) {
		fclose($hnd);
		require("dbconfig.inc.php");
		$_POST["name"] = $_SESSION['OCS']["COMPTE_BASE"];
		$_POST["pass"] = $_SESSION['OCS']["PSWD_BASE"];
		$_POST["host"] = $_SESSION['OCS']["SERVEUR_SQL"];
	}
	else {
		$_POST["name"] = "root";
		$_POST["pass"] = "";
		$_POST["host"] = "localhost";
	}
	$firstAttempt=true;
}*/ 

if(!function_exists('session_start')) {	
	echo "<br><center><font color=red><b>" . $l->g(2035) . "</b></font></center>";
	die();
}

if(!function_exists('xml_parser_create')) {	
	echo "<br><center><font color=orange><b>" . $l->g(2036) . "</b></font></center>";
}

if(!function_exists('mysql_connect')) {	
	echo "<br><center><font color=red><b>" . $l->g(2037) . "</b></font></center>";
	die();
}

if(!function_exists('imagefontwidth')) {	
	echo "<br><center><font color=orange><b>" . $l->g(2038) . "</b></font></center>";
}

if(!function_exists('openssl_open')) {	
	echo "<br><center><font color=orange><b>" . $l->g(2039) . "</b></font></center>";
}

@mkdir($_SERVER["DOCUMENT_ROOT"]."/download");
$pms = "post_max_size";
$umf = "upload_max_filesize";

$valTpms = ini_get( $pms );
$valTumf = ini_get( $umf );

$valBpms = return_bytes( $valTpms );
$valBumf = return_bytes( $valTumf );

if( $valBumf>$valBpms )
	$MaxAvail = $valTpms;
else
	$MaxAvail = $valTumf;

echo "<br><center><font color=orange><b>" . $l->g(2040) . 
									  " " . $MaxAvail . 
									  "<br>" . $l->g(2041) . "</b></font></center>";


if( isset($_POST["name"])) {
		if( (!$link=@mysql_connect($_POST["host"],$_POST["name"],$_POST["pass"]))) {
		$firstAttempt=false;
		echo "<br><center><font color=red><b>" . $l->g(2001) . 
											" " . $l->g(249) . 
											" (" . $l->g(2010) . 
											"=" . $_POST["host"] . 
											" " . $l->g(2011) . 
											"=" .$_POST["name"] . 
											" " . $l->g(2014) . 
											"=" . $_POST["pass"] . ")<br>
							Mysql error: ".mysql_error()."</b></font></center>";
	}
	else
		$instOk = true;
}
if( $hnd = @fopen("dbconfig.inc.php", "r") ) {
		fclose($hnd);
		require("dbconfig.inc.php");
		$valNme = COMPTE_BASE;
		$valPass = PSWD_BASE;
		$valServ = SERVER_WRITE;
}

if( ! $instOk ) {

	echo "<br><form name='fsub' action='install.php' method='POST'><table width='100%'>
	<tr>
		<td align='right' width='30%'>
			<font face='Verdana' size='-1'>" . $l->g(247) . ":&nbsp;&nbsp;&nbsp;</font>
		</td>
		<td width='50%' align='left'><input size=40 name='name' value='$valNme'>
		</td>
	</tr>
	<tr>
		<td align='right' width='30%'>
			<font face='Verdana' size='-1'>" . $l->g(248) . ":&nbsp;&nbsp;&nbsp;</font>
		</td>
		<td width='50%' align='left'><input size=40 type='password' name='pass' value='$valPass'>
		</td>
	</tr>
	<tr>
		<td align='right' width='30%'>
			<font face='Verdana' size='-1'>" . $l->g(250) . ":&nbsp;&nbsp;&nbsp;</font>
		</td>
		<td width='50%' align='left'><input size=40 name='host' value='$valServ'>
		</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
		<tr>
		<td colspan='2' align='center'>
			<input class='bouton' name='enre' type='submit' value=" . $l->g(13) . ">
		</td>
	</tr>
	
	</table></form>";
	die();
}


if($firstAttempt==true && $_POST["pass"] == "") {
	echo "<br><center><font color=orange><b>" . $l->g(2042) . "</b></font></center>";
}

if(!mysql_query("set global max_allowed_packet=2097152;")) {
	echo "<br><center><font color=orange><b>" . $l->g(2043) . "</font></center>";
}

mysql_select_db("ocsweb"); 

if(isset($_POST["label"])) {
	
	if($_POST["label"]!="") {
		@mysql_query( "DELETE FROM deploy WHERE NAME='label'");
		$query = "INSERT INTO deploy VALUES('label','" . $_POST["label"] . "');";
		mysql_query($query) or die(mysql_error());
		echo "<br><center><font color=green><b>" . $l->g(2044) . "</b></font></center>";
	}
	else {
		echo "<br><center><font color=green><b>" . $l->g(2045) . "</b></font></center>";
	}
}

if($_POST["fin"]=="fin") {
	// Configuration done, so try with account from config file
	if(!@mysql_connect($valServ,$valNme,$valPass)) {
		if(mysql_errno()==0) {
			echo "<br><center><font color=red><b>" . $l->g(2043) . 
												" " . $l->g(2044) . 
												"</b><br></font></center>";
			die();
		}
		else
			echo "<br><center><font color=red><b>" . $l->g(2043) . 
												" (" . $l->g(2017) .
												" " . $l->g(2010) .
												"=" . $_POST["host"] .
												" " . $l->g(2011) . 
												"=ocs " . $l->g(2014) . 
												"=ocs)"
											. "</b><br></font></center>";
		
		echo "<br><center><font color=red><b>" . $l->g(2065) . "</b></font></center>";
		unlink("dbconfig.inc.php");
	}
	else {
		echo "<br><center><font color=green><b>" . $l->g(2050) . "</b><br><br><b><a href='index.php'>" . $l->g(2051) . "</a></b></font></center>";
	}	
	die();
}


if(!$ch = @fopen("dbconfig.inc.php","w")) {
	echo "<br><center><font color=red><b>" . $l->g(2052) . "</b></font></center>";
	die();
}

$keepuser=false;

$db_file = "files/ocsbase.sql";
if($dbf_handle = @fopen($db_file, "r")) {
	echo "<br><center><font color=black><b>" . $l->g(2053);
	flush();
	$sql_query = fread($dbf_handle, filesize($db_file));
	fclose($dbf_handle);
	$dejaLance=0;
	$li = 0;
	foreach ( explode(";", "$sql_query") as $sql_line) {
		$li++;
		//echo $sql_line."<br>";
		if(!mysql_query($sql_line)) {
			if (mysql_errno()==1044 && strpos($sql_line, "GRANT ALL")!==false) {
				// Provided user not MySQL Administror
				$keepuser=true;
				continue;
			}
			if(  mysql_errno()==1062 || mysql_errno()==1061 || mysql_errno()==1065 || mysql_errno()==1060 || mysql_errno()==1054 || mysql_errno()==1091 || mysql_errno()==1061) 
				continue;		

			if(  mysql_errno()==1071 ) {
				echo "<br><center><font color=red><b>" . $l->g(2002) . 
												   " " . $li         .
												   ": ". $l->g(2015) . 
												  ":[" . $sql_line   . 
												  "]"  . $l->g(2054) . 
												  "</b><br>";
				continue;
			}
			
			if(mysql_errno()==1007 || mysql_errno()==1050) {
				$dejaLance = 1;
				continue;
			}
			
			echo "<br><center><font color=red><b>"  . $l->g(2002) .
												" " . $li         .
											  " : " . $l->g(2015) .
											  ":["  . $sql_line   . 
											  "]"   . $l->g(2009) . 
											  "</b><br>";
			echo "<b>" . $l->g(2003) . " " . mysql_error() . " (err:" . mysql_errno() . ")</b></font></center>";
			$nberr++;
		}
		echo ".";
		flush();
	}
	echo "</b></font></center>";
	if(!$nberr&&!$dejaLance){
		//update new lvlaccess
		$sql_up_accesslvl="select id,accesslvl,new_accesslvl from operators where new_accesslvl is null or new_accesslvl =''";
		$result = mysql_query($sql_up_accesslvl) or die(mysql_error());
		while($value=mysql_fetch_array($result)){
			unset($new_lvl);
			if ($value['accesslvl'] == 1)
				$new_lvl='sadmin';
			elseif ($value['accesslvl'] == 2)
				$new_lvl='ladmin';
			elseif ($value['accesslvl'] == 3)
				$new_lvl='admin';
			
			if (isset($new_lvl)){
				$sql="UPDATE operators SET new_accesslvl='" . $new_lvl . "' where ID='" . $value['id'] . "'";
				mysql_query($sql);
			}
		}
		echo "<br><center><font color=green><b>" . $l->g(2055) . "</b></font></center>";
		
	}
}
else {
	echo "<br><center><font color=red><b>" . $l->g(2001) . " " . $db_file . " " . $l->g(2013) . "</b></font></center>";
	die();
}
//$keepuser=1;
if ($keepuser) {
	// Provided user not MySQL Administror
	// Keep the account used for migration
	//echo "toto";
	fwrite($ch,"<?php\n");
	fwrite($ch,"define(\"DB_NAME\", \"ocsweb\");\n");
	fwrite($ch,"define(\"SERVER_READ\",\"" . $_POST["host"] . "\");\n");
	fwrite($ch,"define(\"SERVER_WRITE\",\"" . $_POST["host"] . "\");\n");				
	fwrite($ch,"define(\"COMPTE_BASE\",\"" . $_POST["name"] . "\");\n");					
	fwrite($ch,"define(\"PSWD_BASE\",\"" . $_POST["pass"] . "\");\n");					
	fwrite($ch,"?>");
	fclose($ch);
	echo "<br><center><font color=green><b>" . $l->g(2056) . "( " . 
											   $l->g(2017) . " " . 
											   $_POST["name"] . 
											   $l->g(2007) .
					 " )</b></font></center>";

} else {
	// Use account created during installation
	fwrite($ch,"<?php\n");
	fwrite($ch,"define(\"DB_NAME\", \"ocsweb\");\n");
	fwrite($ch,"define(\"SERVER_READ\",\"" . $_POST["host"] . "\");\n");
	fwrite($ch,"define(\"SERVER_WRITE\",\"" . $_POST["host"] . "\");\n");				
	fwrite($ch,"define(\"COMPTE_BASE\",\"ocs\");\n");					
	fwrite($ch,"define(\"PSWD_BASE\",\"ocs\");\n");					
	fwrite($ch,"?>");
	fclose($ch);
	echo "<br><center><font color=green><b>" . $l->g(2056) . " " . $l->g(2004) . "</b></font></center>";
}

if($dejaLance>0)	
	echo "<br><center><font color=green><b>" . $l->g(2057) . "</b></font></center>";
	
echo "<br><center><font color=black><b>" . $l->g(2058);
flush();
//TODO: dernieres tables
$tableEngines = array("hardware"=>"InnoDB","accesslog"=>"InnoDB","bios"=>"InnoDB","memories"=>"InnoDB","slots"=>"InnoDB",
"registry"=>"InnoDB","monitors"=>"InnoDB","ports"=>"InnoDB","storages"=>"InnoDB","drives"=>"InnoDB","inputs"=>"InnoDB",
"modems"=>"InnoDB","networks"=>"InnoDB","printers"=>"InnoDB","sounds"=>"InnoDB","videos"=>"InnoDB","softwares"=>"InnoDB",
"accountinfo"=>"InnoDB","netmap"=>"InnoDB","devices"=>"InnoDB", "locks"=>"HEAP");

$nbconv = 0;
$erralter = false;
foreach( $tableEngines as $tbl=>$eng ) {
	if( $res = mysql_query("show table status like '$tbl'") ) {
		$val = mysql_fetch_array( $res );
		if( $val["Engine"] == $eng ) {
			echo ".";
			flush();
		}
		else {
			$nbconv++;
			echo ".";
			flush();
			if( ! $resAlter = mysql_query("ALTER TABLE $tbl engine='$eng'") ) {
				$nberr++;
				$erralter = true;
				echo "</b></font></center><br><center><font color=red><b>" . $l->g(2059) . "</b><br>";
				echo "<b>mysql error: " . mysql_error() . " (err:" . mysql_errno() . ")</b></font></center>";
			}
		}
	}
	else {
		echo "</b></font></center><br><center><font color=red><b>" . $l->g(2060) . "</b><br>";
		echo "<b>mysql error: " . mysql_error() . " (err:" . mysql_errno() . ")</b></font></center>";
		$nberr++;
		$erralter = true;
	}
}
$oneInnoFailed = false;
$oneHeapFailed = false;
foreach( $tableEngines as $tbl=>$eng ) {
	if( $res = mysql_query("show table status like '$tbl'") ) {
		$val = mysql_fetch_array( $res );
		if( (strcasecmp($val["Engine"],$eng) != 0) && (strcasecmp($eng,"InnoDB") == 0) && $oneInnoFailed == false ) {
			echo "<br><br><center><font color=red><b>" . $l->g(2061) . "</b></font><br>";
			$oneInnoFailed = true;
		}
		if ( (strcasecmp($val["Engine"],$eng)!=0) && (strcasecmp($eng,"HEAP")) && (strcasecmp($val["Engine"],"MEMORY")!=0) && $oneHeapFailed == false  ) {
			echo "<br><br><center><font color=red><b>" . $l->g(2062) . "</b></font><br>";
			$oneHeapFailed = true;
		}
	}
	else {
		echo "</b></font></center><br><center><font color=red><b>" . $l->g(2060) . "</b><br>";
		echo "<b>mysql error: " . mysql_error() . " (err:" . mysql_errno() . ")</b></font></center>";
		$nberr++;
		$erralter = true;
	}
}

if( ! $erralter ) {
	echo "</b></font></center><br><center><font color=green><b>" . $l->g(2063) . " (" . $nbconv . " " . $l->g(2064) . "</b></font></center>";
}
	
if($nberr) {
	echo "<br><center><font color=red><b>" . $l->g(2065) . "</b></font></center>";
	unlink("dbconfig.inc.php");
	die();
}
$nberr=0;
$dir = "files";
$filenames = Array("ocsagent.exe");
$dejaLance=0;
$filMin = "";

mysql_query("DELETE FROM deploy");
mysql_select_db("ocsweb"); 
foreach($filenames as $fil) {
	$filMin = $fil;
	if ( $ledir = @opendir("files")) {
		while($filename = readdir($ledir)) {
			if(strcasecmp($filename,$fil)==0 && strcmp($filename,$fil)!=0  ) {
				//echo "<br><center><font color=green><b>$fil case is '$filename'</b></font></center>";
				$fil = $filename;
			}
		}
		closedir($ledir);
	}
	else {
		echo "<br><center><font color=orange><b>" . $l->g(2066) . 
												  " " . $fil . 
												  " " . $l->g(2067) . "</b></font></center>";
	}
	
	if($fd = @fopen($dir."/".$fil, "r")) {
		$contents = fread($fd, filesize ($dir."/".$fil));
		fclose($fd);	
		$binary = addslashes($contents);	
		$query = "INSERT INTO deploy VALUES('$filMin','$binary');";
		
		if(!mysql_query($query)) {			
			if(mysql_errno()==1007 || mysql_errno()==1050 || mysql_errno()==1062) {
					$dejaLance++;
					continue;
			}
			if(mysql_errno()==2006) {
				echo "<br><center><font color=red><b>" . $l->g(2001) . 
												   " " . $fil        .
												   " " . $l->g(2068) . 
												   "</b></font></center>";
				echo "<br><center><font color=red><b>" . $l->g(2065) . "</b></font></center>";
				unlink("dbconfig.inc.php");
				die();
			} 
			echo "<br><center><font color=red><b>" . $l->g(2001) . 
											   " " . $fil        .
											   " " . $l->g(2012) .
											   "</b><br>";
			echo "<b>" . $l->g(2003) . " " . mysql_error() . "</b></font></center>";		
			$nberr++;
		}
	}
	else {
		echo "<br><center><font color=orange><b>" . $l->g(2006) . 
											  " " . $dir        .
											  "/" . $fil		.
											  " " . $l->g(2070) . 
											  "</b></font></center>";
		$errNorm = true;
	}
}

if($dejaLance>0)	
	echo "<br><center><font color=orange><b>" . $l->g(2071) . "</b></font></center>";

if(!$nberr&&!$dejaLance&&!$errNorm)
	echo "<br><center><font color=green><b>" . $l->g(2072) . "</b></font></center>";

mysql_query("DELETE FROM files");
$nbDeleted = mysql_affected_rows();
if( $nbDeleted > 0)
	echo "<br><center><font color=green><b>" . $l->g(2073) . "</b></font></center>";
else
	echo "<br><center><font color=green><b>" . $l->g(2074) . "</b></font></center>";

if($nberr) {
	echo "<br><center><font color=red><b>" . $l->g(2065) . "</b></font></center>";
	unlink("dbconfig.inc.php");
	die();
}

$row = 1;
$handle = @fopen("subnet.csv", "r");

if( ! $handle ) {
	echo "<br><center><font color=green><b>" . $l->g(2076) . "</b></font></center>";
}
else {
	$errSub = 0;
	$resSub = 0;
	$dejSub = 0;
	echo "<hr><br><center><font color=green><b>" . $l->g(2077) . "</b></font></center>";
	while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
	
		$ipValide = "(([0-9]{1,3}\.){3}[0-9]{1,3})";
		$masqueEntier = "([0-9]{1,3})";
		$masqueValide = "(($ipValide|$masqueEntier)[ ]*$)";
		$exp = $ipValide."[ ]*/[ ]*".$masqueValide;

		if( preg_match(":$exp:",$data[2],$res) ) {
			
			if( @mysql_query("INSERT INTO subnet(netid, name, id, mask) 
			VALUES ('" . $res[1] . "','" . $data[0] . "','" . $data[1] . "','" . $res[4] . "')") ) {
				$resSub++;
				//echo "<br><center><font color=green><b>
				//Network => name: ".$data[0]." ip: ".$res[1]." mask: ".$res[4]." id: ".$data[1]." successfully inserted</b></font></center>";
			}
			else {
				if( mysql_errno() != 1062) {
					$errSub++;
					echo "<br><center><font color=red><b>" . $l->g(2078)   . 
													   " " . $data[0]      .
													   " " . $l->g(2079)   .
													   " " . mysql_errno() .
													  ": " .mysql_error()  .
									 "</b></font></center>";
				}
				else
					$dejSub++;
			}
		}
		else {
			$errSub++;
			echo "<br><center><font color=orange><b>" . $l->g(2080) .
												  " " . $data[0]	.
												  " " . $l->g(2081) .
												 ": " . $data[2]    .
												 ")</b></font></center>";
		}
	}
	fclose($handle);
	echo "<br><center><font color=green><b>" . $l->g(2005) .
									  " => " . $resSub     .
									     " " . $l->g(2016) .
									    ", " . "<font color=orange>" .
											   $dejSub     . 
										 " " . $l->g(2019) .
										 " </font>, <font color=red>" .
											   $errSub     .
										 " " . $l->g(2009) .
										 "</font></b></font></center><hr>";
	
}


echo "<br><center><font color=green><b>" . $l->g(2082) . "</b></font></center>";
flush();

$reqDej = "SELECT COUNT(id) as nbid FROM networks WHERE ipsubnet IS NOT NULL";
$resDej = mysql_query($reqDej) or die(mysql_error());
$valDej = mysql_fetch_array($resDej);
$errNet = 0;
$sucNet = 0;
$dejNet = $valDej["nbid"];

$reqNet = "SELECT hardware_id, id, ipaddress, ipmask FROM networks WHERE ipsubnet='' OR ipsubnet IS NULL";
$resNet = mysql_query($reqNet) or die(mysql_error());
while ($valNet = mysql_fetch_array($resNet) ) {
	$netid = getNetFromIpMask( $valNet["ipaddress"], $valNet["ipmask"] );
	if( !$netid || $valNet["ipaddress"]=="" || $valNet["ipmask"]=="" ) {
		$errNet++;
	}
	else {
		mysql_query("UPDATE networks SET ipsubnet='$netid' WHERE hardware_id='".$valNet["hardware_id"]."' AND id='".$valNet["id"]."'");
		if( mysql_errno() != "") {
			$errNet++;
			echo "<br><center><font color=red><b>" . $l->g(2083)   .
											   " " . $netid        .
											  " ," . $l->g(2008)   . 
											  " "  . mysql_errno() .
											  ": " . mysql_error() . 
											  "</b></font></center>";
		}
		else {
			$sucNet++;
		}
	}	
}
echo "<br><center><font color=green><b>" . $l->g(2084) . 
								  " => " . $sucNet     .
								  	 " " . $l->g(2016) . 
								  	", " . "<font color=orange>" .
										   $dejNet     .
									 " " . $l->g(2085) .
									 ", </font><font color=red>" .
										   $errNet     .
									 " " . $l->g(2086) . 
									 "</font></b></font></center>";

echo "<br><center><font color=green><b>" . $l->g(2087) . "</b></font></center>";
flush();

$reqDej = "SELECT COUNT(mac) as nbid FROM netmap WHERE netid IS NOT NULL";
$resDej = mysql_query($reqDej) or die(mysql_error());
$valDej = mysql_fetch_array($resDej);
$errNet = 0;
$sucNet = 0;
$dejNet = $valDej["nbid"];

$reqNet = "SELECT mac, ip, mask FROM netmap WHERE netid='' OR netid IS NULL";
$resNet = mysql_query($reqNet) or die(mysql_error());
while ($valNet = mysql_fetch_array($resNet) ) {
	$netid = getNetFromIpMask( $valNet["ip"], $valNet["mask"] );
	if( !$netid || $valNet["ip"]=="" || $valNet["mask"]=="" ) {
		$errNet++;
	}
	else {
		mysql_query("UPDATE netmap SET netid= '$netid' WHERE mac='" . $valNet["mac"] . "' AND ip='" . $valNet["ip"] . "'");
		if( mysql_errno() != "") {
			$errNet++;
			echo "<br><center><font color=red><b>" . $l->g(2083) . 
											   " " . $netid      .
											  " ," . $l->g(2008) .
											   " " . mysql_errno() . 
											  ": " . mysql_error() . 
											  "</b></font></center>";
		}
		else {
			$sucNet++;
		}
	}	
}
echo "<br><center><font color=green><b>" . $l->g(2089) . 
 								  " => " . $sucNet     .
									 " " . $l->g(2016) .
									", " . "<font color=orange>" .
										   $dejNet     .
									 " " . $l->g(2085) . 
									 ", </font><font color=red>" .
										   $errNet     .
									 " " . $l->g(2086) . 
									 "</font></b></font></center>";

//ORPH	
echo "<br><center><font color=green><b>" . $l->g(2090);
flush();
//TODO: orphelins dans nouvelle tables
$tables=Array("accountinfo","bios","controllers","drives",
	"inputs","memories","modems","monitors","networks","ports","printers","registry",
	"slots","softwares","sounds","storages","videos","devices");
$cleanedNbr = 0;

foreach( $tables as $laTable) {
		
	$reqSupp = "DELETE FROM $laTable WHERE hardware_id NOT IN (SELECT DISTINCT(id) FROM hardware)";
	$resSupp = @mysql_query( $reqSupp );
	if( mysql_errno() != "") {			
		echo "</b></font></center><br><center><font color=red><b>" . $l->g(2091)   . 
															   " " . $laTable      .
															  ", " . $l->g(2008)   . 
															   " " . mysql_errno() . 
															  ": " . mysql_error() .
															  "</b></font></center>";
	}
	else {
		if( $cleaned = mysql_affected_rows() )
			$cleanedNbr += $cleaned;			
	}
	echo ".";
}	
echo "</b></font></center><br><center><font color=green><b>" . $cleanedNbr . 
														 " " . $l->g(2092) . "</b></font></center>";
flush();

//NETMAP
echo "<br><center><font color=green><b>".$l->g(2093);
flush();
$cleanedNbr = 0;
		
$reqSupp = "DELETE FROM netmap WHERE netid NOT IN(SELECT DISTINCT(ipsubnet) FROM networks)";
$resSupp = @mysql_query( $reqSupp );
if( mysql_errno() != "") {			
	echo "</b></font></center><br><center><font color=red><b>" . $l->g(2094)   .
														  ", " . $l->g(2008)   . 
														   " " . mysql_errno() .
	                                                      ": " . mysql_error() . "</b></font></center>";
}
else {
	if( $cleaned = mysql_affected_rows() )
		$cleanedNbr += $cleaned;			
}

echo "</b></font></center><br><center><font color=green><b>" . $cleanedNbr . 
														 " " . $l->g(2095) . "</b></font></center>";
flush();
/*
echo "<br><center><font color=green><b>Building software cache. Please wait...</b></font></center>";
flush();
mysql_query("TRUNCATE TABLE softwares_name_cache") or die(mysql_error());
mysql_query("INSERT INTO softwares_name_cache(name) SELECT DISTINCT name FROM softwares") or die(mysql_error());

echo "<br><center><font color=green><b>Building registry cache. Please wait...</b></font></center>";
flush();
mysql_query("TRUNCATE TABLE registry_regvalue_cache") or die(mysql_error());
mysql_query("INSERT INTO registry_regvalue_cache(regvalue) SELECT DISTINCT regvalue FROM registry") or die(mysql_error());
*/
function printEnTeteInstall($ent) {
	echo "<br><table border=1 class= \"Fenetre\" WIDTH = '62%' ALIGN = 'Center' CELLPADDING='5'>
	<th height=40px class=\"Fenetre\" colspan=2><b>" . $ent . "</b></th></table>";
}

?>
<br>
<center>
<form name='taginput' action='install.php' method='post'><b>
<font color='black'><?php echo $l->g(2096) . "<br>(" . $l->g(2097) . ")"."</font></b><br><br>"?>
	<input name='label' size='40'>
	<input type='hidden' name='fin' value='fin'>
	<input type='hidden' name='name' value='<?php echo $_POST["name"];?>'>
	<input type='hidden' name='pass' value='<?php echo $_POST["pass"];?>'>
	<input type='hidden' name='host' value='<?php echo $_POST["host"];?>'>
	<input type=submit>
</form></center>

<?php 

function getNetFromIpMask($ip, $mask) {	
	return ( long2ip(ip2long($ip)&ip2long($mask)) ); 
}

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        // Le modifieur 'G' est disponible depuis PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

?>
