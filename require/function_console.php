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

$data_limit = find_limit_values();
require_once('require/function_ipdiscover.php');
if (isset($_SESSION['OCS']["TAGS"])) {
    $sql_tag = mysql2_prepare('select id from hardware h, accountinfo a where a.hardware_id=h.id and a.tag in ', array(), $_SESSION['OCS']["TAGS"]);
    $result = mysql2_query_secure($sql_tag['SQL'], $_SESSION['OCS']["readServer"], $sql_tag['ARG']);
    while ($val = mysqli_fetch_object($result)) {
        $my_id[] = $val->id;
    }
    $myids = mysql2_prepare('', array(), $my_id);
}

if (isset($_SESSION['OCS']['ADMIN_CONSOLE'])) {
    $edit = 0;
} else {
    $edit = 3;
}

$no_restrict = array("OCS_REPORT_NB_ALL_COMPUTOR");

$_SESSION['DATE']['HARDWARE-LASTCOME-TALL'] = date($l->g(1242));
$_SESSION['DATE']['HARDWARE-LASTDATE-TALL'] = date($l->g(1242));
$_SESSION['DATE']['HARDWARE-LASTCOME-SMALL'] = date($l->g(1242), mktime(0, 0, 0, date("m"), date("d") - $data_limit['GUI_REPORT_AGIN_MACH'], date("Y")));

$multi_search = array("OCS_REPORT_NB_CONTACT" => array("FIELD" => 'HARDWARE-LASTCOME', "COMP" => 'tall', "VALUE" => $_SESSION['DATE']['HARDWARE-LASTCOME-TALL']),
    "OCS_REPORT_NB_INV" => array("FIELD" => 'HARDWARE-LASTDATE', "COMP" => 'tall', "VALUE" => $_SESSION['DATE']['HARDWARE-LASTDATE-TALL']),
    "OCS_REPORT_NB_4_MOMENT" => array("FIELD" => 'HARDWARE-LASTCOME', "COMP" => 'small', "VALUE" => $_SESSION['DATE']['HARDWARE-LASTCOME-SMALL']),
    "OCS_REPORT_NB_HARD_DISK_H" => array("FIELD" => '', "COMP" => '', "VALUE" => ''),
    "OCS_REPORT_OSNAME" => array("FIELD" => 'HARDWARE-OSNAME', "COMP" => '', "VALUE" => ''),
    "OCS_REPORT_USERAGENT" => array("FIELD" => 'HARDWARE-USERAGENT', "COMP" => 'exact', "VALUE" => ''),
    "OCS_REPORT_PROCESSORT" => array("FIELD" => 'HARDWARE-PROCESSORT', "COMP" => 'exact', "VALUE" => ''),
    "OCS_REPORT_RESOLUTION" => array("FIELD" => 'VIDEOS-RESOLUTION', "COMP" => '', "VALUE" => ''),
    "OCS_REPORT_WORKGROUP" => array("FIELD" => 'HARDWARE-WORKGROUP', "COMP" => 'exact', "VALUE" => ''),
    "OCS_REPORT_TAG" => array("FIELD" => 'ACCOUNTINFO-TAG', "COMP" => 'exact', "VALUE" => ''),
    "OCS_REPORT_IPSUBNET" => array("FIELD" => 'NETWORKS-IPSUBNET', "COMP" => 'exact', "VALUE" => ''),
    "OCS_REPORT_NB_LIMIT_FREQ_H" => array("FIELD" => 'HARDWARE-PROCESSORS', "COMP" => 'tall', "VALUE" => $data_limit['GUI_REPORT_PROC_MAX']),
    "OCS_REPORT_NB_LIMIT_FREQ_M" => array("FIELD" => 'HARDWARE-PROCESSORS', "COMP" => 'small', "VALUE" => $data_limit['GUI_REPORT_PROC_MINI']),
    "OCS_REPORT_NB_LIMIT_FREQ_B" => array("FIELD" => 'HARDWARE-PROCESSORS,HARDWARE-PROCESSORS', "COMP" => 'tall,small', "VALUE" => $data_limit['GUI_REPORT_PROC_MINI'] . ',' . $data_limit['GUI_REPORT_PROC_MAX']),
    "OCS_REPORT_NB_LIMIT_MEM_H" => array("FIELD" => 'HARDWARE-MEMORY', "COMP" => 'tall', "VALUE" => $data_limit['GUI_REPORT_RAM_MAX']),
    "OCS_REPORT_NB_LIMIT_MEM_M" => array("FIELD" => 'HARDWARE-MEMORY', "COMP" => 'small', "VALUE" => $data_limit['GUI_REPORT_RAM_MINI']),
    "OCS_REPORT_NB_LIMIT_MEM_B" => array("FIELD" => 'HARDWARE-MEMORY,HARDWARE-MEMORY', "COMP" => 'tall,small', "VALUE" => $data_limit['GUI_REPORT_RAM_MINI'] . ',' . $data_limit['GUI_REPORT_RAM_MAX']),
    "OCS_REPORT_NB_NOTIFIED" => array("FIELD" => 'DEVICES-DOWNLOAD', "COMP" => 'exact', "VALUE" => 'NULL', 'VALUE2' => $l->g(482), 'TYPE_FIELD' => "SelFieldValue"),
    "OCS_REPORT_NB_ERR" => array("FIELD" => 'DEVICES-DOWNLOAD', "COMP" => 'exact', "VALUE" => 'NULL', 'VALUE2' => "***" . $l->g(956) . "***", 'TYPE_FIELD' => "SelFieldValue"));
