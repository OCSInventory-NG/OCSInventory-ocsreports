<?php
function ScanDirectory($Directory,$Filetype){
  global $pages_refs;
  $MyDirectory = @opendir($Directory); 
  if (!$MyDirectory)
  	return FALSE; 
  while($Entry = @readdir($MyDirectory)) {
  	if (substr($Entry,-strlen($Filetype)) == $Filetype){
  		$data['name'][]=$Entry;
  		$data['date_create'][]=date ("d M Y H:i:s.", filectime($Directory.$Entry));
  		$data['date_modif'][]=date ("d M Y H:i:s.", filemtime($Directory.$Entry));
  		$data['size'][]=filesize($Directory.$Entry);
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
function read_configuration($ms_cfg_file,$search){
	$fd = fopen ($ms_cfg_file, "r");
      $capture='';
      while( !feof($fd) ) {

         $line = trim( fgets( $fd, 256 ) );
		 if (substr($line,0,2) == "</")
            $capture='';
            
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
         			$find[$value_2_search][]=$line;	
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

?>