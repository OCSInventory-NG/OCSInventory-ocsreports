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

require_once('require/function_ipdiscover.php');

if (!isset($protectedPost['MODIF']) || (isset($protectedPost['MODIF']) && $protectedPost['MODIF'] == "")) {
    echo "<a class='btn btn-info' href='index.php?function=show_ipdiscover'>".$l->g(188)."</a></br></br>";
}

$form_name = 'info_ipdiscover';
$tab_options = $protectedPost;

//recherche de la personne connectée
if (isset($_SESSION['OCS']['TRUE_USER'])) {
    $user = $_SESSION['OCS']['TRUE_USER'];
} else {
    $user = $_SESSION['OCS']['loggeduser'];
}

//suppression d'une adresse mac
if (isset($protectedPost['SUP_PROF'])) {
    //check if we are deleting an identified peripherials ?
    if ($protectedGet['prov'] == "ident") {
        //dismiss manufacturer name and mac to be able to remove it properly.
        $exploded_data = explode(' ', $protectedPost['SUP_PROF']);
        $protectedPost['SUP_PROF'] = $exploded_data[0];
    }

    $sql = "DELETE FROM netmap WHERE mac='%s'";
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $protectedPost['SUP_PROF']);
    $sql = "DELETE FROM network_devices WHERE macaddr='%s'";
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $protectedPost['SUP_PROF']);
    unset($_SESSION['OCS']['DATA_CACHE']['IPDISCOVER_' . $protectedGet['prov']]);
}
//identification d'une adresse mac
if (isset($protectedPost['Valid_modif'])) {
    if (trim($protectedPost['COMMENT']) == "") {
        $ERROR = $l->g(942);
    }
    if (trim($protectedPost['TYPE']) == "") {
        $ERROR = $l->g(943);
    }
    if (isset($ERROR) && $protectedPost['MODIF_ID'] != '') {
        $protectedPost['USER'] = $protectedPost['USER_ENTER'];
    }

    if (!isset($ERROR)) {
        if (!empty($protectedPost['USER_ENTER'])) {
            $sql = "UPDATE network_devices
					SET DESCRIPTION = '%s',
					TYPE = '%s',
					MACADDR = '%s',
					USER = '%s' where MACADDR='%s'";
            $arg = array($protectedPost['COMMENT'], $protectedPost['TYPE'], $protectedPost['mac'], $user, $protectedPost['MODIF_ID']);
        } else {
            if(!check_if_inv_mac_already_exist($protectedPost['mac'])){
                $sql = "INSERT INTO network_devices (DESCRIPTION,TYPE,MACADDR,USER)
                    VALUES('%s','%s','%s','%s')";
                $arg = array($protectedPost['COMMENT'], $protectedPost['TYPE'], $protectedPost['mac'], $user);
            }

        }
        if(isset($sql)){
            mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
        }

        //suppression du cache pour prendre en compte la modif
        unset($_SESSION['OCS']['DATA_CACHE']['IPDISCOVER_' . $protectedGet['prov']]);
    } else {
        $protectedPost['MODIF'] = $protectedPost['mac'];
    }
}

//del the selection
if (!empty($protectedPost['DEL_ALL'])) {
    foreach ($protectedPost as $key => $value) {
        $checkbox = explode('check', $key);
        if (isset($checkbox[1])) {
          $sql = "DELETE FROM netmap WHERE mac='%s'";
          mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $checkbox[1]);
          $sql = "DELETE FROM network_devices WHERE macaddr='%s'";
          mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $checkbox[1]);
          unset($_SESSION['OCS']['DATA_CACHE']['IPDISCOVER_' . $protectedGet['prov']]);
        }
    }
    $tab_options['CACHE'] = 'RESET';
}