$table = array("OCS_REPORT_WORKGROUP" => "hardware",
    "OCS_REPORT_TAG" => "accountinfo",
    "OCS_REPORT_IPSUBNET" => "networks",
    "OCS_REPORT_NB_NOTIFIED" => "devices",
    "OCS_REPORT_NB_ERR" => "devices",
    "OCS_REPORT_OSNAME" => "hardware",
    "OCS_REPORT_USERAGENT" => "hardware",
    "OCS_REPORT_PROCESSORT" => "hardware",
    "OCS_REPORT_RESOLUTION" => "videos",
    "OCS_REPORT_NB_LIMIT_FREQ_H" => "hardware",
    "OCS_REPORT_NB_LIMIT_FREQ_M" => "hardware",
    "OCS_REPORT_NB_LIMIT_FREQ_B" => "hardware",
    "OCS_REPORT_NB_LIMIT_MEM_H" => "hardware",
    "OCS_REPORT_NB_LIMIT_MEM_M" => "hardware",
    "OCS_REPORT_NB_LIMIT_MEM_B" => "hardware",
    "OCS_REPORT_NB_ALL_COMPUTOR" => "hardware",
    "OCS_REPORT_NB_COMPUTOR" => "hardware",
    "OCS_REPORT_NB_CONTACT" => "hardware",
    "OCS_REPORT_NB_INV" => "hardware",
    "OCS_REPORT_NB_4_MOMENT" => "hardware",
    "OCS_REPORT_NB_SNMP" => "snmp",
    "OCS_REPORT_NB_HARD_DISK_H" => "drives",
    "OCS_REPORT_NB_HARD_DISK_M" => "drives",
    "OCS_REPORT_NB_HARD_DISK_B" => "drives"
    , "OCS_REPORT_NB_IPDISCOVER" => "nk"
    , "OCS_REPORT_NB_LAST_INV" => "hardware");

