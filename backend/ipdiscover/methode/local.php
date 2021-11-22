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
//if your script not use ocsbase
//$base = 'OTHER';

require_once('require/ipdiscover/Ipdiscover.php');
$ipdiscover = new Ipdiscover();

$base = "OCS";
connexion_local_read();
mysqli_select_db($link_ocs, $db_ocs);

$sql_black = "SELECT SUBNET,MASK FROM blacklist_subnet";
$res_black = mysql2_query_secure($sql_black, $link_ocs);
while ($row = mysqli_fetch_object($res_black)) {
    $subnetToBlacklist[$row->SUBNET] = $row->MASK;
}

if($ipdiscover->IPDISCOVER_TAG == "1") {
    $req = "SELECT DISTINCT n.netid as ipsubnet,s.name,s.id,CONCAT(n.netid,';',ifnull(s.tag,'')) as pass FROM netmap n 
            LEFT JOIN subnet s ON s.netid=n.netid";
    if (isset($_SESSION['OCS']["mesmachines"]) && $_SESSION['OCS']["mesmachines"] != '' && $_SESSION['OCS']["mesmachines"] != 'NOTAG') {
        $req .= "	and " . $_SESSION['OCS']["mesmachines"] . " order by n.netid";
    } else {
        $req .= " union select netid,name,id,CONCAT(netid,';',ifnull(tag,'')) from subnet";
    }

} else {
    $req = "SELECT DISTINCT n.netid as ipsubnet,s.name,s.id
            FROM netmap n LEFT JOIN subnet s ON s.netid=n.netid ,accountinfo a
		    WHERE (s.TAG IS NULL OR s.TAG = '')";
    if (isset($_SESSION['OCS']["mesmachines"]) && $_SESSION['OCS']["mesmachines"] != '' && $_SESSION['OCS']["mesmachines"] != 'NOTAG') {
        $req .= " and " . $_SESSION['OCS']["mesmachines"] . " order by netid";
    } else {
        $req .= " union select netid,name,id from subnet WHERE TAG IS NULL OR TAG = ''";
    }
}


$res = mysql2_query_secure($req, $link_ocs) or die(mysqli_error($link_ocs));
while ($row = mysqli_fetch_object($res)) {
    unset($id);
    if($ipdiscover->IPDISCOVER_TAG == "1") {
        $list_subnet[] = $row->pass;
        /*
          applied again patch of revision 484 ( fix bug: https://bugs.launchpad.net/ocsinventory-ocsreports/+bug/637834 )
         */
        if (isset($subnetToBlacklist) && is_array($subnetToBlacklist)) {
            foreach ($subnetToBlacklist as $key => $value) {
                if ($key == $row->ipsubnet) {
                    $id = '--' . $l->g(703) . '--';
                }
            }
        }
    
        //this subnet was identify
        if ($row->id != null && !isset($id)) {
            $list_ip[$row->id][$row->pass] = $row->name;
            $list_ip['---' . $l->g(1138) . '---'][$row->pass] = $row->name;
        } elseif (!isset($id)) {
            $no_name = '---' . $l->g(885) . '---';
            $list_ip[$no_name][$row->pass] = $no_name;
            $list_ip['---' . $l->g(1138) . '---'][$row->pass] = $no_name;
        } else {
            $list_ip[$id][$row->pass] = $id;
        }
    } else {
        $list_subnet[] = $row->ipsubnet;
        /*
        applied again patch of revision 484 ( fix bug: https://bugs.launchpad.net/ocsinventory-ocsreports/+bug/637834 )
        */
        if (isset($subnetToBlacklist) && is_array($subnetToBlacklist)) {
            foreach ($subnetToBlacklist as $key => $value) {
                if ($key == $row->ipsubnet) {
                    $id = '--' . $l->g(703) . '--';
                }
            }
        }

        //this subnet was identify
        if ($row->id != null && !isset($id)) {
            $list_ip[$row->id][$row->ipsubnet] = $row->name;
            $list_ip['---' . $l->g(1138) . '---'][$row->ipsubnet] = $row->name;
        } elseif (!isset($id)) {
            $no_name = '---' . $l->g(885) . '---';
            $list_ip[$no_name][$row->ipsubnet] = $no_name;
            $list_ip['---' . $l->g(1138) . '---'][$row->ipsubnet] = $no_name;
        } else {
            $list_ip[$id][$row->ipsubnet] = $id;
        }
    }
    
}
$id_subnet = "ID";
?>