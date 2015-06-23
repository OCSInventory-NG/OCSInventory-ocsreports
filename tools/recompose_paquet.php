<?php
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Erwan GOALOU 2010 (erwan(at)ocsinventory-ng(pt)org)
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================

@session_start();
if($_SESSION['OCS']["lvluser"]==SADMIN){
	$valid='OK';
	$document_root = $_SERVER["DOCUMENT_ROOT"]."/download/";
		$rep = $document_root = $_SERVER["DOCUMENT_ROOT"]."/download/".$protectedGet['id_pack'];
		$dir = opendir($rep);
		while($f = readdir($dir)){
			if ($protectedGet['id_pack'] == ''){
				if ($f != '.' and $f != '..')
		 		  echo "<a href='recompose_paquet.php?id_pack=".$f."'>".$f."</a><br>";
			}else{
				if ($f == "info"){
					//récupération du fichier info
					$filename = $rep.'/'.$f;
					$handle = fopen ($filename, "r");
					$info = fread ($handle, filesize ($filename));
					fclose ($handle);
					//surpression des balises
					$info=substr($info, 1);   
					$info=substr($info,0, -1);
					//récupration par catégories du fichier
					$info_traite=explode(" ",$info);
					//récupération du nom du fichier
					$name=$info_traite[10];
					if (substr($name,0,4) != 'NAME'){
						"<font color=red>PROBLEME AVEC LE NOM DU FICHIER</font><br>";
						$valid='KO';
					}
					if (substr($info_traite[6],0,5) != 'FRAGS'){
						"<font color=red>PROBLEME AVEC LE NOMBRE DE FRAGMENT</font><br>";
						$valid='KO';
					}
					$name=substr($name,6);
					$name=substr($name,0, -1);
					$name=str_replace(".", "_", $name).".zip";
					//récupération du nombre de fragments
					$nb_frag=$info_traite[6];
					$nb_frag=substr($nb_frag,7);
					$nb_frag=substr($nb_frag,0,-1);
				}			
			}
		}
		closedir($dir);
		
		if ($protectedGet['id_pack'] != '' and $valid == 'OK'){
			$temp="";
			$i=1;
			$filename = $rep.'/'.$protectedGet['id_pack'];
			$handfich_final = fopen( $rep.'/'.$name, "a+b" );
			while ($i <= $nb_frag){
				echo "Lecture du fichier ".$filename."-".$i." en cours...<br>";
				$handlefrag = fopen ($filename."-".$i, "r+b");
				$temp = fread ($handlefrag, filesize ($filename."-".$i));
				fclose ($handlefrag);			
				fwrite( $handfich_final, $temp );			
				flush();
				$i++;
			}
			fclose( $handfich_final );
			echo "<br><font color=green>FICHIER CREE</font>";
		}
		
}else
echo "PAGE INDISPONIBLE";


?>
