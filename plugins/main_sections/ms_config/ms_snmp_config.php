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
require_once('require/commandLine/CommandLine.php');
$snmp = new OCSSnmp();
$command = new CommandLine();

$def_onglets['SNMP_RULE'] = $l->g(9001); // Create SNMP type.
$def_onglets['SNMP_CONDITION'] = $l->g(9031); // Configure type conditions
$def_onglets['SNMP_LABEL'] = $l->g(9003); // Create SNMP label
$def_onglets['SNMP_TYPE'] = $l->g(9002);  // Configure SNMP type
$def_onglets['SNMP_MIB'] = $l->g(9008);  // Add MIB file


if(isset($protectedPost['add_mib'])) {
    $result_mib = $snmp->sort_mib($protectedPost);
    if(!$result_mib) {
        msg_error($l->g(573));
        $protectedPost['onglet'] = "SNMP_MIB";
        unset($protectedPost['add_mib']);
    } else {
        msg_success($l->g(572));
        $protectedPost['onglet'] = "SNMP_TYPE";
        unset($protectedPost['add_mib']);
    }
}

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
        if($result_remove == 0){
            msg_success($l->g(572));
        }else{
            msg_error($l->g($result_remove));
        }
    }

    if(isset($protectedPost['create_type'])) {
        $result = $snmp->create_type($protectedPost['type_name']);
        if($result == 0){
          msg_success($l->g(572));
        }else{
          msg_error($l->g($result));
        }
        unset($protectedPost['create_type']);
    }

    echo "<div class='row margin-top30'>
            <div class='col-sm-10'>";
    msg_info($l->g(9007));

    formGroup('text', 'type_name', $l->g(308).' :', '', '', '', '', '', '', "required");

    echo "<input type='submit' name='create_type' id='create_type' class='btn btn-success' value='".$l->g(13)."'>";
    echo "</div></div></br></br></br></br>";

    // Display table of all type created
    $list_fields = array(
        $l->g(49) => 'TYPE_NAME',
    );

    $list_fields['SUP'] = 'ID';
    $tab_options['LBL_POPUP']['SUP'] = 'TYPE_NAME';

    $default_fields = $list_fields;
    $list_col_cant_del = $list_fields;

    $queryDetails = "SELECT DISTINCT ID, TYPE_NAME FROM snmp_types";

    ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
}

/*******************************************SNMP CONDITION*****************************************************/

if($protectedPost['onglet'] == 'SNMP_CONDITION') {

    if(isset($protectedPost['SUP_PROF']) && $protectedPost['SUP_PROF'] != ""){
        // Remove type
        $result_remove = $snmp->delete_type_condition($protectedPost['SUP_PROF']);
        unset($protectedPost['SUP_PROF']);
        if($result_remove == 0){
            msg_success($l->g(572));
        }else{
            msg_error($l->g($result_remove));
        }
    }

    if(isset($protectedPost['create_type_condition'])) {
        $result = $snmp->create_type_condition($protectedPost['type_id'], $protectedPost['condition_oid'], $protectedPost['condition_value']);
        if($result == 0){
          msg_success($l->g(572));
        }else{
          msg_error($l->g($result));
        }
        unset($protectedPost['create_type_condition']);
    }

    if($protectedPost['type_filter'] != "empty" && $protectedPost['type_filter'] != null) {
        $filter = " WHERE c.TYPE_ID = ".$protectedPost['type_filter'];
    } else {
        $filter = "";
    }

    echo "<div class='row margin-top30'>
            <div class='col-sm-10'>";

    $type = $snmp->get_type();

    formGroup('select', 'type_id', 'Type :', '', '', '', '', $type, $type);
    formGroup('text', 'condition_oid', $l->g(9004).' :', '', '', '', '', '', '', '');
    formGroup('text', 'condition_value', $l->g(9005).' :', '', '', '', '', '', '', '');
    echo "<p>".$l->g(9032)."</p>";

    echo "<input type='submit' name='create_type_condition' id='create_type_condition' class='btn btn-success' value='".$l->g(13)."'>";
    echo "</br></br><hr></br>";
    echo "</div></div>";

    $filter_type = ["empty" => "No filter"];
    foreach($type as $id => $name) {
        $filter_type[$id] = $name;
    }

    echo "<div class='row margin-top30'>
            <div class='col-sm-6'>";
    formGroup('select', 'type_filter', $l->g(9011), '', '', $protectedPost['type_filter'], '', $filter_type, $filter_type);
    echo "</div>";
    echo "<div class='col-sm-2'>";
    echo "<input type='submit' name='filter_snmp' id='filter_snmp' class='btn btn-info' value='".$l->g(1109)."'>";
    echo "</div></div></br>";

    // Display table of all type created
    $list_fields = array(
        $l->g(49) => 't.TYPE_NAME',
        'OID' => 'c.CONDITION_OID',
        'Value' => 'c.CONDITION_VALUE',
    );

    $list_fields['SUP'] = 'ID';
    $tab_options['LBL_POPUP']['SUP'] = 'CONDITION_VALUE';

    $default_fields = $list_fields;
    $list_col_cant_del = $list_fields;

    $queryDetails = "SELECT DISTINCT c.ID as ID, t.TYPE_NAME, c.CONDITION_OID, c.CONDITION_VALUE
                    FROM snmp_types_conditions c LEFT JOIN snmp_types t ON t.ID = c.TYPE_ID".$filter;

    ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
}

