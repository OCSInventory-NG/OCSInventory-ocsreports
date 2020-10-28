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

if(isset($protectedPost['SUP_PROF'])){
    $sqlQuery = "DELETE FROM `save_query` WHERE ID = %s";
    $sqlArg = [$protectedPost['SUP_PROF']];
    mysql2_query_secure($sqlQuery, $_SESSION['OCS']["writeServer"], $sqlArg);
}

printEnTete($l->g(2141));

$tab_options = $protectedPost;
$form_name = "affich_save_query";
$table_name = $form_name;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;
echo open_form($form_name, '', '', 'form-horizontal');


//definition of onglet
$def_onglets['QUERY_LIST'] = $l->g(2141); //Category list.
$def_onglets['NEW_QUERY'] = $l->g(2142); //New category

//default => first onglet
if ($protectedPost['onglet'] == "") {
    $protectedPost['onglet'] = "QUERY_LIST";
}

echo '  <div class="col col-md-2">
            <ul class="nav nav-pills nav-stacked navbar-left">
                <li id="current" class="active">
                    <a onclick="pag(\'QUERY_LIST\',\'onglet\',\'affich_save_query\')">'.$l->g(2141).'</a>
                </li>
                <li>
                    <a href="?function=visu_search">'.$l->g(2142).'</a>
                </li>
            </ul>
        </div>';

echo '<div class="col col-md-10" >';

$list_fields = array(
    'name' => 'QUERY_NAME',
    $l->g(53) => 'DESCRIPTION',
);

$tab_options['LIEN_LBL']['name'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_multi_search'] . '&prov=querysave&value=';
$tab_options['LIEN_CHAMP']['name'] = 'id';
$tab_options['LBL']['name'] = $l->g(49);
$list_fields['SUP'] = 'ID';
$tab_options['LBL_POPUP']['SUP'] = 'QUERY_NAME';

$default_fields = $list_fields;
$list_col_cant_del = $list_fields;

$queryDetails = "select ID, QUERY_NAME, DESCRIPTION, ID AS id from save_query";

ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);

echo "</div>";
echo close_form();
if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
    ob_start();
}

?>