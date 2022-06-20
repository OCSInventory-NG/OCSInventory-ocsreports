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
 require_once('require/function_search.php');
 require_once('require/function_admininfo.php');

 $form_name = "lock_affect";

 echo open_form($form_name, '', '', 'form-horizontal');

 echo "<div class='row'>";
 echo "<div class='col-md-12'>";

 $list_id = multi_lot($form_name, $l->g(601));
var_dump($list_id);
 if (is_defined($list_id)) {

     echo "<div class='col col-md-12'>";

     //cas of TAG INFO
     if (is_defined($protectedPost['Valid_modif'])) {
         $info_account_id = admininfo_computer();

         foreach ($protectedPost as $field => $value) {
             if (substr($field, 0, 5) == "check") {
                 $temp = substr($field, 5);
                 if (array_key_exists($temp, $info_account_id)) {
                     //cas of checkboxtag_search
                     foreach ($protectedPost as $field2 => $value2) {
                         $casofcheck = explode('_', $field2);
                         if (isset($casofcheck[1]) && $casofcheck[0] . '_' . $casofcheck[1] == $temp) {
                             if (isset($casofcheck[2])) {
                                 $data_fields_account[$temp] .= $casofcheck[2] . "&&&";
                             }
                         }
                     }
                     if (!isset($data_fields_account[$temp])) {
                         $data_fields_account[$temp] = $protectedPost[$temp];
                     }
                 }
             }
         }

         if (isset($data_fields_account)) {
             updateinfo_computer($list_id, $data_fields_account, 'LIST');
             unset($_SESSION['OCS']['DATA_CACHE']['TAB_MULTICRITERE']);
             echo "<script language='javascript'> window.opener.document.multisearch.submit();</script>";
             echo "<script language='javascript'> window.opener.document.show_all.submit();</script>";
         }
     }

     //CAS OF TELEDEPLOY
     if (is_defined($protectedPost['RAZ']) && $protectedPost['pack_list'] != "") {
         $sql = "select ID from download_enable where fileid='%s'";
         $arg = $protectedPost['pack_list'];
         $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
         $item = mysqli_fetch_object($result);
         require_once('require/function_telediff.php');
         $nb_line_affected = desactive_packet($list_id, $item->ID);
         msg_success($nb_line_affected . " " . $l->g(1026));
     }

     //CAS OF WOL
     require_once('require/wol/WakeOnLan.php');
     $wol = new Wol();

     if (is_defined($protectedPost['WOL'])) {
         $sql = "select IPADDRESS,MACADDR,IPMASK from networks WHERE status='Up' and hardware_id in ";
         $arg = array();
         $tab_result = mysql2_prepare($sql, $arg, $list_id);
         $resultDetails = mysql2_query_secure($tab_result['SQL'], $_SESSION['OCS']["readServer"], $tab_result['ARG']);
         $msg = "";
         while ($item = mysqli_fetch_object($resultDetails)) {
             $broadcast = long2ip(ip2long($item->IPADDRESS) | ~ip2long($item->IPMASK));
             $wol->look_config_wol($broadcast, $item->MACADDR);
             $msg .= "<br>" . $wol->wol_send . "=>" . $item->MACADDR . "/" . $item->IPADDRESS;
         }
         msg_info($msg);
     }

     if (is_defined($protectedPost['WOL_PROGRAM'])) {
        $result = $wol->save_wol($protectedGet['idchecked'], $protectedPost['WOL_DATE']);
        unset($protectedPost['WOL_PROGRAM']);
        unset($protectedPost['WOL_DATE']);
        unset($protectedPost['WOL_PROGRAM']);
        if($result){
            msg_success($l->g(8203));
        }
     }

     //CAS ARCHIVE
     require_once('require/archive/ArchiveComputer.php');
     $archive = new ArchiveComputer();
     if (is_defined($protectedPost['ARCHIVER'])) {
        $result = $archive->archive($list_id);
        unset($protectedPost['ARCHIVER']);
        if($result){
            msg_success($l->g(572));
        }
     }

     if (is_defined($protectedPost['RESTORE'])) {
        $result = $archive->restore($list_id);
        unset($protectedPost['RESTORE']);
        if($result){
            msg_success($l->g(572));
        }
     }
     echo "</div>";

     //tab definition
     if ($_SESSION['OCS']['profile']->getConfigValue('CHANGE_ACCOUNTINFO') == "YES") {
         $def_onglets['TAG'] = $l->g(1022);
     } else {
         $protectedPost['onglet'] = 'SUP_PACK';
     }
     $def_onglets['SUP_PACK'] = $l->g(1021);

     if ($_SESSION['OCS']['profile']->getRestriction('WOL', 'NO') == "NO") {
         $def_onglets['WOL'] = $l->g(1280);
     }

     if ($_SESSION['OCS']['profile']->getConfigValue('ARCHIVE_COMPUTERS') == "YES") {
        $def_onglets['ARCHIVE'] = $l->g(1556);
     }

     if (empty($protectedPost['onglet'])) {
         $protectedPost['onglet'] = "TAG";
     }
     //show onglet
     echo "<p>";
     onglet($def_onglets, $form_name, "onglet", 7);
     echo "</p>";

     if (is_defined($protectedPost['CHOISE'])) {

         if (!isset($protectedPost['onglet']) || $protectedPost['onglet'] == "TAG") {
             require_once('require/function_admininfo.php');
             $field_of_accountinfo = witch_field_more('COMPUTERS');
             $tab_typ_champ = array();
             $i = 0;
             $dont_show_type = array(8, 3);
             echo "</div>";
             echo "<div class='col-md-10 col-md-offset-1'>";
             foreach ($field_of_accountinfo['LIST_FIELDS'] as $id => $lbl) {
                 if (!in_array($field_of_accountinfo['LIST_TYPE'][$id], $dont_show_type)) {
                     if ($field_of_accountinfo['LIST_NAME'][$id] == "TAG") {
                         $truename = "TAG";
                     } else {
                         $truename = "fields_" . $id;
                     }
                     if ($field_of_accountinfo['LIST_TYPE'][$id] == 14) {
                         $tab_typ_champ[$i]['CONFIG']['MAXLENGTH'] = 10;
                         $tab_typ_champ[$i]['CONFIG']['SIZE'] = 10;
                         $tab_typ_champ[$i]['COMMENT_AFTER'] = calendars($truename, $l->g(1270)) . "</td></span><span class='input-group-addon' id='" . $truename . "-addon'><td><input type='checkbox' name='check" . $truename . "' id='check" . $truename . "' " . (isset($protectedPost['check' . $truename]) ? " checked " : "") . ">";
                     } elseif (in_array($field_of_accountinfo['LIST_TYPE'][$id], array(2, 5, 11))) {
                         $sql = "select ivalue as ID,tvalue as NAME from config where name like 'ACCOUNT_VALUE_%s' order by 2";
                         $arg = $field_of_accountinfo['LIST_NAME'][$id] . "%";
                         $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
                         while ($val = mysqli_fetch_array($result)) {
                             $tab_typ_champ[$i]['DEFAULT_VALUE'][$val['ID']] = $val['NAME'];
                         }
                         $tab_typ_champ[$i]['COMMENT_AFTER'] = "</td><td><input type='checkbox' name='check" . $truename . "' id='check" . $truename . "' " . (isset($protectedPost['check' . $truename]) ? " checked " : "") . ">";
                     } else {
                         $tab_typ_champ[$i]['COMMENT_AFTER'] = "</td><td><input type='checkbox' name='check" . $truename . "' id='check" . $truename . "' " . (isset($protectedPost['check' . $truename]) ? " checked " : "") . ">";
                         $tab_typ_champ[$i]['CONFIG']['MAXLENGTH'] = 100;
                         $tab_typ_champ[$i]['CONFIG']['SIZE'] = 30;
                     }
                     $tab_typ_champ[$i]['INPUT_NAME'] = $truename;
                     $tab_typ_champ[$i]['INPUT_TYPE'] = $field_of_accountinfo['LIST_TYPE'][$id];
                     $tab_typ_champ[$i]['CONFIG']['JAVASCRIPT'] = ($java ?? '') . " onclick='document.getElementById(\"check" . $truename . "\").checked = true' ";

                     $tab_name[$i] = $lbl;
                     $i++;
                 }
             }
             modif_values($tab_name, $tab_typ_champ, array('TAG_MODIF' => $protectedPost['MODIF'] ?? '', 'FIELD_FORMAT' => $type_field[$protectedPost['MODIF'] ?? ''] ?? ''), array(
                 'title' => $l->g(895)
             ));
         } elseif ($protectedPost['onglet'] == "SUP_PACK") {
             $queryDetails = "select d_a.fileid,d_a.name
 									from download_available d_a, download_enable d_e
 									where d_e.FILEID=d_a.FILEID group by d_a.NAME  order by 1 desc";
             $resultDetails = mysql2_query_secure($queryDetails, $_SESSION['OCS']["readServer"]);
             $List = [];
             while ($val = mysqli_fetch_array($resultDetails)) {
                 $List[$val["fileid"]] = $val["name"];
             }
             $select = show_modif($List, 'pack_list', 2, $form_name);
             echo "</div>";
             echo "<div class='col-md-10 col-md-offset-1'>";
             echo $select;
             echo "<div class='col-md-12'>";

             if (!empty($protectedPost['pack_list'])) {
                 $sql = "select count(*) c, tvalue from download_enable d_e,devices d
           							where d.name='DOWNLOAD' and d.IVALUE=d_e.ID and d_e.fileid='%s'
           							and d.hardware_id in ";
                 $arg = array($protectedPost['pack_list']);
                 $tab_result = mysql2_prepare($sql, $arg, $list_id);
                 $sql = $tab_result['SQL'] . " group by tvalue";
                 $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $tab_result['ARG']);
                 while ($item = mysqli_fetch_object($result)) {
                     if ($item->tvalue == "") {
                         $value = $l->g(482);
                     } else {
                         $value = $item->tvalue;
                     }
                     echo "<br>" . $item->c . " " . $l->g(1023) . " " . $value . " " . $l->g(1024);
                 }
                 echo "<br><input type='submit' name='RAZ' value='" . $l->g(1025) . "' class='btn'>";
             }
         } elseif ($protectedPost['onglet'] == "WOL") {
            echo "<div class='col-md-8 col-xs-offset-0 col-md-offset-2'>";
            
            echo "<div><input type='checkbox' name='SCHEDULE_WOL' id='SCHEDULE_WOL' style='display:initial;width:20px;height:14px;' class='form-control' onclick='show_hide_wol(\"WOL_DIV\", \"SCHEDULE_WOL\", \"WOL\");'>".$l->g(8200)."</div>";
            echo "<br><input type='submit' name='WOL' id='WOL' value='" . $l->g(13) . "' class='btn'>";
            echo "<div style='display:none;' id='WOL_DIV'>";
            $config['COMMENT_AFTER'][0] = datePick("WOL_DATE");
            $config['SELECT_DEFAULT'][0] = '';
            $config['SIZE'][0] = '8';
            $tab_name = array($l->g(8202));
            $name_field = array("WOL_DATE");
            $type_field = array(14);
            $value_field = array();
            
            $tab_typ_champ = show_field($name_field, $type_field, $value_field, $config);
            modif_values($tab_name, $tab_typ_champ, $tab_hidden ?? '', array('show_button' => false));
            echo "<input type='submit' name='WOL_PROGRAM' value='" . $l->g(8201) . "' class='btn'>";
            echo "</div></div></div>";
         } elseif ($protectedPost['onglet'] == "ARCHIVE") {
            echo "<div class='col-md-8 col-xs-offset-0 col-md-offset-2'>";
            echo "<input type='submit' name='ARCHIVER' value='" . $l->g(1551) . "' class='btn'>";
            echo "<input type='submit' name='RESTORE' value='" . $l->g(1552) . "' class='btn' style='margin-left:10px;'>";
            echo "</div>";
         }
     }
     echo "</div>";
 }
 echo close_form();
 ?>
