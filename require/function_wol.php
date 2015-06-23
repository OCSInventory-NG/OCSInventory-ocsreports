<?php
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Erwan GOALOU 2013 (erwan(at)ocsinventory-ng(pt)org)
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================


/*
 * functions to use wake on line
 * tx to Fash (http://forums.ocsinventory-ng.org/viewtopic.php?id=13008)
 */
class Wol{
  private $nic;
  public $wol_send;
  public function wake($mac,$ip){
  	global $l;
  	//looking for values of wol config
  	$wol_info=look_config_default_values('WOL_PORT');
  	if (!isset($wol_info['name']['WOL_PORT']))
  		$this->wol_send=$l->g(1321);
  	else
  		$wol_port=explode(',', $wol_info['tvalue']['WOL_PORT']);
  	foreach ($wol_port as $k=>$v){
  		if (is_numeric($v)){
  		    $s = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );
		     if( !$s ){ 
		    	 @socket_close($s);
		    	$this->wol_send=$l->g(1322);
		    }
		    else{
		    	$s_opt = socket_set_option($s,SOL_SOCKET,SO_BROADCAST,true);
		    	socket_sendto($s,$this->pacquet($mac),strlen($this->pacquet($mac)),0,"255.255.255.255",$v);
		     	socket_close($s);
		    	$this->wol_send=$l->g(1282);
		    }
  		}
  	}
  //	$this->wol_send='toto';
  	//return $wol_send;
  }
  private function pacquet($Mac){
    $packet = "";
    $macAddr = '';
    $addrByte=explode(':', $Mac);
    foreach ($addrByte as $v)
    	$macAddr .= chr(hexdec($v)); 
    for($i = 0; $i < 6; $i++)
        $packet .= chr(0xFF);
    for ($j = 0; $j < 16; $j++)
        $packet .= $macAddr;
    
    //use bios password?
    $wol_info=look_config_default_values('WOL_BIOS_PASSWD');
    if (isset($wol_info['name']['WOL_BIOS_PASSWD']))
    	$packet .=$wol_info['tvalue']['WOL_BIOS_PASSWD'];
    return $packet;
  }
}
 

?>