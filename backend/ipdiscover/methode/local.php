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

$base = "OCS";
connexion_local_read();
mysqli_select_db($link_ocs, $db_ocs);

$sql_black = "select SUBNET,MASK from blacklist_subnet";
$res_black = mysql2_query_secure($sql_black, $link_ocs);
while ($row = mysqli_fetch_object($res_black)) {
    $subnet_to_balcklist[$row->SUBNET] = $row->MASK;
}
$req = "select distinct ipsubnet,s.name,s.id 
			from networks n left join subnet s on s.netid=n.ipsubnet
			,accountinfo a
		where a.hardware_id=n.HARDWARE_ID 
			and n.status='Up'";
if (isset($_SESSION['OCS']["mesmachines"]) && $_SESSION['OCS']["mesmachines"] != '' && $_SESSION['OCS']["mesmachines"] != 'NOTAG')
    $req .= "	and " . $_SESSION['OCS']["mesmachines"] . " order by ipsubnet";
else
    $req .= " union select netid,name,id from subnet";
$res = mysql2_query_secure($req, $link_ocs) or die(mysqli_error($link_ocs));
while ($row = mysqli_fetch_object($res)) {
    unset($id);
    $list_subnet[] = $row->ipsubnet;
    /* 	foreach ($subnet_to_balcklist as $key=>$value){
      if ($key == $row -> ipsubnet)
      $id='--'.$l->g(703).'--';
      }
     */
    /*
      applied again patch of revision 484 ( fix bug: https://bugs.launchpad.net/ocsinventory-ocsreports/+bug/637834 )
     */
    if (is_array($subnet_to_balcklist)) {
        foreach ($subnet_to_balcklist as $key => $value) {
            if ($key == $row->ipsubnet)
                $id = '--' . $l->g(703) . '--';
        }
    }

    /* foreach ($subnet_to_balcklist as $key=>$value){
      $black=explode('.',$value);
      $nb=count($black);
      $origine=explode('.',$row->ipsubnet);
      $nb--;
      unset($verif);
      while ($black[$nb]){
      if ($black[$nb] != $origine[$nb]){
      $verif=true;
      }
      $nb--;
      }
      if (!isset($verif)){
      $id='--'.$l->g(703).'--';
      }
      } */
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
$id_subnet = "ID";
/* if (!isset($list_ip))
  $INFO="NO_IPDICOVER"; */
?>
