<?php
	$form_name="repart_tag";
	$table_name=$form_name;
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	$list_fields = array ( $_SESSION['OCS']['TAG_LBL']   => "ID", 
						   'Nbr_mach'=>'c');
	$tab_options['FILTRE']['a.tag']=$_SESSION['OCS']['TAG_LBL'];
//	$tab_options['NO_TRI']['LBL_UNIT']='LBL_UNIT';
//	$tab_options['LBL']['LBL_UNIT']="libell� unit�";
	$tab_options['LIEN_LBL']['Nbr_mach']="index.php?".PAG_INDEX."=".$pages_refs['ms_all_computers']."&filtre=a.tag&value=";
	$tab_options['LIEN_CHAMP']['Nbr_mach']="ID";
	$list_col_cant_del=array($_SESSION['OCS']['TAG_LBL']=>$_SESSION['OCS']['TAG_LBL']);
	$default_fields= $list_fields;
	$queryDetails  = "SELECT count(hardware_id) c, a.tag as ID from accountinfo a ";
	
	if (isset($_SESSION['OCS']["mesmachines"]) and $_SESSION['OCS']["mesmachines"] != '')
		$queryDetails  .= "WHERE ".$_SESSION['OCS']["mesmachines"];
	$queryDetails  .= "group by TAG ";
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,95,$tab_options);
	echo "</form>";

?>