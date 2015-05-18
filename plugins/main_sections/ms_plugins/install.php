<?php

/**
 * This function extract the archive content, put it in the ocs reports for initialization 
 */
function install($archiveName){
	
	//var_dump(PLUGINS_DL_DIR.$archiveName);
	
	if (file_exists(PLUGINS_DL_DIR.$archiveName)){
		$archive = new ZipArchive();
		$archive->open(PLUGINS_DL_DIR.$archiveName);		
		$arrayplugin = explode(".", $archiveName);
		$plugindir = $arrayplugin[0];
		$archive->extractTo(MAIN_SECTIONS_DIR."ms_".$plugindir);
		$archive->close();
		unlink(PLUGINS_DL_DIR.$archiveName);
	}
	else{
		echo "Une erreur est survenu lors de l'installation du plugin.";
	}
}

?>