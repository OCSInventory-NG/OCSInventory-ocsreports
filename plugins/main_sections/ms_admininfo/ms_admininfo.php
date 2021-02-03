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
/*
 * It Set Management light
 * Admin your accountinfo
 *
 */
if (AJAX) {
    parse_str($protectedPost['ocs']['0'], $params);
    $protectedPost += $params;
    ob_start();
}

require_once('require/function_admininfo.php');
require_once('require/function_config_generale.php');
require('require/CSV.class.php');



$accountinfo_choise['COMPUTERS'] = $l->g(729);
$accountinfo_choise['SNMP'] = $l->g(1136);

if(isset($protectedPost['addtab_x'])){
  $protectedPost['onglet'] = 4;
}

if (!isset($protectedPost['onglet']) || $protectedPost['onglet'] == '') {
    $protectedPost['onglet'] = 1;
}
$form_name = 'admin_info';
$table_name = $form_name;
$tab_options = $protectedPost;

$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;

$data_on[1] = $l->g(1059);
$data_on[2] = $l->g(1060);
$data_on[3] = $l->g(1701);
$data_on[4] = $l->g(1702);
$data_on[5] = "Import from CSV";

if (isset($protectedPost['MODIF']) && is_numeric($protectedPost['MODIF']) && !isset($protectedPost['Valid_modif']) && $protectedPost['onglet'] == 1) {
    $protectedPost['onglet'] = 2;
    $accountinfo_detail = find_info_accountinfo($protectedPost['MODIF']);
    $protectedPost['newfield'] = $accountinfo_detail[$protectedPost['MODIF']]['name'];
    $protectedPost['newlbl'] = $accountinfo_detail[$protectedPost['MODIF']]['comment'];
    $protectedPost['newtype'] = $accountinfo_detail[$protectedPost['MODIF']]['type'];
    $protectedPost['account_tab'] = $accountinfo_detail[$protectedPost['MODIF']]['id_tab'];
    $protectedPost['accountinfo'] = $accountinfo_detail[$protectedPost['MODIF']]['account_type'];
    $protectedPost['default_value'] = $accountinfo_detail[$protectedPost['MODIF']]['default_value'];
    $hidden = $protectedPost['MODIF'];
}

if (isset($protectedPost['MODIF']) && is_numeric($protectedPost['MODIF']) && !isset($protectedPost['Valid_modif']) && $protectedPost['onglet'] == 3) {
    $protectedPost['onglet'] = 4;
    $val_info = look_config_default_values(array("TAB_ACCOUNTAG_" . $protectedPost['MODIF']));
    $protectedPost['newfield'] = $val_info['tvalue']["TAB_ACCOUNTAG_" . $protectedPost['MODIF']];
    if (isset($protectedGet['nb_field']) && is_numeric($protectedGet['nb_field'])) {
        $protectedPost['2newfield'] = $val_info['comments']["TAB_ACCOUNTAG_" . $protectedPost['MODIF']];
    }
    $hidden = $protectedPost['MODIF'];
}

if (isset($protectedPost['MODIF_OLD']) && is_numeric($protectedPost['MODIF_OLD']) && $protectedPost['Valid_modif'] != "" && $protectedPost['onglet'] == 2) {
    //UPDATE VALUE
    $msg = update_accountinfo($protectedPost['MODIF_OLD'], array('TYPE' => $protectedPost['newtype'],
        'NAME' => $protectedPost['newfield'],
        'COMMENT' => $protectedPost['newlbl'],
        'ID_TAB' => $protectedPost['account_tab'],
        'DEFAULT_VALUE' => $protectedPost['default_value']), $protectedPost['accountinfo']);
    $hidden = $protectedPost['MODIF_OLD'];
} elseif ($protectedPost['Valid_modif'] != "") {
    //ADD NEW VALUE
    $msg = add_accountinfo($protectedPost['newfield'], $protectedPost['newtype'], $protectedPost['newlbl'], $protectedPost['account_tab'], $protectedPost['accountinfo'], $protectedPost['default_value']);
}

