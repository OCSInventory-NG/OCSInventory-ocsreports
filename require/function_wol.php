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
 *  WOL_PORT is define on var.php
 */
class Wol{
  private $nic;
  public function wake($mac,$ip){
    $this->nic = fsockopen("udp://".$ip,WOL_PORT);
    if( !$this->nic ){
      fclose($this->nic);
      return false;
    }
    else{
      fwrite($this->nic, $this->pacquet($mac));
      fclose($this->nic);
      return true;
    }
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
    return $packet;
  }
}
 

?>