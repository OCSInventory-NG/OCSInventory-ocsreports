<?php
//connexion page for ocs
/*
 * Vous pouvez rajouter tout type de connection pour acc�der � OCS
 * Par d�faut, 2 types sont pr�sents:
 * => Connection LOGIN/MDP sur la base OCS
 * => Connection LOGIN/MDP sur le LDAP 
 * Les param�tres de connection sont � d�finir dans le fichier de config de la m�thode
 * Si vous voulez changer l'ordre d'identification ou ajoutez une nouvelle
 * m�thode d'indentification modifiez l'ordre dans le tableau $list_methode.
 * 
 * 
 */
 require_once($_SESSION['OCS']['backend'].'require/connexion.php');

 //Pour avoir un affichage HTML du formulaire de connexion
 //donner la valeur 'HTML' � la variable $affich_method
 //Si vous passez par un SSO
 //d�commentez les deux lignes suivantes
 //$affich_method='SSO';
 //$list_methode=array(0=>"always_ok.php");
  $affich_method='HTML';
 //liste des m�thodes d'identification
 //pages php se trouvant dans le r�pertoire METHODE de AUTH
 //3 pages par d�faut: ldap.php => Connection a un LDAP
 //					   local.php => Connection � la base OCS
 //					   always_ok.php => Connection toujours OK
 $list_methode=array(0=>"local.php");
 //$list_methode=array(0=>"always_ok.php");
 if ($affich_method == 'HTML' and isset($protectedPost['VALID']) and trim($protectedPost['LOGIN']) != ""){
 	$login=$protectedPost['LOGIN'];
 	$mdp=$protectedPost['PASSWD']; 	
 }elseif ($affich_method != 'HTML' and isset($_SERVER['PHP_AUTH_USER'])){
 	$login=$_SERVER['PHP_AUTH_USER'];
 	$mdp=$_SERVER['PHP_AUTH_PW'];  	
 }


if (isset($login) && isset($mdp)){
	$i=0;
	while ($list_methode[$i]){
		require_once('methode/'.$list_methode[$i]);
		if ($login_successful == "OK")
		break;
		$i++;
	}
}

// login ok?
if($login_successful == "OK" and isset($login_successful)) {
	$_SESSION['OCS']["loggeduser"]=$login;
	$_SESSION['OCS']['cnx_origine']=$cnx_origine;
	$_SESSION['OCS']['user_group']=$user_group;
	unset($protectedGet);
}else{	
	//affichage d'un formulaire HTML
	if ($affich_method == 'HTML'){
		$icon_head='NO';
		require_once ($_SESSION['OCS']['HEADER_HTML']);
		if (isset($protectedPost['VALID'])){
			echo "<font color=red><b>".$login_successful."</b></font>";
			flush();
			//pour emp�cher de renvoyer une demande d'identification
			//tout de suite apr�s une mauvaise entr�e
			sleep(2);
		}
		echo "<form name='IDENT' id='IDENT' action='' method='post'>";
		echo "<br><center><table><tr><td align=center>";
		echo "<b>".$l->g(24).":</b></td><td><input type='text' name='LOGIN' id ='LOGIN' value='".(isset($protectedPost['LOGIN']) ? $protectedPost['LOGIN']: '')."'></td></tr><tr><td align=center>";
		echo "<b>".$l->g(217).":</b></td><td><input type='password' name='PASSWD' id ='PASSWD' value='".(isset($protectedPost['PASSWD']) ? $protectedPost['PASSWD']: '')."'></td></tr>";
		echo "<tr><td colspan=2 align=center><br><input type=submit name='VALID' id='VALID'></td></tr>";
		echo "</table></center>";
		echo "</form>";
		//echo $_SESSION['OCS']["loggeduser"];
		require_once($_SESSION['OCS']['FOOTER_HTML']);
		die();
	}else{
   		header('WWW-Authenticate: Basic realm="OcsinventoryNG"');
    	header('HTTP/1.0 401 Unauthorized');
    	die();
	}
}

?>