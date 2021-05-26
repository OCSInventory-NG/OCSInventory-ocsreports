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

require_once('require/cve/Cve.php');
$cve = new Cve();

printEnTete($l->g(1463));

//If RESET
if ($protectedPost['RESET']) {
    unset($protectedPost['FILTRE1']);
    unset($protectedPost['FILTRE2']);
}

if($cve->CVE_ACTIVE != 1){
    $msg = $l->g(1464)."</br>".$l->g(1466);
    msg_info($msg);
}else{
    //form name
    $form_name = 'cve_form';
    //form open
    echo open_form($form_name, '', '', 'form-horizontal');

    //definition of onglet
    $def_onglets['BY_CVSS'] = $l->g(1465); //All CVE by CVSS.
    $def_onglets['BY_SOFT'] = $l->g(1485); //All CVE by software.
    $def_onglets['BY_COMPUTER'] = $l->g(1486); //All CVE by computer.

    //default => first onglet
    if ($protectedPost['onglet'] == "") {
        $protectedPost['onglet'] = "BY_CVSS";
    }

    //show first ligne of onglet
    show_tabs($def_onglets,$form_name,"onglet",true);

    echo '<div class="col col-md-10">';

    /******************************* BY CVSS *******************************/
    if($protectedPost['onglet'] == "BY_CVSS"){

        //Filter CVSS
        if ($protectedPost['FILTRE1'] != "" && $protectedPost['FILTRE2'] != "") {
            $query = " WHERE c.CVSS BETWEEN %s AND %s ";
            $sql['ARG'] = array($protectedPost['FILTRE1'], $protectedPost['FILTRE2']);
        }

        $sql['SQL'] = 'SELECT *, p.PUBLISHER, CONCAT(n.NAME,";",v.VERSION) as search, c.LINK as id 
                    FROM cve_search c LEFT JOIN software_name n ON n.ID = c.NAME_ID
                    LEFT JOIN software_publisher p ON p.ID = c.PUBLISHER_ID
                    LEFT JOIN software_version v ON v.ID = c.VERSION_ID';
        
        if($query != null) {
            $sql['SQL'] .= $query;
        }

        $sql['SQL'] .= ' GROUP BY c.LINK, c.CVSS, c.NAME_ID, c.CVE';

        $list_fields = array(
            $l->g(69) => 'PUBLISHER',
            'soft' => 'NAME',
            'Version' => 'VERSION',
            'CVSS' => 'CVSS',
            'CVE' => 'CVE',
            'Link' => 'LINK'
        );
    }

    /******************************* BY SOFTWARE *******************************/
    if($protectedPost['onglet'] == "BY_SOFT"){
        $sql['SQL'] = 'SELECT *, p.PUBLISHER, CONCAT(n.NAME) as search, c.LINK as id, c.NAME_ID as nameid
                    FROM cve_search c LEFT JOIN software_name n ON n.ID = c.NAME_ID
                    LEFT JOIN software_publisher p ON p.ID = c.PUBLISHER_ID
                    LEFT JOIN software_version v ON v.ID = c.VERSION_ID
                    GROUP BY n.ID';
            
        $list_fields = array(
            $l->g(69) => 'PUBLISHER',
            'soft' => 'NAME'
        );

        $list_fields['SHOW_DETAILS'] = 'nameid';
    }

    /******************************* BY COMPUTER *******************************/
    if($protectedPost['onglet'] == "BY_COMPUTER"){
        $sql['SQL'] = 'SELECT *, CONCAT(c.SOFTWARE_NAME,";",c.PUBLISHER,";",c.VERSION) as search FROM cve_search_computer c';

        $list_fields = array(
            'computer' => 'HARDWARE_NAME',
            $l->g(69) => 'PUBLISHER',
            'soft' => 'SOFTWARE_NAME',
            'Version' => 'VERSION',
            'CVSS' => 'CVSS',
            'CVE' => 'CVE',
            'Link' => 'LINK'
        );
    }

    if (isset($sql)) {
        $default_fields = $list_fields;
        $list_col_cant_del = $default_fields;
        $tab_options['LIEN_LBL']['soft'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_multi_search'] . '&prov=allsoft&value=';
        $tab_options['LIEN_CHAMP']['soft'] = 'search';
        $tab_options['LBL']['soft'] = $l->g(847);
        $tab_options['LIEN_LBL']['computer'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_computer'] . '&head=1&systemid=';
        $tab_options['LIEN_CHAMP']['computer'] = 'HARDWARE_ID';
        $tab_options['LBL']['computer'] = $l->g(23);
        $tab_options['LIEN_LBL']['Link'] = ' ';
        $tab_options['LIEN_CHAMP']['Link'] = 'LINK';
        $tab_options['LBL']['Link'] = $l->g(1467);
        $tab_options['ARG_SQL'] = $sql['ARG'];
        $tab_options['form_name'] = $form_name;
        $tab_options['table_name'] = $form_name;
        $result_exist = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
    }

    /***************** CVSS FILTER *******************/
    if($protectedPost['onglet'] == "BY_CVSS") {
        echo "<button type='button' data-toggle='collapse' data-target='#filter' class='btn'>" . $l->g(735) . "</button>";

        echo "<div id='filter' class='collapse'>";
        echo "<br/>";

        echo '<div class="form-group">
        <div class="col-sm-3"></div>
                <label class="control-label col-sm-2" for="FILTRE1">'.$l->g(1468).'</label>
                <div class="col-sm-1">
                <input name="FILTRE1" id="FILTRE1" type="number" class="form-control" min="0" max="10" value="'.$protectedPost['FILTRE1'].'">';
        echo '</div>
            <label class="control-label col-sm-1" for="FILTRE2">'.$l->g(582).'</label>
            <div class="col-sm-1">
                <input name="FILTRE2" id="FILTRE2" type="number" class="form-control" min="0" max="10" value="'.$protectedPost['FILTRE2'].'">
            </div>
        </div>

        <input type="submit" class="btn btn-success" value="'.$l->g(393).'" name="SUBMIT_FORM">
        <input type="submit" class="btn btn-danger" value="'.$l->g(396).'" name="RESET">';

        echo "</div>";
    }

    if($protectedPost['onglet'] == "BY_SOFT") {
        $infos = $cve->get_software_infos();
        $columns = [
            "VERSION" => "Version",
            "CVE" => "CVE",
            "CVSS" => "CVSS",
            "LINK" => $l->g(1467)
        ];

        foreach ($infos as $key => $values) {
            echo '<div class="modal fade" id="'.$key.'" tabindex="-1" role="dialog" aria-labelledby="detailLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div class="row">
                                <div class="col-md-2"></div>
                                <div class="col-md-7">
                                <h3 class="modal-title" id="detailLabel"><b>'.$values['NAME'].'</b></h3>
                                </div>
                                <div class="col-md-3 ml-auto">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                </div>
                                </div>
                            </div>';
                            foreach($values as $label => $item) {
                                if($label != "NAME") {
                                    echo '<div class="modal-body" style="text-align:left;">
                                    <table style="width:100%" class="table table-striped table-condensed table-hover cell-border dataTable" role="grid">';
                                    foreach($columns as $namec => $column) {
                                        echo '<tr role="row">';
                                        echo '<th style="font-size:12px;">'.$column.'</th>';
                                        echo '<td>';
                                        if($namec == 'LINK') {
                                            echo '<a href="'.$item[$namec].'">'.$item[$namec].'</a>';
                                        } else {
                                            echo $item[$namec];
                                        }
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</table>
                                    </div></br>';
                                }
                            }
            echo '      </div>
                    </div>
                </div>';
        }
    }

    echo "</div>";
    echo close_form();

}

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $sql['SQL'], $tab_options);
}
?>