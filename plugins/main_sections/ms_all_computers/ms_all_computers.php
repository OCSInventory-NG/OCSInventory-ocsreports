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
require_once('require/function_computers.php');
require_once('require/admininfo/Admininfo.php');
//intégration des fonctions liées à la recherche multicritère
require_once('require/function_search.php');
require_once('require/archive/ArchiveComputer.php');

$archive = new ArchiveComputer();
$Admininfo = new Admininfo();

// declare empty filter and value by default
$getFilter = null;
$getValue = null;

// if isset protectedGet, check value in
if(isset($protectedGet['filtre'])) {
    // clean filter value like xxx.yyy, xxx or xxx.yy_yy
    $getFilter = preg_replace("/[^A-Za-z0-9\._]/", "", $protectedGet['filtre']);
}

if(isset($protectedGet['value'])) {
    // Encode all special html chars
    $getValue = htmlspecialchars($protectedGet['value'], ENT_QUOTES, 'UTF-8');
    // Decode special html chars except quotes to prevent SQL injection
    $getValue = htmlspecialchars_decode($getValue, ENT_NOQUOTES);
}
// End check

//show mac address on the tab
$show_mac_addr = true;
$tab_options = $protectedPost;
$tab_options['form_name'] = "show_all";
$form_name = $tab_options['form_name'];
$tab_options['table_name'] = "list_show_all";

if (!is_null($getFilter) && !isset($protectedPost['FILTRE'])) {
    if (substr($getFilter, 0, 9) == "a.fields_") {
        $values_accountinfo = $Admininfo->accountinfo_tab(substr($getFilter, 9));
        if (is_array($values_accountinfo)) {
            $protectedPost['FILTRE_VALUE'] = $values_accountinfo[$getValue];
        }
    }
    $protectedPost['FILTRE'] = $getFilter;
    if (!isset($protectedPost['FILTRE_VALUE'])) {
        $protectedPost['FILTRE_VALUE'] = $getValue;
    }
}

//del the selection
if (!empty($protectedPost['DEL_ALL'])) {
    foreach ($protectedPost as $key => $value) {
        $checkbox = explode('check', $key);
        if (isset($checkbox[1])) {
            deleteDid($checkbox[1]);
        }
    }
    $tab_options['CACHE'] = 'RESET';
}

//delete one computer
if (!empty($protectedPost['SUP_PROF'])) {
    deleteDid($protectedPost['SUP_PROF']);
    $tab_options['CACHE'] = 'RESET';
}

//archive one computer
if (!empty($protectedPost['ARCHIVER'])) {
    $archive->archive($protectedPost['ARCHIVER']);
    $tab_options['CACHE'] = 'RESET';
    unset($protectedPost['ARCHIVER']);
}

//restore one computer
if (!empty($protectedPost['RESTORE'])) {
    $archive->restore($protectedPost['RESTORE']);
    $tab_options['CACHE'] = 'RESET';
    unset($protectedPost['RESTORE']);
}

echo open_form($form_name, '', '', 'form-horizontal');

$def_onglets['ALL'] = $l->g(1557);
$def_onglets['ACTIVE'] = $l->g(1555);
$def_onglets['ARCHIVE'] = $l->g(1554);

if (empty($protectedPost['onglet'])) {
    $protectedPost['onglet'] = "ACTIVE";
}

//show onglet
echo "<p>";
onglet($def_onglets, $form_name, "onglet", 7);
echo "</p></br></br>";

if($protectedPost['onglet'] == "ACTIVE") {
    $icon = "ARCHIVER";
} elseif($protectedPost['onglet'] == "ARCHIVE") {
    $icon = "RESTORE";
}

//BEGIN SHOW ACCOUNTINFO

