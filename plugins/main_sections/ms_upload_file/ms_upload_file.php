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
//Modified on $Date: 2007-02-08 15:53:24 $$Author: plemmet $($Revision: 1.8 $)

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
        
	    var ExtList=new Array('exe','pl','zip');
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
function getVersionFromLinuxAgent($content)
{
	global $l;
	$res=Array();
	$res=explode("use constant VERSION =>", $content);	
	
	if($res[1]=="")
	{
		msg_error($l->g(184));
		return -1;
	}
	return trim($res[1]);	
}

function getVersionFromZip($zipFile)
{
	global $l;
	if ($zip = @zip_open($zipFile)) 
	{		
		$trouve=false;
		while ($zip_entry = zip_read($zip)) 
		{
			if(zip_entry_name ($zip_entry) == "ver")
				if (zip_entry_open($zip, $zip_entry, "r")) 
				{
					$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
					$trouve=true;
					zip_close($zip);
					if( $buf == "0") {
						msg_error($l->g(185));
						return -1;
					}
					return $buf;
				}
		}
		
		if(!$trouve)
		{
			msg_error($l->g(186));
			zip_close($zip);
			return -1;
		}
	}
	else
	{
		msg_error($l->g(187));
		return -1;
	}
}

$form_name="upload_client";
$table_name=$form_name;
if (isset($protectedPost['GO']) and $protectedPost['GO']!= ''){
	//récupération du nom et de l'extention
	$exp_file=explode('.',$_FILES['file_upload']['name']);
	$name_file=$exp_file[0];
	$ext = $exp_file[1];
	if ($ext == "zip" or $ext == "exe"){
		if ($ext == "zip")
			$fname="agent";
		else
			$fname=$name_file.".".$ext;
			
		$platform="windows";
	}elseif ($ext=="pl" or is_numeric($ext)){
		if ($ext == "zip")
			$fname="pl";
		else
			$fname=$name_file.".".$ext;
			
		$platform="linux";		
	}
	
	$filename = $_FILES['file_upload']['tmp_name'];
	$fd = fopen($filename, "r");
	$contents = fread($fd, filesize ($filename));
	fclose($fd);	
//	$binary = addslashes($contents);	
	$binary = $contents;
	if($ext=="zip"){
		$version=getVersionFromZip($filename);	
	}
	else if($ext=="pl"){
		$version=getVersionFromLinuxAgent($binary);		
	}
	else
	{
		$version=$ext;
	}
		
	if($version!=-1){
		if ($ext == "exe"){
			$sql="DELETE FROM deploy where name='%s'";
			$arg=$_FILES['userfile']['name'];
			mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);	
			$sql="INSERT INTO deploy values ('%s','%s')";
			$arg=array($fname,$binary);
			mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);	
		}else{
			$sql="INSERT INTO files values ('%s','%s','%s','%s')";
			$arg=array($fname,$version,$platform,$binary);
			mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);	
			
		}
		msg_success($l->g(137)." ".$_FILES['file_upload']['name']." ".$l->g(234));
		$tab_options['CACHE']='RESET';
	}
	
	
}

if (isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != ''){
	$sql="DELETE FROM deploy where name='%s'";
	$arg=$protectedPost['SUP_PROF'];
	mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);	
	
	$sql="DELETE FROM files where name='%s'";
	$arg=$protectedPost['SUP_PROF'];
	mysql2_query_secure($sql,$_SESSION['OCS']["writeServer"],$arg);	
}




echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	$list_fields=array($l->g(283)=>'function',
					   $l->g(19) => 'version',
					   $l->g(25) => 'os',
					   $l->g(49) => 'name',
					   'SUP'=>'name'
					   );
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;

	$sql= "select '%s' as function,%s,%s,%s,'files' as tab FROM files union select '%s','%s','%s',%s,'deploy' from deploy";
	$tab_options['ARG_SQL']=array($l->g(103),'os','version','name',$l->g(370),'windows','-','name');
	$tab_options['LIEN_LBL'][$l->g(49)]='index.php?'.PAG_INDEX.'='.$pages_refs['ms_view_file'].'&prov=agent&no_header=1&value=';
	$tab_options['LIEN_CHAMP'][$l->g(49)]='name';
	$tab_options['LIEN_TYPE'][$l->g(49)]='POPUP';
	$tab_options['POPUP_SIZE'][$l->g(49)]="width=900,height=600";
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
