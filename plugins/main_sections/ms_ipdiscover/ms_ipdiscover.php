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

$ipdiscover = new Ipdiscover();

if($ipdiscover->IPDISCOVER_TAG == "1") {
    $groupby1 = "PASS";
    $groupby2 = "PASS";
    $groupby3 = "PASS";
    $identifiant = "PASS";
    $on = "PASS";
} else {
    $groupby1 = "d.tvalue";
    $groupby2 = "n.ipsubnet";
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

    if ($protectedPost['onglet'] == "") {
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
    $sql = "SELECT * FROM 
            (
                SELECT 
                n.RSX AS ID, 
                inv.c AS 'INVENTORIE', 
                non_ident.c AS 'NON_INVENTORIE', 
                ipdiscover.c AS 'IPDISCOVER', 
                ident.c AS 'IDENTIFIE', 
                n.TAG,
                n.PASS
		        FROM 
                (
                    SELECT netid AS RSX, 
                    CONCAT(netid,';',ifnull(tag,'')) AS PASS, 
                    TAG FROM netmap 
                    WHERE netid IN ";

    $arg = mysql2_prepare($sql, $arg_sql, $array_rsx);

    $arg['SQL'] .= " GROUP BY netid
                ) 
                n LEFT JOIN
                (
                    SELECT 
                    COUNT(DISTINCT d.hardware_id) AS c,
                    'IPDISCOVER' AS TYPE,
                    d.tvalue AS RSX,
                    a.tag,
                    CONCAT(d.tvalue,';',ifnull(a.tag,'')) as PASS
                    FROM devices d
                    LEFT JOIN accountinfo a ON a.HARDWARE_ID = d.HARDWARE_ID
                    WHERE d.name='IPDISCOVER' AND d.tvalue IN ";

    $arg = mysql2_prepare($arg['SQL'], $arg['ARG'], $array_rsx);

    $arg['SQL'] .= " GROUP BY $groupby1
                )
				ipdiscover ON n.$on=ipdiscover.$on LEFT JOIN 
				(
                    SELECT count(DISTINCT h.ID) AS c, 
                    'INVENTORIE' AS TYPE, 
                    n.ipsubnet AS RSX, 
                    s.TAG as TAG, 
                    CONCAT(n.ipsubnet,';',ifnull(s.tag,'')) as PASS 
                    FROM networks n 
                    LEFT JOIN hardware h ON h.ID = n.HARDWARE_ID
                    LEFT JOIN accountinfo a ON a.HARDWARE_ID = h.ID
                    LEFT JOIN subnet s ON a.TAG = s.TAG AND s.NETID = n.IPSUBNET
                    WHERE n.ipsubnet IN ";

    $arg = mysql2_prepare($arg['SQL'], $arg['ARG'], $array_rsx);

    $arg['SQL'] .= " AND n.status='Up' 
                    GROUP BY $groupby2
                )
				inv ON n.$on=inv.$on LEFT JOIN
				(
                    SELECT 
                    COUNT(DISTINCT mac) AS c,
                    'IDENTIFIE' AS TYPE,
                    netid AS RSX, 
                    TAG,
                    CONCAT(netid,';',ifnull(tag,'')) as PASS
                    FROM netmap 
                    WHERE mac IN 
                    (
                        SELECT 
                        DISTINCT(macaddr) 
                        FROM network_devices
                    ) 
                    AND netid IN ";

    $arg = mysql2_prepare($arg['SQL'], $arg['ARG'], $array_rsx);

    $arg['SQL'] .= " GROUP BY $groupby3
                )
				ident ON n.$on=ident.$on LEFT JOIN
				(
                    SELECT 
                    COUNT(DISTINCT n.mac) AS c, 
                    'NON IDENTIFIE' AS TYPE, 
                    n.netid AS RSX, 
                    n.TAG,
                    CONCAT(n.netid,';',ifnull(n.tag,'')) as PASS
                    FROM netmap n 
                    LEFT JOIN networks ns ON ns.macaddr=n.mac
                    LEFT JOIN accountinfo a ON a.TAG = n.TAG 
                    WHERE n.mac NOT IN ( 
                        SELECT DISTINCT(macaddr) FROM network_devices 
                    ) 
                    AND (ns.macaddr IS NULL) 
                    AND n.netid IN ";

    $arg = mysql2_prepare($arg['SQL'], $arg['ARG'], $array_rsx);

    $arg['SQL'] .= " GROUP BY $groupby3
                )
				non_ident on n.$on=non_ident.$on
            ) 
            ipd";

    $tab_options['ARG_SQL'] = $arg['ARG'];
    
    $list_fields = array(
        'LBL_RSX' => 'LBL_RSX',
        'RSX' => 'ID',
        'INVENTORIE' => 'INVENTORIE',
        'NON_INVENTORIE' => 'NON_INVENTORIE',
        'IPDISCOVER' => 'IPDISCOVER',
        'IDENTIFIE' => 'IDENTIFIE',
    );

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

    $tab_options['REPLACE_WITH_CONDITION']['INVENTORIE']['&nbsp'] = '0';
    $tab_options['REPLACE_WITH_CONDITION']['IPDISCOVER']['&nbsp'] = '0';
    $tab_options['REPLACE_WITH_CONDITION']['NON_INVENTORIE']['&nbsp'] = '0';
    $tab_options['REPLACE_WITH_CONDITION']['IDENTIFIE']['&nbsp'] = '0';

    $tab_options['LBL']['LBL_RSX'] = $l->g(863);
    $tab_options['LBL']['RSX'] = $l->g(869);
    $tab_options['LBL']['INVENTORIE'] = $l->g(364);
    $tab_options['LBL']['NON_INVENTORIE'] = $l->g(365);
    $tab_options['LBL']['IPDISCOVER'] = $l->g(312);
    $tab_options['LBL']['IDENTIFIE'] = $l->g(366);

    //you can modify your subnet if ipdiscover is local define
    if ($_SESSION['OCS']["ipdiscover_methode"] == "OCS" && $_SESSION['OCS']['profile']->getConfigValue('IPDISCOVER') == "YES") {
        $tab_options['LIEN_LBL']['LBL_RSX'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_admin_ipdiscover'] . '&head=1&value=';
        $tab_options['LIEN_CHAMP']['LBL_RSX'] = $identifiant;
    }

    $tab_options['NO_LIEN_CHAMP']['IDENTIFIE'] = array(0);
    $tab_options['NO_TRI']['LBL_RSX'] = 'LBL_RSX';
    
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