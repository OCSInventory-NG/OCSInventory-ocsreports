<?php
/* page de r�cup�ration en local des droits
 * et des tags sur lesquels l'utilisateur
 * a des droits
 * 
 * on doit renvoyer un tableau array('accesslvl'=>%%,'tag_show'=>array(%,%,%,%,%...))
 * si une erreur est rencontr�e, on retourne un code erreur
 * 
 */
	


//nom de la page
$name="local.php";
connexion_local();
mysql_select_db($db_ocs,$link_ocs);
//recherche du niveau de droit de l'utilisateur
$reqOp="SELECT new_accesslvl as accesslvl FROM operators WHERE id='".$_SESSION['OCS']["loggeduser"]."'";
$resOp=mysql_query($reqOp, $link_ocs) or die(mysql_error($link_ocs));
$rowOp=mysql_fetch_object($resOp);
if (isset($rowOp -> accesslvl)){
	$lvluser=$rowOp -> accesslvl;
	$restriction=search_restriction($lvluser."_config.txt");
	//Si l'utilisateur a des droits limit�s
	//on va rechercher les tags sur lesquels il a des droits
	if ($restriction == 'YES'){
		$sql="select tag from tags where login='".$_SESSION['OCS']["loggeduser"]."'";
		$res=mysql_query($sql, $link_ocs) or die(mysql_error($link_ocs));
		while ($row=mysql_fetch_object($res)){	
			$list_tag[$row->tag]=$row->tag;
		}
		if (!isset($list_tag))
			$ERROR=$l->g(893);
	}elseif (($restriction != 'NO')) 
		$ERROR=$restriction;
}else
	$ERROR=$l->g(894);

function search_restriction($ms_cfg_file){
//	$ms_cfg_file= $_SESSION['OCS']["lvluser"]."_config.txt";	
	//show only true sections
	if (file_exists($_SESSION['OCS']['main_sections_dir'].$ms_cfg_file)) {
	      $fd = fopen ($_SESSION['OCS']['main_sections_dir'].$ms_cfg_file, "r");
	      $capture='';
	      while( !feof($fd) ) {
	
	         $line = trim( fgets( $fd, 256 ) );
			
			 if (substr($line,0,2) == "</")
	            $capture='';
	            
	         if ($capture == 'OK_RESTRICTION')
	            $value= $line;            
	         
	         if ($line{0} == "<"){ 	//Getting tag type for the next launch of the loop
	            $capture = 'OK_'.substr(substr($line,1),0,-1);
	         }        
	      }
	   fclose( $fd );
	}else
	return $l->g(894);
	
	return $value;
	
}


?>