/*******************************************SNMP LABEL*****************************************************/

if($protectedPost['onglet'] == 'SNMP_LABEL') {

    if(isset($protectedPost['SUP_PROF']) && $protectedPost['SUP_PROF'] != ""){
        // Remove type
        $result_remove = $snmp->delete_label($protectedPost['SUP_PROF']);
        unset($protectedPost['SUP_PROF']);
        if($result_remove == 0){
            msg_success($l->g(572));
        }else{
            msg_error($l->g($result_remove));
        }
    }

    if(isset($protectedPost['create_label'])) {
        $result = $snmp->create_label($protectedPost['label_name']);
        if($result == 0){
          msg_success($l->g(572));
        }else{
          msg_error($l->g($result));
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
        if($result_remove == 0){
            msg_success($l->g(572));
        }else{
            msg_error($l->g($result_remove));
        }
    }

    if(isset($protectedPost['update_snmp'])) {
        $result = $snmp->snmp_config($protectedPost['type_id'], $protectedPost['label_id'], $protectedPost['oid'], $protectedPost['reconciliation']);
        if($result == 0){
          msg_success($l->g(572));
        }else{
          msg_error($l->g($result));
        }
        unset($protectedPost['update_snmp']);
    }

    if($protectedPost['type_filter'] != "empty" && $protectedPost['type_filter'] != null) {
        $filter = " WHERE c.TYPE_ID ='".$protectedPost['type_filter']."'";
    } else {
        $filter = "";
    }

    $type = $snmp->get_type();
    $label = $snmp->get_label();

    echo "<div class='row margin-top30'>
            <div class='col-sm-10'>";

    formGroup('select', 'type_id', 'Type :', '', '', '', '', $type, $type);
    formGroup('select', 'label_id', 'Label :', '', '', '', '', $label, $label);
    formGroup('text', 'oid', 'OID :', '', '', '', '', '', '', "");
    formGroup('checkbox', 'reconciliation', $l->g(9015).' :', '', '', 'YES', '', '', '');

    echo "<input type='submit' name='update_snmp' id='update_snmp' class='btn btn-success' value='".$l->g(13)."'>";
    echo "</br></br><hr></br>";
    echo "</div></div>";

    $filter_type = ["empty" => "No filter"];
    foreach($type as $id => $name) {
        $filter_type[$id] = $name;
    }

    echo "<div class='row margin-top30'>
            <div class='col-sm-6'>";
    formGroup('select', 'type_filter', $l->g(9011), '', '', $protectedPost['type_filter'], '', $filter_type, $filter_type);
    echo "</div>";
    echo "<div class='col-sm-2'>";
    echo "<input type='submit' name='filter_snmp' id='filter_snmp' class='btn btn-info' value='".$l->g(1109)."'>";
    echo "</div></div></br>";

    // Display table of all type configuration
    $list_fields = array(
        "Type" => 't.TYPE_NAME',
        "Label" => 'l.LABEL_NAME',
        "OID" => "c.OID",
        "RECONCILIATION" => "c.RECONCILIATION"
    );

    $list_fields['SUP'] = 'c.ID';
    $tab_options['LBL_POPUP']['SUP'] = 'LABEL_NAME';

    $default_fields = $list_fields;
    $list_col_cant_del = $list_fields;

    $queryDetails = "SELECT DISTINCT c.ID, t.TYPE_NAME, l.LABEL_NAME, c.OID, c.RECONCILIATION FROM snmp_configs c 
                        LEFT JOIN snmp_types t ON c.TYPE_ID = t.ID
                        LEFT JOIN snmp_labels l ON c.LABEL_ID = l.ID". $filter;

    ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);

}

/*******************************************SNMP TYPE*****************************************************/

if($protectedPost['onglet'] == 'SNMP_MIB') {

    if(isset($protectedPost['SUP_PROF']) && $protectedPost['SUP_PROF'] != ""){
        // Remove config
        $result_remove = $snmp->delete_config($protectedPost['SUP_PROF']);
        unset($protectedPost['SUP_PROF']);
        if($result_remove == 0){
            msg_success($l->g(572));
        }else{
            msg_error($l->g($result_remove));
        }
    }

    if(isset($protectedPost['update_snmp'])) {
        $result_oids = $command->get_mib_oid($protectedPost['mib_file']);

        $protectedPost['select_mib'] = true;
        unset($protectedPost['update_snmp']);
    }

    $mib = $snmp->get_mib();

    echo "<div class='row margin-top30'>
            <div class='col-sm-10'>";
    msg_info($l->g(9009));
    formGroup('select', 'mib_file', 'MIB :', '', '', '', '', $mib, $mib);

    echo "<input type='submit' name='update_snmp' id='update_snmp' class='btn btn-success' value='".$l->g(13)."'>";
    echo "</div></div></br></br>";
}