$accountinfo_value = $Admininfo->interprete_accountinfo($list_fields ?? null, $tab_options);
if (array($accountinfo_value['TAB_OPTIONS'])) {
    $tab_options = $accountinfo_value['TAB_OPTIONS'];
}
if (array($accountinfo_value['DEFAULT_VALUE'])) {
    $default_fields = $accountinfo_value['DEFAULT_VALUE'];
}
$list_fields = $accountinfo_value['LIST_FIELDS'];
//END SHOW ACCOUNTINFO
$list_fields2 = array($l->g(46) => "h.lastdate",
    'NAME' => 'h.name',
    $l->g(949) => "h.ID",
    $l->g(24) => "h.userid",
    $l->g(25) => "h.osname",
    $l->g(568) => "capa",
    $l->g(569) => "h.processors",
    $l->g(33) => "h.workgroup",
    $l->g(275) => "h.osversion",
    $l->g(286) => "h.oscomments",
    $l->g(350) => "h.processort",
    $l->g(351) => "h.processorn",
    $l->g(50) => "h.swap",
    $l->g(352) => "h.lastcome",
    $l->g(353) => "h.quality",
    $l->g(354) => "h.fidelity",
    $l->g(53) => "h.description",
    $l->g(355) => "h.wincompany",
    $l->g(356) => "h.winowner",
    $l->g(357) => "h.useragent",
    $l->g(1551) => "h.archive",
    $l->g(64) => "e.smanufacturer",
    $l->g(284) => "e.bmanufacturer",
    $l->g(36) => "e.ssn",
    $l->g(65) => "e.smodel",
    $l->g(209) => "e.bversion",
    $l->g(34) => "h.ipaddr",
    $l->g(207) => "h.defaultgateway",
    $l->g(9930) => "h.dns",
    $l->g(557) => "h.userdomain",
    $l->g(1247) => "h.ARCH",
    $l->g(210) => "e.bdate",
    $l->g(61) => "vname",
    $l->g(2132) => "ac.category_name",
    $l->g(36) => "e.ssn",
    $l->g(1382) => "e.msn");
if ($show_mac_addr) {
    $list_fields2[$l->g(95)] = "n.macaddr";
    $list_fields2[$l->g(208)] = "n.ipmask";
    $list_fields2[$l->g(331)] = "n.ipsubnet";
}

$list_fields = array_merge($list_fields, $list_fields2);

// Create select field
$select_fields2 = array($l->g(46) => "h.lastdate",
    'NAME' => 'h.name',
    $l->g(949) => "h.ID",
    $l->g(24) => "h.userid",
    $l->g(25) => "h.osname",
    $l->g(568) => "h.memory as capa",
    $l->g(569) => "h.processors",
    $l->g(33) => "h.workgroup",
    $l->g(275) => "h.osversion",
    $l->g(286) => "h.oscomments",
    $l->g(350) => "h.processort",
    $l->g(351) => "h.processorn",
    $l->g(50) => "h.swap",
    $l->g(352) => "h.lastcome",
    $l->g(353) => "h.quality",
    $l->g(354) => "h.fidelity",
    $l->g(53) => "h.description",
    $l->g(355) => "h.wincompany",
    $l->g(356) => "h.winowner",
    $l->g(357) => "h.useragent",
    $l->g(1551) => "h.archive",
    $l->g(64) => "e.smanufacturer",
    $l->g(284) => "e.bmanufacturer",
    $l->g(36) => "e.ssn",
    $l->g(65) => "e.smodel",
    $l->g(209) => "e.bversion",
    $l->g(34) => "h.ipaddr",
    $l->g(207) => "h.defaultgateway",
    $l->g(9930) => "h.dns",
    $l->g(557) => "h.userdomain",
    $l->g(1247) => "h.ARCH",
    $l->g(210) => "e.bdate",
    $l->g(61) => "v.name as vname",
    $l->g(2132) => "ac.category_name",
    $l->g(36) => "e.ssn",
    $l->g(1382) => "e.msn");

// List for select
$select_fields = array_merge($list_fields, $select_fields2);

$tab_options['FILTRE'] = array_flip($list_fields);
$tab_options['FILTRE']['h.name'] = $l->g(23);
asort($tab_options['FILTRE']);


if ($protectedPost['onglet'] != "ALL" && $_SESSION['OCS']['profile']->getConfigValue('ARCHIVE_COMPUTERS') == "YES") {
    $list_fields[$icon] = 'h.ID';

}

if ($_SESSION['OCS']['profile']->getConfigValue('DELETE_COMPUTERS') == "YES") {
    $list_fields['SUP'] = 'h.ID';
}

$list_fields['CHECK'] = 'h.ID';

$list_col_cant_del = array('SUP' => 'SUP', 'NAME' => 'NAME', 'CHECK' => 'CHECK');
if($protectedPost['onglet'] != "ALL") {
    $list_col_cant_del[$icon] = $icon;
}
$default_fields2 = array($_SESSION['OCS']['TAG_LBL']['TAG'] => $_SESSION['OCS']['TAG_LBL'], $l->g(46) => $l->g(46), 'NAME' => 'NAME', $l->g(23) => $l->g(23),
    $l->g(24) => $l->g(24), $l->g(25) => $l->g(25), $l->g(568) => $l->g(568), $l->g(569) => $l->g(569));
