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
    $ajax = true;
} else {
    $ajax = false;
}

$tab_options = $protectedPost;
require_once('require/function_opt_param.php');
//BEGIN SHOW ACCOUNTINFO
require_once('require/function_admininfo.php');
$accountinfo_value = interprete_accountinfo($list_fields, $tab_options);
if (array($accountinfo_value['TAB_OPTIONS']))
    $tab_options = $accountinfo_value['TAB_OPTIONS'];
if (array($accountinfo_value['DEFAULT_VALUE']))
    $default_fields = $accountinfo_value['DEFAULT_VALUE'];
$list_fields = $accountinfo_value['LIST_FIELDS'];
//END SHOW ACCOUNTINFO

$list_fields2 = array($l->g(949) => 'h.ID',
    'DEVICEID' => 'h.DEVICEID',
    'NAME' => 'h.name',
    $l->g(25) => 'h.OSNAME',
    $l->g(275) => 'h.OSVERSION',
    $l->g(51) => 'h.OSCOMMENTS',
    $l->g(350) => 'h.PROCESSORT',
    $l->g(377) => 'h.PROCESSORS',
    $l->g(351) => 'h.PROCESSORN',
    $l->g(26) => 'h.MEMORY',
    $l->g(50) => 'h.SWAP',
    $l->g(46) => 'h.LASTDATE',
    $l->g(820) => 'h.LASTCOME',
    $l->g(353) => 'h.QUALITY',
    $l->g(354) => 'h.FIDELITY',
    $l->g(53) => 'h.DESCRIPTION',
    $l->g(34) => 'h.IPADDR',
    'CHECK' => 'h.ID');
$list_fields = array_merge($list_fields, $list_fields2);
$list_col_cant_del = array('NAME' => 'NAME', 'CHECK' => 'CHECK');
$default_fields2 = array('NAME' => 'NAME', $l->g(46) => $l->g(46), $l->g(820) => $l->g(820), $l->g(34) => $l->g(34));
$default_fields = array_merge($default_fields, $default_fields2);

if (isset($protectedGet['systemid'])) {
    $systemid = $protectedGet['systemid'];
    if ($systemid == "") {
        return $l->g(837);
        die();
    }
} elseif (isset($protectedPost['systemid'])) {
    $systemid = $protectedPost['systemid'];
}