$table_field = array("OCS_REPORT_WORKGROUP" => array($l->g(33) => "workgroup"),
    "OCS_REPORT_TAG" => array($_SESSION['OCS']['TAG_LBL']['TAG'] => "tag"),
    "OCS_REPORT_IPSUBNET" => array($l->g(316) => "ipsubnet"),
    "OCS_REPORT_NB_NOTIFIED" => "devices",
    "OCS_REPORT_NB_ERR" => "devices",
    "OCS_REPORT_OSNAME" => array($l->g(25) => "osname"),
    "OCS_REPORT_USERAGENT" => array($l->g(218) => "useragent"),
    "OCS_REPORT_PROCESSORT" => array($l->g(350) => "processort"),
    "OCS_REPORT_RESOLUTION" => array($l->g(62) => "resolution"),
    "OCS_REPORT_NB_LIMIT_FREQ_H" => "hardware",
    "OCS_REPORT_NB_LIMIT_FREQ_M" => "hardware",
    "OCS_REPORT_NB_LIMIT_FREQ_B" => "hardware",
    "OCS_REPORT_NB_LIMIT_MEM_H" => "hardware",
    "OCS_REPORT_NB_LIMIT_MEM_M" => "hardware",
    "OCS_REPORT_NB_LIMIT_MEM_B" => "hardware",
    "OCS_REPORT_NB_ALL_COMPUTOR" => "hardware",
    "OCS_REPORT_NB_COMPUTOR" => "hardware",
    "OCS_REPORT_NB_CONTACT" => "hardware",
    "OCS_REPORT_NB_INV" => "hardware",
    "OCS_REPORT_NB_4_MOMENT" => "hardware",
    "OCS_REPORT_NB_SNMP" => "snmp",
    "OCS_REPORT_NB_HARD_DISK_H" => "drives",
    "OCS_REPORT_NB_HARD_DISK_M" => "drives",
    "OCS_REPORT_NB_HARD_DISK_B" => "drives"
    , "OCS_REPORT_NB_IPDISCOVER" => "networks"
    , "OCS_REPORT_NB_LAST_INV" => array("NAME" => "NAME", "ID" => "ID", $l->g(25) => "osname", $l->g(218) => "useragent"));


$link = array("OCS_REPORT_WORKGROUP" => array("RELOAD" => 'OCS_REPORT_WORKGROUP'),
    "OCS_REPORT_TAG" => array("RELOAD" => 'OCS_REPORT_TAG'),
    "OCS_REPORT_IPSUBNET" => array("RELOAD" => 'OCS_REPORT_IPSUBNET'),
    "OCS_REPORT_NB_NOTIFIED" => array("PAGE" => 'ms_multi_search'),
    "OCS_REPORT_NB_ERR" => array("PAGE" => 'ms_multi_search'),
    "OCS_REPORT_OSNAME" => array("RELOAD" => 'OCS_REPORT_OSNAME'),
    "OCS_REPORT_USERAGENT" => array("RELOAD" => 'OCS_REPORT_USERAGENT'),
    "OCS_REPORT_PROCESSORT" => array("RELOAD" => 'OCS_REPORT_PROCESSORT'),
    "OCS_REPORT_RESOLUTION" => array("RELOAD" => 'OCS_REPORT_RESOLUTION'),
    "OCS_REPORT_NB_LIMIT_FREQ_H" => array("PAGE" => 'ms_multi_search'),
    "OCS_REPORT_NB_LIMIT_FREQ_M" => array("PAGE" => 'ms_multi_search'),
    "OCS_REPORT_NB_LIMIT_FREQ_B" => array("PAGE" => 'ms_multi_search'),
    "OCS_REPORT_NB_LIMIT_MEM_H" => array("PAGE" => 'ms_multi_search'),
    "OCS_REPORT_NB_LIMIT_MEM_M" => array("PAGE" => 'ms_multi_search'),
    "OCS_REPORT_NB_LIMIT_MEM_B" => array("PAGE" => 'ms_multi_search'),
    "OCS_REPORT_NB_ALL_COMPUTOR" => '',
    "OCS_REPORT_NB_COMPUTOR" => array("PAGE" => 'ms_all_computers'),
    "OCS_REPORT_NB_CONTACT" => array("PAGE" => 'ms_multi_search'),
    "OCS_REPORT_NB_INV" => array("PAGE" => 'ms_multi_search'),
    "OCS_REPORT_NB_4_MOMENT" => array("PAGE" => 'ms_multi_search'),
    "OCS_REPORT_NB_SNMP" => array("PAGE" => 'ms_snmp'),
    "OCS_REPORT_NB_IPDISCOVER" => array("PAGE" => 'ms_ipdiscover'),
    "OCS_REPORT_NB_LAST_INV" => array("RELOAD" => 'OCS_REPORT_NB_LAST_INV'));


