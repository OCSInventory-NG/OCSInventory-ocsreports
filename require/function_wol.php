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
  		    $this->nic = @fsockopen("udp://".$ip,$v);
		    if( !$this->nic ){
		    	@fclose($this->nic);
		    	$this->wol_send=$l->g(1322);
		    }
		    else{
		    	fwrite($this->nic, $this->pacquet($mac));
		     	fclose($this->nic);
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