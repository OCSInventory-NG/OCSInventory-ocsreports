<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */
/*
 * functions to use wake on line
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