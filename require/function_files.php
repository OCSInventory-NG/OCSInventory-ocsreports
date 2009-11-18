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

?>