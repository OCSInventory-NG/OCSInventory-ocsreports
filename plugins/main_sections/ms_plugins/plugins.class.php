<?php

/**
 * 
 * This class give basic functions for plugins developpers. (WIP)
 * 
 * @author Gillles Dubois
 */
class plugins{
	
	// Unused ATM !
	
	protected $menus;
	protected $rights;
	
	private function getMenus(){
		return $this->menus;
	}
	
	private function getRights(){
		return $this->rights;
	}
	
	private function setMenus($table){
		$this->menus = $this->menu + $table;
	}
	
	private function setRights($table){
		$this->rights = $this->rights + $table;
	}
	
	/**
	 * This function create a menu (Not a sub-menu) in OCS inventory.
	 * As default, only super administrator profile can see the created menu.
	 * 
	 * @param string $name : The name of the menu you want to crate
	 * @param integer $label : You need to give a label to your menu, it's like a reference for OCS.
	 * @param String $plugindirectory : Your plugin directory
	 * @param string $menu (Optional) : If you want to create a submenu not a menu
	 */
	public function add_menu($name, $label, $plugindirectory, $menu = ""){
		
		// add menu entry
		if ($menu == ""){
			
			$xmlfile = CONFIG_DIR."main_menu.xml";
	
			if (file_exists($xmlfile)){
				$xml = simplexml_load_file($xmlfile);
				
				$menu = $xml->addChild("menu-elem");
				$menu->addAttribute("id","ms_".$name."");
				$menu->addChild("label","g(".$label.")");
				$menu->addChild("url","ms_".$name."");
				$menu->addChild("submenu"," ");
					
				$xml->asXML($xmlfile);
			}
			
		}
		else{
			
			$xmlfile = CONFIG_DIR."main_menu.xml";
			
			if (file_exists($xmlfile)){
				$xml = simplexml_load_file($xmlfile);
				
				$mainmenu = $xml->xpath("/menu/menu-elem[attribute::id='".$menu."']/submenu");
				$submenu = $mainmenu['0']->addChild("menu-elem");
				$submenu->addAttribute("id","ms_".$name."");
				$submenu->addChild("label","g(".$label.")");
				$submenu->addChild("url","ms_".$name."");
					
				$xml->asXML($xmlfile);
			}
			
		}
		
		// Add url entry for menu
		
		$xmlfile = CONFIG_DIR."urls.xml";
				
		if (file_exists($xmlfile)){
			
			$xml = simplexml_load_file($xmlfile);
			
			$urls = $xml->addChild("url");
			$urls->addAttribute("key","ms_".$name."");
			$urls->addChild("value",$name);
			$urls->addChild("directory","ms_".$plugindirectory."");
			
			$xml->asXML($xmlfile);
			
		}
		

		
		// add permissions for menu
		
		$xmlfile = CONFIG_DIR."profiles/sadmin.xml";
		
		if (file_exists($xmlfile)){
			$xml = simplexml_load_file($xmlfile);
			
			$xml->pages->addChild("page","ms_".$name."");
			
			$xml->asXML($xmlfile);
		}
		

		
		// Add label entry
		
		$file = fopen(PLUGINS_DIR."language/english/english.txt", "a+");
		fwrite($file, $label." ".$name."\n");
		fclose($file);
		
	}
	
