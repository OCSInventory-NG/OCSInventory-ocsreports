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
require_once('require/function_admininfo.php');
$table_name = $form_name;
//search all admininfo for this computer
$info_account_id = admininfo_snmp($systemid);


if (isset($protectedPost['ADMIN']) && $protectedPost['ADMIN'] == 'ADMIN' && !isset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO'])) {
    $_SESSION['OCS']['ADMIN']['ACCOUNTINFO'] = true;
} elseif (isset($protectedPost['ADMIN']) && $protectedPost['ADMIN'] == 'ADMIN' && isset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO'])) {
    unset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO']);
}

if ($_SESSION['OCS']['profile']->getConfigValue('ACCOUNTINFO') == 'YES' && isset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO'])) {
    $admin_accountinfo = true;
}

$list_tab = find_all_account_tab('TAB_ACCOUNTSNMP', 'SNMP', 1);
if ($list_tab != '') {
    if ($protectedPost['Valid_modif'] != "") {
        foreach ($protectedPost as $field => $value) {
            $temp_field = explode('_', $field);
            if (array_key_exists($temp_field[0] . '_' . $temp_field[1], $info_account_id) || $temp_field[0] == 'TAG') {
                //cas of checkbox
                if (isset($temp_field[2])) {
                    $data_fields_account[$temp_field[0] . "_" . $temp_field[1]] .= $temp_field[2] . "&&&";
                } else {
                    $data_fields_account[$field] = $value;
                }
            }
        }
        updateinfo_snmp($systemid, $data_fields_account);
        //search all admininfo for this computer
        $info_account_id = admininfo_snmp($systemid);
    }
    unset($action_updown);
    //UP/DOWN
    if (is_defined($protectedPost['UP'])) {
        $action_updown = 'UP';
    }
    if (is_defined($protectedPost['DOWN'])) {
        $action_updown = 'DOWN';
    }

    if (isset($action_updown)) {
        $new_order = find_new_order($action_updown, $protectedPost[$action_updown], 'SNMP', $protectedPost['onglet']);
        if ($new_order) {
            update_accountinfo_config($new_order['OLD'], array('SHOW_ORDER' => $new_order['NEW_VALUE']));
            update_accountinfo_config($new_order['NEW'], array('SHOW_ORDER' => $new_order['OLD_VALUE']));
        }
    }

    //print_r($info_account_id);
    if (!is_defined($protectedPost['onglet']) || !is_numeric($protectedPost['onglet'])) {
        $protectedPost['onglet'] = $list_tab['FIRST'];
    }
    unset($list_tab['FIRST']);

    echo "<br>";
    echo open_form($form_name);
    onglet($list_tab, $form_name, "onglet", 6);
    echo '<div class="col-md-12" >';
    if ($_SESSION['OCS']['profile']->getConfigValue('ACCOUNTINFO') == 'YES') {
        $show_admin_button = "<a href=# OnClick='pag(\"ADMIN\",\"ADMIN\",\"" . $form_name . "\");'>";
        if (isset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO'])) {
            $show_admin_button .= "<img src='image/success.png'></a>";
        } else {
            $show_admin_button .= "<img src='image/modif_tab.png'></a>";
        }
    } else {
        $show_admin_button = '';
    }
    $sql_admin_info = "select ID,TYPE,NAME,COMMENT,NAME_ACCOUNTINFO,SHOW_ORDER from accountinfo_config where ID_TAB = %s and account_type='SNMP'
						order by SHOW_ORDER ASC";
    $arg_admin_info = array($protectedPost['onglet']);
    $res_admin_info = mysql2_query_secure($sql_admin_info, $_SESSION['OCS']["readServer"], $arg_admin_info);
    $num_row = mysqli_num_rows($res_admin_info);
    $name_field = array();
    $tab_name = array();
    $type_field = array();
    $value_field = array();
    $config['COMMENT_AFTER'] = array();
    $config['SELECT_DEFAULT'] = array();
    $config['JAVASCRIPT'] = array();
    $config['SIZE'] = array();
    $config['DDE'] = array();

    $nb_row = 1;
    while ($val_admin_info = mysqli_fetch_array($res_admin_info)) {
        array_push($config['DDE'], $systemid);
        //if name_accountinfo is not null
        //column name in accountinfo table is name_accountinfo
        //functionality for compatibility with older version of OCS
        //we can't change the name TAG in accountinfo table
        if ($val_admin_info['NAME_ACCOUNTINFO'] != '') {
            $name_accountinfo = trim($val_admin_info['NAME_ACCOUNTINFO']);
        } else {
            $name_accountinfo = 'fields_' . $val_admin_info['ID'];
        }

        $up_png = "";

        if ($nb_row != 1) {
            $up_png .= updown($val_admin_info['ID'], 'UP');
        }

        if ($nb_row != $num_row) {
            $up_png .= updown($val_admin_info['ID'], 'DOWN');
        }
        if ($val_admin_info['TYPE'] == 2
                or $val_admin_info['TYPE'] == 4
                or $val_admin_info['TYPE'] == 7) {
            array_push($config['JAVASCRIPT'], '');
            array_push($config['SIZE'], '');
            if ($admin_accountinfo) {
                array_push($config['COMMENT_AFTER'], $up_png . "<a href=\"index.php?" . PAG_INDEX . "=" . $pages_refs['ms_adminvalues'] . "&head=1&tag=ACCOUNT_SNMP_VALUE_" . $val_admin_info['NAME'] . "\"><img src=image/plus.png></a>");
            } else {
                array_push($config['COMMENT_AFTER'], '');
            }
            array_push($config['SELECT_DEFAULT'], 'YES');
            $field_select_values = find_value_field("ACCOUNT_SNMP_VALUE_" . $val_admin_info['NAME']);
            array_push($value_field, $field_select_values);
            //cas of checkbox
            if ($val_admin_info['TYPE'] == 4) {
                $temp_val = explode('&&&', $info_account_id[$name_accountinfo]);
                foreach ($temp_val as $uneVal) {
                    $protectedPost[$name_accountinfo . '_' . $uneVal] = 'on';
                }
            } else {
                $protectedPost[$name_accountinfo] = $info_account_id[$name_accountinfo];
            }
        } elseif ($val_admin_info['TYPE'] == 6) {
            array_push($value_field, $info_account_id[$name_accountinfo]);
            if ($admin_accountinfo) {
                array_push($config['COMMENT_AFTER'], $up_png . datePick($name_accountinfo));
            } else {
                array_push($config['COMMENT_AFTER'], datePick($name_accountinfo));
            }
            array_push($config['JAVASCRIPT'], "READONLY " . dateOnClick($name_accountinfo));
            array_push($config['SELECT_DEFAULT'], '');
            array_push($config['SIZE'], '8');
        } elseif ($val_admin_info['TYPE'] == 5) {
            array_push($value_field, "accountinfo");
            if ($admin_accountinfo) {
                array_push($config['COMMENT_AFTER'], $up_png);
            } else {
                array_push($config['COMMENT_AFTER'], "");
            }
            array_push($config['SELECT_DEFAULT'], '');
            array_push($config['JAVASCRIPT'], '');
            array_push($config['SIZE'], '');
        } else {
            array_push($value_field, $info_account_id[$name_accountinfo]);
            if ($admin_accountinfo) {
                array_push($config['COMMENT_AFTER'], $up_png);
            } else {
                array_push($config['COMMENT_AFTER'], "");
            }
            array_push($config['SELECT_DEFAULT'], '');
            array_push($config['JAVASCRIPT'], '');
            array_push($config['SIZE'], '');
        }

        array_push($name_field, $name_accountinfo);
        array_push($tab_name, $val_admin_info['COMMENT']);
        if ($_SESSION['OCS']['profile']->getConfigValue('CHANGE_ACCOUNTINFO') == "YES") {
            array_push($type_field, $convert_type[$val_admin_info['TYPE']]);
        } else {
            array_push($type_field, 3);
        }

        $nb_row++;
    }

    $tab_typ_champ = show_field($name_field, $type_field, $value_field, $config);
    if ($_SESSION['OCS']['profile']->getConfigValue('ACCOUNTINFO') == 'YES') {
        $tab_hidden = array('ADMIN' => '', 'UP' => '', 'DOWN' => '');
    }

    modif_values($tab_name, $tab_typ_champ, $tab_hidden, array(
        'form_name' => 'NO_FORM',
        'top_action' => $show_admin_button
    ));

    echo "</div>";
    echo close_form();
}
?>
