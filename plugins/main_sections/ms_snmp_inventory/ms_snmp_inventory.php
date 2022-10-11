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
require_once('require/admininfo/Admininfo.php');

$snmp = new OCSSnmp();
$Admininfo = new Admininfo();

$typeList = $snmp->get_all_type();
$columns = [];

if(empty($typeList)) {
    msg_info($l->g(9014));
} else {

    //definition of onglet
    foreach($typeList as $id => $values) {
        $def_onglets[$id] = $values['TYPENAME'];
        if (empty($protectedPost['onglet'])) {
            $protectedPost['onglet'] = $id;
        }
    }

    $count = count($def_onglets);
    $form_name = "snmp_inventory";

    $table_name = $form_name;

    echo open_form($form_name, '', '', 'form-horizontal');

    //show first lign of onglet
    if($count < 15){
        show_tabs($def_onglets,$form_name,"onglet",true);
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
        echo "<a href='index.php?" . PAG_INDEX . "=" . $pages_refs['ms_csv_snmp'] . "&no_header=1&tablename=all_snmp_export' class='btn btn-action'>".$l->g(9040)."</a>";
        print_item_header($typeList[$protectedPost['onglet']]['TYPENAME']);
        $columns = $snmp->show_columns($typeList[$protectedPost['onglet']]['TABLENAME']);

        $tab_options = $protectedPost;
        $tab_options['form_name'] = $form_name;
        $tab_options['table_name'] = $table_name;

        $accountinfo_value = $Admininfo->interprete_accountinfo($list_fields ?? null, $tab_options, 'SNMP');

        if (array($accountinfo_value['TAB_OPTIONS'])) {
            $tab_options = $accountinfo_value['TAB_OPTIONS'];
        }
        if (array($accountinfo_value['DEFAULT_VALUE'])) {
            $default_fields = $accountinfo_value['DEFAULT_VALUE'];
        }

        $list_fields = $accountinfo_value['LIST_FIELDS'];

        for($i = 0; !empty($columns[$i]); $i++) {
            if($i <= 3) {
                if($columns[$i] == "LASTDATE") {
                    $list_fields2[$l->g(46)] = "s.".$columns[$i];
                } else {
                    $list_fields2[$columns[$i]] = "s.".$columns[$i];
                }
            } else {
                $list_fields3[$columns[$i]] = "s.".$columns[$i];
            }
        }
        $list_fields2['SHOW_DETAILS'] = 'snmp_id';
        $list_fields2['NEW_WINDOW'] = 'tablename';
        $list_fields2['CHECK'] = 'snmp_id';
        $list_fields2['SUP'] = 'snmp_id';
        $list_col_cant_del = $list_fields2;

        $list_fields = array_merge($list_fields, $list_fields2);
        $default_fields = array_merge($default_fields, $list_fields2);

        if(!empty($list_fields3)) {
            $list_fields = array_merge($list_fields,$list_fields3);
        }

        $sql = prepare_sql_tab($list_fields, array('SUP', 'CHECK', 'SHOW_DETAILS', 'NEW_WINDOW'));
        
        $tab_options['FILTRE'] = array_flip($list_fields);

        $queryDetails = $sql['SQL'].", CONCAT(s.ID, ';','".$typeList[$protectedPost['onglet']]['TABLENAME']."') as tablename, s.ID as snmp_id 
                        FROM ".$typeList[$protectedPost['onglet']]['TABLENAME']." s 
                        LEFT JOIN snmp_accountinfo a ON a.SNMP_RECONCILIATION_VALUE = s.".$snmp->getReconciliationColumn($typeList[$protectedPost['onglet']]['TABLENAME'])."
                        AND a.SNMP_TYPE = '".$typeList[$protectedPost['onglet']]['TABLENAME']."'";
        
        $tab_options['ARG_SQL'] = $sql['ARG'];

        ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);


        $infos = $snmp->get_infos($typeList[$protectedPost['onglet']]['TABLENAME'], $columns);
        $snmpAdminInfo = $Admininfo->admininfo_snmp(null, $typeList[$protectedPost['onglet']]['TABLENAME']);

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
                                        if($column == "LASTDATE") {
                                            echo '<th>'.$l->g(46).'</th>';
                                        } else {
                                            echo '<th>'.$column.'</th>';
                                        }
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

    require_once('require/function_search.php');

    $list_fonct["image/mass_affect.png"] = $l->g(430);
    $list_pag["image/mass_affect.png"] = $pages_refs["ms_custom_tag_snmp"];
    add_trait_select($list_fonct, $list_id ?? null, $form_name, $list_pag, true,$typeList[$protectedPost['onglet']]['TABLENAME']);
    echo "<br><br>";

    echo "<a href=# OnClick='confirme(\"\",\"DEL_SEL\",\"" . $form_name . "\",\"DEL_ALL\",\"" . $l->g(900) . "\");'><span class='glyphicon glyphicon-remove delete-span'></span></a>";
    echo "<input type='hidden' id='DEL_ALL' name='DEL_ALL' value=''>";

    echo close_form();

}

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
    ob_start();
}



