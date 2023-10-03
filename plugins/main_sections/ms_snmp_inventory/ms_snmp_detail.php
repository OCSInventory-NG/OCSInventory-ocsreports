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

require_once('require/snmp/Snmp.php');
require_once('require/admininfo/Admininfo.php');

$snmp = New OCSSnmp();
$Admininfo = new Admininfo();

// Initialize empty value
$getType    = null;
$getId      = null;

// Clean protectedGet
if(isset($protectedGet['type']))    $getType = preg_replace("/[^A-Za-z0-9\._]/", "", $protectedGet['type']);
if(isset($protectedGet['id']))      $getId = preg_replace("/[^0-9]/", "", $protectedGet['id']);

// Retrieve AdminiData TAB
$list_tab = $Admininfo->find_all_account_tab('TAB_ACCOUNTAG', 'SNMP', 1);

// Retrieve all equipment informations
$equipmentDetails   = $snmp->getDetails($getType, $getId);
$reconciliation     = $snmp->getReconciliationColumn($getType);

// Print reconciliation field as title
printEnTete($equipmentDetails[$reconciliation]);

echo "<div class='col col-md-12'>";

// XML export button
if ($_SESSION['OCS']['profile']->getRestriction('EXPORT_XML', 'NO') == "NO") {
    echo '<div>';
    echo '<button class= "btn btn-action" onclick=\'location.href="index.php?'.PAG_INDEX.'='.$urls->getUrl('ms_export_snmp').'&no_header=1&type='.$getType.'&id='.$getId.'";\' target="_blank">'.$l->g(1304).'</button>';
    echo '</div>';
}

echo "<br>";

// If there is AdminData Tab
if(!empty($list_tab)) {
    echo "<div class='row'>";
} else {
    echo "<div class='col col-md-3'></div>";
}


// SNMP DETAILS
echo "<div class='col col-md-6' style='margin-top:30px;'>";
echo "<div class='card'>";
echo "<div class='card-header'>".$l->g(9012)."</div>";
echo "<ul class='list-group list-group-flush'>";

foreach($equipmentDetails as $label => $detail) {
    if($label != "ID") {
        echo "<li class='list-group-item'>";
        if($label == "LASTDATE") {
            echo '<span class="summary-header text-left" style="vertical-align:middle;">'.$l->g(46).' :</span>';
        } else {
            echo '<span class="summary-header text-left" style="vertical-align:middle;">'.$label.' :</span>';
        }
        echo '<span class="summary-value text-left" style="vertical-align:middle;">'.$detail.'</span>';
        echo "</li>";
    }
}

echo "</ul>";
echo "</div>";
echo "</div>";


