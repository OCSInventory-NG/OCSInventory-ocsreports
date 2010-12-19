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

echo "<script language='javascript'>  
    
    function getext(filename){
    	 var parts = filename.split('.');
   		return(parts[(parts.length-1)]);    
    }
    
    function namefile(filename){
     	var parts = filename.split('.');
    	return(parts[0]);    
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
				if (filenamenoext != 'ocsagent' && filenamenoext != 'ocspackage' && ExtList[i] == 'exe'){
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
$form_name="upload_client";
$table_name=$form_name;
if (isset($protectedPost['GO']) and $protectedPost['GO']!= ''){
	$fname=$_FILES['file_upload']['name'];
	$platform="windows";	
	$filename = $_FILES['file_upload']['tmp_name'];
	$fd = fopen($filename, "r");
	$contents = fread($fd, filesize ($filename));
	fclose($fd);		
	$binary = $contents;
	$sql="DELETE FROM deploy where name='%s'";
	$arg=$_FILES['userfile']['name'];
	mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);	
	$sql="INSERT INTO deploy values ('%s','%s')";
	$arg=array($fname,$binary);
	mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);	
	msg_success($l->g(137)." ".$_FILES['file_upload']['name']." ".$l->g(234));
	$tab_options['CACHE']='RESET';
}

if (isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != ''){
	$sql="DELETE FROM deploy where name='%s'";
	$arg=$protectedPost['SUP_PROF'];
	mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);	
}

echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
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
		echo "</form>";


if (isset($protectedPost['ADD_FILE']) and $protectedPost['ADD_FILE'] != ''){
	$css="mvt_bordure";
	$form_name1="SEND_FILE";
	
	echo "<form name='".$form_name1."' id='".$form_name1."' method='POST' action='' enctype='multipart/form-data' onsubmit=\"return verif_file_format('file_upload');\">";
	echo '<div class="'.$css.'" >';
	echo $l->g(1048).":<input id='file_upload' name='file_upload' type='file' accept=''>";
	echo "<br><br><input name='GO' id='GO' type='submit' value='".$l->g(13)."'>&nbsp;&nbsp;<input type='button' name='RESET' id='RESET' value='".$l->g(113)."' onclick='submit(".$form_name.")'>";
	echo "</form>";
	echo "<br>";
	echo "</div>";
}


?>
