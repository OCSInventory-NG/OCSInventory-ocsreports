<?php

/*
 * Copyright 2005-2020 OCSInventory-NG/OCSInventory-ocsreports contributors.
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
function get_affiche_methode(){
    if(AUTH_TYPE == 4){
        return "SSO";
    } else if (AUTH_TYPE == 6) {
        return "CAS";
    } else if (AUTH_TYPE == 7) {
        return "SSO_ONLY";
    }else{
        return "HTML";
    }
}
function get_list_methode($identity = false){
    switch (AUTH_TYPE) {
        case 1:
            return array(
                0 => "local.php"
            );

        case 2:
            return array(
                0 => "local.php",
                1 => "ldap.php"
            );

        case 3:
            return array(
                0 => "ldap.php"
            );

        case 4:
            return array(
                0 => "ldap.php"
            );
            break;
        case 5:
            if($identity){
                return array(
                    0 => "ldap.php"
                );
            }else{
                return array(
                    0 => "always_ok.php"
                );
            }
            break;

        case 6:
            return array(
                0 => "cas.php"
            );
            break;
        
        case 7:
            return array(0=>($identity)?"local.php":"sso_only.php");
            break;
            
        default:
            return array(
                0 => "local.php"
            );
        }
}
