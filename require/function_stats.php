<?php
/*
 * Fichier de fonctions pour les statistiques
 * 
 * 
 * 
 * 
 * 
 * 
 */
if ($_SESSION['OCS']['useflash'] == 1){
	require_once(FCHARTS."/Code/PHP/Includes/FusionCharts.php");
	require_once(FCHARTS."/Code/PHP/Includes/FC_Colors.php");
}else{
	
	require_once (JPGRAPH."/src/jpgraph.php");
	require_once (JPGRAPH."/src/jpgraph_pie.php");
	require_once (JPGRAPH."/src/jpgraph_pie3d.php");
	require_once (JPGRAPH."/src/jpgraph_line.php");
	require_once (JPGRAPH."/src/jpgraph_date.php");
}
 //fonction camenbert
# - Date de cr�ation : 18/09/2005
# - nom : camembert.php
# - auteur : opossum_farceur.
# - Object : les camemberts 3D.
# - Source : http://fr3.php.net/manual/fr/function.imagefilledarc.php
# 
  function camembert($arr)
   {    
      $size=2; /* taille de la police, largeur du caract�re */
      $ifw=imagefontwidth($size);                            
       
      $w=850; /* largeur de l'image */
      $h=400; /* hauteur de l'image */
      $a=200; /* grand axe du camembert */
      $b=$a/2; /* 60 : petit axe du camembert */
      $d=$a/2; /* 60 : "�paisseur" du camembert */
      $cx=$w/2-1; /* abscisse du "centre" du camembert */
      $cy=($h-$d)/2; /* 95 : ordonn�e du "centre" du camembert */
       
      $A=138+80; /* grand axe de l'ellipse "englobante" */
      $B=102+80; /* petit axe de l'ellipse "englobante" */
      $oy=-$d/2; /* -30 : du "centre" du camembert � celui de l'ellipse "englobante"*/
    
      $img=imagecreate($w,$h);
      $bgcolor=imagecolorallocate($img,0xCD,0xCD,0xCD);    
      imagecolortransparent($img,$bgcolor);
      $black=imagecolorallocate($img,0,0,0);
                                /* calcule la somme des donn�es */
      for ($i=$sum=0,$n=count($arr);$i<$n;$i++) $sum+=$arr[$i][0];    
       
      /* fin des pr�liminaires : on peut vraiment commencer! */
      for ($i=$v[0]=0,$x[0]=$cx+$a,$y[0]=$cy,$doit=true;$i<$n;$i++) {                                                        
         for ($j=0,$k=16;$j<3;$j++,$k-=8) $t[$j]=($arr[$i][1]>>$k) & 0xFF;
                                /* d�termine les "vraies" couleurs */
         $color[$i]=imagecolorallocate($img,$t[0],$t[1],$t[2]);
                                /* calcule l'angle des diff�rents "secteurs" */
         $v[$i+1]=$v[$i]+round($arr[$i][0]*360/$sum);    
                                                           
         if ($doit) { /* d�termine les couleurs "ombr�es" */
            $shade[$i]=imagecolorallocate($img,max(0,$t[0]-50),max(0,$t[1]-50),max(0,$t[2]-50));
                                                           
            if ($v[$i+1]<180) { /* calcule les coordonn�es des diff�rents parall�logrammes */
               $x[$i+1]=$cx+$a*cos($v[$i+1]*M_PI/180);        
               $y[$i+1]=$cy+$b*sin($v[$i+1]*M_PI/180);    
            }                                        
            else {
               $m=$i+1;
               $x[$m]=$cx-$a; /* c'est comme si on rempla�ait $v[$i+1] par 180� */
               $y[$m]=$cy;    
               $doit=false; /* indique qu'il est inutile de continuer! */
            }
         }
      }
       
      /* dessine la "base" du camembert */
      for ($i=0;$i<$m;$i++) imagefilledarc($img,$cx,$cy+$d,2*$a,2*$b,$v[$i],$v[$i+1],$shade[$i],IMG_ARC_PIE);
       
      /* dessine la partie "verticale" du camembert */                                                        
      for ($i=0;$i<$m;$i++) {                        
         $area=array($x[$i],$y[$i]+$d,$x[$i],$y[$i],$x[$i+1],$y[$i+1],$x[$i+1],$y[$i+1]+$d);
         imagefilledpolygon($img,$area,4,$shade[$i]);            
      }
       
      /* dessine le dessus du camembert */
      for ($i=0;$i<$n;$i++) imagefilledarc($img,$cx,$cy,2*$a,2*$b,$v[$i],$v[$i+1],$color[$i],IMG_ARC_PIE);
    
      /*imageellipse($img,$cx,$cy-$oy,2*$A,2*$B,$black);    // dessine l'ellipse "englobante" */
       
      /* dessine les "fl�ches" et met en place le texte */
      for ($i=0,$AA=$A*$A,$BB=$B*$B;$i<$n;$i++) if ($arr[$i][0]) {
         $phi=($v[$i+1]+$v[$i])/2;
                                /* intersection des "fl�ches" avec l'ellipse "englobante" */
         $px=$a*3*cos($phi*M_PI/180)/4;        
         $py=$b*3*sin($phi*M_PI/180)/4;        
                                /* �quation du 2�me degr� avec 2 racines r�elles et distinctes */    
         $U=$AA*$py*$py+$BB*$px*$px;
         $V=$AA*$oy*$px*$py;                        
         $W=$AA*$px*$px*($oy*$oy-$BB);    
                                /* calcule le pourcentage � afficher */
         $value=number_format(100*$arr[$i][0]/$sum,1,",","")."%";
                                /* �crit le texte � droite */    
         if ($phi<=90 || $phi>270) {
            $root=(-$V+sqrt($V*$V-$U*$W))/$U;
            imageline($img,$px+$cx,$py+$cy,$qx=$root+$cx,$qy=$root*$py/$px+$cy,$black);
            imageline($img,$qx,$qy,$qx+10,$qy,$black);        
           
            imagestring($img,$size,$qx+14,$qy-12,$arr[$i][2],$black);
            imagestring($img,$size,$qx+14,$qy-2,$value,$black);
         }
         else { /* �crit le texte � gauche */
            $root=(-$V-sqrt($V*$V-$U*$W))/$U;
            imageline($img,$px+$cx,$py+$cy,$qx=$root+$cx,$qy=$root*$py/$px+$cy,$black);
            imageline($img,$qx,$qy,$qx-10,$qy,$black);        
                        
            imagestring($img,$size,$qx-12-$ifw*strlen($arr[$i][2]),$qy-12,$arr[$i][2],$black);
            imagestring($img,$size,$qx-12-$ifw*strlen($value),$qy-2,$value,$black);
        }
     }
   
     header("Content-type: image/png");
     imagepng($img);
     imagedestroy($img);
  }
?>