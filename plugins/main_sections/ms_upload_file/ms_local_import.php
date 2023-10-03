<?php

/*
 * Copyright 2005-2021 OCSInventory-NG/OCSInventory-ocsreports contributors.
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
require_once('require/function_files.php');
require_once('require/function_computers.php');
require_once('require/admininfo/Admininfo.php');

$Admininfo = new Admininfo();

printEnTete($l->g(1258));
$form_name="insert_computers";
echo open_form($form_name, '', '', 'form-horizontal');
echo "<br><br><div class='col-md-8 col-xs-offset-0 col-md-offset-2'>";
//list fields for form
$form_fields_typeinput = array(
    'COMPUTER_NAME_GENERIC' => $l->g(35),
    'SERIAL_GENERIC' => $l->g(36),
    'ADDR_MAC_GENERIC' => $l->g(95)
);


if(isset($protectedPost['Valid_modif'])) {
    $error = '';
    if (!is_numeric($protectedPost['NB_COMPUTERS'])) {
        $error .= $l->g(28).',';
    }

    foreach ($form_fields_typeinput as $key=>$value){
        if (trim($protectedPost[$key]) == '') {
            $error .= $value.',';
        } 
    }

    if ($error == "") {
        $check_trait = array();
        foreach ($protectedPost as $key => $value) {
            if ($value != '') {
                if (substr($key,0,7) == 'fields_' or $key == 'TAG') {
                    $temp_field = explode('_', $key);
                    
                    //checkbox cas
                    if (isset($temp_field[2])) {
                        $check_trait[$temp_field[0].'_'.$temp_field[1]] .= $temp_field[2]."&&&";
                    } else {
                        $fields[] = $key;
                        $values_fields[] = $value;
                    }
                }
            }
        }
        //cas of checkbox
        foreach ($check_trait as $key => $value) {
            $fields[] = $key;
            $values_fields[] = $value;
        }

        $i = 0;
        while ($i < $protectedPost['NB_COMPUTERS']) {
            $id_computer = insert_manual_computer($protectedPost,$protectedPost['NB_COMPUTERS']);
            if (!isset($fields)) {
                $fields[] = 'TAG';
                $values_fields[] = '';
            }
            $Admininfo->insertinfo_computer($id_computer,$fields,$values_fields);
            $i++;
        }
        msg_success($l->g(881));
    } else {
        msg_error($l->g(684)."<br>".$error);
    }    
}
$i = 0;
$info_form['FIELDS']['name_field'][$i] = 'NB_COMPUTERS';
$info_form['FIELDS']['type_field'][$i] = 0;
$info_form['FIELDS']['value_field'][$i] = (!empty($protectedPost['NB_COMPUTERS']) ? $protectedPost['NB_COMPUTERS']:'1');
$info_form['FIELDS']['tab_name'][$i] = $l->g(28);
$config[$i]['CONFIG']['SIZE'] = 4;
$config[$i]['CONFIG']['MAXLENGTH'] = 4;
$other_data['COMMENT_AFTER'][$i] = '';
$config[$i]['CONFIG']['JAVASCRIPT'] = $chiffres;
foreach ($form_fields_typeinput as $key => $value) {
    $i++;
    $info_form['FIELDS']['name_field'][$i] = $key;
    $info_form['FIELDS']['type_field'][$i] = 0;
    if ($key == 'ADDR_MAC_GENERIC') {
        $info_form['FIELDS']['value_field'][$i] = (isset($protectedPost[$key]) ? $protectedPost[$key]:RandomMAC());
    } else {
        $info_form['FIELDS']['value_field'][$i] = (isset($protectedPost[$key]) ? $protectedPost[$key]:rand());
    }
    $info_form['FIELDS']['tab_name'][$i] = $value."*";
    $config[$i]['CONFIG']['SIZE'] = 30;
    $other_data['COMMENT_AFTER'][$i] = '_M';
}
$accountinfo_form = $Admininfo->show_accountinfo('','COMPUTERS','5,11,14');
//merge data
$info_form['FIELDS']['name_field'] = array_merge($info_form['FIELDS']['name_field'], $accountinfo_form['FIELDS']['name_field']);
$info_form['FIELDS']['type_field'] = array_merge($info_form['FIELDS']['type_field'], $accountinfo_form['FIELDS']['type_field']);
$info_form['FIELDS']['value_field'] = array_merge($info_form['FIELDS']['value_field'], $accountinfo_form['FIELDS']['value_field']);
$info_form['FIELDS']['tab_name'] = array_merge($info_form['FIELDS']['tab_name'], $accountinfo_form['FIELDS']['tab_name']);
$config = array_merge($config, $accountinfo_form['CONFIG']);
$other_data['COMMENT_AFTER'] = array_merge($other_data['COMMENT_AFTER'], $accountinfo_form['COMMENT_AFTER']);
$tab_typ_champ = show_field($info_form['FIELDS']['name_field'], $info_form['FIELDS']['type_field'], $info_form['FIELDS']['value_field']);
foreach($config as $key=>$value) {
    $tab_typ_champ[$key]['CONFIG'] = $value['CONFIG'];
    $tab_typ_champ[$key]['COMMENT_AFTER'] = $other_data['COMMENT_AFTER'][$key] ?? null;
}

if(isset($tab_typ_champ)) {
    modif_values($info_form['FIELDS']['tab_name'], $tab_typ_champ,$tab_hidden ?? '', array(
        'show_frame' => false
    ));
}
echo "</div>";
echo close_form();
