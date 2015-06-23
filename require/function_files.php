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

function ScanDirectory($Directory,$Filetype){
  global $pages_refs;
  $MyDirectory = @opendir($Directory); 
  if (!$MyDirectory)
  	return FALSE; 
  while($Entry = @readdir($MyDirectory)) {
  	if (substr($Entry,-strlen($Filetype)) == $Filetype and $Filetype != "."){
  		$data['name'][]=$Entry;
  		$data['date_create'][]=date ("d M Y H:i:s.", filectime($Directory.$Entry));
  		$data['date_modif'][]=date ("d M Y H:i:s.", filemtime($Directory.$Entry));
  		$data['size'][]=filesize($Directory.$Entry);
  	}elseif ($Filetype == "."){
  		if (!strripos($Entry,$Filetype))
  		$data[$Entry]=$Entry;
  		unset($data['.'],$data['..']);
  		
  	}
  }
  closedir($MyDirectory);
  return $data;
}
/*
 * $ms_cfg_file= name of file to read
 * $search= array of values to find like array('PAGE_PROFIL'=>'MULTI','RESTRICTION'=>'SINGLE','ADMIN_BLACKLIST'=>'SINGLE') 
 * SINGLE => You have only one value to read
 * MULTI => You have few values to read 
 */
function read_configuration($ms_cfg_file,$search,$id_field=''){
	
	if (!is_readable($ms_cfg_file)){
		return "NO_FILES";
	} 
	$fd = fopen ($ms_cfg_file, "r");
	$capture='';
	while( !feof($fd) ) {
		$line = trim( fgets( $fd, 256 ) );
		if (substr($line,0,2) == "</"){
     		$capture='';
	 	}
     	if ($capture != '') {  
	 		foreach ($search as $value_2_search=>$option){
	 		//	echo $value_2_search."<br>";
        		if ($capture == 'OK_'.$value_2_search){
       				if (strstr($line, ':')){
     					$tab_lbl=explode(":", $line);
      					$find[$value_2_search][$tab_lbl[0]]=$tab_lbl[1];         			
     				}elseif ($option == 'SINGLE'){
        				$find[$value_2_search]=$line;
         			}elseif ($option == 'MULTI'){
						$find[$value_2_search][$line]=$line;
         			}elseif($option == 'MULTI2'){
         				//Fix your id with a field file (the first field only)
         				if ($id_field != '' and $value_2_search == $id_field)
         					$id=$line;
         				if (isset($id))
         					$find[$value_2_search][$id]=$line;	
         				else
         					$find[$value_2_search][]=$line;	
         			}
         		}         	
	 		}
     	}
        if ($line{0} == "<"){ 	//Getting tag type for the next launch of the loop
           $capture = 'OK_'.substr(substr($line,1),0,-1);
        }        
     }
  fclose( $fd );	
  return $find;
}

function update_config_file($ms_cfg_file,$new_value,$sauv='YES'){
	if ($sauv == 'YES')
		getcopy_config_file($ms_cfg_file);
	$ms_cfg_file=$_SESSION['OCS']['CONF_PROFILS_DIR'].$ms_cfg_file."_config.txt";
	
	//$file=fopen($file_name,"w+");
	$new_ms_cfg_file='';
	foreach ($new_value as $name_bal=>$val){
		$new_ms_cfg_file.="<".$name_bal.">\n";
		//fwrite($file,$key." ".$value."/r/n");	
		foreach ($val as $name_value=>$value){
			if (isset($value) and $value != '')
				$new_ms_cfg_file.=$name_value.':'.$value."\n";
			else
				$new_ms_cfg_file.=$name_value."\n";
			//fwrite($file,$key." ".$value."/r/n");				
		}
		$new_ms_cfg_file.="</".$name_bal.">\n\n";		
	}	
	$file=fopen($ms_cfg_file,"w+");
	fwrite($file,$new_ms_cfg_file);	
	fclose( $file );	
	
}
function getcopy_config_file($ms_cfg_file,$record='YES',$sauv=FALSE){
	if ($record != 'YES')
		return FALSE;
	if (!$sauv)
		$newfile=$_SESSION['OCS']['OLD_CONF_DIR'].$ms_cfg_file.'_config_old_'.time();
	else
		$newfile=$_SESSION['OCS']['CONF_PROFILS_DIR'].$sauv.'_config.txt';
		
	$ms_cfg_file=$_SESSION['OCS']['CONF_PROFILS_DIR'].$ms_cfg_file."_config.txt";
	@copy($ms_cfg_file, $newfile);
	return TRUE;
}


function delete_config_file($ms_cfg_file){
	$array_files=explode(',',$ms_cfg_file);
	$i=0;
	while (isset($array_files[$i])){
		getcopy_config_file($array_files[$i]);
		$ms_file=$_SESSION['OCS']['CONF_PROFILS_DIR'].$array_files[$i]."_config.txt";
		unlink($ms_file);
		$i++;
	}
}
function create_profil($new_profil,$lbl_profil,$ref_profil){
	$new_value=read_profil_file($ref_profil);
	$new_value['INFO']['NAME']=$lbl_profil;
	update_config_file($new_profil,$new_value,'NO');
	//getcopy_config_file($protectedPost['ref_profil'],'YES',$protectedPost['new_profil']);	
	
}


function parse_xml_file($file,$tag,$separe){
	$tab_data=array();
	 // open file
    if (!is_readable($file)){
		return "NO_FILES";
	}
	$fp = fopen($file, "r");
    $i=0;
    // read line
    while ( $ln = fgets($fp, 1024)) {     
    	$ln=preg_replace('(\r\n|\n|\r|\t| )','',$ln);
    	//	echo  htmlentities ($ln)."=>".strlen($ln);
		foreach ($tag as $poub=>$key){
		//	echo 	htmlentities (substr($ln,0,strlen($key)+2))." //".$key."<br>";
			if (substr($ln,0,strlen($key)+2) == '<'.$key.'>'){
				$search=array("<".$key.">","</".$key.">");
				$replace=array('','');
				$tab_data[$i][$key]=str_replace($search,$replace,$ln);				
			}						
		}
		//msg_info($key);
		//echo  htmlentities (substr($ln,0,strlen($key)+2))."=>".$separe."<br>";
		if ($ln == "</".$separe.">"){
				$i++;				
		}

    }
    fclose($fp);
    return ($tab_data);
}

function post_ocs_file_to_server($datastream, $url, $port) {
	
	$url = preg_replace("@^http://@i", "", $url);
	$host = substr($url, 0, strpos($url, "/"));
	$uri = strstr($url, "/");
	$reqbody = $datastream;
	
	$contentlength = strlen($reqbody);
	$reqheader =  "POST $uri HTTP/1.1\r\n".
	"Host: $host\n". "User-Agent: OCS_local_".GUI_VER."\r\n".
	"Content-type: application/x-compress\r\n".
	"Content-Length: $contentlength\r\n\r\n".
	"$reqbody\r\n";
	
	$socket = @fsockopen($host, $port, $errno, $errstr);
	
	if (!$socket) {
		$result["errno"] = $errno;
		$result["errstr"] = $errstr;
		return $result;
	}
	fputs($socket, $reqheader);
	
	while (!feof($socket)) {
		$result[] = fgets($socket, 4096);
	}
	
	fclose($socket);
	return $result;
}

?>