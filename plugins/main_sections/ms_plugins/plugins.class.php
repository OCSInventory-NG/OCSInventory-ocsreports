<?php

/**
 * 
 * This class give basic functions for plugins developpers.
 * 
 * @author Gillles Dubois
 */
class plugins{
	
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
	 */
	function add_menu($name, $label){
		
		$xmlfile = OCS_BASE_DIR."config/main_menu.xml";

		if (file_exists($xmlfile)){
			$xml = simplexml_load_file($xmlfile);
		}
		
		$menu = $xml->addChild("menu-elem");
		$menu->addAttribute("id","ms_".$name."");
		$menu->addChild("label","g(".$label.")");
		$menu->addChild("url","ms_".$name."");
		
		$xml->asXML($xmlfile);
		
		$xmlfile = OCS_BASE_DIR."config/urls.xml";
				
		if (file_exists($xmlfile)){
			$xml = simplexml_load_file($xmlfile);
		}
		
		$urls = $xml->addChild("url");
		$urls->addAttribute("key","ms_".$name."");
		$urls->addChild("value",$name);
		$urls->addChild("directory","ms_".$name."");
		
		$xml->asXML($xmlfile);
		
		$xmlfile = OCS_BASE_DIR."config/profiles/sadmin.xml";
		
		if (file_exists($xmlfile)){
			$xml = simplexml_load_file($xmlfile);
		}
		
		$xml->pages->addChild("page","ms_".$name."");
		
		$xml->asXML($xmlfile);
		
		$file = fopen(OCS_BASE_DIR."plugins/language/english/english.txt", "a+");
		fwrite($file, $label." ".$name);
		fclose($file);
		
	}
	
	function del_menu($name, $label){
		
	}
	
	/**
	 * This function create a sub-menu in OCS Iventory
	 * As default, only super administrator profile can see the created menu.
	 * 
	 * @param string $name : The name of the menu you want to crate
	 * @param string $menu : The name of the main menu (see documentation)
	 * @param integer $label : You need to give a label to your menu, it's like a reference for OCS. 
	 */
	function add_sub_menu($name, $menu, $label){
		
		$xmlfile = OCS_BASE_DIR."config/main_menu.xml";
		
		if (file_exists($xmlfile)){
			$xml = simplexml_load_file($xmlfile);
		}
		
		$menu = $xml->addChild("menu-elem");
		$menu->addAttribute("id","ms_".$name."");
		$menu->addChild("label","g(".$label.")");
		$menu->addChild("url","ms_".$name."");
		
		$xml->asXML($xmlfile);
		
		$xmlfile = OCS_BASE_DIR."config/urls.xml";
		
		if (file_exists($xmlfile)){
			$xml = simplexml_load_file($xmlfile);
		}
		
		$urls = $xml->addChild("url");
		$urls->addAttribute("key","ms_".$name."");
		$urls->addChild("value",$name);
		$urls->addChild("directory","ms_".$name."");
		
		$xml->asXML($xmlfile);
		
		$xmlfile = OCS_BASE_DIR."config/profiles/sadmin.xml";
		
		if (file_exists($xmlfile)){
			$xml = simplexml_load_file($xmlfile);
		}
		
		$xml->pages->addChild("page","ms_".$name."");
		
		$xml->asXML($xmlfile);
		
		$file = fopen(OCS_BASE_DIR."plugins/language/english/english.txt", "a+");
		fwrite($file, $label." ".$name);
		fclose($file);
		
	}

	function del_sub_menu($name, $menu, $label){
	
	}
	
	function add_rights($profilename){
		
	}
	
	function del_rights($profilename){
		
	}
	
	/**
	 * This function try to execute your query and throw an error message if this is a problems in the query.
	 * 
	 * @param string $query : Your database query here !
	 */
	function sql_query($query){
		try {
		    $dbh = new PDO('mysql:host=localhost;dbname=ocsweb', 'ocs', 'ocs');
		    $dbh->query($query);
		    $dbh = null;
		} catch (PDOException $e) {
		    print "Error !: " . $e->getMessage() . "<br/>";
		    die();
		}
	}
	
}

?>