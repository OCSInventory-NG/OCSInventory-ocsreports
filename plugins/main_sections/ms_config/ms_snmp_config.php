<?php
/*
 * Copyright 2005-2019 OCSInventory-NG/OCSInventory-ocsreports contributors.
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
$snmp = new Snmp();

$def_onglets['SNMP_RULE'] = $l->g(9001); // Create SNMP type.
$def_onglets['SNMP_LABEL'] = $l->g(9003); // Create SNMP label
$def_onglets['SNMP_TYPE'] = $l->g(9002);  // Configure SNMP type

//default => first onglet
if ($protectedPost['onglet'] == "") {
    $protectedPost['onglet'] = "SNMP_RULE";
}

printEnTete($l->g(9000));

$form_name = 'snmp_config';
$tab_options = $protectedPost;
$table_name = $form_name;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;

echo open_form($form_name, '', 'enctype="multipart/form-data"', 'form-horizontal');

show_tabs($def_onglets,$form_name,"onglet",true);

echo '<div class="col col-md-10" >';

/*******************************************SNMP RULE*****************************************************/

if($protectedPost['onglet'] == 'SNMP_RULE') {

    if(isset($protectedPost['SUP_PROF']) && $protectedPost['SUP_PROF'] != ""){
        // Remove type
        $result_remove = $snmp->delete_type($protectedPost['SUP_PROF']);
        unset($protectedPost['SUP_PROF']);
        if($result_remove == true){
            msg_success($l->g(572));
        }else{
            msg_error($l->g(573));
        }
    }

    if(isset($protectedPost['create_type'])) {
        $result = $snmp->create_type($protectedPost['type_name'], $protectedPost['condition_oid'], $protectedPost['condition_value']);
        if($result == true){
          msg_success($l->g(572));
        }else{
          msg_error($l->g(573));
        }
        unset($protectedPost['create_type']);
    }

    echo "<div class='row margin-top30'>
            <div class='col-sm-10'>";
    msg_info($l->g(9007));

    formGroup('text', 'type_name', $l->g(308).' :', '', '', '', '', '', '', "required");
    formGroup('text', 'condition_oid', $l->g(9004).' :', '', '', '', '', '', '', "required");
    formGroup('text', 'condition_value', $l->g(9005).' :', '', '', '', '', '', '', "required");

    echo "<input type='submit' name='create_type' id='create_type' class='btn btn-success' value='".$l->g(13)."'>";
    echo "</div></div></br></br></br></br>";

    // Display table of all type created
    $list_fields = array(
        $l->g(49) => 'TYPE_NAME',
        'OID' => 'CONDITION_OID',
        'Value' => 'CONDITION_VALUE',
    );

    $list_fields['SUP'] = 'ID';
    $tab_options['LBL_POPUP']['SUP'] = 'TYPE_NAME';

    $default_fields = $list_fields;
    $list_col_cant_del = $list_fields;

    $queryDetails = "SELECT DISTINCT ID, TYPE_NAME, CONDITION_OID, CONDITION_VALUE FROM snmp_types";

    ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
}

/*******************************************SNMP LABEL*****************************************************/

if($protectedPost['onglet'] == 'SNMP_LABEL') {

    if(isset($protectedPost['SUP_PROF']) && $protectedPost['SUP_PROF'] != ""){
        // Remove type
        $result_remove = $snmp->delete_label($protectedPost['SUP_PROF']);
        unset($protectedPost['SUP_PROF']);
        if($result_remove == true){
            msg_success($l->g(572));
        }else{
            msg_error($l->g(573));
        }
    }

    if(isset($protectedPost['create_label'])) {
        $result = $snmp->create_label($protectedPost['label_name']);
        if($result == true){
          msg_success($l->g(572));
        }else{
          msg_error($l->g(573));
        }
        unset($protectedPost['create_label']);
    }

    echo "<div class='row margin-top30'>
            <div class='col-sm-10'>";
    msg_info($l->g(9007));

    formGroup('text', 'label_name', $l->g(9006).' :', '', '', '', '', '', '', "required");

    echo "<input type='submit' name='create_label' id='create_label' class='btn btn-success' value='".$l->g(13)."'>";
    echo "</div></div></br></br></br></br>";

    // Display table of all label created
    $list_fields = array(
        $l->g(49) => 'LABEL_NAME',
    );

    $list_fields['SUP'] = 'ID';
    $tab_options['LBL_POPUP']['SUP'] = 'LABEL_NAME';

    $default_fields = $list_fields;
    $list_col_cant_del = $list_fields;

    $queryDetails = "SELECT DISTINCT ID, LABEL_NAME FROM snmp_labels";

    ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
}

/*******************************************SNMP TYPE*****************************************************/

if($protectedPost['onglet'] == 'SNMP_TYPE') {

    if(isset($protectedPost['SUP_PROF']) && $protectedPost['SUP_PROF'] != ""){
        // Remove config
        $result_remove = $snmp->delete_config($protectedPost['SUP_PROF']);
        unset($protectedPost['SUP_PROF']);
        if($result_remove == true){
            msg_success($l->g(572));
        }else{
            msg_error($l->g(573));
        }
    }

    if(isset($protectedPost['update_snmp'])) {
        $result = $snmp->snmp_config($protectedPost['type_id'], $protectedPost['label_id'], $protectedPost['oid']);
        if($result == true){
          msg_success($l->g(572));
        }else{
          msg_error($l->g(573));
        }
        unset($protectedPost['update_snmp']);
    }

    $type = $snmp->get_type();
    $label = $snmp->get_label();

    echo "<div class='row margin-top30'>
            <div class='col-sm-10'>";

    formGroup('select', 'type_id', 'Type :', '', '', '', '', $type, $type);
    formGroup('select', 'label_id', 'Label :', '', '', '', '', $label, $label);
    formGroup('text', 'oid', 'OID :', '', '', '', '', '', '', "required");

    echo "<input type='submit' name='update_snmp' id='update_snmp' class='btn btn-success' value='".$l->g(13)."'>";
    echo "</div></div></br></br></br></br>";

    // Display table of all type configuration
    $list_fields = array(
        "Type" => 't.TYPE_NAME',
        "Label" => 'l.LABEL_NAME',
        "OID" => "c.OID",
    );

    $list_fields['SUP'] = 'c.ID';
    $tab_options['LBL_POPUP']['SUP'] = 'LABEL_NAME';

    $default_fields = $list_fields;
    $list_col_cant_del = $list_fields;

    $queryDetails = "SELECT DISTINCT c.ID, t.TYPE_NAME, l.LABEL_NAME, c.OID FROM snmp_configs c 
                        LEFT JOIN snmp_types t ON c.TYPE_ID = t.ID
                        LEFT JOIN snmp_labels l ON c.LABEL_ID = l.ID";

    ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
}

echo "</div>";
echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
} 
