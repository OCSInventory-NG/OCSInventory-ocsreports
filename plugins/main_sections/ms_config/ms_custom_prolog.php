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
					'WIDTH'=>'70%',
					'BORDER'=>'0',
					'ALIGN'=>'Center',
					'CELLPADDING'=>'0',
					'BGCOLOR'=>'#C7D9F5',
					'BORDERCOLOR'=>'#9894B5'));

if(!isset($optvalue['PROLOG_FREQ']))
$optvalueselected='SERVER DEFAULT';
else
$optvalueselected='CUSTOM';
$champ_value['VALUE']=$optvalueselected;
$champ_value['CUSTOM']=$l->g(487);
$champ_value['SERVER DEFAULT']=$l->g(488);
if (!isset($protectedGet['origine'])){	
	$champ_value['IGNORED']=$l->g(718);
	$champ_value['VALUE']='IGNORED';	
}
ligne("PROLOG_FREQ",$l->g(724),'radio',$champ_value,array('HIDDEN'=>'CUSTOM','HIDDEN_VALUE'=>$optvalue['PROLOG_FREQ'],'END'=>$l->g(730),'JAVASCRIPT'=>$numeric));
unset($champ_value);

if(!isset($optvalue['INVENTORY_ON_STARTUP']))
$optvalueselected='SERVER DEFAULT';
elseif($optvalue['INVENTORY_ON_STARTUP'] == 0)
$optvalueselected='OFF';
elseif($optvalue['INVENTORY_ON_STARTUP'] == 1)
$optvalueselected='ON';
$champ_value['VALUE']=$optvalueselected;
$champ_value['ON']='ON';
$champ_value['OFF']='OFF';
$champ_value['SERVER DEFAULT']=$l->g(488);
if (!isset($protectedGet['origine'])){	
	$champ_value['IGNORED']=$l->g(718);
	$champ_value['VALUE']='IGNORED';
}
ligne("INVENTORY_ON_STARTUP",$l->g(2121),'radio',$champ_value);
unset($champ_value);

fin_tab($form_name);
 
 
?>
