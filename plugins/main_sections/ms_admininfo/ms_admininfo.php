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
/*
 * It Set Management light
 * Admin your accountinfo
 */
if (AJAX) {
    parse_str($protectedPost['ocs']['0'], $params);
    $protectedPost += $params;
    ob_start();

    $ajax = true;
} else {
    $ajax = false;
}


require_once('require/function_admininfo.php');

$accountinfo_choise['COMPUTERS'] = $l->g(729);
$accountinfo_choise['SNMP'] = $l->g(1136);
if (!isset($protectedPost['onglet']) || $protectedPost['onglet'] == '')
    $protectedPost['onglet'] = 1;
$form_name = 'admin_info';
$table_name = $form_name;
$tab_options = $protectedPost;

$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;

$data_on[1] = $l->g(1059);
$data_on[2] = $l->g(1060);
//$yes_no=array($l->g(454),$l->g(455));
if (isset($protectedPost['MODIF'])
        and is_numeric($protectedPost['MODIF'])
        and ! isset($protectedPost['Valid_modif'])) {
    $protectedPost['onglet'] = 2;
    $accountinfo_detail = find_info_accountinfo($protectedPost['MODIF']);
    $protectedPost['newfield'] = $accountinfo_detail[$protectedPost['MODIF']]['name'];
    $protectedPost['newlbl'] = $accountinfo_detail[$protectedPost['MODIF']]['comment'];
    $protectedPost['newtype'] = $accountinfo_detail[$protectedPost['MODIF']]['type'];
    $protectedPost['account_tab'] = $accountinfo_detail[$protectedPost['MODIF']]['id_tab'];
    $protectedPost['accountinfo'] = $accountinfo_detail[$protectedPost['MODIF']]['account_type'];
    $protectedPost['default_value'] = $accountinfo_detail[$protectedPost['MODIF']]['default_value'];
    $hidden = $protectedPost['MODIF'];
}

if (isset($protectedPost['MODIF_OLD'])
        and is_numeric($protectedPost['MODIF_OLD'])
        and $protectedPost['Valid_modif'] != "") {
    //UPDATE VALUE
    $msg = update_accountinfo($protectedPost['MODIF_OLD'], array('TYPE' => $protectedPost['newtype'],
        'NAME' => $protectedPost['newfield'],
        'COMMENT' => $protectedPost['newlbl'],
        'ID_TAB' => $protectedPost['account_tab'],
        'DEFAULT_VALUE' => $protectedPost['default_value']), $protectedPost['accountinfo']);
    $hidden = $protectedPost['MODIF_OLD'];
} elseif ($protectedPost['Valid_modif'] != "") {
    //ADD NEW VALUE	
    $msg = add_accountinfo($protectedPost['newfield'], $protectedPost['newtype'], $protectedPost['newlbl'], $protectedPost['account_tab'], $protectedPost['accountinfo'], $protectedPost['default_value']);
}

if (isset($msg['ERROR']))
    msg_error($msg['ERROR']);
if (isset($msg['SUCCESS'])) {
    msg_success($msg['SUCCESS']);
    $protectedPost['onglet'] = 1;
}

echo open_form($form_name, '', '', 'form-horizontal');
show_tabs($data_on, $form_name, "onglet", 2);
echo '<div class="col col-md-10" >';

$table = "accountinfo";

if ((isset($protectedPost['ACCOUNTINFO_CHOISE']) && $protectedPost['ACCOUNTINFO_CHOISE'] == 'SNMP' && $protectedPost['onglet'] == 1) || (isset($protectedPost['accountinfo']) && $protectedPost['accountinfo'] == 'SNMP' && $protectedPost['onglet'] == 2)) {
    $array_tab_account = find_all_account_tab('TAB_ACCOUNTSNMP');
    $account_field = "TAB_ACCOUNTSNMP";
} else {
    $array_tab_account = find_all_account_tab('TAB_ACCOUNTAG');
    $account_field = "TAB_ACCOUNTAG";
}

