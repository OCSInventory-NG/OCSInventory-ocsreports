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
require_once('require/function_machine.php');

$snmp = new OCSSnmp();

$typeList = $snmp->get_all_type();
$columns = [];

if(empty($typeList)) {
    msg_info($l->g(9014));
} else {

    //definition of onglet
    foreach($typeList as $id => $values) {
        $def_onglets[$id] = $values['TYPENAME'];
        if ($protectedPost['onglet'] == "") {
            $protectedPost['onglet'] = $id;
        }
    }

    $count = count($def_onglets);
    $form_name = "snmp_inventory";

    $table_name = $form_name;

    echo open_form($form_name, '', '', 'form-horizontal');

    //show first lign of onglet
    if($count < 15){
    show_tabs($def_onglets,$form_name,"onglet",true, $i);
    }

    if ($count >= 15) {
        echo "<div class='col col-md-2'>";
        echo show_modif($def_onglets, 'onglet', 2, $form_name) . "</div>";
    }
    echo '<div class="col col-md-10" >';

    if($protectedPost['onglet'] != "") {
        $protectedPost['TABLENAME'] = $typeList[$protectedPost['onglet']]['TABLENAME'];

        if(isset($protectedPost['SUP_PROF']) && $protectedPost['SUP_PROF'] != "") {
            $sql_sup = "DELETE FROM %s WHERE ID = %s";
            $arg_sup = array($protectedPost['TABLENAME'], $protectedPost['SUP_PROF']);
            $result = mysql2_query_secure($sql_sup, $_SESSION['OCS']["writeServer"], $arg_sup);
            unset($protectedPost['SUP_PROF']);
        }

        //del the selection
        if (isset($protectedPost['DEL_ALL']) && $protectedPost['DEL_ALL'] != '') {
            foreach ($protectedPost as $key => $value) {
                $checkbox = explode('check', $key);
                if (isset($checkbox[1])) {
                    $sql_sup_all = "DELETE FROM %s WHERE ID IN (%s)";
                    $arg_sup_all = array($protectedPost['TABLENAME'], $checkbox[1]);
                    $result_all = mysql2_query_secure($sql_sup_all, $_SESSION['OCS']["writeServer"], $arg_sup_all);
                }
            }
            unset($protectedPost['DEL_ALL']);
        }

        print_item_header($typeList[$protectedPost['onglet']]['TYPENAME']);
        $columns = $snmp->show_columns($typeList[$protectedPost['onglet']]['TABLENAME']);

        $tab_options = $protectedPost;
        $tab_options['form_name'] = $form_name;
        $tab_options['table_name'] = $table_name;

        for($i = 0; $columns[$i] != null; $i++) {
            if($i <= 3) {
                $list_fields[$columns[$i]] = $columns[$i];
            } else {
                $list_fields2[$columns[$i]] = $columns[$i];
            }
        }
        $list_fields['SHOW_DETAILS'] = 'ID';
        $list_fields['CHECK'] = 'ID';
        $list_fields['SUP'] = 'ID';
        $list_col_cant_del = $list_fields;
        $default_fields = $list_fields;

        if($list_fields2 != null) {
            $list_fields = array_merge($list_fields,$list_fields2);
        }
        
        $tab_options['FILTRE'] = array_flip($list_fields);
        $queryDetails = "SELECT * FROM ".$typeList[$protectedPost['onglet']]['TABLENAME'];

        ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);

        $infos = $snmp->get_infos($typeList[$protectedPost['onglet']]['TABLENAME'], $columns);

        foreach ($infos as $key => $values) {
            echo '<div class="modal fade" id="'.$key.'" tabindex="-1" role="dialog" aria-labelledby="detailLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div class="row">
                                <div class="col-md-2"></div>
                                <div class="col-md-7">
                                <h3 class="modal-title" id="detailLabel"><b>'.$l->g(9012).'</b></h3>
                                </div>
                                <div class="col-md-3 ml-auto">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                </div>
                                </div>
                            </div>
                            <div class="modal-body" style="text-align:left;">
                                <table style="width:100%" class="table table-striped table-condensed table-hover cell-border dataTable" role="grid">';
                                foreach($columns as $column) {
                                    echo '<tr role="row">';
                                    echo '<th>'.$column.'</th>';
                                    echo '<td>';
                                    if(strpos($values[$column], " - ") !== false) {
                                        $list = explode(" - ", $values[$column]);
                                        natsort($list);
                                        foreach($list as $lists) {
                                            echo $lists.'<br>';
                                        }
                                    } else {
                                        echo $values[$column];
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
            echo '              </table>
                            </div>
                        </div>
                    </div>
                </div>';
        }
        
    }

    echo '</div>';

    echo "<a href=# OnClick='confirme(\"\",\"DEL_SEL\",\"" . $form_name . "\",\"DEL_ALL\",\"" . $l->g(900) . "\");'><span class='glyphicon glyphicon-remove delete-span'></span></a>";
    echo "<input type='hidden' id='DEL_ALL' name='DEL_ALL' value=''>";

    echo close_form();

}

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
    ob_start();
}



