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
/* page de récupération en local des droits
 * et des tags sur lesquels l'utilisateur
 * a des droits
 *
 * on doit renvoyer un tableau array('accesslvl'=>%%,'tag_show'=>array(%,%,%,%,%...))
 * si une erreur est rencontrée, on retourne un code erreur
 *
 */
require_once ('require/function_files.php');
//nom de la page
$name = "local.php";
connexion_local_read();
mysqli_select_db($link_ocs, $db_ocs);
//recherche du niveau de droit de l'utilisateur
$reqOp = "SELECT new_accesslvl as accesslvl FROM operators WHERE id='%s'";
$argOp = array($_SESSION['OCS']["loggeduser"]);
$resOp = mysql2_query_secure($reqOp, $link_ocs, $argOp);
$rowOp = mysqli_fetch_object($resOp);
if (isset($rowOp->accesslvl)) {
    $lvluser = $rowOp->accesslvl;

    $profile_config = PROFILES_DIR . $lvluser . '.xml';

    if (!file_exists($profile_config)) {
        migrate_config_2_2();
    }

    $profile_serializer = new XMLProfileSerializer();
    $profile = $profile_serializer->unserialize($lvluser, file_get_contents($profile_config));

    $restriction = $profile->getRestriction('GUI');
    $restrictions = $profile->getRestrictions();
    //Si l'utilisateur a des droits limités
    //on va rechercher les tags sur lesquels il a des droits
    if ($restriction == 'YES') {
        $sql = "select tag from tags where login='%s'";
        $arg = array($_SESSION['OCS']["loggeduser"]);
        $res = mysql2_query_secure($sql, $link_ocs, $arg);
        while ($row = mysqli_fetch_object($res)) {
            // Check for wildcard
            if (str_contains($row->tag, '*') || str_contains($row->tag,'?')) {
                $wildcard = true;
                $row->tag = str_replace("*", "%", $row->tag);
                $row->tag = str_replace("?", "_", $row->tag);
                if($wildcard === true){
                    $sql_wildcard = "SELECT TAG FROM `accountinfo` WHERE TAG LIKE '$row->tag' GROUP BY TAG";
                    $res_wildcard = mysql2_query_secure($sql_wildcard, $link_ocs);
                    while ($row_wildcard = mysqli_fetch_object($res_wildcard)) {
                        $list_tag[$row_wildcard->TAG] = $row_wildcard->TAG;
                    }
                    
                }      
            }else{
                $list_tag[$row->tag] = $row->tag;
            }
        }

        if (!isset($list_tag)) {
            $ERROR = $l->g(893);
        }

        // if user is restricted on all pages and has no tag assigned, he has the right to connect but no access
        if (!isset($list_tag) && !in_array('NO', $restrictions)) {
            $ERROR = $l->g(896);
        }

    } elseif ($restriction != 'NO') {
        $ERROR = $restriction;
    }
} else {
    $ERROR = $l->g(894);
}
