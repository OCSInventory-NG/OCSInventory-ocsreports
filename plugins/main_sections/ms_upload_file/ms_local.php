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
//Modified on $Date: 2010 Erwan Goalou
require_once('require/function_files.php');


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
        var ExtList=new Array('ocs','OCS,'xml','XML');
		filename = document.getElementById(champ).value.toLowerCase();
		fileExt = getext(filename);
		for (i=0; i<ExtList.length; i++)
		{
			if ( fileExt == ExtList[i] ) 
			{
				return (true);
			}
		}
		alert('".mysql_real_escape_string($l->g(559))."');
		return (false);
     }
          
</script>";
//   
$css="mvt_bordure";
$form_name1="SEND_FILE";
$data_config=look_config_default_values(array('LOCAL_SERVER','LOCAL_PORT'),'',array('TVALUE'=>array('LOCAL_SERVER'=>'localhost'),
																					'IVALUE'=>array('LOCAL_PORT'=>'80')));
$port = $data_config['ivalue']['LOCAL_PORT'];
$server = $data_config['tvalue']['LOCAL_SERVER'];


if(is_uploaded_file($_FILES['file_upload']['tmp_name'])) {
	
		$fd = fopen($_FILES['file_upload']['tmp_name'], "r");
		if ($_FILES['file_upload']['size'] != 0){
			$contents = fread($fd, filesize ($_FILES['file_upload']['tmp_name']));
			fclose($fd);
	
			$result = post_ocs_file_to_server($contents, "http://".$server."/ocsinventory", $port);
			
			if (isset($result["errno"])) {
				$errno = $result["errno"];
				$errstr = $result["errstr"];
				msg_error($l->g(344). " ". $errno . " / " . $errstr);
			}else {
				if( ! strstr ( $result[0], "200") )
					msg_error($l->g(344). " " . $result[0]);
				else {
					msg_success($l->g(287)." OK");
				}
			}
		}else
			msg_error($l->g(1244));
}
printEntete("<i>".$l->g(288)." (".$l->g(560).": http://".$server.":".$port.")");
echo "<br>";
echo "<form name='".$form_name1."' id='".$form_name1."' method='POST' action='' enctype='multipart/form-data' onsubmit=\"return verif_file_format('file_upload');\">";
echo '<div class="'.$css.'" >';
echo $l->g(1048).":<input id='file_upload' name='file_upload' type='file' accept=''>";
echo "<br><br><input name='GO' id='GO' type='submit' value='".$l->g(13)."'>";
echo "</form>";
echo "<br>";
echo "</div>";

?>
