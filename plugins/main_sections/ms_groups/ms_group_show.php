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

$tab_options = $protectedPost;
require_once('require/function_opt_param.php');
//BEGIN SHOW ACCOUNTINFO
require_once('require/function_admininfo.php');

if(isset($list_fields)) {
    $accountinfo_value = interprete_accountinfo($list_fields, $tab_options);
    if (array($accountinfo_value['TAB_OPTIONS'])) {
        $tab_options = $accountinfo_value['TAB_OPTIONS'];

    }
    if (array($accountinfo_value['DEFAULT_VALUE'])) {
        $default_fields = $accountinfo_value['DEFAULT_VALUE'];
    }
    $list_fields = $accountinfo_value['LIST_FIELDS'];
    //END SHOW ACCOUNTINFO
}


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
    $l->g(24) => 'h.userid',
    $l->g(36) => 'b.ssn',
    'CHECK' => 'h.ID');
$list_fields = isset($list_fields) ? array_merge($list_fields, $list_fields2) : $list_fields2;
$list_col_cant_del = array('NAME' => 'NAME', 'CHECK' => 'CHECK');
$default_fields2 = array('NAME' => 'NAME', $l->g(46) => $l->g(46), $l->g(820) => $l->g(820), $l->g(34) => $l->g(34), $l->g(24) => $l->g(24));
$default_fields = isset($default_fields) ? array_merge($default_fields, $default_fields2) : $default_fields2;

if (isset($protectedGet['systemid'])) {
    $systemid = $protectedGet['systemid'];
    if ($systemid == "") {
        return $l->g(837);
    }
} elseif (isset($protectedPost['systemid'])) {
    $systemid = $protectedPost['systemid'];
}