//all fields repart on categories
$repart = array("OCS_REPORT_WORKGROUP" => "ELSE",
    "OCS_REPORT_TAG" => "ELSE",
    "OCS_REPORT_IPSUBNET" => "ELSE",
    "OCS_REPORT_NB_NOTIFIED" => "ELSE",
    "OCS_REPORT_NB_ERR" => "ELSE",
    "OCS_REPORT_OSNAME" => "SOFT",
    "OCS_REPORT_USERAGENT" => "SOFT",
    "OCS_REPORT_PROCESSORT" => "HARD",
    "OCS_REPORT_RESOLUTION" => "HARD",
    "OCS_REPORT_NB_LIMIT_FREQ_H" => "HARD",
    "OCS_REPORT_NB_LIMIT_FREQ_M" => "HARD",
    "OCS_REPORT_NB_LIMIT_FREQ_B" => "HARD",
    "OCS_REPORT_NB_LIMIT_MEM_H" => "HARD",
    "OCS_REPORT_NB_LIMIT_MEM_M" => "HARD",
    "OCS_REPORT_NB_LIMIT_MEM_B" => "HARD",
    "OCS_REPORT_NB_ALL_COMPUTOR" => "ACTIVITY",
    "OCS_REPORT_NB_COMPUTOR" => "ACTIVITY",
    "OCS_REPORT_NB_CONTACT" => "ACTIVITY",
    "OCS_REPORT_NB_INV" => "ACTIVITY",
    "OCS_REPORT_NB_4_MOMENT" => "ACTIVITY",
    "OCS_REPORT_NB_SNMP" => "ACTIVITY",
    "OCS_REPORT_NB_HARD_DISK_H" => "HARD",
    "OCS_REPORT_NB_HARD_DISK_M" => "HARD",
    "OCS_REPORT_NB_HARD_DISK_B" => "HARD"
    , "OCS_REPORT_NB_IPDISCOVER" => "ACTIVITY"
    , "OCS_REPORT_NB_LAST_INV" => "ACTIVITY");

