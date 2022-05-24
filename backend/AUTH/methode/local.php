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
connexion_local_read();

$reqOp = "SELECT ID, PASSWORD_VERSION FROM operators WHERE ID='%s'";
$arg_reqOp = array($login);

$resOp = mysql2_query_secure($reqOp, $_SESSION['OCS']["readServer"], $arg_reqOp);
$rowOp = mysqli_fetch_object($resOp);

if (isset($rowOp->PASSWORD_VERSION) && $rowOp->PASSWORD_VERSION === '0') {
    $reqOp = "SELECT ID, USER_GROUP FROM operators WHERE ID='%s' and PASSWD ='%s'";
    $arg_reqOp = array($login, md5($protectedMdp));

    $resOp = mysql2_query_secure($reqOp, $_SESSION['OCS']["readServer"], $arg_reqOp);
    $rowOp = mysqli_fetch_object($resOp);

    if (isset($rowOp->ID)) {
        $login_successful = "OK";
        $user_group = $rowOp->USER_GROUP;
        $type_log = 'CONNEXION';

        if (version_compare(PHP_VERSION, '5.3.7') >= 0) {
            require_once('require/function_users.php');
            updatePasswordMd5toHash($login, $mdp);
        }
    } else {
        $login_successful = $l->g(180);
        $type_log = 'BAD CONNEXION';
    }
} else {
    $reqOp = "SELECT ID, USER_GROUP, PASSWD, PASSWORD_VERSION FROM operators WHERE ID = '%s'";
    
    $arg_reqOp = array($login);
    $resOp = mysql2_query_secure($reqOp, $_SESSION['OCS']["readServer"], $arg_reqOp);
    $rowOp = mysqli_fetch_object($resOp);

    $login_status = false;

    // if password version is set to 1 = md5 version so update to PASSWORD_CRYPT
    if(isset($rowOp->ID) && $rowOp->PASSWORD_VERSION == 1) {
        if(password_verify($mdp, $rowOp->PASSWD)) {
            require_once('require/function_users.php');
            $update = updatePasswordMd5toHash($login, $mdp);
            if($update) {
                $login_status = true;
            }
        }
    }

    if ($login_status == true || (isset($rowOp->PASSWD) && hash(PASSWORD_CRYPT, $mdp) == $rowOp->PASSWD)) {
        $login_successful = "OK";
        $user_group = $rowOp->USER_GROUP;
        $type_log = 'CONNEXION';
    } else {
        $login_successful = $l->g(180);
        $type_log = 'BAD CONNEXION';
    }
}

$value_log = 'USER:' . $login;
$cnx_origine = "LOCAL";

addLog($type_log, $value_log);
?>