<?php

/**
 * Scan the DL plugins dir.
 * If the plugin isn't installed in the ocsreports, this function call the "install" function
 */
function scan_downloaded_plugins(){
	
  if(!file_exists(PLUGINS_DL_DIR)){
  	mkdir(PLUGINS_DL_DIR,'0777',true);
  }
	
  // Scan plugins download directory
  $directory = PLUGINS_DL_DIR;
  $scanned_directory = array_diff(scandir($directory), array('..', '.'));
  
  // Debug purposes
  //var_dump($scanned_directory);
  
	if (!empty($scanned_directory) == true){
		foreach ($scanned_directory as $key => $value){
			install($value);
		}
	}

}

?>