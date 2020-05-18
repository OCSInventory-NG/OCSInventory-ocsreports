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

if($cve->CVE_ACTIVE != 1){
    $msg = $l->g(1464)."</br>".$l->g(1466);
    msg_info($msg);
}else{
    //form name
    $form_name = 'cve_form';
    //form open
    echo open_form($form_name, '', '', 'form-horizontal');

    //definition of onglet
    $def_onglets['ALL_CVE'] = $l->g(1465); //All CVE.
    $def_onglets['0-1'] = $l->g(1468).' [ 0-1 ]';
    $def_onglets['1-2'] = $l->g(1468).' [ 1-2 ]';
    $def_onglets['2-3'] = $l->g(1468).' [ 2-3 ]';
    $def_onglets['3-4'] = $l->g(1468).' [ 3-4 ]';
    $def_onglets['4-5'] = $l->g(1468).' [ 4-5 ]';
    $def_onglets['5-6'] = $l->g(1468).' [ 5-6 ]';
    $def_onglets['6-7'] = $l->g(1468).' [ 6-7 ]';
    $def_onglets['7-8'] = $l->g(1468).' [ 7-8 ]';
    $def_onglets['8-9'] = $l->g(1468).' [ 8-9 ]';
    $def_onglets['9-10'] = $l->g(1468).' [ 9-10 ]';
    $def_onglets['10'] = $l->g(1468).' [ 10 ]';

    //default => first onglet
    if ($protectedPost['onglet'] == "") {
        $protectedPost['onglet'] = "ALL_CVE";
    }

    //show first ligne of onglet
    show_tabs($def_onglets,$form_name,"onglet",true);

    echo '<div class="col col-md-10">';

    /******************************* ALL CVE *******************************/
    if($protectedPost['onglet'] == "ALL_CVE"){
        $sql['SQL'] = 'SELECT *, p.PUBLISHER, CONCAT(n.NAME,";",v.VERSION) as search, c.LINK as id 
                    FROM cve_search c LEFT JOIN software_name n ON n.ID = c.NAME_ID
                    LEFT JOIN software_publisher p ON p.ID = c.PUBLISHER_ID
                    LEFT JOIN software_version v ON v.ID = c.VERSION_ID
                    GROUP BY c.LINK, c.CVSS, c.NAME_ID, c.CVE';
    }

    /******************************* PER VULNERABILITIES *******************************/
    else{
        $sql['SQL'] = 'SELECT *, p.PUBLISHER, CONCAT(n.NAME,";",v.VERSION) as search, c.LINK as id 
                        FROM cve_search c LEFT JOIN software_name n ON n.ID = c.NAME_ID
                        LEFT JOIN software_publisher p ON p.ID = c.PUBLISHER_ID
                        LEFT JOIN software_version v ON v.ID = c.VERSION_ID ';
        if($protectedPost['onglet'] != '10'){
            $vulnerability = explode('-', $protectedPost['onglet']);
            $sql['SQL'] .= 'WHERE c.CVSS >= '. doubleval($vulnerability[0]) .' AND c.CVSS < '. doubleval($vulnerability[1]);  
        }else{
            $sql['SQL'] .= 'WHERE cvss = '. addslashes($protectedPost['onglet']);
        }
        $sql['SQL'] .= ' GROUP BY c.LINK, c.CVSS, c.NAME_ID, c.CVE';
    }

    if (isset($sql)) {
        $list_fields = array($l->g(69) => 'PUBLISHER',
            'soft' => 'NAME',
            'Version' => 'VERSION',
            'CVSS' => 'CVSS',
            'CVE' => 'CVE',
            'Link' => 'LINK'
        );
        $default_fields = $list_fields;
        $list_col_cant_del = $default_fields;
        $tab_options['LIEN_LBL']['soft'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_multi_search'] . '&prov=allsoft&value=';
        $tab_options['LIEN_CHAMP']['soft'] = 'search';
        $tab_options['LBL']['soft'] = $l->g(847);
        $tab_options['LIEN_LBL']['link'] = ' ';
        $tab_options['LIEN_CHAMP']['link'] = 'link';
        $tab_options['LBL']['link'] = $l->g(1467);
        $tab_options['ARG_SQL'] = $sql['ARG'];
        $tab_options['form_name'] = $form_name;
        $tab_options['table_name'] = $form_name;
        $result_exist = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
    }

    echo "</div>";
    echo close_form();

}

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $sql['SQL'], $tab_options);
}
?>