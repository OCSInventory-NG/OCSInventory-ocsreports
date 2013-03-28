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
    $packet = "\xFF\xFF\xFF\xFF\xFF\xFF";
    for ($j = 0; $j < 16; $j++){
      for($i = 0; $i < 12; $i=$i + 2){
      	$packet .= chr(hexdec(substr($Mac, $i, 2)));
      }
    }
    return $packet;
  }
}
 


function wake_on_wan($addr, $mac,$port = WOL_PORT, $pwd='')
{
	$addr_byte = explode(':', $mac); //Suppression des doubles points et creation du tableau
	$hw_addr = ''; //variable de l'adresse physique
	for($a=0; $a <6; $a++)
	{
		$hw_addr .= chr(hexdec($addr_byte[$a]));
	}
	$msg = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);
	for ($a = 1; $a <= 16; $a++)
		$msg .= $hw_addr;
	$mdp = strlen($pwd);
	if($mdp!=0)
	{
		$completion = 6 - $mdp;
		$msg .= $pwd;
		for($i=0;$i<$completion;$i++)
		$msg .= chr(hexdec(00));
	}
	$s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
	if($s == false) {
		echo '<b>Erreur du cot&eacute; serveur ou parametres invalides</b><br />';
		echo "<i>Creation du socket impossible</i>\n";
		return FALSE;
	}
	else
	{
		$opt_ret = socket_set_option($s, 1, 6, TRUE);
		if($opt_ret <0) {
			echo "<b>Erreur du cot&eacute; serveur</b><i>socket_set_option</i>\n";
			return FALSE;
		}
		if(socket_sendto($s, $msg, strlen($msg), 0, $addr, $port)) {
			echo "<b>Signal de reveil envoy&eacute; &agrave; $addr pour $mac sur le port $port</b><br />";
			if($mdp!=0)
			echo "<b>Mot de passe utilisateur BIOS : oui</b>\n";
			else
			echo "\n";
			socket_close($s);
			return TRUE;
		}
		else {
			echo "<b>Erreur lors de l'envoi du signal</b><br />";
			echo "<i>Mauvais parametres ou parametres absents</i>\n<br />";
			return FALSE;
		}
	}
}



?>