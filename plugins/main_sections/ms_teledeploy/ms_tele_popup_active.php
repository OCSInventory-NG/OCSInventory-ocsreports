<?php

require_once('require/function_telediff.php');
if (is_numeric($protectedGet["active"])){
	$form_name="form_active";
	//ouverture du formulaire
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	
	if (!isset($protectedPost['FILE_SERV'])){
		$default="localhost/DOWNLOAD";
		$values=look_config_default_values(array('DOWNLOAD_URI_INFO','DOWNLOAD_URI_FRAG'));
		$protectedPost['FILE_SERV']=$values['tvalue']['DOWNLOAD_URI_FRAG'];
		$protectedPost['HTTPS_SERV']=$values['tvalue']['DOWNLOAD_URI_INFO'];
		if ($protectedPost['FILE_SERV'] == "")
			$protectedPost['FILE_SERV']=$default;
		if ($protectedPost['HTTPS_SERV'] == "")
			$protectedPost['HTTPS_SERV']=$default;
	}
	if ($_SESSION['OCS']["use_redistribution"] == 1){
		$reqGroupsServers = "SELECT DISTINCT name,id FROM hardware WHERE deviceid='_DOWNLOADGROUP_'";
		$resGroupsServers = mysql2_query_secure( $reqGroupsServers, $_SESSION['OCS']["readServer"] );
		while( $valGroupsServers = mysql_fetch_array( $resGroupsServers ) ) {
				$groupListServers[$valGroupsServers["id"]]=$valGroupsServers["name"];
		}	
	}
	
	if (isset($protectedPost['Valid_modif_x']) and $protectedPost['Valid_modif_x'] != ''){
		$error ="";
		$opensslOk = function_exists("openssl_open");
		if( $opensslOk )
			$httpsOk = @fopen("https://".$protectedPost["HTTPS_SERV"]."/".$protectedGet["active"]."/info", "r");
		else
			$error = "WARNING: OpenSSL for PHP is not properly installed. Your https server validity was not checked !<br>";
			
		if (!$httpsOk)
			$error .= $l->g(466)." https://".$protectedPost["HTTPS_SERV"]."/".$protectedGet["active"]."/<br>";
		else
			fclose( $httpsOk );
			
		if ($protectedPost['choix_activ']= "MAN"){
			$reqFrags = "SELECT fragments FROM download_available WHERE fileid='".$protectedGet["active"]."'";
			$resFrags = mysql_query( $reqFrags, $_SESSION['OCS']["readServer"] );	
			$valFrags = mysql_fetch_array( $resFrags );
			$fragAvail = ($valFrags["fragments"] > 0) ;
			if( $fragAvail ){
				$fragOk = @fopen("http://".$protectedPost["FILE_SERV"]."/".$protectedGet["active"]."/".$protectedGet["active"]."-1", "r");
			}
			else
				$fragOk = true;			
		}else
			$fragOk = true;
		
		if (!$fragOk)
			$error .= $l->g(467)." http://".$protectedPost['FILE_SERV']."/".$protectedGet["active"]."/<br>";
		elseif( $fragAvail ) 
			fclose( $fragOk );	
		
		if (! $fragOk or ! $httpsOk){
			$error .= "<br>".$l->g(468)."<br>";
			$error .= "<input type='submit' name='YES' value='".$l->g(455)."'>&nbsp&nbsp&nbsp<input type='submit' name='NO' value='".$l->g(454)."'>";
		}
		
		if ($error != '')
			msg_warning($error);
					
	}	
	
	if ((!$error and $protectedPost['valid'] and $protectedPost['choix_activ'] == "MAN") or $protectedPost['YES']){
		activ_pack($protectedGet["active"],$protectedPost["HTTPS_SERV"],$protectedPost['FILE_SERV']);
		echo "<script> alert('".$l->g(469)."');window.opener.document.packlist.submit(); self.close();</script>";	
	}
		
	if (!$error and $protectedPost['valid'] and $protectedPost['choix_activ'] == "AUTO"){
		activ_pack_server($protectedGet["active"],$protectedPost["HTTPS_SERV"],$protectedPost['choix_groupserv']);
		echo "<script> alert('".$l->g(469)."');window.opener.document.packlist.submit(); self.close();</script>";	
	}			
	
	
	
	if ($_SESSION['OCS']["use_redistribution"] == 1){
		$list_choise['MAN']=$l->g(650);
		$list_choise['AUTO']=$l->g(649);
		$choix_activ=$l->g(514).' : '.show_modif($list_choise,'choix_activ',2,$form_name)."<br>";
		echo $choix_activ;
	}else{
		$protectedPost['choix_activ']= "MAN";
				
	}
	echo "<br>";
	if (isset($protectedPost['choix_activ']) and $protectedPost['choix_activ'] != ''){
		if ($protectedPost['choix_activ'] == "MAN"){
			$tab_name=array($l->g(471),$l->g(470));
			$name_field=array("FILE_SERV","HTTPS_SERV");
			$type_field=array(0,0);
			$value_field=array($protectedPost['FILE_SERV'],$protectedPost['HTTPS_SERV']);
		}else{
			if (count($groupListServers) == 0)
				msg_error($l->g(660));
			else{
				$tab_name=array($l->g(651),$l->g(470));
				$name_field=array("FILE_SERV","HTTPS_SERV");
				$type_field=array(2,0);
				$value_field=array($groupListServers,$protectedPost['HTTPS_SERV']);		
			}
		}
		
		if (isset($name_field)){
			$tab_typ_champ=show_field($name_field,$type_field,$value_field);
			foreach ($tab_typ_champ as $id=>$values){
						$tab_typ_champ[$id]['CONFIG']['SIZE']=30;
						if ($tab_typ_champ[$id]['INPUT_TYPE'] == 0)
						$tab_typ_champ[$id]['COMMENT_BEHING']='/'.$protectedGet["active"];
			}		
			tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$l->g(465).' => '.$protectedGet["active"]);
		}
	}
	//fermeture du formulaire.
	echo "</form>";
}else
	msg_error($l->g(1129));


?>
