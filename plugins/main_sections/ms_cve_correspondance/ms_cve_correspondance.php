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
    $tab_options = $protectedPost;
}

require('require/cve/Cve.php');
$cve = new Cve();

//form name
$form_name = 'cve_correspondance';
//form open
echo open_form($form_name, '', '', 'form-horizontal');
//definition of onglet
$def_onglets['NEW_CORR'] = $l->g(1473); //Category list.
$def_onglets['LIST_CORR'] = $l->g(1474); //New category

//default => first onglet
if ($protectedPost['onglet'] == "") {
    $protectedPost['onglet'] = "NEW_CORR";
}

//show first lign of onglet
show_tabs($def_onglets,$form_name,"onglet",true);

echo '<div class="col col-md-10" >';

/******************************* NEW CORRESPONDANCE ******************************/
if($protectedPost['onglet'] == "NEW_CORR") {

    if(isset($protectedPost['valid'])){
        $result = $cve->add_regex($protectedPost['regex'], $protectedPost['publish_result'], $protectedPost['name_result']);
        if($result == false){
          msg_error($l->g(573));
        }else{
          msg_success($l->g(572));
        }
    }

    if(isset($protectedPost['valid_csv'])){
        $result = $cve->csv_treatment($_FILES);
        if($result == false){
          msg_error($l->g(573));
        }else{
          msg_success($l->g(572));
        }
    }

    echo "<div class='row margin-top30'>
            <div class='col-sm-10'>";
    formGroup('text', 'regex', $l->g(1475).' :', '', '', '', '', '', '', "required");
    echo "<p>".$l->g(358)."</p><br>";
    formGroup('text', 'publish_result', $l->g(1476).' :', '', '', '', '', '', '', '');
    formGroup('text', 'name_result', $l->g(1477).' :', '', '', '', '', '', '', "");
    echo "<input type='submit' name='valid' id='valid' class='btn btn-success' value='".$l->g(13)."'>";
    echo "</div></div>";

    echo close_form();
    $url = $cve->getUrl();
    // Open new form for csv file
    echo open_form('cve_csv', '', 'enctype="multipart/form-data"', 'form-horizontal');
    echo "<div class='row margin-top30'>
            <div class='col-sm-10'>";

    echo "<a href='".$url."/ocsreports/files/cve/csv_example.csv' download>".$l->g(1480)."</a>";
    echo "<br><br>";
    formGroup('file', 'csv_file', $l->g(1478).' :', '', '', $protectedPost['csv_file'], '', '', '', "accept='.csv'");
    echo "<input type='submit' name='valid_csv' id='valid_csv' class='btn btn-success' value='".$l->g(1479)."'>";
    echo "</div></div>";
}
/******************************* LIST CORRESPONDANCE ******************************/
if($protectedPost['onglet'] == "LIST_CORR") {
    //delete regex
    if (is_defined($protectedPost['SUP_PROF'])) {
        $reqDcatall = "DELETE FROM cve_search_correspondance WHERE ID = ".$protectedPost['SUP_PROF'];
        mysqli_query($_SESSION['OCS']["writeServer"], $reqDcatall) or die(mysqli_error($_SESSION['OCS']["writeServer"]));
        unset($protectedPost['SUP_PROF']);
    }

    $sql['SQL'] = 'SELECT * FROM cve_search_correspondance';

    $list_fields = array(
        $l->g(1475) => 'NAME_REG',
        $l->g(1476) => 'PUBLISH_RESULT',
        $l->g(1477) => 'NAME_RESULT',
    );

    $list_fields['SUP'] = 'ID';
    $list_col_cant_del = array('SUP' => 'SUP');
    $default_fields = $list_fields;
    $list_col_cant_del = $default_fields;
    $tab_options['ARG_SQL'] = $sql['ARG'];
    $tab_options['form_name'] = $form_name;
    $tab_options['table_name'] = $form_name;

    $result = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
}

echo '</div>';
echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $sql['SQL'], $tab_options);
}