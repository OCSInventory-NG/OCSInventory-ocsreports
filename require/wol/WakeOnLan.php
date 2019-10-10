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

 /**
  * Class for Wake on Lan
  */
class Wol
{

    public $wol_send;

    /**
     * Send Wakeonlan
     * @param  string 	$broadcast     	[description]
     * @param  string 	$macaddr       	[description]
     * @param  int 	$port	 	[description]
     * @param  string 	$serverIP	[The source IP address to use for sending the WOL packets]
     * @return [type]                	[description]
     */
    public function wake_on_lan($broadcast, $macaddr, $port, $serverIP) {

        $addr_byte = explode(':', $macaddr);
        $hw_addr = '';
        for ($a = 0; $a < 6; $a++)
        {
            $hw_addr .= chr(hexdec($addr_byte[$a]));
        }

        $msg = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);

        for ($a = 1; $a <= 16; $a++)
        {
            $msg .= $hw_addr;
        }

        // send it to the broadcast address using UDP
        // SQL_BROADCAST option isn't help!!
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        if ($socket == false) {
		return FALSE;
        } else {
		// setting a broadcast option to socket:
		if( socket_set_option($socket, 1, 6, TRUE) < 0) {
			return FALSE;
		}

		try {
	    		socket_bind($socket, $serverIP);
	            	if(socket_sendto($socket, $msg, strlen($msg), 0, $broadcast, $port)) {
        	        	socket_close($socket);
	        	        return TRUE;
	        	} else {
        	        	return FALSE;
            		}
		} catch (Exception $e) {
			socket_close($socket);
		    	return FALSE;
	    	}

	}
    }

    /**
     * Look config WOL
     * @param  string $broadcast [description]
     * @param  string $macaddr   [description]
     * @param  string $serverIP  [The source IP address to use for sending the WOL packets]
     * @return [type]            [description]
     */
    public function look_config_wol($broadcast, $macaddr, $serverIP){

        // Looking for values of WOL config
        $wol_info = look_config_default_values('WOL_PORT');
        if (!isset($wol_info['name']['WOL_PORT'])) {
            $this->wol_send = 'No port defined for WOL';
	    return FALSE;
	}
	
	// Get ports to use for sending WOL packets
	$wol_port = explode(',', $wol_info['tvalue']['WOL_PORT']);

	// Try sending WOL packets on every ports defined
	$success = FALSE;
        foreach ($wol_port as $port) {
            if (is_numeric($port)) {
		    if ($this->wake_on_lan($broadcast, $macaddr, $port, $serverIP)) {
			$this->wol_send = "Wake On Lan order sent";
			$success = TRUE;
		    } 
            }
        }

	return $success;
    }

    /**
     * Save wol date en id machine in DB
     * @param  string $idchecked [description]
     * @param  string $date_wol  [description]
     * @return boolean            [description]
     */
    public function save_wol($idchecked, $date_wol){
        $form_date = date("Y-m-d H:i", strtotime($date_wol));

        $sql_wol = "INSERT INTO schedule_WOL (MACHINE_ID, WOL_DATE) VALUES ('%s', '%s')";
        $arg_wol = array($idchecked, $form_date);

        $result_verif = mysql2_query_secure($sql_wol, $_SESSION['OCS']["writeServer"], $arg_wol);

        if($result_verif == true){
            return true;
        } else {
            return false;
        }
    }
}