//all lbl fields
$lbl_field = array("OCS_REPORT_WORKGROUP" => $l->g(778),
    "OCS_REPORT_TAG" => $l->g(779),
    "OCS_REPORT_IPSUBNET" => $l->g(780),
    "OCS_REPORT_NB_NOTIFIED" => $l->g(781),
    "OCS_REPORT_NB_ERR" => $l->g(782),
    "OCS_REPORT_OSNAME" => $l->g(783),
    "OCS_REPORT_USERAGENT" => $l->g(784),
    "OCS_REPORT_PROCESSORT" => $l->g(785),
    "OCS_REPORT_RESOLUTION" => $l->g(786),
    "OCS_REPORT_NB_LIMIT_FREQ_H" => $l->g(787) . " <b>" . show_modif($data_limit['GUI_REPORT_PROC_MAX'], "GUI_REPORT_PROC_MAX", $edit, '', array('JAVASCRIPT' => valid_modif("GUI_REPORT_PROC_MAX"))) . "</b> " . $l->g(1239),
    "OCS_REPORT_NB_LIMIT_FREQ_M" => $l->g(788) . " <b>" . show_modif($data_limit['GUI_REPORT_PROC_MINI'], "GUI_REPORT_PROC_MINI", $edit, '', array('JAVASCRIPT' => valid_modif("GUI_REPORT_PROC_MINI"))) . "</b> " . $l->g(1239),
    "OCS_REPORT_NB_LIMIT_FREQ_B" => $l->g(789) . " <b>" . $data_limit['GUI_REPORT_PROC_MINI'] . "</b> " . $l->g(1239) . " " . $l->g(582) . " <b>" . $data_limit['GUI_REPORT_PROC_MAX'] . "</b> " . $l->g(1239),
    "OCS_REPORT_NB_LIMIT_MEM_H" => $l->g(790) . " <b>" . show_modif($data_limit['GUI_REPORT_RAM_MAX'], "GUI_REPORT_RAM_MAX", $edit, '', array('JAVASCRIPT' => valid_modif("GUI_REPORT_RAM_MAX"))) . "</b> " . $l->g(1240),
    "OCS_REPORT_NB_LIMIT_MEM_M" => $l->g(791) . " <b>" . show_modif($data_limit['GUI_REPORT_RAM_MINI'], "GUI_REPORT_RAM_MINI", $edit, '', array('JAVASCRIPT' => valid_modif("GUI_REPORT_RAM_MINI"))) . "</b> " . $l->g(1240),
    "OCS_REPORT_NB_LIMIT_MEM_B" => $l->g(792) . " <b>" . $data_limit['GUI_REPORT_RAM_MINI'] . "</b> " . $l->g(1240) . " " . $l->g(582) . " <b>" . $data_limit['GUI_REPORT_RAM_MAX'] . "</b> " . $l->g(1240),
    "OCS_REPORT_NB_ALL_COMPUTOR" => $l->g(793),
    "OCS_REPORT_NB_COMPUTOR" => $l->g(794),
    "OCS_REPORT_NB_CONTACT" => $l->g(795),
    "OCS_REPORT_NB_INV" => $l->g(796),
    "OCS_REPORT_NB_4_MOMENT" => $l->g(797) . " <b>" . show_modif($data_limit['GUI_REPORT_AGIN_MACH'], "GUI_REPORT_AGIN_MACH", $edit, '', array('JAVASCRIPT' => valid_modif("GUI_REPORT_AGIN_MACH"))) . "</b> " . $l->g(496),
    "OCS_REPORT_NB_HARD_DISK_H" => $l->g(813) . " <b>" . show_modif($data_limit['GUI_REPORT_DD_MAX'], "GUI_REPORT_DD_MAX", $edit, '', array('JAVASCRIPT' => valid_modif("GUI_REPORT_DD_MAX"))) . "</b> " . $l->g(1240),
    "OCS_REPORT_NB_HARD_DISK_M" => $l->g(814) . " <b>" . show_modif($data_limit['GUI_REPORT_DD_MINI'], "GUI_REPORT_DD_MINI", $edit, '', array('JAVASCRIPT' => valid_modif("GUI_REPORT_DD_MINI"))) . "</b> " . $l->g(1240),
    "OCS_REPORT_NB_HARD_DISK_B" => $l->g(815) . " <b>" . $data_limit['GUI_REPORT_DD_MINI'] . "</b> " . $l->g(1240) . " " . $l->g(582) . " <b>" . $data_limit['GUI_REPORT_DD_MAX'] . "</b> " . $l->g(1240),
    "OCS_REPORT_NB_IPDISCOVER" => $l->g(913),
    "OCS_REPORT_NB_SNMP" => $l->g(1241));