	/**
	 * This function delete a menu or a submenu in OCS inventory.
	 * As default, only super administrator profile can see the created menu.
	 *
	 * @param string $name : The name of the menu you want to delete
	 * @param integer $label : You need to give the label of the deleted menu.
	 * @param string $menu (Optional) : If you want to delete a submenu not a menu
	 */
	public function del_menu($name, $label, $menu = ""){
		
		// Delete menu and all his sub menu
		if ($menu == ""){
			$xmlfile = CONFIG_DIR."main_menu.xml";
			
			if (file_exists($xmlfile)){
				$xml = simplexml_load_file($xmlfile);
				
				$mainmenu = $xml->xpath("/menu");
					
				foreach ($mainmenu as $listmenu){
						
					foreach ($listmenu as $info){
							
						if ($info['id'] == "ms_".$name){
								
							$dom=dom_import_simplexml($info);
							$dom->parentNode->removeChild($dom);
								
						}
							
					}
						
				}
				
				//var_dump($xml->asXML());
				$xml->asXML($xmlfile);
				
			}
	
		}
		else{
		
			$xmlfile = CONFIG_DIR."main_menu.xml";
			
			if (file_exists($xmlfile)){
				$xml = simplexml_load_file($xmlfile);
				
				$mainmenu = $xml->xpath("/menu/menu-elem[attribute::id='".$menu."']/submenu");
					
				foreach ($mainmenu as $submenu){
						
					foreach ($submenu as $info){
							
						if ($info['id'] == "ms_".$name){
								
							$dom=dom_import_simplexml($info);
							$dom->parentNode->removeChild($dom);
								
						}
							
					}
						
				}
					
				//var_dump($xml->asXML());
				$xml->asXML($xmlfile);
				
			}
		
		}
				
		
		// Remove Url node
		
		$xmlfile = CONFIG_DIR."urls.xml";
		
		if (file_exists($xmlfile)){
			$xml = simplexml_load_file($xmlfile);
			
			foreach ($xml as $value){
			
				if( $value['key'] == "ms_".$name ){
			
					$dom=dom_import_simplexml($value);
					$dom->parentNode->removeChild($dom);
				}
			}
			
			//var_dump($xml->asXML());
			$xml->asXML($xmlfile);
			
		}
		
		// Remove permissions
		
		$xmlfile = CONFIG_DIR."profiles/sadmin.xml";
		
		if (file_exists($xmlfile)){
			$xml = simplexml_load_file($xmlfile);
			
			$mypage = $xml->pages->page;
			
			foreach ($mypage as $pages){
			
				if($pages == "ms_".$name){
			
					$dom=dom_import_simplexml($pages);
					$dom->parentNode->removeChild($dom);
				}
			
			}
			
			//var_dump($xml->asXML());
			$xml->asXML($xmlfile);
			
		}

		// Remove Label entry
		
		$reading = fopen(PLUGINS_DIR.'language/english/english.txt', 'a+');
		$writing = fopen(PLUGINS_DIR.'language/english/english.tmp', 'w');
		
		$replaced = false;
		
		while (!feof($reading)) {
			$line = fgets($reading);
			if (stristr($line, $label." ".$name)) {
				$line = "";
				$replaced = true;
			}
			fputs($writing, $line);
		}
		fclose($reading); fclose($writing);
		// might as well not overwrite the file if we didn't replace anything
		if ($replaced)
		{
			rename(PLUGINS_DIR.'language/english/english.tmp', PLUGINS_DIR.'language/english/english.txt');
		} else {
			unlink(PLUGINS_DIR.'language/english/english.tmp');
		}
		
	}
	
	/**
	 * This function is used to add permission to see a page for a fixed profile
	 * (admin / ladmin / etc etc...)
	 * @param string $profilename : The name of the profile
	 * @param string $page : Name of the page u want to be seed by the profile
	 */
	public function add_rights($profilename, $page){

		if ($profilename == "sadmin"){ exit; }
		
		$xmlfile = CONFIG_DIR."profiles/".$profilename.".xml";
		
		if (file_exists($xmlfile)){
			$xml = simplexml_load_file($xmlfile);
			
			$xml->pages->addChild("page","ms_".$page."");
			
			$xml->asXML($xmlfile);
		}

		
	}
	
	/**
	 * This function is used for remove permission on one plugin's page for a fixed profile
	 * (admin / ladmin / etc etc...)
	 * @param string $profilename : The name of the profile
	 * @param string $page : Name of the page u want to be seed by the profile
	 */
	public function del_rights($profilename, $page){
		
		if ($profilename == "sadmin"){ exit; }
		
		$xmlfile = CONFIG_DIR."profiles/".$profilename.".xml";
		
		$xml = simplexml_load_file($xmlfile);
		
		$mypage = $xml->pages->page;
		
		foreach ($mypage as $pages){
		
			if($pages == $page){
		
				$dom=dom_import_simplexml($pages);
				$dom->parentNode->removeChild($dom);
			}
		
		}
		
		$xml->asXML($xmlfile);
		
	}
	

	/**
	 * This function try to execute your query and throw an error message if this is a problems in the query.
	 * 
	 * @param string $query : Your database query here !
	 * @return mixed : Result returned by the query
	 */
	public function sql_query($query){
		try {
		    $dbh = new PDO('mysql:host='.SERVER_WRITE.';dbname='.DB_NAME.'', COMPTE_BASE, PSWD_BASE);
		    $req = $dbh->query($query);
		    $anwser = $req ->fetch();
		    $dbh = null;
		    
		    return $anwser;
		} catch (PDOException $e) {
		    print "Error !: " . $e->getMessage() . "<br/>";
		    die();
		}
	}
	
}

?>