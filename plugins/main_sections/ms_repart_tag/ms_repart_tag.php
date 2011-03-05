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

	$form_name="repart_tag";
	$table_name=$form_name;
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	require_once('require/function_admininfo.php');
	$info_tag=witch_field_more('COMPUTERS');
	/*echo $l->g(340)." ".show_modif($info_tag['LIST_FIELDS'],'TAG_CHOISE',2,$form_name,array('DEFAULT' => "NO"));
	echo $protectedPost['TAG_CHOISE'];
	if (isset($protectedPost['TAG_CHOISE']) and $protectedPost['TAG_CHOISE'] != 1){
		$tag='a.fields_'.$protectedPost['TAG_CHOISE'];		
	}else{*/
		$tag='a.tag';
		$protectedPost['TAG_CHOISE'] = 1;
//	}
	$list_fields = array ( $info_tag['LIST_FIELDS'][$protectedPost['TAG_CHOISE']]   => "ID", 
						   'Nbr_mach'=>'c');
	$tab_options['FILTRE'][$tag]=$info_tag['LIST_FIELDS'][$protectedPost['TAG_CHOISE']];
//	$tab_options['NO_TRI']['LBL_UNIT']='LBL_UNIT';
//	$tab_options['LBL']['LBL_UNIT']="libell� unit�";
	$tab_options['LIEN_LBL']['Nbr_mach']="index.php?".PAG_INDEX."=".$pages_refs['ms_all_computers']."&filtre=".$tag."&value=";
	$tab_options['LIEN_CHAMP']['Nbr_mach']="ID";
	$tab_options['LBL']['Nbr_mach']=$l->g(1120);
	$list_col_cant_del=array($info_tag['LIST_FIELDS'][$protectedPost['TAG_CHOISE']]=>$info_tag['LIST_FIELDS'][$protectedPost['TAG_CHOISE']]);
	$default_fields= $list_fields;
	$queryDetails  = "SELECT count(hardware_id) c, ".$tag." as ID from accountinfo a ";
	
	if (isset($_SESSION['OCS']["mesmachines"]) and $_SESSION['OCS']["mesmachines"] != '')
		$queryDetails  .= "WHERE ".$_SESSION['OCS']["mesmachines"];
	$queryDetails  .= "group by ".$tag;
	//require_once('require/function_admininfo.php');
	$tab_options['REPLACE_VALUE']=replace_tag_value('COMPUTERS');
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,95,$tab_options);
	echo "</form>";

?>