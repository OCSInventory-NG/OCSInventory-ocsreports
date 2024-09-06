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
$form_name = "repart_tag";
$table_name = $form_name;
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;
echo open_form($form_name, '', '', 'form-horizontal');
if (!is_defined($protectedPost['TAG_CHOISE'])) {
    $protectedPost['TAG_CHOISE'] = 'a.TAG';
}

$configToLookOut = [
    'EXCLUDE_ARCHIVE_COMPUTER' => 'EXCLUDE_ARCHIVE_COMPUTER'
];

$excludeArchived = look_config_default_values($configToLookOut)['ivalue']['EXCLUDE_ARCHIVE_COMPUTER'] ?? '';

//BEGIN SHOW ACCOUNTINFO
require_once('require/admininfo/Admininfo.php');
$Admininfo = new Admininfo();
$accountinfo_value = $Admininfo->interprete_accountinfo($list_fields ?? null, $tab_options);

$list_fields = $accountinfo_value['LIST_FIELDS'];
$list_fields_flip = array_flip($list_fields);
//END SHOW ACCOUNTINFO
?>
<div class="row">
    <div class="col col-md-4 col-xs-offset-0 col-md-offset-4">
        <div class="form-group">
            <label class="control-label col-sm-4" for="TAG_CHOISE"><?php echo $l->g(340) ?></label>
            <div class="col-sm-8">
                <?php echo show_modif($list_fields_flip, 'TAG_CHOISE', 2, $form_name, array('DEFAULT' => "NO")); ?>
            </div>
        </div>
    </div>
</div>
<?php
if (isset($protectedPost['TAG_CHOISE'])) {
    // clean filter value like xxx.yyy, xxx or xxx.yy_yy
    $tag = preg_replace("/[^A-Za-z0-9\._]/", "", $protectedPost['TAG_CHOISE']);
}
if (array($accountinfo_value['TAB_OPTIONS'])) {
    $tab_options = $accountinfo_value['TAB_OPTIONS'];
}
unset($list_fields);
$list_fields[$list_fields_flip[$tag]] = $tag;
$list_fields['Nbr_mach'] = 'c';
$tab_options['LIEN_LBL']['Nbr_mach'] = "index.php?" . PAG_INDEX . "=" . $pages_refs['ms_all_computers'] . "&filtre=" . $tag . "&value=";
$tab_options['LIEN_CHAMP']['Nbr_mach'] = "ID";
$tab_options['LBL']['Nbr_mach'] = $l->g(1120);
$tab_options['NO_SEARCH']['c'] = 'c';

$list_col_cant_del = $list_fields;
$default_fields = $list_fields;
$queryDetails = "SELECT count(hardware_id) c, %s as ID, %s from accountinfo a";

if($excludeArchived == 1) {
    $queryDetails .= " left join hardware h on h.id = a.hardware_id";
}

$queryDetails .= " where %s !='' ";

$tab_options['ARG_SQL'] = array($tag, $tag, $tag);

if (is_defined($_SESSION['OCS']["mesmachines"])) {
    $queryDetails .= " AND " . $_SESSION['OCS']["mesmachines"];
}

if($excludeArchived == 1) {
    $queryDetails .= " AND h.archive is null";
}

$tab_options['ARG_SQL'][] = $tag;
$queryDetails .= " group by $tag";

ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
}
?>