if (isset($msg['ERROR'])) {
    msg_error($msg['ERROR']);
}
if (isset($msg['SUCCESS'])) {
    msg_success($msg['SUCCESS']);
    $protectedPost['onglet'] = 1;
}

if (isset($protectedPost['MODIF_OLD']) && is_numeric($protectedPost['MODIF_OLD']) && $protectedPost['Valid_modif'] != "" && $protectedPost['onglet'] == 4) {
    //UPDATE VALUE
    update_config("TAB_ACCOUNTAG_" . $protectedPost['MODIF_OLD'], 'TVALUE', $protectedPost['newfield']);
    if (isset($protectedPost['2newfield'])) {
        update_config("TAB_ACCOUNTAG_" . $protectedPost['MODIF_OLD'], 'COMMENTS', $protectedPost['2newfield'], false);
    }
    $hidden = $protectedPost['MODIF_OLD'];
    $protectedPost['onglet'] = 3;
} elseif ($protectedPost['Valid_modif'] != "" && $protectedPost['onglet'] == 4) {
    //ADD NEW VALUE
    //vérification que le nom du champ n'existe pas pour les nouveaux champs
    if (trim($protectedPost['newfield']) != '') {
        $sql_verif = "SELECT count(*) c FROM config WHERE TVALUE = '%s' and NAME like '%s'";
        //echo $sql_verif;
        $arg_verif = array($protectedPost['newfield'], "TAB_ACCOUNTAG_%");
        $res_verif = mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"], $arg_verif);
        //echo $val_verif = mysqli_fetch_array( $res_verif );
        $val_verif = mysqli_fetch_array($res_verif);
        if ($val_verif['c'] > 0) {
            $ERROR = $l->g(656);
        }
    } else {
        $ERROR = $l->g(1068);
    }

    if (!isset($ERROR)) {
        $sql_new_value = "SELECT max(ivalue) max FROM config WHERE  NAME like '%s'";
        $arg_new_value = array("TAB_ACCOUNTAG_%");
        $res_new_value = mysql2_query_secure($sql_new_value, $_SESSION['OCS']["readServer"], $arg_new_value);
        $val_new_value = mysqli_fetch_array($res_new_value);
        if ($val_new_value['max'] == "") {
            $val_new_value['max'] = 0;
        }
        $val_new_value['max'] ++;
        $sql_insert = "INSERT INTO config (NAME,TVALUE,IVALUE";
        if (isset($protectedPost['2newfield'])) {
            $sql_insert .= ",COMMENTS";
        }
        $sql_insert .= ") VALUES('%s','%s','%s'";
        if (isset($protectedPost['2newfield'])) {
            $sql_insert .= ",'%s'";
        }
        $sql_insert .= ")";
        $arg_insert = array("TAB_ACCOUNTAG_" . $val_new_value['max'], $protectedPost['newfield'], $val_new_value['max']);
        if (isset($protectedPost['2newfield'])) {
            array_push($arg_insert, $protectedPost['2newfield']);
        }
        mysql2_query_secure($sql_insert, $_SESSION['OCS']["writeServer"], $arg_insert);
        //si on ajoute un champ, il faut créer la colonne dans la table downloadwk_pack
        msg_success($l->g(1069));
        if ($protectedGet['form']) {
            reloadform_closeme($protectedGet['form']);
        }
        $protectedPost['onglet'] = 3;
    } else {
        msg_error($ERROR);
        $protectedPost['onglet'] = 3;
    }

}

if (isset($hidden) && is_numeric($hidden)) {
    $tab_hidden['MODIF_OLD'] = $hidden;
}

if ($protectedPost['onglet'] == 5) {
    echo open_form($form_name, '', 'enctype="multipart/form-data"', 'form-horizontal');
} else {
    echo open_form($form_name, '', '', 'form-horizontal');
}

show_tabs($data_on, $form_name, "onglet", true);
echo '<div class="col col-md-10" >';

$table = "accountinfo";

