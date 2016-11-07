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
//If you want a html form for the connexion
//put $affich_method='HTML'
$affich_method = 'HTML';
//If you use an SSO connexion
//use this configuration
//$affich_method='SSO';
//$list_methode=array(0=>"always_ok.php");
// Author: FranciX
// http://forums.ocsinventory-ng.org/viewtopic.php?pid=30974
//If you use an CAS connexion
//use this configuration
//$affich_method='CAS';
//$list_methode=array(0=>"always_ok.php");
//list of the identification method
//3 pages by default: ldap.php => LDAP Connexion
//					   local.php => Local connexion on ocs base
//					   always_ok.php => connexion always ok
$list_methode = array(0 => "local.php");
// $list_methode=array(0=>"ldap.php");

if ($affich_method == 'HTML' && isset($protectedPost['Valid_CNX']) && trim($protectedPost['LOGIN']) != "") {
	$login = $protectedPost['LOGIN'];
	$mdp = $_POST['PASSWD'];
	$protectedMdp = $protectedPost['PASSWD'];
} elseif ($affich_method == 'CAS') {
	require_once('methode/cas.php');
} elseif ($affich_method != 'HTML' && isset($_SERVER['PHP_AUTH_USER'])) {
	$login = $_SERVER['PHP_AUTH_USER'];
	$mdp = $_SERVER['PHP_AUTH_PW'];
} elseif ($affich_method == 'SSO' && isset($_SERVER['HTTP_AUTH_USER'])) {
	$login = $_SERVER['HTTP_AUTH_USER'];
	$mdp = 'NO_PASSWD';
}

if (isset($login) && isset($mdp)) {
	foreach ($list_methode as $uneMethode) {
		require_once 'methode/' . $uneMethode;
		if ($login_successful == "OK") {
			break;
		}
	}
}
// login ok?
if ($login_successful == "OK" && isset($login_successful)) {
	$_SESSION['OCS']["loggeduser"] = $login;
	$_SESSION['OCS']['cnx_origine'] = $cnx_origine;
	$_SESSION['OCS']['user_group'] = $user_group;

	if ($protectedGet) {
		$get = '';
		$first = true;

		foreach ($protectedGet as $key => $value) {
			if ($first) {
				$get .= '?' . $key . '=' . $value;
				$first = false;
			} else {
				$get .= '&' . $key . '=' . $value;
			}
		}
		header('Location: index.php' . $get);
	} else {
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
		?>
		<div class="container">
			<div class="col-md-4 col-md-offset-4">
				<img class="profile-img" src="image/sphere-ocs.png" />
				<div class="center-block text-center">
					<?php require_once('plugins/language/language.php'); ?>
				</div>
				<br />
				<form method="post" name="CHANGE">

					<div class="form-group">
						<label for="LOGIN"><?= $l->g(24); ?> :</label>
						<input type="text" class="form-control" name="LOGIN" id="LOGIN" value="<?= $protectedPost['LOGIN']; ?>" placeholder="<?= $l->g(24); ?>">
					</div>
					<div class="form-group">
						<label for="PASSWD"><?= $l->g(217); ?> :</label>
						<input type="password" class="form-control" name="PASSWD" id="PASSWD" value="<?= $protectedPost['PASSWD']; ?>" placeholder="<?= $l->g(217); ?>">
					</div>

					<input type="submit" class="btn btn-lg btn-block btn-success" style="background-color: #961b7e" name="Valid_CNX" value="<?= $l->g(13); ?>" />
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
