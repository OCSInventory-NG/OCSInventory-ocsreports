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
/* This module automatically inserts valid LDAP users into OCS operators table.
 *
 * The userlevel is defined according to conditions defined in the following configuration fields:
 *
 * - CONEX_LDAP_FILTER1
 * - CONEX_LDAP_FILTER1_ROLE
 * - CONEX_LDAP_FILTER2
 * - CONEX_LDAP_FILTER2_ROLE
 *
 * If any of these attributes are defined (and found on the LDAP query), they're used to determine the correct
 * user level and role.
 *
 * in case of success, an array is returned with the access data in the following format:
 * array('accesslvl'=>%%,'tag_show'=>array(%,%,%,%,%...))
 *
 * else, an error code is returned.
 *
 * CONEX_LDAP_FILTER1="thisGuyIsAdmin"
 * CONEX_LDAP_FILTER1_ROLE="user"
 * CONEX_LDAP_FILTER2="thisGuyIsAdmin"
 * CONEX_LDAP_FILTER2_ROLE="sadmin"
 * In logical terms:
 * if thisGuyIsAdmin=0 then
 *    role=user
 * else if thisGuyIsAdmin=1 then
 *    role=sadmin
 *
 *    Note: the default user levels in OCS currently are "admin", "ladmin" and "sadmin". The above is just an example.
 *
 */
if ($_SESSION['OCS']['cnx_origine'] != "LDAP") {
    return false;
}

require_once ('require/function_files.php');
// page name
$name = "ldap.php";
connexion_local_read();

// select the main database
mysqli_select_db($link_ocs, $db_ocs);

// retrieve LDAP-related config values into an array
$sql = "select substr(NAME,7) as NAME,TVALUE from config where NAME like '%s'";
$arg = array("%CONEX%");
$res = mysql2_query_secure($sql, $link_ocs, $arg);
while ($item = mysqli_fetch_object($res)) {
    $config[$item->NAME] = $item->TVALUE;
}

// checks if the user already exists
$reqOp = "SELECT new_accesslvl as accesslvl FROM operators WHERE id='%s'";
$argOp = array($_SESSION['OCS']["loggeduser"]);
$resOp = mysql2_query_secure($reqOp, $link_ocs, $argOp);

// defines the user level according to specific LDAP filter
// default: normal user
$defaultRole = $config['LDAP_CHECK_DEFAULT_ROLE'];

if (isset($_SESSION['OCS']['details']["filter"])) {
    $defaultRole = $config[$_SESSION['OCS']['details']["filter"]];
}

// uncomment this section for DEBUG
// note: cannot use the global DEBUG variable because this happens before the toggle is available.
/*
  echo ("field1: ".$f1_name." value=".$f1_value." condition: ".$config['LDAP_CHECK_FIELD1_VALUE']." role=".$config['LDAP_CHECK_FIELD1_ROLE']." level=".$config['LDAP_CHECK_FIELD1_USERLEVEL']."<br>");
  echo ("field2: ".$item['CONEX_LDAP_CHECK_FIELD2_NAME']." value=".$f2_value." condition: ".$config['LDAP_CHECK_FIELD2_VALUE']." role=".$config['LDAP_CHECK_FIELD2_ROLE']." level=".$config['LDAP_CHECK_FIELD2_USERLEVEL']."<br>");
  echo ("user: ".$_SESSION['OCS']["loggeduser"]." will have level=".$defaultLevel." and role=".$defaultRole."<br>");
 */
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
            $_SESSION['OCS']['details']['givenname'],
            $_SESSION['OCS']['details']['sn'],
            "",
            "LDAP",
            $defaultRole,
            $_SESSION['OCS']['details']['mail'],
            "NULL"
        );
    } else {
        // else update it
        $reqInsert = "UPDATE operators SET
                        NEW_ACCESSLVL='%s',
                        EMAIL='%s'
                    WHERE ID='%s'";

        $arg_insert = array($defaultRole,
            $_SESSION['OCS']['details']['mail'],
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