//formulaire de saisie de l'identification de l'adresse mac
if (is_defined($protectedPost['MODIF'])) {
    //cas d'une modification de la donnée déjà saisie
    if ($protectedGet['prov'] == "ident" && !isset($protectedPost['COMMENT'])) {
        $sql = "SELECT DESCRIPTION,TYPE,MACADDR,USER FROM network_devices WHERE id ='%s'";
        $arg = $protectedPost['MODIF'];
        $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
        $val = mysqli_fetch_array($res);
        $protectedPost['COMMENT'] = $val['DESCRIPTION'];
        $protectedPost['MODIF'] = $val['MACADDR'];
        $protectedPost['TYPE'] = $val['TYPE'];
        $protectedPost['USER'] = $val['USER'];
        $protectedPost['MODIF_ID'] = $protectedPost['MODIF'];
    }

    if(isset($protectedPost['USER']) && isset($protectedPost['MODIF_ID'])) {
        $tab_hidden['USER_ENTER'] = $protectedPost['USER'];
        $tab_hidden['MODIF_ID'] = $protectedPost['MODIF_ID'];
    }

    //si on est dans le cas d'une modif, on affiche le login qui a saisi la donnée
    if (isset($protectedPost['MODIF_ID']) && $protectedPost['MODIF_ID'] != '') {
        $tab_name[3] = $l->g(944) . ": ";
        $title = $l->g(945);
    } else {
        $title = $l->g(946);
    }

    $sql = "SELECT DISTINCT NAME FROM devicetype ";
    $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
    while ($row = mysqli_fetch_object($res)) {
        $list_type[$row->NAME] = $row->NAME;
    }

    $tab_name = array($l->g(944), $l->g(95), $l->g(53), $l->g(66));
    $name_field = array('USER', 'MAC', 'COMMENT', 'TYPE');
    $type_field = array(13, 13, 0, 2);
    $value_field =  array($_SESSION["OCS"]["loggeduser"] ?? '', $protectedPost['MODIF'] ?? '', $protectedPost['COMMENT'] ?? '', $list_type ?? []);
    $tab_typ_champ = show_field($name_field, $type_field, $value_field);
    $tab_hidden['mac'] = $protectedPost['MODIF'];
    if (isset($ERROR)) {
        msg_error($ERROR);
    }

    foreach ($tab_typ_champ as $id => $values) {
        if($tab_typ_champ[$id]["INPUT_TYPE"] == 2) {
            $tab_typ_champ[$id]['CONFIG']['SELECTED_VALUE'] = $protectedPost[$tab_typ_champ[$id]['INPUT_NAME']] ?? 0;
        }
    }

    modif_values($tab_name, $tab_typ_champ, $tab_hidden, array(
        'title' => $title
    ));

} else { //affichage des périphériques
    if (!(isset($protectedPost["pcparpage"]))) {
        $protectedPost["pcparpage"] = 5;
    }
    if (isset($protectedGet['value'])) {
        $explode = explode(";", $protectedGet['value']);
        $value_preg = preg_replace("/[^A-zA-Z0-9\._]/", "", $explode[0]);
        $tag = addslashes($explode[1] ?? '');

        if ($protectedGet['prov'] == "no_inv") {
            $title = $l->g(947);
            $sql = "SELECT 
                    ip, mac, mask, date, name, n.TAG
                    FROM netmap n 
                    LEFT JOIN networks ns ON ns.macaddr=n.mac
                    WHERE n.mac NOT IN ( 
                        SELECT DISTINCT(macaddr) FROM network_devices 
                    ) 
                    AND (ns.macaddr IS NULL) 
                    AND n.netid IN ('%s')";
            if($tag != "") {
                $sql .= " AND n.TAG = '%s'";
            }

            $tab_options['ARG_SQL'] = array($value_preg, $tag);
            $list_fields = array($l->g(34) => 'ip',
                $l->g(95) => 'mac',
                $l->g(208) => 'mask',
                $l->g(232) => 'date',
                $l->g(318) => 'name',
                'TAG' => 'TAG');
            $tab_options['FILTRE'] = array_flip($list_fields);
            $tab_options['ARG_SQL_COUNT'] = array($value_preg, $tag);
            $list_fields['SUP'] = 'mac';
            $list_fields['CHECK'] = 'mac';
            $list_fields['MODIF'] = 'mac';
            if(isset($tab_options['MODIF']) && !is_array($tab_options['MODIF'])) $tab_options['MODIF'] = array();
            $tab_options['MODIF']['IMG'] = "image/prec16.png";
            $tab_options['LBL']['MODIF'] = $l->g(114);
            $default_fields = $list_fields;

        } elseif ($protectedGet['prov'] == "ident") {
            $title = $l->g(948);
            $sql = "SELECT n.ID,n.TYPE,n.DESCRIPTION,a.IP,a.MAC,a.MASK,a.NETID,a.NAME,a.date,n.USER
                    FROM network_devices n LEFT JOIN netmap a ON a.mac=n.macaddr
                    WHERE netid = '%s'";
            if($tag != "") {
                $sql .= " AND TAG = '%s'";
            }
            $tab_options['ARG_SQL'] = array($value_preg, $tag);
            $list_fields = array($l->g(66) => 'TYPE', $l->g(53) => 'DESCRIPTION',
                $l->g(34) => 'IP',
                $l->g(95) => 'MAC',
                $l->g(208) => 'MASK',
                $l->g(316) => 'NETID',
                $l->g(318) => 'NAME',
                $l->g(232) => 'date',
                $l->g(369) => 'USER',
                'TAG' => 'TAG');
            $tab_options['FILTRE'] = array_flip($list_fields);
            $tab_options['ARG_SQL_COUNT'] = array($value_preg, $tag);
            $list_fields['SUP'] = 'MAC';
            $list_fields['MODIF'] = 'ID';
            $default_fields = array($l->g(34) => $l->g(34), $l->g(66) => $l->g(66), $l->g(53) => $l->g(53),
                $l->g(95)  => 'MAC', $l->g(232) => $l->g(232), $l->g(369) => $l->g(369), 'SUP' => 'SUP', 'MODIF' => 'MODIF');
        } elseif ($protectedGet['prov'] == "inv" || $protectedGet['prov'] == "ipdiscover") {
            if(isset($list_fields)) {
                //BEGIN SHOW ACCOUNTINFO
                require_once('require/function_admininfo.php');
                    $accountinfo_value = interprete_accountinfo($list_fields, $tab_options);
                if (array($accountinfo_value['TAB_OPTIONS']))
                    $tab_options = $accountinfo_value['TAB_OPTIONS'];
                if (array($accountinfo_value['DEFAULT_VALUE']))
                    $default_fields = $accountinfo_value['DEFAULT_VALUE'];
                $list_fields = $accountinfo_value['LIST_FIELDS'];
                $tab_options['FILTRE'] = array_flip($list_fields);
                //END SHOW ACCOUNTINFO
            }
            
            $list_fields2 = array($l->g(46) => "h.lastdate",
                'NAME' => 'h.name',
                $l->g(24) => "h.userid",
                $l->g(25) => "h.osname",
                $l->g(33) => "h.workgroup",
                $l->g(275) => "h.osversion",
                $l->g(34) => "h.ipaddr",
                $l->g(95) => 'n.macaddr',
                $l->g(557) => "h.userdomain");

            $tab_options["replace_query_arg"]['MD5_DEVICEID'] = " md5(deviceid) ";
            $list_fields = isset($list_fields) ? array_merge($list_fields, $list_fields2) : $list_fields2;
            $sql = prepare_sql_tab($list_fields);
            $list_fields = array_merge($list_fields, array('MD5_DEVICEID' => "MD5_DEVICEID"));
            $tab_options['ARG_SQL'] = $sql['ARG'];
            if ($protectedGet['prov'] == "inv") {
                $title = $l->g(1271);
                $sql = $sql['SQL'] . ",md5(deviceid) as MD5_DEVICEID from accountinfo a,hardware h LEFT JOIN networks n ON n.hardware_id=h.id";
                $sql .= " where ipsubnet='%s' and status='Up' and a.hardware_id=h.id";
                if($tag != "") {
                    $sql .= " AND TAG = '%s'";
                }
            } else {
                $title = $l->g(492);
                $sql = $sql['SQL'] . " from accountinfo a,hardware h left join devices d on d.hardware_id=h.id";
                $sql .= " where a.hardware_id=h.id and (d.ivalue=1 or d.ivalue=2) and d.name='IPDISCOVER' and d.tvalue='%s'";
            }
            $sql .= " group by h.id";

            array_push($tab_options['ARG_SQL'], $value_preg, $tag);
            $default_fields['NAME'] = 'NAME';
            $default_fields[$l->g(34)] = $l->g(34);
            $default_fields[$l->g(24)] = $l->g(24);
            $default_fields[$l->g(25)] = $l->g(25);
            $default_fields[$l->g(275)] = $l->g(275);
            $tab_options['ARG_SQL_COUNT'] = array($value_preg);
            $tab_options['FILTRE']['h.name'] = $l->g(49);
            $tab_options['FILTRE']['h.userid'] = $l->g(24);
            $tab_options['FILTRE']['h.osname'] = $l->g(25);
            $tab_options['FILTRE']['h.ipaddr'] = $l->g(34);
        }
        printEnTete($title);
        echo "<br><br>";

        $tab_options['LBL']['MAC'] = $l->g(95);

        $list_col_cant_del = array($l->g(66) => $l->g(66), 'SUP' => 'SUP', 'CHECK' => 'CHECK', 'MODIF' => 'MODIF');
        $table_name = "IPDISCOVER_" . $protectedGet['prov'] . "_" . str_replace(" ", "",str_replace(".", "",$value_preg));
        $tab_options['table_name'] = $table_name;
        $form_name = $table_name;
        $tab_options['form_name'] = $form_name;
        echo open_form($form_name, '', '', 'form-horizontal');
        $result_exist = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
        $fipdisc = "ipdiscover-util.pl";
        $values = look_config_default_values(array('IPDISCOVER_IPD_DIR'), '', array('IPDISCOVER_IPD_DIR' => array('TVALUE' => VARLIB_DIR)));
        $IPD_DIR = $values['tvalue']['IPDISCOVER_IPD_DIR'] . "/ipd";
        if ($scriptPresent = @stat($fipdisc)) {
            $filePresent = true;
            if (!is_executable($fipdisc)) {
                $msg_info = $fipdisc . " " . $l->g(341);
            } else if (!is_writable($IPD_DIR)) {
                $msg_info = $l->g(342) . " " . $fipdisc . " (" . $IPD_DIR . ")";
            }
            if (!isset($msg_info)) {
                echo "<p><input type='button' onclick=window.open(\"index.php?" . PAG_INDEX . "=" . $pages_refs['ms_ipdiscover_analyse'] . "&head=1&rzo=" . $value_preg . "\",\"analyse\",\"location=0,status=0,scrollbars=1,menubar=0,resizable=0,width=800,height=650\") name='analyse' value='" . $l->g(317) . "' class='btn'></p>";
            } else {
                msg_info($msg_info);
            }

            if ($protectedGet['prov'] == "no_inv"){
              echo "<a href=# OnClick='confirme(\"\",\"DEL_SEL\",\"" . $form_name . "\",\"DEL_ALL\",\"" . $l->g(900) . "\");'><span class='glyphicon glyphicon-remove delete-span'></span></a>";
              echo "<input type='hidden' id='DEL_ALL' name='DEL_ALL' value=''>";
            }
        }

        echo close_form();
    }
}
if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $sql, $tab_options);
}
?>
