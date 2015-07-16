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



debut_tab(array('CELLSPACING'=>'5',
					'WIDTH'=>'80%',
					'BORDER'=>'0',
					'ALIGN'=>'Center',
					'CELLPADDING'=>'0',
					'BGCOLOR'=>'#C7D9F5',
					'BORDERCOLOR'=>'#9894B5'));
if ($optvalue['FREQUENCY'] == 0 and isset($optvalue['FREQUENCY']))
$optvalueselected = 'ALWAYS';
elseif($optvalue['FREQUENCY'] == -1)
$optvalueselected = 'NEVER';
elseif(!isset($optvalue['FREQUENCY']))
$optvalueselected='SERVER DEFAULT';
else
$optvalueselected ='CUSTOM';
$champ_value['VALUE']=$optvalueselected;
$champ_value['ALWAYS']=$l->g(485);
$champ_value['NEVER']=$l->g(486);
$champ_value['CUSTOM']=$l->g(487);
$champ_value['SERVER DEFAULT']=$l->g(488);
if (!isset($protectedGet['origine'])){	
	$champ_value['IGNORED']=$l->g(718);
	$champ_value['VALUE']='IGNORED';
}
ligne("FREQUENCY",$l->g(494),'radio',$champ_value,array('HIDDEN'=>'CUSTOM','HIDDEN_VALUE'=>$optvalue['FREQUENCY'],'END'=>$l->g(496),'JAVASCRIPT'=>$numeric));
fin_tab($form_name);

?>