// ACCOUNT INFO
if(!empty($list_tab)) {
    $form_name = 'admin_info_snmp';
    $table_name = $form_name;

    // Retrieve accountinfo for this equipment
    $info_account_id = $Admininfo->admininfo_snmp($equipmentDetails[$reconciliation], $getType, $getId);

    if (isset($protectedPost['ADMIN']) && $protectedPost['ADMIN'] == 'ADMIN' && !isset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO'])) {
        $_SESSION['OCS']['ADMIN']['ACCOUNTINFO'] = true;
    } elseif (isset($protectedPost['ADMIN']) && $protectedPost['ADMIN'] == 'ADMIN' && isset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO'])) {
        unset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO']);
    }

    if ($_SESSION['OCS']['profile']->getConfigValue('ACCOUNTINFO') == 'YES' && isset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO'])) {
        $admin_accountinfo = true;
    }

    if (isset($protectedPost['Valid_modif'])) {
        if (!is_defined($protectedPost['onglet']) || !is_numeric($protectedPost['onglet'])) {
            $protectedPost['onglet'] = $list_tab['FIRST'];
        }

        $sql_admin_info = "SELECT ID, NAME_ACCOUNTINFO FROM accountinfo_config WHERE ID_TAB = %s AND account_type='SNMP' ORDER BY SHOW_ORDER ASC";
        $arg_admin_info = array($protectedPost['onglet']);

        $res_admin_info = mysql2_query_secure($sql_admin_info, $_SESSION['OCS']["readServer"], $arg_admin_info);

        while ($val_admin_info = mysqli_fetch_array($res_admin_info)) {
            if ($val_admin_info['NAME_ACCOUNTINFO']) {
                $data_fields_account[$val_admin_info['NAME_ACCOUNTINFO']] = "";
            } else {
                $data_fields_account["fields_" . $val_admin_info["ID"]] = "";
            }
        }

        foreach ($protectedPost as $field => $value) {
            $temp_field = explode('_', $field);

            if ((isset($temp_field[1]) && isset($info_account_id[$getType][$getId]) && (array_key_exists($temp_field[0] . '_' . $temp_field[1], $info_account_id[$getType][$getId]))) || $temp_field[0] == 'TAG') {
                //cas of checkbox
                if (isset($temp_field[2])) {
                    $temp_field2 = $temp_field;
                    unset($temp_field2[0]);
                    unset($temp_field2[1]);
                    $new_value = implode(' ', $temp_field2);
                    $data_fields_account[$temp_field[0] . "_" . $temp_field[1]] .= $new_value . "&&&";
                } else {
                    $data_fields_account[$field] = $value;
                }
            }

        }
        $Admininfo->updateinfo_snmp($equipmentDetails[$reconciliation], $getType, $data_fields_account);
        //search all admininfo for this computer
        $info_account_id = $Admininfo->admininfo_snmp($equipmentDetails[$reconciliation], $getType, $getId);
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
        $new_order = $Admininfo->find_new_order($action_updown, $protectedPost[$action_updown], 'SNMP', $protectedPost['onglet']);
        if ($new_order) {
            $Admininfo->update_accountinfo_config($new_order['OLD'], array('SHOW_ORDER' => $new_order['NEW_VALUE']));
            $Admininfo->update_accountinfo_config($new_order['NEW'], array('SHOW_ORDER' => $new_order['OLD_VALUE']));
        }
    }

    if (!is_defined($protectedPost['onglet']) || !is_numeric($protectedPost['onglet'])) {
        $protectedPost['onglet'] = $list_tab['FIRST'];
    }
    
    unset($list_tab['FIRST']);

    echo "<div class='col col-md-6' style='margin-top:30px;'>";
    echo "<div class='card'>";
    echo "<div class='card-header'>".$l->g(56)."</div>";
    echo "<ul class='list-group list-group-flush'>";
    echo "<li class='list-group-item'>";
    echo open_form($form_name, '', '', 'form-horizontal');

    onglet($list_tab, $form_name, "onglet", 6);

    $sql_admin_info = "SELECT ID,TYPE,NAME,COMMENT,NAME_ACCOUNTINFO,SHOW_ORDER,DEFAULT_VALUE FROM accountinfo_config WHERE ID_TAB = %s AND account_type='SNMP' ORDER BY SHOW_ORDER ASC";
    $arg_admin_info = array($protectedPost['onglet']);

    $show_admin_button = null;
    if ($_SESSION['OCS']['profile']->getConfigValue('ACCOUNTINFO') == 'YES') {
        $show_admin_button = "<a href=# OnClick='pag(\"ADMIN\",\"ADMIN\",\"" . $form_name . "\");'>";
        if (isset($_SESSION['OCS']['ADMIN']['ACCOUNTINFO'])) {
            $show_admin_button .= "<span class='glyphicon glyphicon-ok'></span></a>";
        } else {
            $show_admin_button .= "<span class='glyphicon glyphicon-cog'></span></a>";
        }
    }

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
        array_push($config['DDE'], $equipmentDetails[$reconciliation]);

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
            $up_png .= $Admininfo->updown($val_admin_info['ID'], 'UP');
        }

        if ($nb_row != $num_row) {
            $up_png .= $Admininfo->updown($val_admin_info['ID'], 'DOWN');
        }

        if ($val_admin_info['TYPE'] == 2 || $val_admin_info['TYPE'] == 5 || $val_admin_info['TYPE'] == 11) {
            array_push($config['JAVASCRIPT'], '');
            array_push($config['SIZE'], '');

            if (isset($admin_accountinfo) && $admin_accountinfo) {
                array_push($config['COMMENT_AFTER'], $up_png . "<a href=# onclick=window.open(\"index.php?" . PAG_INDEX . "=" . $pages_refs['ms_adminvalues'] . "&head=1&tag=ACCOUNT_SNMP_VALUE_" . $val_admin_info['NAME'] . "\")><img src=image/plus.png></a>");
            } else {
                array_push($config['COMMENT_AFTER'], '');
            }

            array_push($config['SELECT_DEFAULT'], 'YES');
            $field_select_values = $Admininfo->find_value_field("ACCOUNT_SNMP_VALUE_" . $val_admin_info['NAME']);
            asort($field_select_values);

            //cas of checkbox
            if ($val_admin_info['TYPE'] == 5) {
                $temp_val = explode('&&&', $info_account_id[$getType][$getId][$name_accountinfo]);
                $i = 0;
                $tp_readonly = "";
                while (is_defined($temp_val[$i])) {
                    if ($_SESSION['OCS']['profile']->getConfigValue('CHANGE_ACCOUNTINFO') == "YES") {
                        $protectedPost[$name_accountinfo . '_' . $temp_val[$i]] = 'on';
                    } else {
                        $tp_readonly .= $field_select_values[$temp_val[$i]] . ";";
                    }

                    $i++;
                }

                if ($tp_readonly != '') {
                    array_push($value_field, substr($tp_readonly, 0, -1));
                } else {
                    array_push($value_field, $field_select_values);
                }
            } else {
                $protectedPost[$name_accountinfo] = $info_account_id[$getType][$getId][$name_accountinfo];

                if ($_SESSION['OCS']['profile']->getConfigValue('CHANGE_ACCOUNTINFO') == "YES") {
                    array_push($value_field, $field_select_values);
                } else {
                    array_push($value_field, $field_select_values[$protectedPost[$name_accountinfo]]);
                }
            }
        } elseif ($val_admin_info['TYPE'] == 14) {
            $info_account_id[$getType][$getId][$name_accountinfo] = date($l->g(1242), strtotime($info_account_id[$getType][$getId][$name_accountinfo]));
            array_push($value_field, $info_account_id[$getType][$getId][$name_accountinfo]);
            
            if ($_SESSION['OCS']['profile']->getConfigValue('CHANGE_ACCOUNTINFO') == "YES") {
                if (isset($admin_accountinfo) && $admin_accountinfo) {
                    array_push($config['COMMENT_AFTER'], $up_png . datePick($name_accountinfo));
                } else {
                    array_push($config['COMMENT_AFTER'], datePick($name_accountinfo));
                }

                array_push($config['JAVASCRIPT'], "READONLY");
                array_push($config['SELECT_DEFAULT'], '');
                array_push($config['SIZE'], '8');
            }
        } elseif ($val_admin_info['TYPE'] == 5) {
            array_push($value_field, "accountinfo");

            if (isset($admin_accountinfo) && $admin_accountinfo) {
                array_push($config['COMMENT_AFTER'], $up_png);
            } else {
                array_push($config['COMMENT_AFTER'], "");
            }

            array_push($config['SELECT_DEFAULT'], '');
            array_push($config['JAVASCRIPT'], '');
            array_push($config['SIZE'], '');
        } else {
            array_push($value_field, $info_account_id[$getType][$getId][$name_accountinfo]);

            if (isset($admin_accountinfo) && $admin_accountinfo) {
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
            array_push($type_field, $val_admin_info['TYPE']);
        }

        $nb_row++;
    }

    // If is a select get default data
    foreach($name_field as $key => $value){
        if($config['SELECT_DEFAULT'][$key] == 'YES'){
            $sql_selected_data = "SELECT ".$value." FROM `snmp_accountinfo` WHERE `SNMP_TYPE` = '".$getType."' AND `SNMP_RECONCILIATION_VALUE` = '".$equipmentDetails[$reconciliation]."'";
            $result = mysql2_query_secure($sql_selected_data, $_SESSION['OCS']["readServer"]);
            while ($admininfo_default_data = mysqli_fetch_array($result)) {
                $config['SELECTED_VALUE'][$key] = $admininfo_default_data[$value];
            }
        }
    }

    $tab_typ_champ = show_field($name_field, $type_field, $value_field, $config);

    if ($_SESSION['OCS']['profile']->getConfigValue('ACCOUNTINFO') == 'YES') {
        $tab_hidden = array('ADMIN' => '', 'UP' => '', 'DOWN' => '');
    }

    $showbutton = true;

    if ($_SESSION['OCS']['profile']->getConfigValue('CHANGE_ACCOUNTINFO') != "YES") {
        $showbutton = false;
    }

    echo "<div class='row' style='margin-top:10px'>";

    if($_SESSION['OCS']['profile']->getConfigValue('ACCOUNTINFO') == 'YES'){
        echo "<ul class='nav nav-tabs pull-right'>";
            echo $show_admin_button;
        echo "</ul>";
    }

    echo "<div class='col col-md-6 col-md-offset-3'>";
    modif_values($tab_name, $tab_typ_champ, $tab_hidden ?? [], array(
        'show_button' => $showbutton,
        'form_name' => $form_name = 'NO_FORM',
        'top_action' => $show_admin_button,
        'show_frame' => false
    ));
    echo "</div>";
    echo "</div>";

    echo close_form();
    echo "</li>";
    echo "</ul>";
    echo "</div>";
    echo "</div>";

    echo "</div>";
}

echo "</div>";

if (AJAX) {
    ob_end_clean();
}
