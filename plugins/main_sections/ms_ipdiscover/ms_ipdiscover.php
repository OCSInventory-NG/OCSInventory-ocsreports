<?php
/*
 * Copyright 2005-2020 OCSInventory-NG/OCSInventory-ocsreports contributors.
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

require_once('require/ipdiscover/Ipdiscover.php');
require_once('require/snmp/Snmp.php');

// checking if IPD_RECONCILIATION is set to "Yes" in any snmp config
// if not there will be no SNMP data in IPD table
$snmp = new OCSSnmp();
$snmpTables = $snmp->getIPDReconciliationColumns();

$ipdiscover = new Ipdiscover();

if($ipdiscover->IPDISCOVER_TAG == "1") {
    $groupby1 = "PASS";
    $groupby2 = "PASS";
    $groupby3 = "PASS";
    $identifiant = "PASS";
    $on = "PASS";
} else {
    $groupby1 = "devices.tvalue";
    $groupby2 = "networks.ipsubnet";
    $groupby3 = "netid";
    $identifiant = "ID";
    $on = "RSX";
}

$form_name = 'ipdiscover';
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;

echo open_form($form_name, '', '', 'form-horizontal');

if (isset($_SESSION['OCS']["ipdiscover"])) {
    $dpt = array_keys($_SESSION['OCS']["ipdiscover"]);
    foreach ($dpt as $key => $value) {
        $list_index[$key] = $value;
    }
    asort($list_index);
    if (!isset($protectedPost['onglet'])) {
        $protectedPost['onglet'] = 1;
    }

    //show first ligne of onglet
    show_tabs($list_index,$form_name,"onglet",true);
} else {
    msg_info(mb_strtoupper($l->g(1134)));
}

echo '<div class="col col-md-10">';

if (isset($protectedPost['onglet'])) {
    $array_rsx = $ipdiscover->find_all_subnet($dpt[$protectedPost['onglet']]);
    foreach($array_rsx as $key => $value) {
        $explode = explode(";",$value);
        if(trim($explode[0]) != "" && strpos($explode[0], ":") == false) {
            $array_rsx[$key] = $explode[0];
        } else {
            unset($array_rsx[$key]);
        }
    }

    if(empty($array_rsx)) {
        $array_rsx[] = 0;
    }

    $tab_options['VALUE']['LBL_RSX'] = $_SESSION['OCS']["ipdiscover"][$dpt[$protectedPost['onglet']]];

    $arg_sql = array();

    # base query for ipd table
    $sql = "SELECT * FROM 
            (
                SELECT 
                    base_query.RSX AS ID, 
                    inventoried.count AS 'INVENTORIE', 
                    non_identified.count AS 'NON_INVENTORIE', 
                    ipdiscover.count AS 'IPDISCOVER', 
                    identified.count AS 'IDENTIFIE', ";

    if ($snmpTables) {
        $sql .= "SNMP_total.count AS 'SNMP',";
    }

    $sql.=  "base_query.TAG, 
            base_query.PASS
        FROM (
            SELECT 
                netid AS RSX, 
                CONCAT(netid, ';', IFNULL(tag, '')) AS PASS, 
                TAG 
            FROM 
                netmap 
            WHERE 
                netid IN ";
                    

    $arg = mysql2_prepare($sql, $arg_sql, $array_rsx);
    
    # IPDISCOVER devices
    $arg['SQL'] .= " GROUP BY $groupby3
                ) base_query 
                LEFT JOIN (
                    SELECT 
                        COUNT(DISTINCT devices.hardware_id) AS count, 
                        'IPDISCOVER' AS TYPE, 
                        devices.tvalue AS RSX, 
                        accountinfo.tag, 
                        CONCAT(devices.tvalue, ';', IFNULL(accountinfo.tag, '')) AS PASS
                    FROM 
                        devices
                    LEFT JOIN 
                        accountinfo ON accountinfo.HARDWARE_ID = devices.HARDWARE_ID
                    WHERE 
                        devices.name='IPDISCOVER' AND devices.tvalue IN ";

    $arg = mysql2_prepare($arg['SQL'], $arg['ARG'], $array_rsx);

    # INVENTORIED devices
    $arg['SQL'] .= " GROUP BY 
                        $groupby1
                ) ipdiscover ON base_query.$on=ipdiscover.$on 
                LEFT JOIN (
                    SELECT 
                        COUNT(DISTINCT hardware.ID) AS count, 
                        'INVENTORIE' AS TYPE, 
                        networks.ipsubnet AS RSX, 
                        subnet.TAG, 
                        CONCAT(networks.ipsubnet, ';', IFNULL(subnet.tag, '')) AS PASS
                    FROM 
                        networks
                    LEFT JOIN 
                        hardware ON hardware.ID = networks.HARDWARE_ID
                    LEFT JOIN 
                        accountinfo ON accountinfo.HARDWARE_ID = hardware.ID
                    LEFT JOIN 
                        subnet ON accountinfo.TAG = subnet.TAG AND subnet.NETID = networks.IPSUBNET
                    WHERE 
                        networks.ipsubnet IN ";

    $arg = mysql2_prepare($arg['SQL'], $arg['ARG'], $array_rsx);
    
    # IDENTIFIED devices
    $arg['SQL'] .= " AND networks.status='Up' 
                    GROUP BY 
                        $groupby2
                ) inventoried ON base_query.$on=inventoried.$on 
                LEFT JOIN (
                SELECT 
                    COUNT(DISTINCT mac) AS count, 
                    'IDENTIFIE' AS TYPE, 
                    netid AS RSX, 
                    TAG, 
                    CONCAT(netid, ';', IFNULL(tag, '')) AS PASS
                FROM 
                    netmap
                WHERE 
                    mac IN (SELECT DISTINCT(macaddr) FROM network_devices) 
                    AND netid IN ";

    $arg = mysql2_prepare($arg['SQL'], $arg['ARG'], $array_rsx);
    
    # NON IDENTIFIED (NON-INVENTORIED) devices
    $arg['SQL'] .= " GROUP BY 
                        $groupby3
                ) identified ON base_query.$on=identified.$on 
                LEFT JOIN (
                    SELECT 
                        COUNT(DISTINCT netmap.mac) AS count, 
                        'NON IDENTIFIE' AS TYPE, 
                        netmap.netid AS RSX, 
                        netmap.TAG, 
                        CONCAT(netmap.netid, ';', IFNULL(netmap.tag, '')) AS PASS
                    FROM 
                        netmap
                    LEFT JOIN 
                        networks ON networks.macaddr = netmap.mac ";
    // adding LEFT JOINS for SNMP tables
    if ($snmpTables) {
        foreach($snmpTables as $snmpTable) {
            $arg['SQL'] .= " LEFT JOIN $snmpTable[TABLE_TYPE_NAME] ON $snmpTable[TABLE_TYPE_NAME].$snmpTable[LABEL_NAME] = netmap.IP ";
        }
    }

    $arg['SQL'] .= " WHERE ";
    // adding WHERE clause for SNMP tables
    if ($snmpTables) {
        foreach($snmpTables as $snmpTable) {
            $arg['SQL'] .= " $snmpTable[TABLE_TYPE_NAME].$snmpTable[LABEL_NAME] IS NULL AND ";
        }
    }

    $arg['SQL'] .= " netmap.mac NOT IN (SELECT DISTINCT(macaddr) FROM network_devices) 
                        AND (networks.macaddr IS NULL) 
                        AND netmap.netid IN ";

    $arg = mysql2_prepare($arg['SQL'], $arg['ARG'], $array_rsx);

    if ($snmpTables) {
        $arg['SQL'] .= " GROUP BY 
                        $groupby3
                ) non_identified on base_query.$on=non_identified.$on 
                LEFT JOIN (
                    SELECT SUM(count) AS count, RSX
                    FROM ( ";
        foreach($snmpTables as $snmpTable) {
            $arg['SQL'] .= " SELECT 
                        COUNT(DISTINCT $snmpTable[TABLE_TYPE_NAME].$snmpTable[LABEL_NAME]) AS count, 
                        'SNMP' AS TYPE, 
                        CONCAT(SUBSTRING_INDEX($snmpTable[LABEL_NAME], '.', 3), '.0') AS RSX, 
                        CONCAT(CONCAT(SUBSTRING_INDEX($snmpTable[LABEL_NAME], '.', 3), '.0'), ';', '') AS PASS
                    FROM 
                        $snmpTable[TABLE_TYPE_NAME]
                    WHERE CONCAT(SUBSTRING_INDEX($snmpTable[LABEL_NAME], '.', 3), '.0') IN ";
            $arg = mysql2_prepare($arg['SQL'], $arg['ARG'], $array_rsx);

           $arg['SQL'] .= " GROUP BY 
                                RSX" ;

            if (next($snmpTables)) {
                $arg['SQL'] .= " UNION ALL ";
            }

        }
        $arg['SQL'] .= " ) AS snmp_union
                        GROUP BY RSX
                    ) SNMP_total ON base_query.RSX=SNMP_total.RSX
                ";

    } else {
        $arg['SQL'] .= " GROUP BY 
                            $groupby3
                    ) non_identified on base_query.$on=non_identified.$on
                ";

        $tab_options['ARG_SQL'] = $arg['ARG'];
    }

    // pls do not remove the space after ipd
    $arg['SQL'] .= " ) ipd ";
    $tab_options['ARG_SQL'] = $arg['ARG'];

    $list_fields = array(
        'LBL_RSX' => 'LBL_RSX',
        'RSX' => 'ID',
        'INVENTORIE' => 'INVENTORIE',
        'NON_INVENTORIE' => 'NON_INVENTORIE',
        'IPDISCOVER' => 'IPDISCOVER',
        'IDENTIFIE' => 'IDENTIFIE',
    );

    if ($snmpTables) {
        $list_fields['SNMP'] = 'SNMP';
    }

    if($ipdiscover->IPDISCOVER_TAG == "1") {
        $list_fields['TAG'] = "TAG";
    }

    $table_name = "IPDISCOVER";
    $tab_options['table_name'] = $table_name;
    $default_fields = $list_fields;
    $list_col_cant_del = array('RSX' => 'RSX', 'SUP' => 'SUP');

    $tab_options['LIEN_LBL']['INVENTORIE'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_custom_info'] . '&head=1&prov=inv&value=';
    $tab_options['LIEN_CHAMP']['INVENTORIE'] = $identifiant;
    $tab_options['NO_LIEN_CHAMP']['INVENTORIE'] = array(0);

    $tab_options['LIEN_LBL']['IPDISCOVER'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_multi_search'] . '&prov=ipdiscover1&value=';
    $tab_options['LIEN_CHAMP']['IPDISCOVER'] = $identifiant;
    $tab_options['NO_LIEN_CHAMP']['IPDISCOVER'] = array(0);

    $tab_options['LIEN_LBL']['NON_INVENTORIE'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_custom_info'] . '&prov=no_inv&head=1&value=';
    $tab_options['LIEN_CHAMP']['NON_INVENTORIE'] = $identifiant;
    $tab_options['NO_LIEN_CHAMP']['NON_INVENTORIE'] = array(0);

    $tab_options['LIEN_LBL']['IDENTIFIE'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_custom_info'] . '&prov=ident&head=1&value=';
    $tab_options['LIEN_CHAMP']['IDENTIFIE'] = $identifiant;

    # adding SNMP link
    $tab_options['LIEN_LBL']['SNMP'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_custom_info'] . '&prov=snmp&head=1&value=';
    $tab_options['LIEN_CHAMP']['SNMP'] = $identifiant;

    $tab_options['REPLACE_WITH_CONDITION']['INVENTORIE']['&nbsp'] = '0';
    $tab_options['REPLACE_WITH_CONDITION']['IPDISCOVER']['&nbsp'] = '0';
    $tab_options['REPLACE_WITH_CONDITION']['NON_INVENTORIE']['&nbsp'] = '0';
    $tab_options['REPLACE_WITH_CONDITION']['IDENTIFIE']['&nbsp'] = '0';
    # snmp
    $tab_options['REPLACE_WITH_CONDITION']['SNMP']['&nbsp'] = '0';

    $tab_options['LBL']['LBL_RSX'] = $l->g(863);
    $tab_options['LBL']['RSX'] = $l->g(869);
    $tab_options['LBL']['INVENTORIE'] = $l->g(364);
    $tab_options['LBL']['NON_INVENTORIE'] = $l->g(365);
    $tab_options['LBL']['IPDISCOVER'] = $l->g(312);
    $tab_options['LBL']['IDENTIFIE'] = $l->g(366);
    $tab_options['LBL']['SNMP'] = $l->g(1136);

    //you can modify your subnet if ipdiscover is local define
    if ($_SESSION['OCS']["ipdiscover_methode"] == "OCS" && $_SESSION['OCS']['profile']->getConfigValue('IPDISCOVER') == "YES") {
        $tab_options['LIEN_LBL']['LBL_RSX'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_admin_ipdiscover'] . '&head=1&value=';
        $tab_options['LIEN_CHAMP']['LBL_RSX'] = $identifiant;
    }

    $tab_options['NO_LIEN_CHAMP']['IDENTIFIE'] = array(0);
    $tab_options['NO_TRI']['LBL_RSX'] = 'LBL_RSX';
    $tab_options['NO_SEARCH']['LBL_RSX'] = 'LBL_RSX';
    $tab_options['SPECIAL_SEARCH'] = 'IPD';
    
    $strEnTete = "<p>" . $dpt[$protectedPost['onglet']] . " <br><br>";
    printEnTete($strEnTete);
    $result_exist = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
}

echo '</div>';
echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $arg['SQL'], $tab_options);
}