$sql_field = array("OCS_REPORT_WORKGROUP" => array('ARG' => array('count(distinct workgroup) c', $table["OCS_REPORT_WORKGROUP"], '')),
    "OCS_REPORT_TAG" => array('ARG' => array('count(distinct tag)  c', $table["OCS_REPORT_TAG"], '')),
    "OCS_REPORT_IPSUBNET" => array('ARG' => array('count(distinct ipsubnet) c', $table["OCS_REPORT_IPSUBNET"], '')),
    "OCS_REPORT_NB_NOTIFIED" => array('SQL' => "select %s from %s where NAME='%s' and TVALUE is null",
        'ARG' => array('count(distinct hardware_id) c', $table["OCS_REPORT_NB_NOTIFIED"], 'DOWNLOAD')),
    "OCS_REPORT_NB_ERR" => array('SQL' => "select %s from %s where NAME='%s' and TVALUE like '%s'",
        'ARG' => array('count(distinct hardware_id) c', $table["OCS_REPORT_NB_ERR"], "DOWNLOAD", "ERR_%")),
    "OCS_REPORT_OSNAME" => array('ARG' => array('count(distinct osname) c', $table["OCS_REPORT_OSNAME"], '')),
    "OCS_REPORT_USERAGENT" => array('ARG' => array('count(distinct useragent) c', $table["OCS_REPORT_USERAGENT"], '')),
    "OCS_REPORT_PROCESSORT" => array('ARG' => array('count(distinct processort) c', $table["OCS_REPORT_PROCESSORT"], '')),
    "OCS_REPORT_RESOLUTION" => array('ARG' => array('count(distinct resolution) c', $table["OCS_REPORT_RESOLUTION"], '')),
    "OCS_REPORT_NB_LIMIT_FREQ_H" => array('ARG' => array('count(id) c', $table["OCS_REPORT_NB_LIMIT_FREQ_H"], "where processors >= " . $data_limit['GUI_REPORT_PROC_MAX'])),
    "OCS_REPORT_NB_LIMIT_FREQ_M" => array('ARG' => array('count(id) c', $table["OCS_REPORT_NB_LIMIT_FREQ_M"], "where processors <= " . $data_limit['GUI_REPORT_PROC_MINI'])),
    "OCS_REPORT_NB_LIMIT_FREQ_B" => array('ARG' => array('count(id) c', $table["OCS_REPORT_NB_LIMIT_FREQ_B"], "where processors <= " . $data_limit['GUI_REPORT_PROC_MAX'] . " and processors >= " . $data_limit['GUI_REPORT_PROC_MINI'])),
    "OCS_REPORT_NB_LIMIT_MEM_H" => array('ARG' => array('count(id) c', $table["OCS_REPORT_NB_LIMIT_MEM_H"], "where memory >= " . $data_limit['GUI_REPORT_RAM_MAX'])),
    "OCS_REPORT_NB_LIMIT_MEM_M" => array('ARG' => array('count(id) c', $table["OCS_REPORT_NB_LIMIT_MEM_M"], "where memory <= " . $data_limit['GUI_REPORT_RAM_MINI'])),
    "OCS_REPORT_NB_LIMIT_MEM_B" => array('ARG' => array('count(id) c', $table["OCS_REPORT_NB_LIMIT_MEM_B"], "where memory <= " . $data_limit['GUI_REPORT_RAM_MAX'] . " and memory >= " . $data_limit['GUI_REPORT_RAM_MINI'])),
    "OCS_REPORT_NB_ALL_COMPUTOR" => array('ARG' => array('count(id) c', $table["OCS_REPORT_NB_ALL_COMPUTOR"], '')),
    "OCS_REPORT_NB_COMPUTOR" => array('ARG' => array('count(id) c', $table["OCS_REPORT_NB_COMPUTOR"], '')),
    "OCS_REPORT_NB_CONTACT" => array('SQL' => "select %s from %s where lastcome >= date_format(sysdate(),'%s')",
        'ARG' => array('count(id) c', $table["OCS_REPORT_NB_CONTACT"], "%Y-%m-%d 00:00:00")),
    "OCS_REPORT_NB_INV" => array('SQL' => "select %s from %s where lastdate > date_format(sysdate(),'%s')",
        'ARG' => array('count(id) c', $table["OCS_REPORT_NB_INV"], "%Y-%m-%d 00:00:00")),
    "OCS_REPORT_NB_4_MOMENT" => array('ARG' => array('count(id) c', $table["OCS_REPORT_NB_4_MOMENT"], "where unix_timestamp(lastcome) < unix_timestamp(sysdate())-(" . $data_limit['GUI_REPORT_AGIN_MACH'] . "*86400)")),
    "OCS_REPORT_NB_HARD_DISK_H" => array('SQL' => "select %s from %s where type='%s' and free>%s",
        'ARG' => array('count(distinct(hardware_id)) c', $table["OCS_REPORT_NB_HARD_DISK_H"], "Hard Drive", $data_limit['GUI_REPORT_DD_MAX'])),
    "OCS_REPORT_NB_HARD_DISK_M" => array('SQL' => "select %s from %s where type='%s' and free<%s",
        'ARG' => array('count(distinct(hardware_id)) c', $table["OCS_REPORT_NB_HARD_DISK_M"], "Hard Drive", $data_limit['GUI_REPORT_DD_MINI'])),
    "OCS_REPORT_NB_HARD_DISK_B" => array('SQL' => "select %s from %s where type='%s' and free<%s and free>=%s",
        'ARG' => array('count(distinct(hardware_id)) c', $table["OCS_REPORT_NB_HARD_DISK_B"], "Hard Drive", $data_limit['GUI_REPORT_DD_MAX'], $data_limit['GUI_REPORT_DD_MINI'])),
    "OCS_REPORT_NB_IPDISCOVER" => array('SQL' => "select %s c from netmap ",
        'ARG' => array(count_noinv_network_devices())),
    "OCS_REPORT_NB_SNMP" => array('ARG' => array('count(id) c', $table["OCS_REPORT_NB_SNMP"], '')));