$default_fields = array_merge($default_fields, $default_fields2);
if($protectedPost['onglet'] != "ALL") {
    $sql = prepare_sql_tab($select_fields, array('SUP', $icon, 'CHECK'));
} else {
    $sql = prepare_sql_tab($select_fields, array('SUP', 'CHECK'));
}

$tab_options['ARG_SQL'] = $sql['ARG'];
$queryDetails = $sql['SQL'] . " from hardware h
				LEFT JOIN accountinfo a ON a.hardware_id=h.id  ";

if ($show_mac_addr) {
    $queryDetails .= "	LEFT JOIN networks n ON n.hardware_id=h.id ";
}
// BIOS INFOS
$queryDetails .= "LEFT JOIN bios e ON e.hardware_id=h.id ";

//ASSETS CATEGORY
$queryDetails .= "LEFT JOIN assets_categories ac ON ac.ID = h.CATEGORY_ID ";

// VIDEOS CARDS INFOS
$queryDetails .= "LEFT JOIN videos v ON v.hardware_id=h.id ";
$queryDetails .= "where deviceid<>'_SYSTEMGROUP_' AND deviceid<>'_DOWNLOADGROUP_' ";

// ARCHIVE COMPUTERS
if($protectedPost['onglet'] == "ACTIVE") {
    $queryDetails .= "AND h.archive IS NULL ";
} elseif($protectedPost['onglet'] == "ARCHIVE") {
    $queryDetails .= "AND h.archive IS NOT NULL ";
}


// TAG RESTRICTIONS
if (!is_null($getValue) && $getFilter == "a.TAG") {
    $queryDetails .= "AND a.TAG= '$getValue' ";
}

// ACCOUNTINFO RESTRICTIONS
if (!is_null($getValue) && strpos($getFilter, 'a.fields_') === 0){
    $queryDetails .= "AND ".$getFilter."= '$getValue' ";
}

if (is_defined($_SESSION['OCS']["mesmachines"])) {
    $queryDetails .= "AND " . $_SESSION['OCS']["mesmachines"];
}
$queryDetails .= " group by h.id";
$tab_options['LBL_POPUP']['SUP'] = 'name';
$tab_options['LBL']['SUP'] = $l->g(122);
$tab_options['TRI']['DATE']['e.bdate'] = "%m/%d/%Y"; // BIOS date format in database (varchar)

$entete = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);

// Mass_action boutons
// Groups / Delete / Lock result / mass processing / Config / deploy
if ($_SESSION['OCS']['profile']->getConfigValue('DELETE_COMPUTERS') == "YES") {
    $list_fonct["image/delete.png"] = $l->g(122);
    $list_pag["image/delete.png"] = $pages_refs["ms_custom_sup"];
    $tab_options['LBL_POPUP']['SUP'] = 'name';
}
$list_fonct["image/cadena_ferme.png"] = $l->g(1019);
$list_fonct["image/mass_affect.png"] = $l->g(430);
if ($_SESSION['OCS']['profile']->getConfigValue('CONFIG') == "YES") {
    $list_fonct["image/config_search.png"] = $l->g(107);
    $list_pag["image/config_search.png"] = $pages_refs['ms_custom_param'];
}
if ($_SESSION['OCS']['profile']->getConfigValue('TELEDIFF') == "YES") {
    $list_fonct["image/tele_search.png"] = $l->g(428);
    $list_pag["image/tele_search.png"] = $pages_refs["ms_custom_pack"];
}
$list_pag["image/groups_search.png"] = $pages_refs["ms_custom_groups"];

$list_pag["image/cadena_ferme.png"] = $pages_refs["ms_custom_lock"];
$list_pag["image/mass_affect.png"] = $pages_refs["ms_custom_tag"];
add_trait_select($list_fonct, $list_id ?? null, $form_name, $list_pag, true);
echo "<br><br>";

if ($entete && $_SESSION['OCS']['profile']->getConfigValue('DELETE_COMPUTERS') == "YES") {
    echo "<a href=# OnClick='confirme(\"\",\"DEL_SEL\",\"" . $form_name . "\",\"DEL_ALL\",\"" . $l->g(900) . "\");'><span class='glyphicon glyphicon-remove delete-span'></span></a>";
    echo "<input type='hidden' id='DEL_ALL' name='DEL_ALL' value=''>";
}

echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
}

?>
