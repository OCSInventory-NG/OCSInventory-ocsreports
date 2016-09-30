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
	if(AJAX){
		parse_str($protectedPost['ocs']['0'], $params);
		$protectedPost+=$params;
		ob_start();
		$ajax = true;
	}
	else{
		$ajax=false;
	}
	$form_name="repart_tag";
	$table_name=$form_name;
	$tab_options=$protectedPost;
	$tab_options['form_name']=$form_name;
	$tab_options['table_name']=$table_name;
	echo open_form($form_name, '', '', 'form-horizontal');
	if (!isset($protectedPost['TAG_CHOISE']) or $protectedPost['TAG_CHOISE'] == '')
		$protectedPost['TAG_CHOISE'] = 'a.TAG';
	//BEGIN SHOW ACCOUNTINFO
	require_once('require/function_admininfo.php');
	$accountinfo_value=interprete_accountinfo($list_fields,$tab_options);
	$list_fields=$accountinfo_value['LIST_FIELDS'];
	$list_fields_flip=array_flip($list_fields);
	//END SHOW ACCOUNTINFO

    ?>
    <div class="row">
        <div class="col col-md-4 col-xs-offset-0 col-md-offset-4">
            <div class="form-group">
                <label class="control-label col-sm-4" for="TAG_CHOISE"><?php echo $l->g(340) ?></label>
                <div class="col-sm-8">
                    <?php echo show_modif($list_fields_flip,'TAG_CHOISE',2,$form_name,array('DEFAULT' => "NO")); ?>
                </div>
            </div>
        </div>
    </div>
    <?php

	if (isset($protectedPost['TAG_CHOISE'])){
		$tag=$protectedPost['TAG_CHOISE'];		
	}
	if (array($accountinfo_value['TAB_OPTIONS'])){
		$tab_options['replace_query_arg']['ID']=$tag;
	}
	unset($list_fields);
	$list_fields['ID']='ID';
	$tab_options['LBL']['ID']=$list_fields_flip[$tag];
	$list_fields['Nbr_mach']='c';
	$tab_options['LIEN_LBL']['Nbr_mach']="index.php?".PAG_INDEX."=".$pages_refs['ms_all_computers']."&filtre=".$tag."&value=";
	$tab_options['LIEN_CHAMP']['Nbr_mach']="ID";
	$tab_options['LBL']['Nbr_mach']=$l->g(1120);
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;
	$queryDetails  = "SELECT count(hardware_id) c, %s as ID from accountinfo a where %s !='' ";
	$tab_options['ARG_SQL']=array($tag,$tag);
	if (isset($_SESSION['OCS']["mesmachines"]) and $_SESSION['OCS']["mesmachines"] != '')
		$queryDetails  .= " AND ".$_SESSION['OCS']["mesmachines"];
	$tab_options['ARG_SQL'][]=$tag;	
	$queryDetails  .= "group by ID";
	ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
	echo close_form();
	
	
	if ($ajax){
		ob_end_clean();
		tab_req($list_fields,$default_fields,$list_col_cant_del,$queryDetails,$tab_options);

	}
	?>