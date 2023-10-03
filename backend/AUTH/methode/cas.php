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
require_once(PHPCAS);

function get_cas_config() {
    connexion_local_read();
    $sql = "select NAME,TVALUE from config where NAME like '%s'";
    $arg = array('%CAS_%');
    $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
    while ($item = mysqli_fetch_object($res)) {
        $config[$item->NAME] = $item->TVALUE;
        define($item->NAME, $item->TVALUE);
    }
    return $config;
}

$config = get_cas_config();
$cas = new phpCas();
// Enable debugging
$cas->setLogger();
// Enable verbose error messages. Disable in production
$cas->setVerbose(true);

if (!isset($sql_update)) {
    $cas->client(CAS_VERSION_2_0, $config['CAS_HOST'], (int)$config['CAS_PORT'], $config['CAS_URI']);
    // uncomment following line if not using server validation
    $cas->setNoCasServerValidation();
    $cas->forceAuthentication();
    $login = $cas->getUser();
    $mdp = "";

    if ($login) {
        $login_successful = "OK";
        $cnx_origine = "CAS";
        $user_group = "CAS";
    }
}

?>
