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
/*
 * Add value in config table
 */
require_once('require/function_config_generale.php');
$form_name = 'admin_values_config' . $protectedGet['tag'];
$table_name = $form_name;
$data_on[1] = $l->g(1059);
$data_on[2] = $l->g(1060);
if (isset($protectedGet['new_field']) && is_numeric($protectedGet['new_field'])) {
    $lbl_new_field = $l->g($protectedGet['new_field']);
} else {
    $lbl_new_field = $l->g(80);
}
//if no tab selected
if (!is_defined($protectedPost['onglet'])) {
    $protectedPost['onglet'] = 1;
}

$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;

//faire la vérif sur le tag en get
//for update name
if (isset($protectedPost['MODIF']) && is_numeric($protectedPost['MODIF']) && !isset($protectedPost['Valid_modif'])) {
    $protectedPost['onglet'] = 2;
    $val_info = look_config_default_values(array($protectedGet['tag'] . "_" . $protectedPost['MODIF']));
    $protectedPost['newfield'] = $val_info['tvalue'][$protectedGet['tag'] . "_" . $protectedPost['MODIF']];
    if (isset($protectedGet['nb_field']) && is_numeric($protectedGet['nb_field'])) {
        $protectedPost['2newfield'] = $val_info['comments'][$protectedGet['tag'] . "_" . $protectedPost['MODIF']];
    }
    $hidden = $protectedPost['MODIF'];
}

echo open_form($form_name, '', '', 'form-horizontal');
if($protectedPost['onglet'] == '1'){
    echo '<div class="col-md-12">';
}else{
    echo '<div class="col-md-11">';
}
echo '<p>';
onglet($data_on, $form_name, "onglet", 2);
echo '</p>';

if ($protectedPost['onglet'] == 1) {
    $tab_options['CACHE'] = 'RESET';

    //delete few fields
    if (is_defined($protectedPost['del_check'])) {
        $list = $protectedPost['del_check'];
        $sql_delete = "DELETE FROM config WHERE name like '%s' and ivalue in (%s)";
        $arg_delete = array($protectedGet['tag'] . "_%", $list);
        mysql2_query_secure($sql_delete, $_SESSION['OCS']["writeServer"], $arg_delete);
        if ($protectedGet['form']) {
            reloadform_closeme($protectedGet['form']);
        }
    }

    //delete on field
    if (isset($protectedPost['SUP_PROF'])) {
        delete($protectedGet['tag'] . "_" . $protectedPost['SUP_PROF']);
    }

    $queryDetails = "select IVALUE,TVALUE from config where name like '" . $protectedGet['tag'] . "\_%'";

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
} elseif ($protectedPost['onglet'] == 2) {
    if (isset($protectedPost['MODIF_OLD']) && is_numeric($protectedPost['MODIF_OLD']) && $protectedPost['Valid_modif'] != "") {
        //UPDATE VALUE
        update_config($protectedGet['tag'] . "_" . $protectedPost['MODIF_OLD'], 'TVALUE', $protectedPost['newfield']);
        if (isset($protectedPost['2newfield'])) {
            update_config($protectedGet['tag'] . "_" . $protectedPost['MODIF_OLD'], 'COMMENTS', $protectedPost['2newfield'], false);
        }
        $hidden = $protectedPost['MODIF_OLD'];
    } elseif ($protectedPost['Valid_modif'] != "") {
        //ADD NEW VALUE
        //vérification que le nom du champ n'existe pas pour les nouveaux champs
        if (trim($protectedPost['newfield']) != '') {
            $sql_verif = "SELECT count(*) c FROM config WHERE TVALUE = '%s' and NAME like '%s'";
            //echo $sql_verif;
            $arg_verif = array($protectedPost['newfield'], $protectedGet['tag'] . "_%");
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
            $arg_new_value = array($protectedGet['tag'] . "_%");
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
            $arg_insert = array($protectedGet['tag'] . "_" . $val_new_value['max'], $protectedPost['newfield'], $val_new_value['max']);
            if (isset($protectedPost['2newfield'])) {
                array_push($arg_insert, $protectedPost['2newfield']);
            }
            mysql2_query_secure($sql_insert, $_SESSION['OCS']["writeServer"], $arg_insert);
            //si on ajoute un champ, il faut créer la colonne dans la table downloadwk_pack
            msg_success($l->g(1069));
            if ($protectedGet['form']) {
                reloadform_closeme($protectedGet['form']);
            }
        } else {
            msg_error($ERROR);
        }
    }

    if (isset($hidden) && is_numeric($hidden)) {
        $tab_hidden['MODIF_OLD'] = $hidden;
    }
    //NAME FIELD
    $name_field = array("newfield");
    $tab_name[0] = $lbl_new_field;
    $type_field = array(0);
    $value_field = array($protectedPost['newfield']);
    if (isset($protectedGet['nb_field']) && is_numeric($protectedGet['nb_field'])) {
        array_push($name_field, '2newfield');
        array_push($tab_name, $l->g($protectedGet['nb_field']));
        array_push($type_field, 0);
        array_push($value_field, $protectedPost['2newfield']);
    }

    $tab_typ_champ = show_field($name_field, $type_field, $value_field);
    $tab_typ_champ[0]['CONFIG']['SIZE'] = 20;
    if (isset($protectedGet['nb_field']) && is_numeric($protectedGet['nb_field'])) {
        $tab_typ_champ[1]['CONFIG']['SIZE'] = 20;
    }

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