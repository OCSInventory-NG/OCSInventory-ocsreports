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

echo open_form($form_name, '', '', 'form-horizontal');
show_tabs($data_on,$form_name,"onglet",true);
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
}

echo "</div>";
echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
}
?>
