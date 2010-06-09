<?php
//
// -------------------------------------------------------------------------------
// Description : 	Affichage de la carte des sites DGA
// -------------------------------------------------------------------------------
// Nom du fichier : contenu\pages\accueil\cartedga.php
// Auteur :			DUBREIL Dominique
// Version :		1.0
// Date cr�ation :	10/10/07
// -------------------------------------------------------------------------------
// Affiche la carte des sites de la DGA
//

//@session_start();

//include ('preferences.php');
  Header("Content-type: image/png");         /* Header HTTP � renvoyer : PNG */
  $im = imagecreatefrompng("image/france.png");
	

  $rouge = ImageColorAllocate($im, 255, 0, 0);
  $cyan = ImageColorAllocate($im, 0, 255, 255);
  $noir   = ImageColorAllocate($im, 0, 0, 0);
  $vert   = ImageColorAllocate($im, 0, 255, 0);
  $gris   = ImageColorAllocate($im, 192, 192, 192);
  $orange   = ImageColorAllocate($im, 236, 134, 50);
  $bleu   = ImageColorAllocate($im, 0, 51, 153);

 
  $sql  = "SELECT * FROM dga_sites";
  
  $selection = mysql_query($sql);

  while ($article=mysql_fetch_object($selection))
  {
        $x  =0.9*$article->x20;
        $y  =0.9*$article->y20;
        $IdSite    = $article->IdSite;
        $NomSite   = $article->NomSite;
		$Site = $article->TAG;
		
		$position  = $article->position;

  		
        if ( $Site != "" )
		{
			imagefilledrectangle($im,$x-3,$y-3,$x+3,$y+3,$gris);
			imagefilledrectangle($im,$x-11,$y-3,$x-5,$y+3,$gris);
			$color=$noir;
	  			  
		 	imagerectangle($im,$x-3,$y-3,$x+3,$y+3,$noir);
			imagerectangle($im,$x-11,$y-3,$x-5,$y+3,$noir);
			
	  	  	$l=5*strlen($NomSite);
          	if ($position=="R") ImageString($im,1,$x+5,$y-3,$NomSite,$color);
         	if ($position=="L") ImageString($im,1,$x-$l-11,$y-3,$NomSite,$color);
        	if ($position=="B") ImageString($im,1,$x-$l/2,$y+4,$NomSite,$color);
	  	 	if ($position=="T") ImageString($im,1,$x-$l/2,$y-11,$NomSite,$color);
  
		}
   }
   
   // Postes de travail
   
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
        $IdSite    = $article->IdSite;
        $NomSite   = $article->NomSite;
		$Site = $article->TAG;
		$Ent    = $article->Entite;
		$position  = $article->position;
		
		$sql2='SELECT COUNT(h.id) FROM hardware h LEFT JOIN accountinfo a ON a.hardware_id=h.id WHERE a.TAG=\''.$Site.'_DSK\'';
		$selection2 = mysql_query($sql2);
		$nbmachines=mysql_result($selection2,0);
		// DEBUG print ('<H1>'.$nbmachines.'</H1>');
		
		if ($nbmachines!=0)
		{
//			Recherche si site concern�.
			imagefilledrectangle($im,$x-3,$y-3,$x+3,$y+3,$cyan);
			$color=$bleu;
		  	imagerectangle($im,$x-3,$y-3,$x+3,$y+3,$noir);
		}
   }
   
    // Serveurs
   
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
        $IdSite    = $article->IdSite;
        $NomSite   = $article->NomSite;
		$Site = $article->TAG;
		$Ent    = $article->Entite;
		$position  = $article->position;
		
		$sql2='SELECT COUNT(h.id) FROM hardware h LEFT JOIN accountinfo a ON a.hardware_id=h.id WHERE a.TAG=\''.$Site.'_SRV\'';
		$selection2 = mysql_query($sql2);
		$nbmachines=mysql_result($selection2,0);
		// DEBUG print ('<H1>'.$nbmachines.'</H1>');
		
		if ($nbmachines!=0)
		{
//			Recherche si site concern�.
			imagefilledrectangle($im,$x-11,$y-3,$x-5,$y+3,$rouge);
			$color=$bleu;
		  	imagerectangle($im,$x-11,$y-3,$x-5,$y+3,$noir);
		}
   }
   
   
  ImagePng($im);
  ImageDestroy($im);
?>
