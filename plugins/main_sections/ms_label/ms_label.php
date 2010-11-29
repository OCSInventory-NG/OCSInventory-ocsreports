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

printEntete($l->g(263));

if( $protectedPost["newlabel"]!="" && str_replace(" ", "", $protectedPost["newlabel"] )!="" ) {
	$protectedPost["newlabel"] = str_replace(array("\t","\n","\r"), array("","",""), $protectedPost["newlabel"] );
	@mysql_query("DELETE FROM deploy WHERE name='label'");
	$queryL = "INSERT INTO deploy VALUES('label','".$protectedPost["newlabel"]."');";
	mysql_query($queryL) or die(mysql_error());
	msg_success($l->g(260));
}
else if(isset($protectedPost["newlabel"])) {
	@mysql_query("DELETE FROM deploy WHERE name='label'");
	msg_success($l->g(261));
}

$reqL="SELECT content FROM deploy WHERE name='label'";
$resL=mysql_query($reqL) or die(mysql_error());
$con = mysql_fetch_row($resL);

if($con[0]) {
	//echo "<br><center><FONT FACE='tahoma' SIZE=2 color='green'><b>Label actuel: \"".$con[0]."\"</b></font></center>";
}
else {
	if(!isset($protectedPost["newlabel"]))
		echo "<br><center><FONT FACE='tahoma' SIZE=2 color='green'><b>".$l->g(264)."</b></font></center>";
}
$con[0] = stripslashes($con[0]);
?><br>
<center><b><?php echo $l->g(262);?></b>
<form name='lab' method='post'>
	<textarea name='newlabel'><?php echo $con[0]?></textarea><br>
	<input name='sublabel' type='submit' value='<?php echo $l->g(13);?>'>
</form>
</center>