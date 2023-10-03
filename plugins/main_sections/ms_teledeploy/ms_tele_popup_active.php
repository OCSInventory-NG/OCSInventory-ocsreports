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
require_once('require/function_telediff.php');
$info_id = found_info_pack($protectedGet["active"]);
if (!isset($info_id['ERROR'])) {

    $form_name = "form_active";
    $action_redirect = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_tele_activate']."&active=".$protectedGet['active'];

    //ouverture du formulaire
    echo open_form($form_name, $action_redirect, '', 'form-horizontal');
    if ((!isset($protectedPost['FILE_SERV']) && isset($protectedPost['choix_activ']) && $protectedPost['choix_activ'] == 'MAN') || !isset($protectedPost['HTTPS_SERV'])) {
        $default = $_SERVER["SERVER_ADDR"] . "/download";
        $values = look_config_default_values(array('DOWNLOAD_URI_INFO', 'DOWNLOAD_URI_FRAG'));
        $protectedPost['FILE_SERV'] = $values['tvalue']['DOWNLOAD_URI_FRAG'] ?? '';
        $protectedPost['HTTPS_SERV'] = $values['tvalue']['DOWNLOAD_URI_INFO'] ?? '';
        if ($protectedPost['FILE_SERV'] == "") {
            $protectedPost['FILE_SERV'] = $default;
        }
        if ($protectedPost['HTTPS_SERV'] == "") {
            $protectedPost['HTTPS_SERV'] = $default;
        }
    }



    $protectedPost['choix_activ'] = "MAN";
    echo "<input type='hidden' name='choix_activ' value='MAN'>";
    echo "<br>";
    if (is_defined($protectedPost['choix_activ'])) {
        if ($protectedPost['choix_activ'] == "MAN") {
            $tab_name = array($l->g(471), $l->g(470));
            $name_field = array("FILE_SERV", "HTTPS_SERV");
            $type_field = array(0, 0);
            $value_field = array($protectedPost['FILE_SERV'], $protectedPost['HTTPS_SERV']);
        }

        if (isset($name_field)) {
            $tab_typ_champ = show_field($name_field, $type_field, $value_field);
            foreach ($tab_typ_champ as $id => $values) {
                $tab_typ_champ[$id]['CONFIG']['SIZE'] = 30;
                if ($tab_typ_champ[$id]['INPUT_TYPE'] == 0) {
                    $tab_typ_champ[$id]['COMMENT_AFTER'] = '/' . $protectedGet["active"];
                    if ($id == 0) {
                        $tab_typ_champ[$id]['COMMENT_BEFORE'] = 'http://';
                    } else {
                        $tab_typ_champ[$id]['COMMENT_BEFORE'] = 'https://';
                    }
                }
            }
            modif_values($tab_name, $tab_typ_champ, $tab_hidden ?? '', array(
                'title' => $l->g(465) . ' => ' . $info_id['NAME'] . " (" . $protectedGet["active"] . ")"
            ));
        }
    }

    //fermeture du formulaire.
    echo close_form();
} else {
    msg_error($info_id['ERROR']);
}
?>