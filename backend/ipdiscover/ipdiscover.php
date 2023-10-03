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
require_once(BACKEND . 'require/connexion.php');
require_once(BACKEND . 'require/auth.manager.php');
$list_methode = array(0 => "local.php");
if (!isset($_SESSION['OCS']["ipdiscover"])) {
    $i = 0;
    //methode pour le calcul des droits
    while (isset($list_methode[$i]) == true) {
        require_once('methode/' . $list_methode[$i]);
        //on garde les droits de l'utilisateur sur l'ipdiscover
        if (isset($list_ip)) {
            $tab_ip[$list_methode[$i]] = $list_ip;
            unset($list_ip);
        }
        $i++;
    }
}
unset($list_ip);
if (isset($tab_ip)) {
    foreach ($list_methode as $script) {
        if (isset($tab_ip[$script])) {
            foreach ($tab_ip[$script] as $ip => $lbl) {
                $list_ip[$ip] = $lbl;
            }
        }
    }
    if (isset($list_ip)) {
        $_SESSION['OCS']["ipdiscover"] = $list_ip;
        $_SESSION['OCS']["ipdiscover_methode"] = $base;
        $_SESSION['OCS']["ipdiscover_id"] = $id_subnet;
        $_SESSION['OCS']["subnet_ipdiscover"] = $list_subnet;
    }
}
if (isset($tab_info) && !isset($_SESSION['OCS']["ipdiscover"])) {
    $_SESSION['OCS']["ipdiscover"] = $tab_info;
    $_SESSION['OCS']["ipdiscover_methode"] = $base;
}