if ((isset($protectedPost['ACCOUNTINFO_CHOISE']) && $protectedPost['ACCOUNTINFO_CHOISE'] == 'SNMP' && $protectedPost['onglet'] == 1) || (isset($protectedPost['accountinfo']) && $protectedPost['accountinfo'] == 'SNMP' && $protectedPost['onglet'] == 2)) {
    $array_tab_account = find_all_account_tab('TAB_ACCOUNTSNMP');
    $account_field = "TAB_ACCOUNTSNMP";
} else {
    $array_tab_account = find_all_account_tab('TAB_ACCOUNTAG');
    $account_field = "TAB_ACCOUNTAG";
}

if ($protectedPost['onglet'] == 1) {
    ?>
    <div class="row">
        <div class="col col-md-6 col-md-offset-3">

            <?php
            formGroup('select', 'ACCOUNTINFO_CHOISE', $l->g(56), '', '', $protectedPost['ACCOUNTINFO_CHOISE'], '', $accountinfo_choise, $accountinfo_choise, 'onchange="document.admin_info.submit();"');
            ?>
        </div>
    </div>

    <?php
    if ($protectedPost['ACCOUNTINFO_CHOISE'] == "SNMP") {
        $account_choise = "SNMP";
    } else {
        $account_choise = "COMPUTERS";
    }

    $tab_options['CACHE'] = 'RESET';
    if (is_defined($protectedPost['del_check'])) {
        $list = $protectedPost['del_check'];
        $tab_values = explode(',', $list);
        $i = 0;
        while ($tab_values[$i]) {
            del_accountinfo($tab_values[$i]);
            $i++;
        }
    }

    if (is_defined($protectedPost['SUP_PROF'])) {
        del_accountinfo($protectedPost['SUP_PROF']);
    }
    $array_fields = array($l->g(1098) => 'NAME',
        $l->g(1063) => 'COMMENT',
        $l->g(66) => 'TYPE',
        $l->g(1061) => 'ID_TAB');

    $queryDetails = "select ID," . implode(',', $array_fields) . " from accountinfo_config where ACCOUNT_TYPE = '" . $account_choise . "'";

    if (!isset($protectedPost['SHOW'])) {
        $protectedPost['SHOW'] = 'NOSHOW';
    }
    if (!(isset($protectedPost["pcparpage"]))) {
        $protectedPost["pcparpage"] = 10;
    }

    $list_fields = $array_fields;

    $list_fields['SUP'] = 'ID';
    $list_fields['CHECK'] = 'ID';
    $list_fields['MODIF'] = 'ID';
    $list_col_cant_del = array($l->g(1063) => $l->g(1063), $l->g(66) => $l->g(66), $l->g(1061) => $l->g(1061), 'SUP' => 'SUP', 'CHECK' => 'CHECK', 'MODIF' => 'MODIF');
    $default_fields = $list_col_cant_del;
    $tab_options['REPLACE_VALUE'][$l->g(66)] = $type_accountinfo;
    $tab_options['REPLACE_VALUE'][$l->g(1061)] = $array_tab_account;
    $tab_options['LBL_POPUP']['SUP'] = 'NAME';
    $tab_options['REQUEST']['SUP'] = "select name_accountinfo AS FIRST from accountinfo_config where ACCOUNT_TYPE = '" . $account_choise . "'";
    $tab_options['FIELD']['SUP'] = 'NAME';
    $tab_options['EXIST']['SUP'] = 'NAME';
    $tab_options['REQUEST']['CHECK'] = "select name_accountinfo AS FIRST from accountinfo_config where ACCOUNT_TYPE = '" . $account_choise . "'";
    $tab_options['FIELD']['CHECK'] = 'NAME';
    $tab_options['EXIST']['CHECK'] = 'NAME';
    $nb_result = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
    //traitement par lot
    del_selection($form_name);

    //}
} elseif ($protectedPost['onglet'] == 2) {
    //NAME FIELD
    $config['JAVASCRIPT'][1] = $sql_field;
    $name_field = array("accountinfo", "newfield");
    $tab_name = array($l->g(56) . ": ", $l->g(1070) . ": ");
    if (isset($protectedPost['MODIF_OLD']) || $protectedPost['MODIF'] != '') {
        $hidden = ($protectedPost['MODIF'] != '' ? $protectedPost['MODIF'] : $protectedPost['MODIF_OLD']);
        $type_field = array(3, 3);
        $value_field = array($protectedPost['accountinfo'], $protectedPost['newfield']);
    } else {
        $type_field = array(2, 0);
        $value_field = array($accountinfo_choise, $protectedPost['newfield']);
    }

    if (isset($hidden) && is_numeric($hidden)) {
        $tab_hidden['MODIF_OLD'] = $hidden;
    }

    array_push($name_field, "newlbl");
    array_push($tab_name, $l->g(80) . ":");
    array_push($type_field, 0);
    array_push($value_field, $protectedPost['newlbl']);

    array_push($name_field, "newtype");
    array_push($tab_name, $l->g(1071) . ":");
    array_push($type_field, 2);
    array_push($value_field, $type_accountinfo);

    array_push($name_field, "account_tab");
    array_push($tab_name, $l->g(1061) . ":");
    array_push($type_field, 2);
    array_push($value_field, $array_tab_account);

    if ($protectedPost['newtype'] == 8) { //for QRCODE type
        array_push($name_field, "default_value");
        array_push($tab_name, $l->g(1099) . ":");
        array_push($type_field, 2);
        array_push($value_field, $array_qr_values);
    }

    $tab_typ_champ = show_field($name_field, $type_field, $value_field, $config);

    $tab_typ_champ[3]['COMMENT_AFTER']="<input type='image' name='addtab' src='image/plus.png'>";

    if( (isset($protectedPost['MODIF']) && $protectedPost['MODIF'] != "") || (isset($protectedPost['MODIF_OLD']) && $protectedPost['MODIF_OLD'] != "") ){
        formGroup('hidden', 'MODIF_OLD', '', '', '', $protectedPost['MODIF'], '', '', '', '');
        formGroup('hidden', 'newfield', '', '', '', $protectedPost['newfield']);
        formGroup('hidden', 'accountinfo', '', '', '', $protectedPost['accountinfo']);
        formGroup('text', 'accountinfo', $l->g(56), '', '', $protectedPost['accountinfo'], '', '', '', "disabled");
        formGroup('text', 'newfield', $l->g(1070), 30, 255, $protectedPost['newfield'], '', '', '', "disabled");

    }else{
        formGroup('select', 'accountinfo', $l->g(56), '', '', $protectedPost['ACCOUNTINFO_CHOISE'], '', $tab_typ_champ[0]['DEFAULT_VALUE'], $tab_typ_champ[0]['DEFAULT_VALUE'], "onKeyPress=\"return scanTouche(event,/[0-9a-zA-Z_-]/)\" onkeydown='convertToUpper(this)' onkeyup='convertToUpper(this)' onblur='convertToUpper(this)'");
        formGroup('text', 'newfield', $l->g(1070), 30, 255, $protectedPost['newfield'], '', '', '', "onkeypress='return scanTouche(event,/[0-9a-zA-Z_-]/)' onkeydown='convertToUpper(this)' onkeyup='convertToUpper(this)' onblur='convertToUpper(this)'");
    }
    formGroup('text', 'newlbl', $l->g(80), 30, 255, $protectedPost['newlbl']);
    formGroup('select', 'newtype', $l->g(1071), '', '', $protectedPost['newtype'], '', $tab_typ_champ[3]['DEFAULT_VALUE'], $tab_typ_champ[3]['DEFAULT_VALUE'], "onchange='document.admin_info.submit();'");
    formGroup('select', 'account_tab', $l->g(1061), '', '', $protectedPost['account_tab'], '', $tab_typ_champ[4]['DEFAULT_VALUE'], $tab_typ_champ[4]['DEFAULT_VALUE'],'', $tab_typ_champ[3]['COMMENT_AFTER']);

    if($protectedPost['newtype'] == 8){
        formGroup('select', 'default_value', $l->g(1099), '', '', $protectedPost['default_value'], '', $tab_typ_champ[5]['DEFAULT_VALUE'], $tab_typ_champ[5]['DEFAULT_VALUE'], '', '');
    }

?>

<div class="row">
    <div class="col-md-12">
        <input type="submit" name="Valid_modif" value="<?php echo $l->g(13) ?>" class="btn btn-success">
    </div>
</div>


<?php
}elseif($protectedPost['onglet'] == 3){
  $tab_options['CACHE'] = 'RESET';

  //delete few fields
  if (is_defined($protectedPost['del_check'])) {
      $list = $protectedPost['del_check'];
      $sql_delete = "DELETE FROM config WHERE name like '%s' and ivalue in (%s)";
      $arg_delete = array("TAB_ACCOUNTAG_%", $list);
      mysql2_query_secure($sql_delete, $_SESSION['OCS']["writeServer"], $arg_delete);
      if ($protectedGet['form']) {
          reloadform_closeme($protectedGet['form']);
      }
  }

  //delete on field
  if (isset($protectedPost['SUP_PROF'])) {
      delete("TAB_ACCOUNTAG_" . $protectedPost['SUP_PROF']);
  }

  $queryDetails = "select IVALUE,TVALUE from config where name like 'TAB_ACCOUNTAG\_%'";

  if (!isset($protectedPost['SHOW'])) {
      $protectedPost['SHOW'] = 'NOSHOW';
  }
  if (!(isset($protectedPost["pcparpage"]))) {
      $protectedPost["pcparpage"] = 5;
  }

  $list_fields[$l->g(224)] = 'TVALUE';
  $list_fields['SUP'] = 'IVALUE';
  $list_fields['MODIF'] = 'IVALUE';
  $list_fields['CHECK'] = 'IVALUE';
  $tab_options['LBL_POPUP']['SUP'] = 'TVALUE';
  $list_col_cant_del = $list_fields;
  $default_fields = $list_col_cant_del;
  $are_result = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
  //traitement par lot
  if ($are_result) {
      del_selection($form_name);
      if ($protectedGet['form']) {
          reloadform_closeme($protectedGet['form']);
      }
  }

}elseif($protectedPost['onglet'] == 4){

  //NAME FIELD
  $name_field = array("newfield");
  $tab_name[0] = $l->g(80);
  $type_field = array(0);
  $value_field = array($protectedPost['newfield']);

  $tab_typ_champ = show_field($name_field, $type_field, $value_field);
  $tab_typ_champ[0]['CONFIG']['SIZE'] = 20;

  modif_values($tab_name, $tab_typ_champ, $tab_hidden, array(
      'form_name' => 'NO_FORM'
  ));

} elseif ($protectedPost['onglet'] == 5) {
    // 2nd - CSV already sent
    if (isset($protectedPost['valid_csv'])) {
        // create new csv obj
        $csvObj = new CSV();
        $protectedPost['csv_filename'] = $csvObj->saveCSV($_FILES['csv_file'], $_FILES['csv_file']['name']);
        // open csv
        $handle = $csvObj->openCSV($protectedPost['csv_filename']);
        // use first line as header
        $protectedPost['csv_header'] = $csvObj->readCSVHeader();
        echo "<div class='row margin-top30'>
        <div class='col-sm-10'>";
        // if file can not be read correctly (probably due to wrong separator), close and delete it
        if ($protectedPost['csv_header'] == false ) {
            msg_error($l->g(9206));
            fclose($handle);
            $delete_csv = $csvObj->deleteCSV($protectedPost['csv_filename']);
            $protectedPost['wrong_file'] = 'wrong file';
            echo "<br><br><input type='submit' class='btn btn-success' value=".$l->g(188)."><br><br>";
        } else {
            msg_info($l->g(9208));
            msg_success($l->g(9207));
            // display form for CSV field selection
            formGroup('select', 'csv_field', $l->g(9200), '', '', $protectedPost['csv_field'], '', $protectedPost['csv_header'], $protectedPost['csv_header']);
            echo "<br><br><input type='submit' name='valid_csv_field' id='valid_csv_field' class='btn btn-success' value=".$l->g(1264)."><br><br>";
            echo "<input type='hidden' name ='csv_filename' id='csv_filename' value= ".$protectedPost['csv_filename'].">";
            // close file
            fclose($handle);
        }

    // 3rd - selection for OCS field 
    } elseif (!isset($protectedPost['column_select']) && isset($protectedPost['valid_csv_field'])) {
        // set defaultTable if necessary
        if (isset($protectedPost['column_select'])) {
            $defaultTable = $protectedPost['column_select'];
        } else {
            $defaultTable = null;
        }
    
        // association with OCS fields is achieved with 2 fields hardware>NAME or bios>SSN
        $tabs_available = array('hardware - machine name', 'bios - serial number');
        // display form for OCS field selection
        echo open_form('csv_assoc', '', '', '');
        ?>
    
            <div class="col-sm-10">
            <?php echo msg_info($l->g(9209)); ?>
                <div class="form-group">
                    <label class='control-label col-sm-2' for='table_select'><?php echo $l->g(9201) ?></label>
                    <div class="col-sm-10">
                        <select class="form-control" name="column_select">
                            <?php 
                            if ($defaultTable != null ) {
                                echo "<option value=".$defaultTable." >".$defaultTable."</option>";
                            } else {
                                foreach ($tabs_available as $tab) {
                                    echo "<option value=".$tab." >".$tab."</option>";
                                }
                            } 
                            ?>
                        <input type='hidden' name ='csv_field' id='csv_field' value= '<?php echo $protectedPost['csv_field']; ?>'>
                        <input type='hidden' name ='csv_filename' id='csv_filename' value= '<?php echo $protectedPost['csv_filename']; ?>'>
                        </select>
                    </div>
                    <div class='col-sm-10'>
    
                        <br><br><input type='submit' name='valid_ocs_field' id='valid_ocs_field' class='btn btn-success' value='<?php echo $l->g(1264) ?>.'><br><br>
                    </div>
                </div>
            </div>
                
    
        <?php echo close_form();
    
    // 4th - links between CSV fields and OCS fields
    } elseif (isset($protectedPost['valid_ocs_field']) && isset($protectedPost['csv_field'])) {
        $csvObj = new CSV();
        $handle = $csvObj->openCSV($protectedPost['csv_filename']);
        $header = $csvObj->readCSVHeader();
        // delete csv field of reconciliation from header > cant link it with any other field
        unset($header[$protectedPost['csv_field']]);
        
        // get ocs fields from accountinfo_config
        $req = "SELECT ID, NAME from accountinfo_config WHERE account_type = 'computers'";
        $ocs_fields = mysql2_query_secure($req, $_SESSION['OCS']["readServer"]);
        $ocs_fields = mysqli_fetch_all($ocs_fields, MYSQLI_ASSOC);
        array_unshift($ocs_fields, "----");

        echo '<div class="col-sm-10">';
        msg_info($l->g(9210));
        echo '  <div class="dataTables_scrollBody" style="overflow: auto; width: 100%;"> 
                    <table width="100%" class="table table-striped table-condensed table-hover cell-border dataTable no-footer" role="grid" style="margin-left: 0px; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 40%; text-align:center;"><font>CSV FIELD</font></th>
                                <th style="width: 100%; text-align:center;"><font>OCS FIELD</font></th><br><br>
                            </tr>
                        </thead>
                        <tbody>';
        foreach($header as $key => $column) {
        echo '              <tr>';
        echo '                  <td style="width: 40%; text-align:center;">'.$column.'</td>';
        echo '                  <td><select style="width: 100%;" class="form-control" type="text" name="link_'.$key.'">';
                                foreach($ocs_fields as $id => $ocs_field) {
                                echo '<option value="'.$ocs_field['ID'].'">'.$ocs_field['NAME'].'</option>';
                                }
        echo '                  </td>';
        echo '              </tr>';
        }
        echo '          </tbody>';
        echo '      </table>';
        echo '  </div>';
        echo "  <br><br><input type='submit' name='valid_links' id='valid_links' class='btn btn-success' value=".$l->g(1264)."><br><br>";
        echo "  <input type='hidden' name ='csv_filename' id='csv_filename' value=".$protectedPost['csv_filename'].">";
        echo "  <input type='hidden' name ='csv_field' id='csv_field' value=".$protectedPost['csv_field'].">
                <input type='hidden' name ='column_select' id='column_select' value=".$protectedPost['column_select'].">
            </div>";
       
        


    // 5th - results
    } elseif (isset($protectedPost['valid_links'])) {
        $errors = array();
        $csvObj = new CSV();
        $handle = $csvObj->openCSV($protectedPost['csv_filename']);

        // get array of links
        foreach ($protectedPost as $key => $value) {
            if (strpos($key, 'link_') === 0) {
                $links[$key] = $value;
            }
        }
        // remove empty links + format ID for future query
        foreach ($links as $key => $link) {
             if ($link == '-') {
                unset($links[$key]);
             } else {
                 $links[$key] = "fields_".$link;
             }
        }

        function logCSVErrors($lvl) {
            switch ($lvl) {
                case '1':
                    return $error = 9202;
                case '2':
                    return $error = 9203;
                case '3':
                    return $error = 9204;
            }
        }

        // req to retrieve hardware id
        $sql_h_id = "SELECT %s FROM %s WHERE %s = '%s'";
        $i = 0;
        while ($line = $csvObj->readCSVLine()) {
            $i++;
            // first line means header
            if ($i == 1) {
                continue;
            } else {
                // if csv field chosen by user is empty (reconciliation cannot be achieved on an empty field)
                if ($line[$protectedPost['csv_field']] != '') {
                    if ($protectedPost['column_select'] == 'hardware') {
                        $table_select = 'hardware';
                        $column_select = 'NAME';
                        $id_column = 'ID';
                    } else {
                        $table_select = 'bios';
                        $column_select = 'SSN';
                        $id_column = 'hardware_id';
                    }
                    // csv field index = value index
                    $args = array($id_column, $table_select, $column_select, $line[$protectedPost['csv_field']]);
                    $h_id = mysql2_query_secure($sql_h_id, $_SESSION['OCS']["readServer"], $args);
                    // if device exists
                    if ($h_id = mysqli_fetch_assoc($h_id)) {
                        // update fields 
                        foreach ($links as $index => $field) {
                            $index = str_replace('link_', '', $index);
                            $req_update = "UPDATE accountinfo SET %s = '%s' WHERE hardware_id = %s";
                            $args_update = array($field, trim($line[$index]), $h_id[$id_column]);
                            if (mysql2_query_secure($req_update, $_SESSION['OCS']["readServer"], $args_update)) {
                                $success = 1;
                            } else {
                                $lvl = '3';
                                $errors[$i] = logCSVErrors($lvl);
                            }
                        }
                    } else {
                        $lvl = '2';
                        $errors[$i] = logCSVErrors($lvl);
                    }
                } else {
                    $lvl = '1';
                    $errors[$i] = logCSVErrors($lvl);
                }
            }
        }
        // close file once data has been imported
        fclose($handle);
        $delete_csv = $csvObj->deleteCSV($protectedPost['csv_filename']);
        echo "<br><input type='submit' name='import_new' id='import_new' class='btn btn-success' value=". $l->g(188)."><br><br>";

        if ($success != '') {
            msg_info($l->g(9205));
        }
        foreach ($errors as $key => $error) {
            $error = "CSV line $key : ".$l->g($error);
            msg_error($error);
        }
        
    // 1st - import csv
    } else {
        // Open new form for csv file
        echo open_form('admininfo_csv', '', 'enctype="multipart/form-data"', 'form-horizontal');
        echo "<div class='row margin-top30'>
                <div class='col-sm-10'>";
    
        echo "<br><br>";
        formGroup('file', 'csv_file', 'Import CSV file :', '', '', $protectedPost['csv_file'], '', '', '', "accept='.csv'");
        echo "<input type='submit' name='valid_csv' id='valid_csv' class='btn btn-success' value='".$l->g(1479)."'><br><br>";
        echo "</div>";
    }
}
echo "</div>";

echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
}
?>