function define_tab($data_on = array()) {
    global $l;
    $data_on['ACTIVITY'] = mb_strtoupper($l->g(798), 'UTF-8');
    $data_on['SOFT'] = mb_strtoupper($l->g(20), 'UTF-8');
    $data_on['HARD'] = mb_strtoupper($l->g(799), 'UTF-8');
    $data_on['ELSE'] = mb_strtoupper($l->g(800), 'UTF-8');
    if ($_SESSION['OCS']['profile']->getConfigValue('CONSOLE') == "YES") {
        $data_on['ADMIN']['MSG'] = mb_strtoupper($l->g(915), 'UTF-8');

        if (!isset($default)) {
            $default = 'MSG';
        }
    }
    return array('DATA' => $data_on, 'DEFAULT' => $default);
}

function show_active_tab($data_on) {
    global $repart;
    //witch fields not show
    $no_show = look_config_default_values('OCS_REPORT_%', 1);
    if (is_array($no_show)) {
        foreach ($no_show['name'] as $key => $value) {
            if (!isset($_SESSION['OCS']['ADMIN_CONSOLE'])) {
                unset($repart[$key]);
            }
        }
    }
    foreach ($repart as $value) {
        $data[$value] = $value;
    }

    foreach ($data_on['DATA'] as $key => $value) {
        if (!isset($data[$key])) {
            if (!isset($_SESSION['OCS']['ADMIN_CONSOLE'])) {
                unset($data_on['DATA'][$key]);
            }
        }
        if (is_array($value)) {
            foreach ($value as $key1 => $value1) {
                $data_on['DATA'][$key1] = $value1;
            }
            unset($data_on['DATA'][$key]);
        }
    }

    return $data_on;
}

function list_field($tab) {
    global $repart, $lbl_field;
    foreach ($repart as $key => $value) {
        if ($value == $tab) {
            $result[$key] = $lbl_field[$key];
        }
    }
    return $result;
}