if (!($_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES")) {
    $sql_verif = "select workgroup from hardware where workgroup='GROUP_4_ALL' and ID='%s'";
    $arg = $systemid;
    $res_verif = mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"], $arg);
    $item_verif = mysqli_fetch_object($res_verif);
    if ($item_verif == "")
        die("FORBIDDEN");
}


if (isset($protectedGet['state'])) {
    $state = $protectedGet['state'];
    if ($state == "MAJ")
        echo "<script language='javascript'>window.location.reload();</script>\n";
}// fin if

if (isset($protectedGet["suppack"])) {
    if ($_SESSION['OCS']["justAdded"] == false) {
        require_once('require/function_telediff.php');
        desactive_packet($systemid, $protectedGet["suppack"]);
    } else
        $_SESSION['OCS']["justAdded"] = false;
} else
    $_SESSION['OCS']["justAdded"] = false;


//update values if user want modify groups' values
if ($protectedPost['Valid_modif'] && !isset($protectedPost['modif'])) {
    if (trim($protectedPost['NAME']) != '' and trim($protectedPost['DESCR']) != '') {
        $req = "UPDATE hardware SET " .
                "NAME='%s'," .
                "DESCRIPTION='%s' " .
                "where ID='%s' and (deviceid = '_SYSTEMGROUP_' or deviceid ='_DOWNLOADGROUP_')";
        $arg = array($protectedPost['NAME'], $protectedPost['DESCR'], $systemid);
        $result = mysql2_query_secure($req, $_SESSION['OCS']["writeServer"], $arg);
    } else {

        echo "<script>alert('" . $l->g(627) . "')</script>";
    }
}
$queryMachine = "SELECT REQUEST,
						  CREATE_TIME,
						  NAME,
						  XMLDEF,
						  DESCRIPTION,LASTDATE,OSCOMMENTS,DEVICEID FROM hardware h left join groups g on g.hardware_id=h.id 
				  WHERE ID=%s AND (deviceid ='_SYSTEMGROUP_' or deviceid='_DOWNLOADGROUP_')";
$arg = $systemid;
$result = mysql2_query_secure($queryMachine, $_SESSION['OCS']["readServer"], $arg);
$item = mysqli_fetch_object($result);

if (!$item) {
    echo "<script language='javascript'>wait(0);</script>";
    echo "<center><font class='warn'>" . $l->g(623) . "</font></center>";
    flush();
    die();
}

if ($item->REQUEST != "" || $item->XMLDEF != "")
    $pureStat = false;
else {
    $pureStat = true;
}

if ($item->CREATE_TIME == "")
    $server_group = true;
else
    $server_group = false;
incPicker();
$tdhdpb = "<td  align='left' width='20%'>";
$tdhfpb = "</td>";
$tdhd = "<td  align='left' width='20%'><b>";
$tdhf = ":</b></td>";
$tdpopup = "<td align='left' width='20%' onclick=\"javascript: OuvrirPopup('group_chang_value.php', '', 'resizable=no, location=no, width=400, height=200, menubar=no, status=no, scrollbars=no, menubar=no')\">";

//if user clic on modify
if ($protectedPost['MODIF_x']) {
    //don't show the botton modify
    $img_modif = "";
    //list of input we can modify
    $name = show_modif($item->NAME, 'NAME', 0);
    $description = show_modif($item->DESCRIPTION, 'DESCR', 1);
    //show new bottons
    $button_valid = "<input title='" . $l->g(625) . "' value='" . $l->g(625) . "' name='Valid_modif' type='submit' class='btn btn-success'>";
    $button_reset = "<input title='" . $l->g(626) . "' value='" . $l->g(626) . "' name='Reset_modif' type='submit' class='btn btn-danger'>";
} else { //only show the botton for modify
    $img_modif = "<input title='" . $l->g(115) . "' value='" . $l->g(115) . "' name='MODIF_x' type='submit' class='btn'><br />";
    $name = $item->NAME;
    $description = $item->DESCRIPTION;
    $button_valid = "";
    $button_reset = "";
}
//form for modify values of group's
echo open_form('CHANGE');
echo "<table align='center' width='65%' border='0' cellspacing=20 bgcolor='#C7D9F5' style='border: solid thin; border-color:#A1B1F9'>";


echo "<tr>" . $tdhd . $l->g(577) . $tdhf . $tdhdpb . $name . $tdhfpb;
echo $tdhd . $l->g(593) . $tdhf . $tdhdpb . dateTimeFromMysql($item->LASTDATE) . $tdhfpb;
if (!$pureStat)
    echo "</tr><tr>" . $tdhd . $l->g(594) . $tdhf . $tdhdpb . date("F j, Y, g:i a", $item->CREATE_TIME) . $tdhfpb;
echo "</tr><tr><td>&nbsp;</td></tr>";
echo $tdhd . $l->g(615) . $tdhf . "<td  align='left' width='20%' colspan='3'>";
if (!$pureStat) {
    echo $item->REQUEST;

    //affichage des requetes qui ont formé ce groupe
    if ($item->XMLDEF != "") {
        $tab_list_sql = regeneration_sql($item->XMLDEF);
        $i = 1;
        while ($tab_list_sql[$i]) {
            echo $i . ") => " . $tab_list_sql[$i] . "<br>";
            $i++;
        }
    }
} else {
    echo $l->g(595);
}

echo "</tr><tr>" . $tdhd . $l->g(53) . $tdhf . $tdhdpb . $description . $tdhfpb;


if ($_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES")
    echo "<tr><td align='center' colspan=4><br />" . $button_valid . "&nbsp&nbsp" . $button_reset . "&nbsp&nbsp" . $img_modif . "<br /></td></tr>";
echo "$tdhfpb</table>";
echo close_form();
$td1 = "<td height=20px id='color' align='center'><FONT FACE='tahoma' SIZE=2 color=blue><b>";
$td2 = "<td height=20px bgcolor='white' align='center'>";
$td3 = $td2;
$td4 = "<td height=20px bgcolor='#F0F0F0' align='center'>";
//*/// END COMPUTER SUMMARY
if ($server_group) {
    $sql_affect_pack = "select da.NAME, da.PRIORITY,da.FRAGMENTS,da.SIZE,da.OSNAME,de.INFO_LOC,de.CERT_FILE,de.CERT_PATH,de.PACK_LOC
			from download_enable de,download_available da 
			where de.GROUP_ID =%s 
			and da.FILEID=de.FILEID
			group by de.fileid;";
    $arg = $systemid;
    $res_affect_pack = mysql2_query_secure($sql_affect_pack, $_SESSION['OCS']["readServer"], $arg);
    $i = 0;
    while ($val_affect_pack = mysqli_fetch_array($res_affect_pack)) {
        $PACK_LIST[$i]['NAME'] = $val_affect_pack['NAME'];
        $PACK_LIST[$i]['PRIORITY'] = $val_affect_pack['PRIORITY'];
        $PACK_LIST[$i]['FRAGMENTS'] = $val_affect_pack['FRAGMENTS'];
        $PACK_LIST[$i]['SIZE'] = $val_affect_pack['SIZE'];
        $PACK_LIST[$i]['OSNAME'] = $val_affect_pack['OSNAME'];
        $PACK_LIST[$i]['INFO_LOC'] = $val_affect_pack['INFO_LOC'];
        $PACK_LIST[$i]['CERT_FILE'] = $val_affect_pack['CERT_FILE'];
        $PACK_LIST[$i]['CERT_PATH'] = $val_affect_pack['CERT_PATH'];
        $PACK_LIST[$i]['PACK_LOC'] = $val_affect_pack['PACK_LOC'];
        $i++;
    }

    if (isset($PACK_LIST)) {
        echo "<table BORDER='0' WIDTH = '95%' ALIGN = 'Center' CELLPADDING='0' BGCOLOR='#C7D9F5' BORDERCOLOR='#9894B5'>";
        echo "<tr><td height=20px colspan=10 align='center'>" . $l->g(481) . "</td></tr>";
        echo "<tr><td></td>";
        foreach ($PACK_LIST[0] as $key => $value) {
            echo $td2 . "<i><b>" . $key . "</b></i></td>";
        }
        echo "</tr>";
        $i = 0;
        while ($PACK_LIST[$i]) {
            echo "<tr>";
            echo "<td bgcolor='white' align='center' valign='center'><img width='15px' src='image/red.png'></td>";
            $ii++;
            $td3 = $ii % 2 == 0 ? $td2 : $td4;
            foreach ($PACK_LIST[$i] as $key => $value) {
                echo $td3 . $value . "</td>";
            }
            echo "</tr>";
            $i++;
            //print_r($valDeploy);
        }
        echo "</table>";
    }
    require(MAIN_SECTIONS_DIR . "/" . $_SESSION['OCS']['url_service']->getDirectory('ms_server_redistrib') . "/ms_server_redistrib.php");
} else {



//	if( isset($protectedGet["action"]) || isset($protectedPost["action_form"]) ) {
//		require("ajout_maj.php");
//		die();
//	}

    if (!isset($protectedGet["option"])) {
        $opt = $l->g(500);
    } else {
        $opt = stripslashes(urldecode($protectedGet["option"]));
    }


    $lblAdm = Array($l->g(500));
    $imgAdm = Array("ms_config");
    $lblHdw = Array($l->g(580), $l->g(581));
    $imgHdw = Array("ms_all_computersred", "ms_all_computers",);

    echo "<table width='20%' border=0 align='center' cellpadding='0' cellspacing='0'>
			<tr>";
    echo img($imgAdm[0], $lblAdm[0], 1, $opt);

    if (!$pureStat)
        echo img($imgHdw[0], $lblHdw[0], 1, $opt);

    echo img($imgHdw[1], $lblHdw[1], 1, $opt);
    echo "</tr></table>";

    echo"<br><br><br>";

    switch ($opt) :
        case $l->g(500): print_perso($systemid);
            break;
        case $l->g(581):
            print_computers_cached($systemid);
            break;
        case $l->g(580):
            print_computers_real($systemid);
            break;
        default : print_perso($systemid);
            break;
    endswitch;
}
if (!$ajax) {
    echo "<script language='javascript'>wait(0);</script>";
    flush();
    echo "</body>";
    echo "</html>";
}
ob_end_flush();

function regeneration_sql($valGroup) {

    $tab = xml_decode($valGroup);
    $cherche = array("<xmldef>", "</REQUEST>", "</xmldef>");
    $replace = array("", "", "");
    $tab = str_replace($cherche, $replace, $tab);
    $tab_list_sql = explode("<REQUEST>", trim($tab));
    unset($tab_list_sql[0]);
    return($tab_list_sql);
}

function form_action_group($systemid) {
    global $l;
    $reqGrpStat = "SELECT REQUEST,XMLDEF FROM groups WHERE hardware_id=%s";
    $resGrpStat = mysql2_query_secure($reqGrpStat, $_SESSION['OCS']["readServer"], $systemid);
    $valGrpStat = mysqli_fetch_array($resGrpStat);
    echo "<center>" . $l->g(585) . ": <select name='actshowgroup' id='actshowgroup'>";
    if (($valGrpStat['REQUEST'] == "" || $valGrpStat['REQUEST'] == null) && ($valGrpStat['XMLDEF'] == "" || $valGrpStat['XMLDEF'] == null))
        echo "<option value='0'>" . $l->g(818) . "</option></select>";
    else
        echo "<option value='0'>" . $l->g(590) . "</option><option value='1'>" . $l->g(591) . "</option><option value='2'>" . $l->g(592) . "</option></select>";
    echo "<input type='submit' value='" . $l->g(13) . "' name='modify' id='modify'></center>";
}

function update_computer_group($hardware_id, $group_id, $static) {
    $resDelete = "DELETE FROM groups_cache WHERE hardware_id=%s AND group_id=%s";
    $arg = array($hardware_id, $group_id);
    //echo $resDelete;
    mysql2_query_secure($resDelete, $_SESSION['OCS']["writeServer"], $arg);
    if ($static != 0) {
        $reqInsert = "INSERT INTO groups_cache(hardware_id, group_id, static) VALUES (%s, %s, %s)";
        $arg = array($hardware_id, $group_id, $static);
        $resInsert = mysql2_query_secure($reqInsert, $_SESSION['OCS']["writeServer"], $arg);
    }
}

function print_computers_real($systemid) {

    global $l, $list_fields, $list_col_cant_del, $default_fields, $tab_options, $protectedPost;
    if (isset($protectedPost["actshowgroup"]) && $protectedPost["modify"] != "") {
        foreach ($protectedPost as $key => $val) {//check65422
            if (substr($key, 0, 5) == "check") {
                update_computer_group(substr($key, 5), $systemid, $protectedPost["actshowgroup"]);
            }
        }
        $tab_options['CACHE'] = 'RESET';
    }
    //group 2.0 version
    $sql_group = "SELECT xmldef FROM groups WHERE hardware_id='%s'";
    $arg = $systemid;
    $resGroup = mysql2_query_secure($sql_group, $_SESSION['OCS']["readServer"], $arg);
    $valGroup = mysqli_fetch_array($resGroup); //group old version
    if (!$valGroup["xmldef"]) {
        $sql_group = "SELECT request FROM groups WHERE hardware_id='%s'";
        $arg = $systemid;
        $resGroup = mysql2_query_secure($sql_group, $_SESSION['OCS']["readServer"], $arg);
        $valGroup = mysqli_fetch_array($resGroup);
        $request = $valGroup["request"];
        $tab_id = array();
        $result_value = mysqli_query($_SESSION['OCS']["readServer"], $request) or die(mysqli_error($_SESSION['OCS']["readServer"]));
        $fied_id_name = mysqli_field_name($result_value, 0);
        while ($value = mysqli_fetch_array($result_value)) {
            $tab_id[] = $value[$fied_id_name];
        }
    } else {
        $tab_list_sql = regeneration_sql($valGroup["xmldef"]);
        $i = 1;
        $tab_id = array();
        while ($tab_list_sql[$i]) {
            if ($tab_id != array()) {
                if (strtolower(substr($tab_list_sql[$i], 0, 19)) == "select distinct id ")
                    $tab_list_sql[$i] .= " and id in (" . implode(",", $tab_id) . ")";
                else
                    $tab_list_sql[$i] .= " and hardware_id in (" . implode(",", $tab_id) . ")";
                unset($tab_id);
            }
            $result_value = mysqli_query($_SESSION['OCS']["readServer"], xml_decode($tab_list_sql[$i])) or die(mysqli_error($_SESSION['OCS']["readServer"]));
            while ($value = mysqli_fetch_array($result_value)) {
                $tab_id[] = $value["HARDWARE_ID"];
            }
            $i++;
        }
    }

    if ($tab_id == array()) {
        msg_warning($l->g(766));
        return false;
    }
    $form_name = "calcul_computer_groupcache";
    $table_name = $form_name;
    echo "<font color=red><b>" . $l->g(927) . "</b></font>";
    echo open_form($form_name);
    $queryDetails = "SELECT ";
    foreach ($list_fields as $lbl => $value) {
        $queryDetails .= $value . ",";
    }
    $queryDetails = substr($queryDetails, 0, -1) . " FROM  hardware h LEFT JOIN accountinfo a ON a.hardware_id=h.id
						where h.id in (" . implode(",", $tab_id) . ") and deviceid <> '_SYSTEMGROUP_' 
										AND deviceid <> '_DOWNLOADGROUP_'";
    if (isset($mesmachines) && $mesmachines != '')
        $queryDetails .= $mesmachines;
    $tab_options['FILTRE'] = array('h.NAME' => 'Nom');

    $tab_options['form_name'] = $form_name;
    $tab_options['table_name'] = $table_name;
    ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
    form_action_group($systemid);
    echo close_form();
    if (AJAX) {
        ob_end_clean();
        tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
        ob_start();
    }
}

function print_computers_cached($systemid) {

    global $l, $server_group, $protectedPost, $list_fields, $list_col_cant_del, $default_fields, $tab_options;
    //print_r($protectedPost);
    //traitement des machines du groupe
    if (isset($protectedPost["actshowgroup"]) && $protectedPost["modify"] != "") {
        foreach ($protectedPost as $key => $val) {//check65422
            if (substr($key, 0, 5) == "check") {
                update_computer_group(substr($key, 5), $systemid, $protectedPost["actshowgroup"]);
            }
        }
        $tab_options['CACHE'] = 'RESET';
    }
    if ($_SESSION['OCS']['profile']->getRestriction('GUI') == "YES") {
        $sql_mesMachines = "select hardware_id from accountinfo a where " . $_SESSION['OCS']["mesmachines"];
        $res_mesMachines = mysql2_query_secure($sql_mesMachines, $_SESSION['OCS']["readServer"]);
        $mesmachines = "(";
        while ($item_mesMachines = mysqli_fetch_object($res_mesMachines)) {
            $mesmachines .= $item_mesMachines->hardware_id . ",";
        }
        $mesmachines = "and e.hardware_id IN " . substr($mesmachines, 0, -1) . ")";
    }

    $form_name = "list_computer_groupcache";
    $table_name = $form_name;
    echo open_form($form_name);

    $queryDetails = "SELECT ";
    foreach ($list_fields as $lbl => $value) {
        $queryDetails .= $value . ",";
    }
    $queryDetails = substr($queryDetails, 0, -1) . " FROM  hardware h LEFT JOIN accountinfo a ON a.hardware_id=h.id
						,groups_cache e
						where group_id='" . $systemid . "' and h.id=e.HARDWARE_ID ";
    if (isset($mesmachines) && $mesmachines != '')
        $queryDetails .= $mesmachines;

    $tab_options['FILTRE'] = array('h.NAME' => 'Nom');
    $tab_options['form_name'] = $form_name;
    $tab_options['table_name'] = $table_name;
    $statut = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
    if ($statut) {
        form_action_group($systemid);
    }
    echo close_form();
    if (AJAX) {
        ob_end_clean();
        tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
        ob_start();
    }
}

function print_perso($systemid) {
    global $l, $td1, $td2, $td3, $td4, $pages_refs, $protectedGet;
    $i = 0;
    $queryDetails = "SELECT * FROM devices WHERE hardware_id=$systemid";
    $resultDetails = mysqli_query($_SESSION['OCS']["readServer"], $queryDetails) or die(mysqli_error($_SESSION['OCS']["readServer"]));
    $form_name = 'config_group';
    echo open_form($form_name);
    echo "<table BORDER='0' WIDTH = '95%' ALIGN = 'Center' CELLPADDING='0' BGCOLOR='#C7D9F5' BORDERCOLOR='#9894B5'>";

    //echo "<tr><td>&nbsp;&nbsp;</td> $td1 "."Libellé"." </td> $td1 "."Valeur"." </td><td>&nbsp;</td></tr>";		
    while ($item = mysqli_fetch_array($resultDetails, MYSQLI_ASSOC)) {
        $optPerso[$item["NAME"]]["IVALUE"] = $item["IVALUE"];
        $optPerso[$item["NAME"]]["TVALUE"] = $item["TVALUE"];
    }

    $ii++;
    $td3 = $ii % 2 == 0 ? $td2 : $td4;
    //IPDISCOVER
    echo "<tr><td bgcolor='white' align='center' valign='center'>" . (isset($optPerso["IPDISCOVER"]) && $optPerso["IPDISCOVER"]["IVALUE"] != 1 ? "<img width='15px' src='image/red.png'>" : "&nbsp;") . "</td>&nbsp;</td>";
    echo $td3 . $l->g(489) . "</td>";
    if (isset($optPerso["IPDISCOVER"])) {
        if ($optPerso["IPDISCOVER"]["IVALUE"] == 0)
            echo $td3 . $l->g(490) . "</td>";
        else if ($optPerso["IPDISCOVER"]["IVALUE"] == 2)
            echo $td3 . $l->g(491) . " " . $optPerso["IPDISCOVER"]["TVALUE"] . "</td>";
        else if ($optPerso["IPDISCOVER"]["IVALUE"] == 1)
            echo $td3 . $l->g(492) . " " . $optPerso["IPDISCOVER"]["TVALUE"] . "</td>";
    }
    else {
        echo $td3 . $l->g(493) . "</td>";
    }
    if ($_SESSION['OCS']['profile']->getConfigValue('CONFIG') == "YES") {
        //echo "<td align=center rowspan=8><a href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_custom_param']."&head=1&idchecked=".$systemid."&origine=group\">
        //<img src='image/modif_a.png' title='".$l->g(285)."'></a></td></tr>";
        echo "<td align=center rowspan=8><a class='btn' href=\"index.php?" . PAG_INDEX . "=" . $pages_refs['ms_custom_param'] . "&head=1&idchecked=" . $systemid . "&origine=group\">
		" . $l->g(285) . "</a></td></tr>";
    }

    $ii++;
    $td3 = $ii % 2 == 0 ? $td2 : $td4;
    $field_name = array('DOWNLOAD', 'DOWNLOAD_CYCLE_LATENCY', 'DOWNLOAD_PERIOD_LENGTH', 'DOWNLOAD_FRAG_LATENCY',
        'DOWNLOAD_PERIOD_LATENCY', 'DOWNLOAD_TIMEOUT', 'PROLOG_FREQ', 'SNMP');
    $optdefault = look_config_default_values($field_name);
    //FREQUENCY
    echo "<tr><td bgcolor='white' align='center' valign='center'>" . (isset($optPerso["FREQUENCY"]) ? "<img width='15px' src='image/red.png'>" : "&nbsp;") . "</td>";
    echo $td3 . $l->g(494) . "</td>";
    if (isset($optPerso["FREQUENCY"])) {
        if ($optPerso["FREQUENCY"]["IVALUE"] == 0)
            echo $td3 . $l->g(485) . "</td>";
        else if ($optPerso["FREQUENCY"]["IVALUE"] == -1)
            echo $td3 . $l->g(486) . "</td>";
        else
            echo $td3 . $l->g(495) . " " . $optPerso["FREQUENCY"]["IVALUE"] . " " . $l->g(496) . "</td>";
    }
    else {
        echo $td3 . $l->g(497) . "</td>";
    }

    echo "</tr>";

    //DOWNLOAD_SWITCH
    echo "<tr><td bgcolor='white' align='center' valign='center'>" . (isset($optPerso["DOWNLOAD_SWITCH"]) ? "<img width='15px' src='image/red.png'>" : "&nbsp;") . "</td>";
    echo $td3 . $l->g(417) . " <font color=green size=1><i>DOWNLOAD</i></font> </td>";
    if (isset($optPerso["DOWNLOAD_SWITCH"])) {
        if ($optPerso["DOWNLOAD_SWITCH"]["IVALUE"] == 0)
            echo $td3 . $l->g(733) . "</td>";
        else if ($optPerso["DOWNLOAD_SWITCH"]["IVALUE"] == 1)
            echo $td3 . $l->g(205) . "</td>";
        else
            echo $td3 . "</td>";
    }
    else {
        echo $td3 . $l->g(488) . "(";
        if ($optdefault['ivalue']["DOWNLOAD"] == 1)
            echo $l->g(205);
        else
            echo $l->g(733);
        echo ")</td>";
    }

    echo "</tr>";

    //DOWNLOAD_CYCLE_LATENCY
    optperso("DOWNLOAD_CYCLE_LATENCY", $l->g(720) . " <font color=green size=1><i>DOWNLOAD_CYCLE_LATENCY</i></font>", $optPerso, 1, $optdefault['ivalue']["DOWNLOAD_CYCLE_LATENCY"], $l->g(511));

    //DOWNLOAD_FRAG_LATENCY
    optperso("DOWNLOAD_FRAG_LATENCY", $l->g(721) . " <font color=green size=1><i>DOWNLOAD_FRAG_LATENCY</i></font>", $optPerso, 1, $optdefault['ivalue']["DOWNLOAD_FRAG_LATENCY"], $l->g(511));


    //DOWNLOAD_PERIOD_LATENCY
    optperso("DOWNLOAD_PERIOD_LATENCY", $l->g(722) . " <font color=green size=1><i>DOWNLOAD_PERIOD_LATENCY</i></font>", $optPerso, 1, $optdefault['ivalue']["DOWNLOAD_PERIOD_LATENCY"], $l->g(511));

    //DOWNLOAD_PERIOD_LENGTH
    optperso("DOWNLOAD_PERIOD_LENGTH", $l->g(723) . " <font color=green size=1><i>DOWNLOAD_PERIOD_LENGTH</i></font>", $optPerso, 1, $optdefault['ivalue']["DOWNLOAD_PERIOD_LENGTH"]);

    //PROLOG_FREQ
    optperso("PROLOG_FREQ", $l->g(724) . " <font color=green size=1><i>PROLOG_FREQ</i></font>", $optPerso, 1, $optdefault['ivalue']["PROLOG_FREQ"], $l->g(730));

    //SNMP_SWITCH
    echo "<tr><td bgcolor='white' align='center' valign='center'>" . (isset($optPerso["SNMP_SWITCH"]) ? "<img width='15px' src='image/red.png'>" : "&nbsp;") . "</td>";
    echo $td3 . $l->g(1197) . " <font color=green size=1><i>SNMP_SWITCH</i></font></td>";
    if (isset($optPerso["SNMP_SWITCH"])) {
        if ($optPerso["SNMP_SWITCH"]["IVALUE"] == 0)
            echo $td3 . $l->g(733) . "</td>";
        else if ($optPerso["SNMP_SWITCH"]["IVALUE"] == 1)
            echo $td3 . $l->g(205) . "</td>";
        else
            echo $td3 . "</td>";
    }
    else {
        echo $td3 . $l->g(488) . "(";
        if ($optdefault['ivalue']["SNMP"] == 1)
            echo $l->g(205);
        else
            echo $l->g(733);
        echo ")</td>";
    }
    echo "</tr>";

    //TELEDEPLOY
    require_once('require/function_machine.php');
    show_packages($systemid, "ms_group_show");

    if ($_SESSION['OCS']['profile']->getConfigValue('TELEDIFF') == "YES") {
        echo "<tr>
		<td colspan='10' align='right'>
		<a href=\"index.php?" . PAG_INDEX . "=" . $pages_refs['ms_custom_pack'] . "&head=1&idchecked=" . $systemid . "&origine=group\">" . $l->g(501) . "
		</a>
		</td></tr>";
    }
    echo "</table>";
    echo close_form();
}

function img($i, $a, $avail, $opt) {
    global $systemid, $protectedGet;

    if ($opt == $a) {
        $suff = "_a";
    }

    if ($avail) {
        $href = "<a href='index.php?" . PAG_INDEX . "=" . $protectedGet[PAG_INDEX] . "&head=1&systemid=" . urlencode($systemid) . "&option=" . urlencode($a) . "'>";
        $fhref = "</a>";
        $img = '<button type="button" class="btn">' . $a . '</button>';
    } else {
        $href = "";
        $fhref = "";
        $img = '<button type="button" class="btn">' . $a . '</button>';
    }

    return "<td width='80px' align='center'>" . $href . $img . $fhref . "</td>";
}

function show_stat($fileId) {
    global $td3, $protectedGet, $pages_refs;

    echo $td3 . "<a href=\"index.php?" . PAG_INDEX . "=" . $pages_refs['ms_tele_stats'] . "&head=1&stat=" . $fileId . "&group=" . $protectedGet['systemid'] . "\" target=_blank><img src='image/stat.png'></a></td>";
}

?>