if (!($_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES")) {
    $sql_verif = "select workgroup from hardware where workgroup='GROUP_4_ALL' and ID='%s'";
    $arg = $systemid;
    $res_verif = mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"], $arg);
    $item_verif = mysqli_fetch_object($res_verif);
    if ($item_verif == "") {
        die("FORBIDDEN");
    }
}


if (isset($protectedGet['state'])) {
    $state = $protectedGet['state'];
    if ($state == "MAJ") {
        echo "<script language='javascript'>window.location.reload();</script>\n";
    }
}

if (isset($protectedGet["suppack"])) {
    if ($_SESSION['OCS']["justAdded"] == false) {
        require_once('require/function_telediff.php');
        desactive_packet($systemid, $protectedGet["suppack"]);
    } else {
        $_SESSION['OCS']["justAdded"] = false;
    }
} else {
    $_SESSION['OCS']["justAdded"] = false;
}

//update values if user want modify groups' values
if (isset($protectedPost['Valid_modif']) && !isset($protectedPost['modif']) && !isset($protectedPost['MODIF'])) {
    if (trim($protectedPost['NAME']) != '' && trim($protectedPost['DESCR']) != '') {
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
						  DESCRIPTION,LASTDATE,OSCOMMENTS,DEVICEID FROM hardware h left join `groups` g on g.hardware_id=h.id
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

if ($item->REQUEST != "" || $item->XMLDEF != "") {
    $pureStat = false;
} else {
    $pureStat = true;
}

if ($item->CREATE_TIME == "") {
    $server_group = true;
} else {
    $server_group = false;
}


$tdpopup = "onclick=\"javascript: OuvrirPopup('group_chang_value.php', '', 'resizable=no, location=no, width=400, height=200, menubar=no, status=no, scrollbars=no, menubar=no')";

//if user clic on modify
if (isset($protectedPost['MODIF_x'])) {
    //don't show the botton modify
    $img_modif = "";
    //list of input we can modify
    $name = show_modif($item->NAME, 'NAME', 0);
    $description = show_modif($item->DESCRIPTION, 'DESCR', 1);
    //show new bottons
    echo "<div class='btn-toolbar'>";
    $button_valid = "<input title='" . $l->g(625) . "' value='" . $l->g(625) . "' name='Valid_modif' type='submit' class='btn btn-success'>";
    echo "</div>";
} else { //only show the botton for modify
    $img_modif = "<input title='" . $l->g(115) . "' value='" . $l->g(115) . "' name='MODIF_x' type='submit' class='btn'>";
    $name = $item->NAME;
    $description = $item->DESCRIPTION;
    $button_valid = "";
    $button_reset = "";
}
//form for modify values of group's
echo open_form('CHANGE', '', '', 'form-horizontal');

$dataValue = [];
$labelValue = [];

$labelValue[] = $l->g(577);
$dataValue[] = $name;

$labelValue[] = $l->g(593);
$dataValue[] = dateTimeFromMysql($item->LASTDATE);

if (!$pureStat) {
    $labelValue[] = $l->g(594);
    $dataValue[] = date("F j, Y, g:i a", $item->CREATE_TIME);
}

$labelValue[] = $l->g(615);

if (!$pureStat) {

    $temp = $item->REQUEST;
    //affichage des requetes qui ont formé ce groupe
    if ($item->XMLDEF != "") {
        $tab_list_sql = regeneration_sql($item->XMLDEF);
        $i = 1;
        foreach ($tab_list_sql as $sql) {
            $temp .= $i . ") => " . $tab_list_sql[$i];
            $i++;
        }
    }
    $dataValue[] = $temp;
} else {
    $dataValue[] = $l->g(595);
}

$labelValue[] = $l->g(53);
$dataValue[] = $description;

?>
<div class="row">
    <div class="col-md-8 col-md-offset-2">
<?php
show_resume($dataValue, $labelValue);
?>
    </div>
</div>
<div class="row rowMarginTop30">
    <div class="col-md-12">
        <?php

        if ($_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES") {
            echo $button_valid;
            echo $button_reset ?? '';
            echo $img_modif;
        }

        ?>
    </div>
</div>
<?php
echo close_form();

if ($server_group) {

    require(MAIN_SECTIONS_DIR . "/" . $_SESSION['OCS']['url_service']->getDirectory('ms_server_redistrib') . "/ms_server_redistrib.php");

}
else {

    if (!isset($protectedGet["option"])) {
        $opt = $l->g(500);
    } else {
        $opt = stripslashes(urldecode($protectedGet["option"]));
    }

    $notif = array($l->g(9950));
    $lblAdm = array($l->g(500));
    $imgAdm = array("ms_config");
    $lblHdw = array($l->g(580), $l->g(581));
    $imgHdw = array("ms_all_computersred", "ms_all_computers",);
        echo "<div class='row rowMarginTop30'>";
    echo img($lblAdm[0], 1);

    if (!$pureStat) {
        echo img($notif[0], 1);
        echo img($lblHdw[0], 1);

    }

    echo img($lblHdw[1], 1);


        if( $_SESSION['OCS']['profile']->getConfigValue('TELEDIFF')=="YES" ){
            echo "<a href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_custom_pack']."&head=1&idchecked=".$systemid."&origine=mach\" class='btn btn-success' >".$l->g(501)."</a>";
        }
        echo "</div>";

        echo "<div class='row rowMarginTop30'>";
        echo "<div class='col-md-10 col-md-offset-1'>";
    switch ($opt) :
        case $l->g(500): print_perso($systemid);
            break;
        case $l->g(581):
            print_computers_cached($systemid);
            break;
        case $l->g(580):
            print_computers_real($systemid);
            break;
        case $l->g(9950):
            print_notification_form($systemid, $protectedPost['RECURRENCE'] ?? '');
            break;
        default : print_perso($systemid);
            break;
    endswitch;
}
if (!AJAX) {
    echo "<script language='javascript'>wait(0);</script>";
    flush();
    echo "</div>";
    echo "</div>";
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
    $reqGrpStat = "SELECT REQUEST,XMLDEF FROM `groups` WHERE hardware_id=%s";
    $resGrpStat = mysql2_query_secure($reqGrpStat, $_SESSION['OCS']["readServer"], $systemid);
    $valGrpStat = mysqli_fetch_array($resGrpStat);
    if (($valGrpStat['REQUEST'] == "" || $valGrpStat['REQUEST'] == null) && ($valGrpStat['XMLDEF'] == "" || $valGrpStat['XMLDEF'] == null)) {
        $arrayData = array(
            '0' => $l->g(818)
        );
    } else {
        $arrayData = array(
            '0' => $l->g(590),
            '1' => $l->g(591),
            '2' => $l->g(592)
        );
    }
    echo "<div class='col-md-8 col-md-offset-2'>";
    formGroup('select', 'actshowgroup', $l->g(585), '', '', '', '', $arrayData, $arrayData, '');
    echo "<input type='submit' name='modify' class='btn btn-success' value=".$l->g(13).">";
    echo "</div>";

}

function update_computer_group($hardware_id, $group_id, $static) {
    $resDelete = "DELETE FROM groups_cache WHERE hardware_id=%s AND group_id=%s";
    $arg = array($hardware_id, $group_id);
    mysql2_query_secure($resDelete, $_SESSION['OCS']["writeServer"], $arg);
    if ($static != 0) {
        $reqInsert = "INSERT INTO groups_cache(hardware_id, group_id, static) VALUES (%s, %s, %s)";
        $arg = array($hardware_id, $group_id, $static);
        mysql2_query_secure($reqInsert, $_SESSION['OCS']["writeServer"], $arg);
    }
}

function print_notification_form($systemid, $recurrence) {
    global $protectedPost, $l;
    echo "<div class='col-md-10 col-md-offset-1'>";

    msg_info('Please check that the notification configuration has been set correctly, otherwise sending reports through notifications will not work');
    $recurrences[''] = "";
    $recurrences['DAILY'] = "daily";
    $recurrences['MONTHLY'] = "monthly";
    $recurrences['WEEKLY'] = "weekly";
    
    // check if group already has a report
    $sql = "SELECT * FROM `reports_notifications` WHERE GROUP_ID = %s";
    $args_rec = array($systemid, $recurrence);
    $result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $args_rec);
    $current_rec = mysqli_fetch_assoc($result);

    if (isset($protectedPost['UPDATE_RECURRENCE']) && $protectedPost['RECURRENCE'] != '') {
        // if not already existing, insert
        if (isset($result) && $result->num_rows == 0) {
            $mails = explode(',', $protectedPost['MAIL']);
            $mails = json_encode($protectedPost['MAIL']);
            $datetime = date("Y-m-d H:i:s");
            $end_date = isset($protectedPost['END_DATE_VALUE']) ? (New DateTime($protectedPost['END_DATE_VALUE']))->format('Y-m-d H:i:s') : NULL;
            $sql = "INSERT INTO `reports_notifications` (GROUP_ID, RECURRENCE, END_DATE, WEEKDAY, DATE_CREATED, LAST_EXEC, MAIL) VALUES (%s, '%s', '%s', '%s', '%s', '%s', '%s')";
            $args_rec = array($systemid, $recurrence, $end_date, $protectedPost['WEEKDAY'] ?? '', $datetime, $datetime, $mails);
            $result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $args_rec);

            if ($result) {
                msg_success('Report created successfully');
            }
        } else {
            $mails = json_encode($protectedPost['MAIL']);
            $datetime = date("Y-m-d H:i:s");
            $end_date = isset($protectedPost['END_DATE_VALUE']) ? (New DateTime($protectedPost['END_DATE_VALUE']))->format('Y-m-d H:i:s') : NULL;
            $sql = "UPDATE `reports_notifications` SET RECURRENCE = '%s', END_DATE = '%s', WEEKDAY = '%s', DATE_CREATED = '%s', LAST_EXEC = '%s', MAIL = '%s' WHERE ID = %s";
            $args_rec = array($recurrence, $end_date, $protectedPost['WEEKDAY'] ?? '', $datetime, $datetime, $mails, $current_rec['ID']);
            $result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $args_rec);
            
            if ($result) {
                msg_success('Report updated successfully');
            }
        }

    } elseif (isset($protectedPost['UPDATE_RECURRENCE']) && $protectedPost['RECURRENCE'] == '') {
        msg_error('Please set a recurrence');
    }

    $cur_mails = isset($protectedPost['MAIL']) ? $protectedPost['MAIL'] : json_decode($current_rec['MAIL']);
    $cur_rec = isset($protectedPost['RECURRENCE']) ? $protectedPost['RECURRENCE'] : $current_rec['RECURRENCE'];
    $cur_end_date = isset($protectedPost['END_DATE_VALUE']) ? $protectedPost['END_DATE_VALUE'] : $current_rec['END_DATE'];
    $cur_weekday = isset($protectedPost['WEEKDAY']) ? $protectedPost['WEEKDAY'] : $current_rec['WEEKDAY'];

    // new to handle the default rec
    $recurrence = $cur_rec ?? 'DAILY';
    // show form to set a reccurence
    formGroup('select', 'RECURRENCE', 'Report recurrence :', '', '', $cur_rec ?? 'DAILY', '', $recurrences, $recurrences, 'onchange="this.form.submit();"');
    formGroup('text', 'MAIL', 'Recipient(s) : ', '', '', $cur_mails ?? '', '', '', "disabled");
    
    if (isset($recurrence) && $recurrence == 'DAILY') {
        $rec_options = array("END_DATE");
    } elseif (isset($recurrence) && $recurrence == 'WEEKLY') {
        $rec_options = array("WEEKDAY", "END_DATE");
    } elseif (isset($recurrence) && $recurrence == 'MONTHLY') {
        $rec_options = array("END_DATE");
    }

    foreach ($rec_options as $option) {
            
        if ($option == "WEEKDAY") {
            // show weekday form
            $recurrence_days = array(0  => "Monday",
            1  => "Tuesday",
            2  => "Wednesday",
            3  => "Thursday",
            4  => "Friday",
            5  => "Saturday",
            6  => "Sunday");
            formGroup('select', 'WEEKDAY', 'Day of report :', '', '', $cur_weekday ?? '', '', $recurrence_days, $recurrence_days, 'onchange="this.form.submit();"');
        }

        if ($option == "END_DATE") {
            echo "<label class='control-label col-sm-2' for='END_DATE_ON'>End Date : </label>
            <div class='col-sm-3'>";
            
            if ($protectedPost['END_DATE_RADIO'] == 'OFF') {
                unset($current_rec['END_DATE']);
            }

            if (!isset($current_rec) && !isset($protectedPost['END_DATE_RADIO'])) {
                $protectedPost['END_DATE_RADIO'] = 'OFF';
            }

            if ((isset($protectedPost['END_DATE_RADIO']) && $protectedPost['END_DATE_RADIO'] == 'ON') || (isset($current_rec['END_DATE']) && $current_rec['END_DATE'] != '0000-00-00 00:00:00')) {
                echo "<input type='radio' id='END_DATE_ON' name='END_DATE_RADIO' value='ON' onclick='this.form.submit();' checked/>ON";
                echo "<input type='radio' id='END_DATE_OFF' name='END_DATE_RADIO' value='OFF' onclick='this.form.submit();'/>OFF";
            } elseif((isset($protectedPost['END_DATE_RADIO']) && $protectedPost['END_DATE_RADIO'] == 'OFF') || (isset($current_rec['END_DATE']) && $current_rec['END_DATE'] == '0000-00-00 00:00:00')) {
                echo "<input type='radio' id='END_DATE_ON' name='END_DATE_RADIO' value='ON' onclick='this.form.submit();'/>ON";
                echo "<input type='radio' id='END_DATE_OFF' name='END_DATE_RADIO' value='OFF' onclick='this.form.submit();' checked/>OFF";
            }

            echo "</div>";


            // show end_date calendar if user checked the ON radio button 
            if ((isset($protectedPost['END_DATE_RADIO']) && $protectedPost['END_DATE_RADIO'] == 'ON') || (isset($current_rec['END_DATE']) && $current_rec['END_DATE'] != '0000-00-00 00:00:00')) {
                // report will be sent every 1st
                $value = isset($cur_end_date) && $cur_end_date != '0000-00-00 00:00:00' ? $cur_end_date : '';
                echo "
                <div class='col-sm-3'>
                <div class='input-group date form_datetime' id='date_form'>
                <input type='text' class='form-control' name='END_DATE_VALUE' id='end_date' value='$value'/>
                <span class='input-group-addon'>
                    ".calendars('END_DATE_VALUE', $l->g(1270))."
                </span>

                </div></div>";
            }

        }

    }
        
        echo "<br><br><br><input title='UPDATE_RECURRENCE' value='Update' name='UPDATE_RECURRENCE' type='submit' class='btn btn-success'>";
    
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
    $sql_group = "SELECT xmldef FROM `groups` WHERE hardware_id='%s'";
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
        while (isset($tab_list_sql[$i])) {
            if ($tab_id != array()) {
                if (strtolower(substr($tab_list_sql[$i], 0, 19)) == "select distinct id ") {
                    $tab_list_sql[$i] .= " and id in (" . implode(",", $tab_id) . ")";
                } else {
                    $tab_list_sql[$i] .= " and hardware_id in (" . implode(",", $tab_id) . ")";
                }
                unset($tab_id);
            }
            $result_value = mysqli_query($_SESSION['OCS']["readServer"], xml_decode($tab_list_sql[$i])) or die(mysqli_error($_SESSION['OCS']["readServer"]));

            while ($value = mysqli_fetch_array($result_value)) {
                $tab_id[] = $value["ID"];
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
    foreach ($list_fields as $value) {
        $queryDetails .= $value . ",";
    }
    $queryDetails = substr($queryDetails, 0, -1) . " FROM  hardware h LEFT JOIN accountinfo a ON a.hardware_id=h.id
                        LEFT JOIN bios b ON b.hardware_id=h.id 
						where h.id in (" . implode(",", $tab_id) . ") and deviceid <> '_SYSTEMGROUP_' AND deviceid <> '_DOWNLOADGROUP_'";
    if (isset($mesmachines) && $mesmachines != '') {
        $queryDetails .= $mesmachines;
    }
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
    global $protectedPost, $list_fields, $list_col_cant_del, $default_fields, $tab_options;
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
    echo open_form($form_name, '', '', 'form-horizontal');

    $queryDetails = "SELECT ";
    foreach ($list_fields as $value) {
        $queryDetails .= $value . ",";
    }
    $queryDetails = substr($queryDetails, 0, -1) . " FROM  hardware h LEFT JOIN accountinfo a ON a.hardware_id=h.id
                        LEFT JOIN bios b ON b.hardware_id=h.id ,groups_cache e
						where group_id='" . $systemid . "' and h.id=e.HARDWARE_ID ";
    if (isset($mesmachines) && $mesmachines != '') {
        $queryDetails .= $mesmachines;
    }

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
    global $l, $pages_refs;

    $i = 0;
    $queryDetails = "SELECT * FROM devices WHERE hardware_id=$systemid";
    $resultDetails = mysqli_query($_SESSION['OCS']["readServer"], $queryDetails) or die(mysqli_error($_SESSION['OCS']["readServer"]));
    $form_name = 'config_group';
    echo open_form($form_name, '', '', 'form-horizontal');

    while ($item = mysqli_fetch_array($resultDetails, MYSQLI_ASSOC)) {
        $optPerso[$item["NAME"]]["IVALUE"] = $item["IVALUE"];
        $optPerso[$item["NAME"]]["TVALUE"] = $item["TVALUE"];
    }

    $field_name = array('DOWNLOAD', 'DOWNLOAD_CYCLE_LATENCY', 'DOWNLOAD_PERIOD_LENGTH', 'DOWNLOAD_FRAG_LATENCY',
        'DOWNLOAD_PERIOD_LATENCY', 'DOWNLOAD_TIMEOUT', 'PROLOG_FREQ', 'SNMP');
    $optdefault = look_config_default_values($field_name);

     //IPDISCOVER
    if (isset($optPerso["IPDISCOVER"])) {
        $default = '';
        if ($optPerso["IPDISCOVER"]["IVALUE"] == 0) {
            $supp = $l->g(490);
        } else if ($optPerso["IPDISCOVER"]["IVALUE"] == 2) {
            $supp = $l->g(491) . $optPerso["IPDISCOVER"]["TVALUE"];
        } else if ($optPerso["IPDISCOVER"]["IVALUE"] == 1) {
            $supp = $l->g(492) . $optPerso["IPDISCOVER"]["TVALUE"];
        }
    } else {
        $default = $l->g(493);
    }


    optpersoGroup('IPDISCOVER', $l->g(489), '', '', $default, $supp ?? '');

    //FREQUENCY
    if (isset($optPerso["FREQUENCY"])) {
        $default = '';
        if ($optPerso["FREQUENCY"]["IVALUE"] == 0) {
            $supp = $l->g(485);
        } else if ($optPerso["FREQUENCY"]["IVALUE"] == -1) {
            $supp = $l->g(486);
        } else {
            $supp = $l->g(495) . $optPerso["FREQUENCY"]["IVALUE"] . $l->g(496);
        }
    } else {
        $supp = '';
        $default = $l->g(497);
    }

    optpersoGroup('FREQUENCY', $l->g(494), '', '', $default, $supp ?? '');

    //DOWNLOAD_SWITCH
    if (isset($optPerso["DOWNLOAD_SWITCH"])) {
        $default = '';
        if ($optPerso["DOWNLOAD_SWITCH"]["IVALUE"] == 0) {
            $supp = $l->g(733);
        } else if ($optPerso["DOWNLOAD_SWITCH"]["IVALUE"] == 1) {
            $supp = $l->g(205);
        } else {
            $supp = null;
        }
    }
    else {
        $supp = '';
        if ($optdefault['ivalue']["DOWNLOAD"] == 1) {
            $default = $l->g(205);
        } else {
            $default = $l->g(733);
        }
    }

    //DOWNLOAD
    optpersoGroup("DOWNLOAD", $l->g(417), "DOWNLOAD", '', $default, $supp ?? '');

    if(isset($optPerso["DOWNLOAD_CYCLE_LATENCY"])){
        $default = '';
        $supp = $optPerso["DOWNLOAD_CYCLE_LATENCY"]["IVALUE"] . " ".$l->g(511);
    } else{
        $supp = '';
        $default = $optdefault['ivalue']["DOWNLOAD_CYCLE_LATENCY"] . " ".$l->g(511);
    }

    //DOWNLOAD_CYCLE_LATENCY
    optpersoGroup("DOWNLOAD_CYCLE_LATENCY", $l->g(720), "DOWNLOAD_CYCLE_LATENCY", $optPerso ?? '', $default, $supp ?? '');

    if(isset($optPerso['DOWNLOAD_FRAG_LATENCY']['IVALUE'])){
        $default = '';
        $supp = $optPerso['DOWNLOAD_FRAG_LATENCY']['IVALUE'] . " " . $l->g(511);
    } else{
        $default = $optdefault['ivalue']["DOWNLOAD_FRAG_LATENCY"]. " " . $l->g(511);
        $supp = '';
    }
    //DOWNLOAD_FRAG_LATENCY
    optpersoGroup("DOWNLOAD_FRAG_LATENCY", $l->g(721), "DOWNLOAD_FRAG_LATENCY", $optPerso ?? '', $default, $supp ?? '');

    if(isset($optPerso['DOWNLOAD_PERIOD_LATENCY']['IVALUE'])){
        $default = '';
        $supp = $optPerso['DOWNLOAD_PERIOD_LATENCY']['IVALUE'] . " " . $l->g(511);
    } else{
        $default = $optdefault['ivalue']["DOWNLOAD_PERIOD_LATENCY"]. " " . $l->g(511);
        $supp = '';
    }
    //DOWNLOAD_PERIOD_LATENCY
    optpersoGroup("DOWNLOAD_PERIOD_LATENCY", $l->g(722), "DOWNLOAD_PERIOD_LATENCY", $optPerso ?? '', $default, $supp ?? '');


    if(isset($optPerso['DOWNLOAD_PERIOD_LENGTH']['IVALUE'])){
        $default = '';
        $supp = $optPerso['DOWNLOAD_PERIOD_LENGTH']['IVALUE'];
    } else{
        $default = $optdefault['ivalue']["DOWNLOAD_PERIOD_LENGTH"];
        $supp = '';
    }
    //DOWNLOAD_PERIOD_LENGTH
    optpersoGroup("DOWNLOAD_PERIOD_LENGTH", $l->g(723), "DOWNLOAD_PERIOD_LENGTH", $optPerso ?? '', $default, $supp ?? '');

    if(isset($optPerso['PROLOG_FREQ']['IVALUE'])){
        $default = '';
        $supp = $optPerso['PROLOG_FREQ']['IVALUE'] . " " . $l->g(730);
    } else{
        $default = $optdefault['ivalue']["PROLOG_FREQ"] . " " . $l->g(730);
        $supp = '';
    }
    //PROLOG_FREQ
    optpersoGroup("PROLOG_FREQ", $l->g(724), "PROLOG_FREQ", $optPerso ?? '', $default, $supp ?? '');

    //SNMP_SWITCH
     if (isset($optPerso["SNMP_SWITCH"])) {
         $default = '';
        if ($optPerso["SNMP_SWITCH"]["IVALUE"] == 0) {
            $supp = $l->g(733);
        } else if ($optPerso["SNMP_SWITCH"]["IVALUE"] == 1) {
            $supp = $l->g(205);
        } else {
            $supp = null;
        }
    } else {
         $supp = '';
        if (isset($optdefault['ivalue']["SNMP"]) && $optdefault['ivalue']["SNMP"] == 1) {
            $default = $l->g(205);
        } else {
            $default = $l->g(733);
        }
    }

    optpersoGroup('SNMP_SWITCH', $l->g(1197), 'SNMP_SWITCH', '', $default, $supp);

    //TELEDEPLOY
    require_once('require/function_machine.php');
    show_packages($systemid, "ms_group_show");

    if ($_SESSION['OCS']['profile']->getConfigValue('CONFIG') == "YES") {
        echo "<a class='btn btn-success' href=\"index.php?" . PAG_INDEX . "=" . $pages_refs['ms_custom_param'] . "&head=1&idchecked=" . $systemid . "&origine=group\">
		" . $l->g(285) . "</a>";
    }

    echo close_form();
}

function img($a, $avail) {
    global $systemid, $protectedGet;

    if ($avail) {
        $href = "<a href='index.php?" . PAG_INDEX . "=" . $protectedGet[PAG_INDEX] . "&head=1&systemid=" . urlencode($systemid) . "&option=" . urlencode($a) . "'>";
        $fhref = "</a>";
        $img = '<button type="button" class="btn btn-default spaceX-10-right">' . $a . '</button>';
    } else {
        $href = "";
        $fhref = "";
        $img = '<button type="button" class="btn btn-default spaceX-10-right">' . $a . '</button>';
    }

    return $href . $img . $fhref;
}

function show_stat($fileId) {
    global $protectedGet, $pages_refs;

    echo "<a href=\"index.php?" . PAG_INDEX . "=" . $pages_refs['ms_tele_stats'] . "&head=1&stat=" . $fileId . "&group=" . $protectedGet['systemid'] . "\"><span class='glyphicon glyphicon-stats'></span></a>";
}



/**
 * @param array $data
 * @param array $labels
 */
function show_resume($data, $labels) {

    $nb_col = 2;
    $i = 0;
    foreach ($data as $key => $value) {
        if ($i % $nb_col == 0) {
            echo '<div class="row">';
        }

        echo '<div class="col col-md-6">';


        if (trim($value) != '') {
            echo '<span class="summary-header text-left">' . $labels[$key] . ' :</span>';
            echo '<span class="summary-value text-left">' . $value . '</span>';
        }

        echo '</div>';

        $i++;
        if ($i % $nb_col == 0) {
            echo '</div>';
        }
    }

}

?>