echo "</div>";
echo close_form();

if(isset($protectedPost['select_mib'])) {

    $type = $snmp->get_type();
    $label = $snmp->get_label();

    echo '<div name="snmp_mib_list">';
    echo open_form('snmp_mib_list', '', '', 'form-horizontal');
    echo '<div class="col col-md-2" ></div>
          <div class="col col-md-10"><hr>';
    echo '<div class="row margin-top30" style="margin-bottom:30px;">
            <div class="col-sm-10">';
    msg_info($l->g(9016));
    formGroup('select', 'type_id', 'Type :', '', '', '', '', $type, $type);
    echo "</div></div></br>";
    echo '</div>';

    echo '<div class="row" name="snmp_row">';
    echo '<div class="col-sm-1"></div>';
    echo '<div class="col-sm-10">';
    echo '<div class="col-md-3" style="padding-left:0;">';
    echo '<input type="text" id="myInput" class="form-control" onkeyup="searchInMIB()" placeholder="'.$l->g(9021).' ..">';
    echo '</div>';
    echo '<div class="tableContainer">
            <div id="affich_regex_wrapper" class="dataTables_wrapper form-inline no-footer">
                <div>
                    <div class="dataTables_scroll">
                        <div class="dataTables_scrollHead" style="overflow: hidden; position: relative; border: 0px; width: 100%;">
                            <div class="dataTables_scrollHeadInner" style="box-sizing: content-box; width: 100%; padding-left: 0px;">
                                <table width="100%" class="table table-striped table-condensed table-hover cell-border dataTable no-footer" role="grid" style="margin-left: 0px; width: 100%;">
                                    <thead>
                                        <tr role="row">
                                            <th tabindex="0" aria-controls="affich_regex" rowspan="1" colspan="1" style="width: 12%;" aria-label="Use"><font>'.$l->g(9020).'</font></th>
                                            <th tabindex="0" aria-controls="affich_version" rowspan="1" colspan="1" style="width: 22%;" aria-label="Description"><font>'.$l->g(9019).'</font></th>
                                            <th tabindex="0" aria-controls="affich_version" rowspan="1" colspan="1" style="width: 22%;" aria-label="Label"><font>'.$l->g(9018).'</font></th>
                                            <th tabindex="0" aria-controls="affich_publisher" rowspan="1" colspan="1" style="width: 22%;" aria-label="Numeric OID"><font>'.$l->g(9017).'</font></th>
                                            <th tabindex="0" aria-controls="affich_version" rowspan="1" colspan="1" style="width: 12%;" aria-label="Reconciliation"><font>'.$l->g(9015).'</font></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>';
    echo '              <div class="dataTables_scrollBody" style="overflow: auto; width: 100%;">
                        <table id="mib_info" class="table table-striped table-condensed table-hover cell-border dataTable" role="grid" aria-describedby="affich_regex_info" style="width: 100%;">
                            <tbody>';
    
    foreach($result_oids as $name => $oid) {
        echo '                  <tr class="odd">';
        echo '                      <td valign="top" colspan="1" style="width: 12%; text-align:center;"><input type="checkbox" class="perso_checkbox" id="checkbox_'.$name.'" name="checkbox_'.$name.'" value="YES"></td>';
        echo '                      <td valign="top" colspan="1" style="width: 22%; text-align:center;">'.$name.'</td>';
        echo '                      <td valign="top" colspan="1" style="width: 22%; text-align:center;"><select style="width: 100%;" class="form-control" type="text" name="label_'.$name.'">';
                                    foreach($label as $id => $lbl) {
                                        echo '<option value="'.$id.'">'.$lbl.'</option>';
                                    }
        echo '                      </td>';
        echo '                      <td valign="top" colspan="1" style="width: 22%; text-align:center;" class="affich_publisher">'.$oid.'<input class="form-control" type="hidden" name="oid_'.$name.'" value="'.$oid.'"></td>';
        echo '                      <td valign="top" colspan="1" style="width: 12%; text-align:center;" class="affich_publisher"><input type="checkbox" class="perso_checkbox" id="reconciliation_'.$name.'" name="reconciliation_'.$name.'" value="YES"></td>';
        echo '                  </tr>';
    }

    echo '                  </tbody>';
    echo '              </table>';
    echo '              </div>
                    </div>
                </div>
            </div>
        </div>';

    echo "<input type='submit' name='add_mib' id='add_mib' class='btn btn-success' value='".$l->g(13)."'>";
    echo "</div></div>";
    echo close_form();
}

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
}

