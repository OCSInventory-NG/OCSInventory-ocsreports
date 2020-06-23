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
//connexion page for ocs
/*
 * You can add your connexion page for ocs access
 * You have 2 default connexion
 * => Connexion LOGIN/PASSWD on OCS base
 * => Connexion LOGIN/PASSWD on LDAP
 * If you want add you method to connect to ocs
 * add your page on /require and modify $list_methode
 *
 */
require_once(BACKEND . 'require/connexion.php');
require_once(BACKEND . 'require/auth.manager.php');


// You don't have to change these variables anymore, see var.php
$affich_method = get_affiche_methode();
$list_methode = get_list_methode();

if ($affich_method == 'HTML' && isset($protectedPost['Valid_CNX']) && trim($protectedPost['LOGIN']) != "") {
    $login = $protectedPost['LOGIN'];
    $mdp = $_POST['PASSWD'];
    $protectedMdp = $protectedPost['PASSWD'];
} elseif ($affich_method == 'CAS') {
    require_once('methode/cas.php');
} elseif ($affich_method == 'SSO' && isset($_SERVER['REMOTE_USER']) && !empty($_SERVER['REMOTE_USER'])) {
    $login = $_SERVER['REMOTE_USER'];
    $mdp = 'NO_PASSWD';
} elseif ($affich_method == 'SSO' && isset($_SERVER['HTTP_AUTH_USER']) && !empty($_SERVER['HTTP_AUTH_USER'])) {
    $login = $_SERVER['HTTP_AUTH_USER'];
    $mdp = 'NO_PASSWD';
} elseif ($affich_method != 'HTML' && isset($_SERVER['PHP_AUTH_USER'])) {
    $login = $_SERVER['PHP_AUTH_USER'];
    $mdp = $_SERVER['PHP_AUTH_PW'];
} 

if (isset($login) && isset($mdp)) {
    $i = 0;
    while ($list_methode[$i]) {
        require_once('methode/' . $list_methode[$i]);
        if ($login_successful == "OK")
            break;
        $i++;
    }
}
// login ok?
if ($login_successful == "OK" && isset($login_successful)) {
    $_SESSION['OCS']["loggeduser"] = $login;
    $_SESSION['OCS']['cnx_origine'] = $cnx_origine;
    $_SESSION['OCS']['user_group'] = $user_group;

    if($protectedGet){

        $get = '';
        $first = true;

        foreach ($protectedGet as $key => $value){
            if($first){
                $get .= '?' . $key . '=' . $value;
                $first = false;
            } else{
                $get .= '&' . $key . '=' . $value;
            }
        }
        header('Location: index.php'.$get);
    } else{
        unset($protectedGet);
    }

} else {
    //show HTML form
    if ($affich_method == 'HTML') {
        require_once (HEADER_HTML);
        if (isset($protectedPost['Valid_CNX'])) {
            $login_successful = $l->g(180);
            msg_error($login_successful);
            flush();
            //you can't send a new login/passwd before 2 seconds
            sleep(2);
        }
        if (DEMO) {
            msg_info($l->g(24) . ": " . DEMO_LOGIN . "<br/>" . $l->g(217) . ": " . DEMO_PASSWD);
        }
        $value_logo = look_config_default_values('CUSTOM_THEME');
        if(is_null($value_logo)){
          $value_logo['tvalue']['CUSTOM_THEME'] = DEFAULT_THEME;
        }
        ?>
        <div class="container">
            <div class="col-md-4 col-md-offset-4">
                <?php echo '<img class="profile-img" src="themes/'.$value_logo['tvalue']['CUSTOM_THEME'].'/logo.png" />'; ?>
                <div class="center-block text-center">
                    <?php require_once(LANGUAGE_DIR . 'language.php'); ?>
                </div>
                <br />
                <form method="post" name="CHANGE">

                    <div class="form-group">
                        <label for="LOGIN"><?php echo $l->g(243); ?> :</label>
                        <input type="text" class="form-control" name="LOGIN" id="LOGIN" value='<?php echo preg_replace("/[^A-Za-z0-9\.]/", "", $protectedPost['LOGIN']); ?>' placeholder="<?php echo $l->g(243); ?>">
                    </div>
                    <div class="form-group">
                        <label for="PASSWD"><?php echo $l->g(217); ?> :</label>
                        <input type="password" class="form-control" name="PASSWD" id="PASSWD" value='<?php echo preg_replace("/[^A-Za-z0-9\.]/", "", $protectedPost['PASSWD']); ?>' placeholder="<?php echo $l->g(217); ?>">
                    </div>

                    <input type="submit" class="btn btn-lg btn-block btn-success" id="btn-logon" name="Valid_CNX" value="<?php echo $l->g(13); ?>" />
                </form>
            </div>
        </div><!-- /container -->

        <?php
        require_once(FOOTER_HTML);
        die();
    } else {
        header('WWW-Authenticate: Basic realm="OcsinventoryNG"');
        header('HTTP/1.0 401 Unauthorized');
        die();
    }
}
?>
