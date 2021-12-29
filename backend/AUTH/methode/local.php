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
$reqOp = "SELECT id,PASSWORD_VERSION FROM operators WHERE id='%s'";
$arg_reqOp = array($login);
$resOp = mysql2_query_secure($reqOp, $_SESSION['OCS']["readServer"], $arg_reqOp);
$rowOp = mysqli_fetch_object($resOp);
$oldpassword = false;
if (array_key_exists("PASSWORD_VERSION", $_SESSION['OCS']) && $_SESSION['OCS']['PASSWORD_VERSION'] === false || (isset($rowOp) && $rowOp->PASSWORD_VERSION < $_SESSION['OCS']['PASSWORD_VERSION'])) {
    $oldpassword = true;
}

if ($oldpassword && $rowOp->PASSWORD_VERSION === '0') {
    $reqOp = "SELECT id,user_group FROM operators WHERE id='%s' and passwd ='%s'";
    $arg_reqOp = array($login, md5($protectedMdp));
    $resOp = mysql2_query_secure($reqOp, $_SESSION['OCS']["readServer"], $arg_reqOp);
    $rowOp = mysqli_fetch_object($resOp);
    if (isset($rowOp->id)) {
        $login_successful = "OK";
        $user_group = $rowOp->user_group;
        $type_log = 'CONNEXION';
        if (version_compare(PHP_VERSION, '5.3.7') >= 0) {
            require_once('require/function_users.php');
            updatePassword($login, $mdp);
        }
    } else {
        $login_successful = $l->g(180);
        $type_log = 'BAD CONNEXION';
    }
} else {
    $reqOp = "SELECT id,user_group,passwd FROM operators WHERE id='%s'";
    $arg_reqOp = array($login);
    $resOp = mysql2_query_secure($reqOp, $_SESSION['OCS']["readServer"], $arg_reqOp);
    $rowOp = mysqli_fetch_object($resOp);
    if (isset($rowOp->id) && password_verify($mdp, $rowOp->passwd)) {
        if ($oldpassword) {
            require_once('require/function_users.php');
            updatePassword($login, $mdp);
        }
        $login_successful = "OK";
        $user_group = $rowOp->user_group;
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