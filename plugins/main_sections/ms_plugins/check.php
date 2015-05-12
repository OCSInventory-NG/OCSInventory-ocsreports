<?php

/**
 * This function check if the plugin is already in the DATABASE and installed.
 * If not an entry is created in the table "plugins" with the plugins infos.
 */
function check($plugarray){
	
	$conn = new PDO('mysql:host=localhost;dbname=ocsweb;charset=utf8', 'ocs', 'ocs');


	foreach ($plugarray as $key => $value){
		
		$query = $conn->query("SELECT EXISTS( SELECT * FROM `plugins` WHERE name = '".$value."' ) AS name_exists");
		$anwser = $query->fetch();
		
		// If the plugin isn't in the database ... add it
		if($anwser[0] == false){
			
			require PLUGINS_INSTALL_DIR."ms_".$value."/install.php";
			
			// Retrieve infos from the plugin_version_plugname functions and add it to the database
			
			$fonc = "plugin_version_".$value;
			$infoplugin = $fonc();

			$conn->query("INSERT INTO `ocsweb`.`plugins` (`id`, `name`, `version`, `licence`, `author`, `verminocs`, `activated`, `reg_date`) 
					VALUES (NULL, '".$infoplugin['name']."', '".$infoplugin['version']."', '".$infoplugin['license']."', '".$infoplugin['author']."', '".$infoplugin['verMinOcs']."', '1', CURRENT_TIMESTAMP);");
		
			// Initialize the plugins requirement (New menus, Set permissions etc etc)
			
			$init = "plugin_init_".$value;
			$infoplugin = $init();			
			
		}
		
	}
	
}

?>

