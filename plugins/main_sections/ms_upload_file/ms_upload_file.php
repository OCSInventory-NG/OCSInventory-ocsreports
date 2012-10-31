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
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        // Le modifieur 'G' est disponible depuis PHP 5.1.0
        case 'g':
            $val *= 1024;
            break;
       /* case 'm':
            $val *= 1024;*/
        case 'k':
            $val *= 1024;
            break;
        default : $val= substr($val,0,-1);
        }
    return $val;
}
echo "<script language='javascript'>  
    
    function getext(filename){
    	 var parts = filename.split('.');
   		return(parts.pop());    
    }
    
    function namefile(filename){
    	var	parts	=	new Array();
   		var	parts2	=	new Array();
     	
   		parts = filename.split('.');
     	parts2= parts[0].split('\\\');
     	var part2return=parts2.pop();
    	return(part2return);    
    }    

    function verif_file_format(champ){
        
	    var ExtList=new Array('exe');
		filename = document.getElementById(champ).value.toLowerCase();
		fileExt = getext(filename);
		for (i=0; i<ExtList.length; i++)
		{
			if ( fileExt == ExtList[i] ) 
			{
				filenamenoext=namefile(filename);
				if (filenamenoext != 'ocsagent' && filenamenoext != 'ocs-ng-windows-agent-setup' && filenamenoext != 'ocspackage' && ExtList[i] == 'exe'){
					alert('".mysql_real_escape_string($l->g(1243))."');
					return (false);
				}
				return (true);
			}
		}
		alert('".mysql_real_escape_string($l->g(168))."');
		return (false);
     }
          
</script>";

$umf = "upload_max_filesize";
$valTumf = ini_get( $umf );
$valBumf = return_bytes( $valTumf );

$form_name="upload_client";
/*if( $valBumf>$valBpms )
	$MaxAvail = trim($valTpms,"m");
else
	$MaxAvail = trim($valTumf,"m");
echo "<br><center><font color=orange><b>" . $l->g(2040) . " " . $MaxAvail . $l->g(1240) . "<br>" . $l->g(2041) . "</b></font></center>";
*/
$table_name=$form_name;
if (isset($_FILES['file_upload']['name'])){
	if ($_FILES['file_upload']['size'] != 0){
		$fname=$_FILES['file_upload']['name'];
		$platform="windows";	
		$filename = $_FILES['file_upload']['tmp_name'];
		$fd = fopen($filename, "r");
		$contents = fread($fd, filesize ($filename));
		fclose($fd);		
		$binary = $contents;
		$sql="DELETE FROM deploy where name='%s'";
		$arg=$fname;
		mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);	
		$sql="INSERT INTO deploy values ('%s','%s')";
		$arg=array($fname,$binary);
		$result=mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);	
		if (!$result)
			msg_error($l->g(2003).mysql_errno($_SESSION['OCS']["writeServer"])."<br>".mysql_error($_SESSION['OCS']["writeServer"]));
		else{
			msg_success($l->g(137)." ".$_FILES['file_upload']['name']." ".$l->g(234));
			$tab_options['CACHE']='RESET';
		}
	}else{
		msg_error($l->g(920));
	}
}

if (isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != ''){
	$sql="DELETE FROM deploy where name='%s'";
	$arg=$protectedPost['SUP_PROF'];
	mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);	
}
if (!isset($protectedPost['ADD_FILE'])){
	echo open_form($form_name);
	$list_fields=array($l->g(283)=>'function',
					   $l->g(49) => 'name',
					   'SUP'=>'name'
					   );
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;

	$sql= "select '%s' as function,%s from deploy where name != 'label'";
	$tab_options['ARG_SQL']=array($l->g(370),'name');
	$tab_options['LIEN_LBL'][$l->g(49)]='index.php?'.PAG_INDEX.'='.$pages_refs['ms_view_file'].'&prov=agent&no_header=1&value=';
	$tab_options['LIEN_CHAMP'][$l->g(49)]='name';
	$tab_options['LIEN_TYPE'][$l->g(49)]='POPUP';
	$tab_options['POPUP_SIZE'][$l->g(49)]="width=900,height=600";
	printEntete($l->g(1245));
	echo "<br>";
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql,$form_name,80,$tab_options);
	//echo show_modif($name,'ADD_FILE',8,"",$configinput=array('DDE'=>100));
	echo "<input type=submit name=ADD_FILE value='".$l->g(1048)."'>";
	echo close_form();
}

if (isset($protectedPost['ADD_FILE']) and $protectedPost['ADD_FILE'] != ''){
	$css="mvt_bordure";
	$form_name1="SEND_FILE";
	//search max_allowed_packet value on mysql conf
	$sql="SHOW VARIABLES LIKE 'max_allowed_packet'";
	$result=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"]);
	$value=mysql_fetch_array($result);
	//pass oct to Mo
	$upload_max_filesize=$value['Value']/1048576;

	msg_info($l->g(2022).' '.$valBumf.$l->g(1240)."<br>".$l->g(2106)." ".$upload_max_filesize.$l->g(1240));
	//echo "post_max_size=".$valTpms.$l->g(1240).'//upload_max_filesize='.$valTumf.$l->g(1240);
	echo open_form($form_name1,'',"enctype='multipart/form-data' onsubmit=\"return verif_file_format('file_upload');\"");
	echo '<div class="'.$css.'" >';
	echo $l->g(1048).": <input id='file_upload' name='file_upload' type='file' accept=''>";
	echo "<br><br><input name='GO' id='GO' type='submit' value='".$l->g(13)."'>&nbsp;&nbsp;";
	//echo "<input type='button' name='RESET' id='RESET' value='".$l->g(113)."' onclick='submit(".$form_name.")'>";
	echo "</div>";
	echo close_form();
	echo "<br>";

}


?>