if ($protectedPost['onglet'] == 1) {
    ?>
    <div class="row">
        <div class="col col-md-6 col-md-offset-3">

            <?php
            formGroup('select', 'ACCOUNTINFO_CHOISE', $l->g(56), '', '', $protectedPost, '', $accountinfo_choise, $accountinfo_choise, 'onchange="document.admin_info.submit();"');
            ?>
        </div>
    </div>

    <?php
    if ($protectedPost['ACCOUNTINFO_CHOISE'] == "SNMP")
        $account_choise = "SNMP";
    else
        $account_choise = "COMPUTERS";

    $tab_options['CACHE'] = 'RESET';
    if (isset($protectedPost['del_check']) && $protectedPost['del_check'] != '') {
        $list = $protectedPost['del_check'];
        $tab_values = explode(',', $list);
        $i = 0;
        while ($tab_values[$i]) {
            del_accountinfo($tab_values[$i]);
            $i++;
        }
    }

    if (isset($protectedPost['SUP_PROF']) && $protectedPost['SUP_PROF'] != '') {
        del_accountinfo($protectedPost['SUP_PROF']);
    }
    $array_fields = array($l->g(1098) => 'NAME',
        $l->g(1063) => 'COMMENT',
        $l->g(66) => 'TYPE',
        $l->g(1061) => 'ID_TAB');

    $queryDetails = "select ID," . implode(',', $array_fields) . " from accountinfo_config where ACCOUNT_TYPE = '" . $account_choise . "'";

    if (!isset($protectedPost['SHOW']))
        $protectedPost['SHOW'] = 'NOSHOW';
    if (!(isset($protectedPost["pcparpage"])))
        $protectedPost["pcparpage"] = 10;

    $list_fields = $array_fields;

    $list_fields['SUP'] = 'ID';
    $list_fields['CHECK'] = 'ID';
    $list_fields['MODIF'] = 'ID';
    $list_col_cant_del = array($l->g(1063) => $l->g(1063), $l->g(66) => $l->g(66), $l->g(1061) => $l->g(1061), 'SUP' => 'SUP', 'CHECK' => 'CHECK', 'MODIF' => 'MODIF');
    $default_fields = $list_col_cant_del;
    $tab_options['REPLACE_VALUE'][$l->g(66)] = $type_accountinfo;
    $tab_options['REPLACE_VALUE'][$l->g(1061)] = $array_tab_account;
    $tab_options['LBL_POPUP']['SUP'] = 'NAME';
    $tab_options['REQUEST']['SUP'] = "select name_accountinfo AS FIRST from accountinfo_config where ACCOUNT_TYPE = '" . $account_choise . "'";
    $tab_options['FIELD']['SUP'] = 'NAME';
    $tab_options['EXIST']['SUP'] = 'NAME';
    $tab_options['REQUEST']['CHECK'] = "select name_accountinfo AS FIRST from accountinfo_config where ACCOUNT_TYPE = '" . $account_choise . "'";
    $tab_options['FIELD']['CHECK'] = 'NAME';
    $tab_options['EXIST']['CHECK'] = 'NAME';
    $nb_result = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
    //traitement par lot
    del_selection($form_name);

    //}	
}elseif ($protectedPost['onglet'] == 2) {
    //NAME FIELD
    $config['JAVASCRIPT'][1] = $sql_field;
    $name_field = array("accountinfo", "newfield");
    $tab_name = array($l->g(56) . ": ", $l->g(1070) . ": ");
    if (isset($protectedPost['MODIF_OLD']) || $protectedPost['MODIF'] != '') {
        $hidden = ($protectedPost['MODIF'] != '' ? $protectedPost['MODIF'] : $protectedPost['MODIF_OLD']);
        $type_field = array(3, 3);
        $value_field = array($protectedPost['accountinfo'], $protectedPost['newfield']);
    } else {
        $type_field = array(2, 0);
        $value_field = array($accountinfo_choise, $protectedPost['newfield']);
    }

    if (isset($hidden) && is_numeric($hidden)) {
        $tab_hidden['MODIF_OLD'] = $hidden;
    }

    //if (isset($protectedGet['admin'])){
    array_push($name_field, "newlbl");
    array_push($tab_name, $l->g(80) . ":");
    array_push($type_field, 0);
    array_push($value_field, $protectedPost['newlbl']);

    array_push($name_field, "newtype");
    array_push($tab_name, $l->g(1071) . ":");
    array_push($type_field, 2);
    array_push($value_field, $type_accountinfo);


    array_push($name_field, "account_tab");
    array_push($tab_name, $l->g(1061) . ":");
    array_push($type_field, 2);
    array_push($value_field, $array_tab_account);

    if ($protectedPost['newtype'] == 8) { //for QRCODE type
        array_push($name_field, "default_value");
        array_push($tab_name, $l->g(1099) . ":");
        array_push($type_field, 2);
        array_push($value_field, $array_qr_values);
    }

    $tab_typ_champ = show_field($name_field, $type_field, $value_field, $config);

    formGroup('select', 'accountinfo', $l->g(56), '', '', $protectedPost, '', $tab_typ_champ[0]['DEFAULT_VALUE'], $tab_typ_champ[0]['DEFAULT_VALUE'], "onKeyPress=\"return scanTouche(event,/[0-9a-zA-Z_-]/)\" onkeydown='convertToUpper(this)' onkeyup='convertToUpper(this)' onblur='convertToUpper(this)'");
    formGroup('text', 'newfield', $l->g(1070), 30, 255, $protectedPost['newfield'], '', '', '', "onkeypress='return scanTouche(event,/[0-9a-zA-Z_-]/)' onkeydown='convertToUpper(this)' onkeyup='convertToUpper(this)' onblur='convertToUpper(this)'");
    formGroup('text', 'newlbl', $l->g(80), 30, 255, $protectedPost['newlbl']);
    formGroup('select', 'newtype', $l->g(1071), '', '', $protectedPost, '', $tab_typ_champ[3]['DEFAULT_VALUE'], $tab_typ_champ[3]['DEFAULT_VALUE'], "document.admin_info.submit();");
    formGroup('select', 'account_tab', $l->g(1061), '', '', $protectedPost, '', $tab_typ_champ[4]['DEFAULT_VALUE'], $tab_typ_champ[4]['DEFAULT_VALUE']);
}
?>

<div class="row">
    <div class="col-md-12">
        <input type="submit" name="Valid_modif" value="<?php echo $l->g(1363) ?>" class="btn btn-success">
        <input type="submit" name="Reset_modif" value="<?php echo $l->g(1364) ?>" class="btn btn-danger">
    </div>
</div>


<?php
echo "</div>";
echo close_form();

if ($ajax) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
}
?>

