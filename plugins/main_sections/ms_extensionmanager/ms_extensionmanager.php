<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

if (AJAX) {
    parse_str($protectedPost['ocs']['0'], $params);
    $protectedPost += $params;

    ob_start();
}

require_once 'require/extensions/ExtensionManager.php';
require_once 'require/extensions/ExtensionCommon.php';

printEnTete($l->g(7008));

?>
<div class="container">
    <div class="col col-md-12">
<?php

$extMgr = new ExtensionManager();
if($extMgr->checkPrerequisites()){
    $extMgr->checkInstallableExtensions();
    
	if (!empty($extMgr->installableExtensions_errors)) {
		$extensions_errors .= '<ul>';
		foreach($extMgr->installableExtensions_errors as $error_msg) {
			$extensions_errors .= '<li>'.$error_msg.'</li>';
		}
		$extensions_errors .= '</ul>';
		msg_error($extensions_errors);
	}
	
    if(empty($extMgr->installableExtensionsList)){
        msg_warning($l->g(7014).' ('.EXT_DL_DIR.').');
    }else{   
        echo open_form("PluginInstall", '', '', 'form-horizontal');
        ?>
            <div class="form-group">
                <div class="col col-sm-5 col-sm-offset-3">
                    <select class="form-control" name="extensions">
                        <?php
                        foreach ($extMgr->installableExtensionsList as $key => $value) {
                            echo "<option value=$value >$value</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col col-sm-2">
                    <input type="submit" class="form-control btn btn-success" value="Install">
                </div>
            </div>
        <?php
        echo close_form();
    }
    
}else{
    msg_warning($l->g(7014));
}

?>
    </div>
</div>
<?php

if (!AJAX) {
    if(isset($protectedPost['extensions'])){
        $result = $extMgr->installExtension($protectedPost['extensions']);
        if($result === true) {
            msg_success($l->g(7017));
        } elseif($result == 'isInstalled') {
            msg_error($l->g(7018));
        } else {
            msg_error($l->g(7019));
        }
    }

    if(isset($protectedPost['SUP_PROF'])){
        $desinstall = $extMgr->deleteExtension($protectedPost['SUP_PROF']);
        if($desinstall) {
            msg_success($l->g(7020));
        } else {
            msg_error($l->g(7019));
        }
    }
}

// Plugins Tab
printEnTete($l->g(7009));
$form_name = "show_all_extensions";
$table_name = $form_name;
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;
echo open_form($form_name, '', '', 'form-horizontal');

$list_fields = array('ID' => 'id',
    $l->g(7002) => 'name',
    $l->g(53) => 'description',
    $l->g(7003) => 'version',
    $l->g(7005) => 'author',
    $l->g(7004) => 'licence'
);

$tab_options['FILTRE'] = array_flip($list_fields);
$tab_options['FILTRE']['NAME'] = $l->g(49);
asort($tab_options['FILTRE']);
$list_fields['SUP'] = 'ID';
$list_col_cant_del = array('SUP' => 'SUP');
$default_fields = array($l->g(7002) => $l->g(7002), $l->g(7003) => $l->g(7003), $l->g(7004) => $l->g(7005), $l->g(7006), $l->g(7006));
$sql = prepare_sql_tab($list_fields, $list_col_cant_del);
$tab_options['ARG_SQL'] = $sql['ARG'];
$queryDetails = $sql['SQL'] . ",ID from extensions";
$tab_options['LBL_POPUP']['SUP'] = $l->g(7007) . " ";
$tab_options['LBL']['SUP'] = $l->g(122);
$tab_options['LIEN_CHAMP']['NAME'] = 'ID';
$tab_options['LBL']['NAME'] = $l->g(49);
ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
}
