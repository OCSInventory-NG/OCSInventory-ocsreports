<?php
/*
 * Created on 19 mars 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 //
// -------------------------------------------------------------------------------
// Description : 	Map d'affichage de la carte des sites DGA
// -------------------------------------------------------------------------------
// Nom du fichier : .\ms_maps.php
// Auteur :			DUBREIL Dominique
// Version :		1.0
// Date création :	10/10/07
// -------------------------------------------------------------------------------
// Permet de définir les zones de clics sur l'image de la carte
//

//if no SADMIN=> view only your computors


 //DEBUG  print ('<H1>'.$_SESSION["mesmachines"].'<H1>');

  
  // Poste de travail
  echo "<map name=france>";

  if ($_SESSION['OCS']["mesmachines"]!="")
  {
  	
  	  $listesite=$_SESSION['OCS']["mesmachines"];
  	  $listesite=str_replace("_DSK","",$listesite);
	  $sql = "SELECT * FROM dga_sites a WHERE ".$listesite;
  }
  else
  	  $sql = "SELECT * FROM dga_sites";
 
  $selection = mysql_query($sql);

  while ($article=mysql_fetch_object($selection))
  {
        $x  =0.9*$article->x20;
        $y  =0.9*$article->y20;
		$idsite	= $article->IdSite;
        $NomSite = $article->NomSite;
		
		$Tag = $article->TAG;
		// 
		$sql2='SELECT COUNT(h.id) FROM hardware h LEFT JOIN accountinfo a ON a.hardware_id=h.id WHERE a.TAG=\''.$Tag.'_DSK\'';
		//echo $sql2;
		$selection2 = mysql_query($sql2);
		$nbmachines=mysql_result($selection2,0);
		// DEBUG print ('<H1>'.$nbmachines.'</H1>');
		
		if ($nbmachines!=0)
			print ('<area shape="RECT" coords="'.($x-3).','.($y-3).','.($x+3).','.($y+3).'" title="'.$NomSite.'- Nb Poste de travail:'.$nbmachines.'" href="?cuaff='.strtoupper($Tag).'_DSK" >');

  }
  
  // Serveur
  if ($_SESSION['OCS']["mesmachines"]!="")
  {
  	
  	  $listesite=$_SESSION['OCS']["mesmachines"];
  	  $listesite=str_replace("_SRV","",$listesite);
	  $sql = "SELECT * FROM dga_sites a WHERE ".$listesite;
  }
  else
  	  $sql = "SELECT * FROM dga_sites";
 
  $selection = mysql_query($sql);
  
  while ($article=mysql_fetch_object($selection))
  {
        $x  =0.9*$article->x20;
        $y  =0.9*$article->y20;
		$idsite	= $article->IdSite;
        $NomSite = $article->NomSite;
		
		$Tag = $article->TAG;
		// 
		$sql2='SELECT COUNT(h.id) FROM hardware h LEFT JOIN accountinfo a ON a.hardware_id=h.id WHERE a.TAG=\''.$Tag.'_SRV\'';
		$selection2 = mysql_query($sql2);
		$nbmachines=mysql_result($selection2,0);
		// DEBUG print ('<H1>'.$nbmachines.'</H1>');
		
		if ($nbmachines!=0)
			print ('<area shape="RECT" coords="'.($x-11).','.($y-3).','.($x-5).','.($y+3).'" title="'.$NomSite.'- Nb Serveurs :'.$nbmachines.'" href="?cuaff='.strtoupper($Tag).'_SRV" >');

  }
  
  echo "</map>";
  
  echo "<CENTER><img src='index.php?".PAG_INDEX."=".$pages_refs['ms_maps_show']."&no_header=1' USEMAP='#france' border=0></CENTER>";
 //echo "<CENTER><img src='image/france.png' USEMAP='#france' border=0></CENTER>";
//image/france.png
?>