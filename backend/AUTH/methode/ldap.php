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
/*
 * LDAP custom authentication module
 *
 * This module will check and report if a LDAP user is valid based on the configuration supplied.
 * Adapted by Erico Mendonca <emendonca@novell.com> from original OCS code.
 *
 * I'm fetching a few LDAP attributes to fill in the user record, namely sn,cn,givenname and mail.
 * */

connexion_local_read();
$sql = "select substr(NAME,7) as NAME,TVALUE from config_ldap where NAME like '%s'";
$arg = array('%CONEX%');
$res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);

while ($item = mysqli_fetch_object($res)) {
    $config[$item->NAME] = $item->TVALUE;
    define($item->NAME, $item->TVALUE);
}

// copies the config values to the session area
$_SESSION['OCS']['config'] = $config;

$login_successful = verif_pw_ldap($login, $mdp);

if($login_successful == "BAD LOGIN OR PASSWORD") {
    $login_successful = $l->g(180);
}
// Check if defaultRole is not empty
if($login_successful == "OK") {
    $defaultRole = $config['LDAP_CHECK_DEFAULT_ROLE'];

    if (isset($_SESSION['OCS']['details']["filter"])) {
        $defaultRole = $config[$_SESSION['OCS']['details']["filter"]];
    }
    
    if(trim($defaultRole) == "") {
        $login_successful = $l->g(894);
    }
}
$cnx_origine = "LDAP";
$user_group = "LDAP";

function verif_pw_ldap($login, $pw) {
    $info = search_on_loginnt($login);
    if ($info["nbResultats"] != 1) {
        // login doesn't exist
        return ("BAD LOGIN OR PASSWORD");
    }
    return (ldap_test_pw($info[0]["dn"], $pw) ? "OK" : "BAD LOGIN OR PASSWORD");
}

function search_on_loginnt($login) {
    // default attributes for query
    $attributs = array("dn", "cn", "givenname", "sn", "mail", "title", "memberof");
    foreach ($_SESSION['OCS']['config'] as $config_elem => $value) {
        if (preg_match('/^LDAP_FILTER[0-9]*$/', $config_elem)) {
            if(trim($value) != "" && $value != null) {
                $filter = str_replace("&amp;", "&", $value);
                $ds = ldap_connection();
                $filtre = "(".$filter."(".LOGIN_FIELD."={$login}))";
                $sr = ldap_search($ds, DN_BASE_LDAP, $filtre, $attributs);
                $lce = ldap_count_entries($ds, $sr);
                $info = ldap_get_entries($ds, $sr);
                ldap_close($ds);
                $info["nbResultats"] = $lce;

                $filter_num = (int) preg_replace('/[^0-9]/', '', $config_elem);

                if($info["nbResultats"] == 1) {
                    $_SESSION['OCS']['details']["filter"] = "LDAP_FILTER".$filter_num."_ROLE";
                }
            } 
        }
    }
    
    // Default login ldap
    if((trim($filter) == "" && $filter == null) || (isset($info["nbResultats"]) && $info["nbResultats"] != 1)) {
        $ds = ldap_connection();
        $filtre = "(".LOGIN_FIELD."={$login})";
        $sr = ldap_search($ds, DN_BASE_LDAP, $filtre, $attributs);
        $lce = ldap_count_entries($ds, $sr);
        $info = ldap_get_entries($ds, $sr);
        ldap_close($ds);
        $info["nbResultats"] = $lce;
    }

    // save user fields in session
    $_SESSION['OCS']['details']['givenname'] = $info[0]['givenname'][0] ?? '';
    $_SESSION['OCS']['details']['sn'] = $info[0]['sn'][0] ?? '';
    $_SESSION['OCS']['details']['cn'] = $info[0]['cn'][0] ?? '';
    $_SESSION['OCS']['details']['mail'] = $info[0]['mail'][0] ?? '';
    $_SESSION['OCS']['details']['title'] = $info[0]['title'][0] ?? '';
    
    return $info;
}

function ldap_test_pw($dn, $pw) {
    $ds = ldap_connection();
    if (!$ds || !$pw) {
        return false;
    } else {
        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, LDAP_PROTOCOL_VERSION);
        $r = ldap_bind($ds, $dn, $pw);
        ldap_close($ds);
        return $r;
    }
}

function ldap_connection() {

    if(AUTH_LDAP_SKIP_CERT){
        putenv('LDAPTLS_REQCERT=never'); 
    }

    $ds = ldap_connect(LDAP_SERVEUR, LDAP_PORT);

    ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, LDAP_PROTOCOL_VERSION);
    ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);

    if (defined('ROOT_DN') && ROOT_DN != '') {
        $b = ldap_bind($ds, ROOT_DN, htmlspecialchars_decode(ROOT_PW));
    } else { //Anonymous bind
        $b = ldap_bind($ds);
    }

    if (!$b) {
        return false;
    } else {
        return $ds;
    }
}

?>
