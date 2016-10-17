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

require_once 'require/function_commun.php';

if (!class_exists('plugins')) {
    require 'plugins.class.php';
}

if (!function_exists('rrmdir')) {
    require 'functions_delete.php';
}

if (!function_exists('exec_plugin_soap_client')) {
    require 'functions_webservices.php';
}

if (!function_exists('install')) {
    require 'functions_check.php';
}

if ($protectedPost['SUP_PROF'] != '') {
    delete_plugin($protectedPost['SUP_PROF']);
    $tab_options['CACHE'] = 'RESET';
}

if (is_defined($protectedPost['del_check'])) {
    $delarray = explode(",", $protectedPost['del_check']);

    foreach ($delarray as $value) {
        delete_plugin($value);
    }
    $tab_options['CACHE'] = 'RESET';
}

$dep_check = checkDependencies();
$per_check = checkWritable();

// Plugins Install menu.
printEnTete($l->g(7008));
?>
<div class="container">
    <div class="col col-md-12">

        <?php
        echo open_form("PluginInstall", '', '', 'form-horizontal');

        $availablePlugins = scan_downloaded_plugins();

        if (!$dep_check || !$per_check) {

            msg_error($l->g(6009));
        } else {
            if (!empty($availablePlugins)) {
                ?>
                <div class="form-group">
                    <div class="col col-sm-5 col-sm-offset-3">
                        <select class="form-control" name="plugin">
                            <?php
                            foreach ($availablePlugins as $key => $value) {
                                $name = explode(".", $value);
                                $info = new SplFileInfo(PLUGINS_DL_DIR . "/" . $value);

                                if ($info->getExtension() == "zip") {
                                    echo "<option value=$value >$name[0]</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col col-sm-2">
                        <input type="submit" class="form-control btn btn-success" value="Install">
                    </div>
                </div>

                <?php
            } else {
                msg_warning($l->g(7014));
            }
        }


        echo close_form();
        ?>
    </div>
</div>
<?php
if (isset($protectedPost['plugin'])) {

    $pluginArchive = $protectedPost['plugin'];

    $bool = install($pluginArchive);

    if ($bool) {
        $pluginame = explode(".", $pluginArchive);

        $plugintab = array("name" => $pluginame[0]);

        $isok = check($plugintab);

        mv_computer_detail($pluginame[0]);
        $result = mv_server_side($pluginame[0]);

        if ($result) {
            exec_plugin_soap_client($pluginame[0], 1);
        }

        if ($isok) {
            $msg = $l->g(6003) . " " . $pluginame[0] . " " . $l->g(7013);
            msg_success($msg);
        } else {
            $msg = $l->g(2001) . " " . $pluginame[0] . " " . $l->g(7011) . "<br>" . $l->g(7012);
            msg_error($msg);
        }
    } else {
        msg_error($l->g(7010));
    }
}

// Plugins Tab
printEnTete($l->g(7009));

$form_name = "show_all_plugins";
$table_name = $form_name;
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;

echo open_form($form_name, '', '', 'form-horizontal');
$list_fields = array('ID' => 'id',
    $l->g(7002) => 'name',
    $l->g(7003) => 'version',
    $l->g(7004) => 'licence',
    $l->g(7005) => 'author',
    $l->g(7006) => 'reg_date'
);

$tab_options['FILTRE'] = array_flip($list_fields);
$tab_options['FILTRE']['NAME'] = $l->g(49);
asort($tab_options['FILTRE']);
$list_fields['SUP'] = 'ID';
$list_fields['CHECK'] = 'ID';

$list_col_cant_del = array('SUP' => 'SUP', 'CHECK' => 'CHECK');
$default_fields = array($l->g(7002) => $l->g(7002), $l->g(7003) => $l->g(7003), $l->g(7004) => $l->g(7005), $l->g(7006), $l->g(7006));
$sql = prepare_sql_tab($list_fields, $list_col_cant_del);
$tab_options['ARG_SQL'] = $sql['ARG'];
$queryDetails = $sql['SQL'] . ",ID from plugins";
$tab_options['LBL_POPUP']['SUP'] = $l->g(7007) . " ";
$tab_options['LBL']['SUP'] = $l->g(122);

$tab_options['LIEN_LBL']['NAME'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_pluginsmanager'] . '&head=1&id=';
$tab_options['LIEN_CHAMP']['NAME'] = 'ID';
$tab_options['LBL']['NAME'] = $l->g(49);

ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
$img['image/delete.png'] = $l->g(162);
del_selection($form_name);
echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
}
?>