function show_console_field($fields, $form_name) {
    global $sql_field, $myids, $no_restrict, $table, $link, $pages_refs, $multi_search;
    $no_groups_sql = " deviceid != '_SYSTEMGROUP_' and deviceid != '_DOWNLOADGROUP_' ";
    $no_show = look_config_default_values('OCS_REPORT_%', 1);

    echo "<table ALIGN = 'Center' cellspacing='5' CELLPADDING='4'><tr ><td align =center><font size=2>";
    foreach ($fields as $key => $value) {
      if($sql_field[$key]['ARG'] != null){
            if (isset($_SESSION['OCS']['ADMIN_CONSOLE'])) {
                if (isset($no_show['name'][$key])) {
                    $icon = "<td align=center><a href=# OnClick='pag(\"" . $key . "\",\"NO_VISIBLE\",\"" . $form_name . "\");'><img src='image/red.png'></a></td>";
                } else {
                    $icon = "<td align=center><a href=# OnClick='pag(\"" . $key . "\",\"VISIBLE\",\"" . $form_name . "\");'><img src='image/green.png'></a></td>";
                }
            } else {
                $icon = "";
            }

            $arg_result = $sql_field[$key]['ARG'];

            if (isset($sql_field[$key]['SQL'])) {
                $sql_result = $sql_field[$key]['SQL'];
            } else {
                $sql_result = "select %s from %s %s";
            }

            if ($table[$key] == 'hardware') {
                if (is_defined($arg_result[2])) {
                    $sql_result .= " and " . $no_groups_sql;
                } else {
                    $sql_result .= " where " . $no_groups_sql;
                }
            }


            if ($myids) {
                if (!in_array($key, $no_restrict)) {
                    if (is_defined($arg_result[2]) || $table[$key] == 'hardware' || is_defined($sql_field[$key]['SQL'])) {
                        $sql_result .= " and ";
                    } else {
                        $sql_result .= " where ";
                    }

                    if ($table[$key] != 'hardware' && $table[$key] != 'snmp' && $table[$key] != 'nk') {
                        $sql_result .= $table[$key] . ".hardware_id in " . $myids['SQL'];
                    } elseif ($table[$key] == 'hardware') {
                        $sql_result .= " id in " . $myids['SQL'];
                    } elseif ($table[$key] == 'snmp') {

                    } elseif ($table[$key] == 'nk') {
                        $sql_result = substr($sql_result, 0, -4);
                    }

                    if (is_array($sql_field[$key]['ARG'])) {
                        $arg_result = array_merge($arg_result, $myids['ARG']);
                    } else {
                        $arg_result = $myids['ARG'];
                    }
                }
            }
            if (!isset($_SESSION['OCS']['COUNT_CONSOLE'][$key])) {
                $res = mysql2_query_secure($sql_result, $_SESSION['OCS']["readServer"], $arg_result);
                if ($res) {
                    $count = mysqli_fetch_object($res);
                    $_SESSION['OCS']['COUNT_CONSOLE'][$key] = $count->c;
                }
            }
            if (isset($_SESSION['OCS']['COUNT_CONSOLE'][$key]) && is_numeric($_SESSION['OCS']['COUNT_CONSOLE'][$key])) {
                $id_count = $_SESSION['OCS']['COUNT_CONSOLE'][$key];
                if (is_array($link[$key]) && $id_count != 0) {
                    if (isset($link[$key]['PAGE'])) {
                        $link_me_begin = "<a href='index.php?" . PAG_INDEX . "=" . $pages_refs[$link[$key]['PAGE']];
                        if (isset($multi_search[$key]['FIELD'])) {
                            $link_me_begin .= "&fields=" . $multi_search[$key]['FIELD'] . "&comp=" . $multi_search[$key]['COMP'] . "&values=" . $multi_search[$key]['VALUE'] . "&values2=" . $multi_search[$key]['VALUE2'] . "&type_field=" . $multi_search[$key]['TYPE_FIELD'];
                        }
                        $link_me_begin .= "'>";
                        $link_me_end = "</a>";
                    } elseif (isset($link[$key]['RELOAD'])) {
                        $link_me_begin = "<a href=# OnClick='pag(\"" . $link[$key]['RELOAD'] . "\",\"SHOW_ME\",\"" . $form_name . "\");'>";
                        $link_me_end = "</a>";
                    }
                } else {
                    $link_me_begin = "";
                    $link_me_end = "";
                }

                echo $value . "</font></td><td>&nbsp;</td><td align=center><font size=2><B>" . $link_me_begin . $id_count . $link_me_end . "</B></font></td>" . $icon . "</tr><tr><td align =center><font size=2>";
            }
        }
      }
      echo "</table>";

}

function find_limit_values() {
    $arg = look_config_default_values('GUI_REPORT_%', 1);
    return $arg['ivalue'];
}

function valid_modif($name) {
    global $form_name;
    return "onKeyPress=\"return scanTouche(event,/[0-9]/)\" onkeydown='convertToUpper(this)'
		  onkeyup='convertToUpper(this)'
		  onblur='pag(\"" . $name . "\",\"UPDATE_VALUE\",\"" . $form_name . "\");'
		  onclick='convertToUpper(this)'";
}

?>
