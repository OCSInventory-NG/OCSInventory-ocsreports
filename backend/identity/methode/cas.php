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

require_once ('require/function_files.php');
// page name
$name = "cas.php";
connexion_local_read();

// select the main database
mysqli_select_db($link_ocs, $db_ocs);

// retrieve CAS-related config values into an array
$sql = "select NAME,TVALUE from config where NAME like '%s'";
$arg = array("%CAS%");
$res = mysql2_query_secure($sql, $link_ocs, $arg);
while ($item = mysqli_fetch_object($res)) {
    $config[$item->NAME] = $item->TVALUE;
}

// checks if the user already exists
$reqOp = "SELECT new_accesslvl as accesslvl FROM operators WHERE id='%s'";
$argOp = array($_SESSION['OCS']["loggeduser"]);
$resOp = mysql2_query_secure($reqOp, $link_ocs, $argOp);

// defines the user level according to specific CAS filter
$defaultRole = $config['CAS_DEFAULT_ROLE'];


//if defaultRole is define
if (isset($defaultRole) && trim($defaultRole) != '') {
    // if it doesn't exist, create the user record
    if (!mysqli_fetch_object($resOp)) {
        $reqInsert = "INSERT INTO operators (
            ID,
            FIRSTNAME,
            LASTNAME,
            PASSWD,
            COMMENTS,
            NEW_ACCESSLVL,
            EMAIL,
            USER_GROUP
                )
                VALUES ('%s','%s', '%s', '%s','%s', '%s', '%s', '%s')";

        $arg_insert = array($_SESSION['OCS']["loggeduser"],
            'Default',
            $_SESSION['OCS']['details']['sn'] ?? '',
            "",
            "CAS",
            $defaultRole,
            $_SESSION['OCS']['details']['mail'] ?? '',
            "NULL"
        );
    } else {
        // else update it
        $reqInsert = "UPDATE operators SET
                        EMAIL='%s'
                    WHERE ID='%s'";

        $arg_insert = array(
            $_SESSION['OCS']['details']['mail'] ?? '',
            $_SESSION['OCS']["loggeduser"]);
    }
    connexion_local_write();
    // select the main database
    mysqli_select_db($link_ocs, $db_ocs);
    // Execute the query to insert/update the user record
    mysql2_query_secure($reqInsert, $link_ocs, $arg_insert);

    // repeat the query and define the needed OCS variables
    // note: original OCS code below
    connexion_local_read();

    // select the main database
    mysqli_select_db($link_ocs, $db_ocs);
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
        //if this user has RESTRICTION
        //search all tag for this user
        if ($restriction == 'YES') {
            $sql = "select tag from tags where login='%s'";
            $arg = array($_SESSION['OCS']["loggeduser"]);
            $res = mysql2_query_secure($sql, $link_ocs, $arg);
            while ($row = mysqli_fetch_object($res)) {
                // Check for wildcard
                if (strpos($row->tag, '*') !== false || strpos($row->tag,'?') !== false) {
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
    
        } elseif (($restriction != 'NO')) {
            $ERROR = $restriction;
        }
    } else {
        $ERROR = $l->g(894);
    }
} else {
    $ERROR = $l->g(1278);
}
?>
