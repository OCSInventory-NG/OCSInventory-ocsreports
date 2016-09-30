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

//UPDATE/DELETE
if ($protectedPost['Valid_modif']){
	$sql="DELETE FROM deploy WHERE name='%s'";
	$arg="label";
	$msg=$l->g(261);
	
	if (trim ($protectedPost['lbl']) != ""){
		$protectedPost["lbl"] = str_replace(array("\t","\n","\r"), array("","",""), $protectedPost["lbl"] );
		mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
		$sql="INSERT INTO deploy VALUES('%s','%s')";
		$arg=array('label',$protectedPost["lbl"]);
		$msg=$l->g(260);
	}
	
	mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);
	msg_success($msg);
}
//Looking for the label
$reqL="SELECT content FROM deploy WHERE name='%s'";
$arg="label";
$resL=mysql2_query_secure($reqL,$_SESSION['OCS']["readServer"],$arg);
$val = mysqli_fetch_object($resL);
printEntete($l->g(263));
$form_name='admin_info';
echo open_form($form_name);
?>
<div class="row">
    <div class="col-md-4 col-md-offset-4">
		<label for="lbl"><?php echo $l->g(262); ?> :</label>
		<input type="text" class="form-control" name="lbl" value="<?php echo $val->content; ?>">
    </div>
</div>
<br />
<div class="row">
    <div class="col-md-12">
        <input type="submit" name="Valid_modif" value="<?php echo $l->g(1363) ?>" class="btn btn-success">
        <input type="submit" name="Reset_modif" value="<?php echo $l->g(1364) ?>" class="btn btn-danger">
    </div>
</div>
<?php


echo close_form();
	?>