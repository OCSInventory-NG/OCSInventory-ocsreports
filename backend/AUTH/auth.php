<?php
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
 require_once($_SESSION['OCS']['backend'].'require/connexion.php');
 //If you want a html form for the connexion
 //put $affich_method='HTML'
 $affich_method='HTML';
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
 $list_methode=array(0=>"local.php");
 //$list_methode=array(0=>"always_ok.php");
 if ($affich_method == 'HTML' and isset($protectedPost['Valid_modif_x']) and trim($protectedPost['LOGIN']) != ""){
 	$login=$protectedPost['LOGIN'];
 	$mdp=$protectedPost['PASSWD']; 
 }elseif ($affich_method == 'CAS'){
	require_once('methode/cas.php'); 	
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
	//show HTML form
	if ($affich_method == 'HTML'){
		$icon_head='NO';
		require_once ($_SESSION['OCS']['HEADER_HTML']);
		if (isset($protectedPost['Valid_modif_x'])){
			msg_error($login_successful);
			flush();
			//you can't send a new login/passwd before 2 seconds
			sleep(2);
		}
		echo "<br>";
		//echo "<form name='IDENT' id='IDENT' action='' method='post'>";
		$name_field=array("LOGIN","PASSWD");
			$tab_name=array($l->g(24).": ",$l->g(217).":");
			$type_field= array(0,4);	
			$value_field=array($protectedPost['LOGIN'],'');
			$tab_typ_champ=show_field($name_field,$type_field,$value_field);
		foreach ($tab_typ_champ as $id=>$values){
			$tab_typ_champ[$id]['CONFIG']['SIZE']=20;
		}
		if (isset($tab_typ_champ)){
		//	echo '<div class="mvt_bordure" >';
			tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden);
		//	echo "</div>";
		}	
		/*echo "<br><center><table><tr><td align=center>";
		echo "<b>".$l->g(24).":</b></td><td><input type='text' name='LOGIN' id ='LOGIN' value='".(isset($protectedPost['LOGIN']) ? $protectedPost['LOGIN']: '')."'></td></tr><tr><td align=center>";
		echo "<b>".$l->g(217).":</b></td><td><input type='password' name='PASSWD' id ='PASSWD' value='".(isset($protectedPost['PASSWD']) ? $protectedPost['PASSWD']: '')."'></td></tr>";
		echo "<tr><td colspan=2 align=center><br><input type=submit name='VALID' id='VALID'></td></tr>";
		echo "</table></center>";
		echo "</form>";*/
		require_once($_SESSION['OCS']['FOOTER_HTML']);
		die();
	}else{
   		header('WWW-Authenticate: Basic realm="OcsinventoryNG"');
    	header('HTTP/1.0 401 Unauthorized');
    	die();
	}
}

?>