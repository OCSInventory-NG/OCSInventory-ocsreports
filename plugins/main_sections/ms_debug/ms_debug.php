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
//$header_html = 'NO';
$form_name = 'debug';


if (isset($protectedPost["MODE"]) && $protectedPost["MODE"] == 5 && !isset($_SESSION['OCS']['TRUE_USER'])){
    msg_info($_SESSION['OCS']['loggeduser'] . " " . $l->g(889) . " " . $protectedPost["FUSER"]);
}

//liste des modes de fonctionnement
$list_mode[1] = $l->g(1010);
$list_mode[2] = $l->g(1011);
$list_mode[3] = $l->g(1012);
$list_mode[4] = $l->g(1013);
if (isset($_SESSION['OCS']['TRUE_USER'])) {
    $list_mode[5] = 'NOFUSER';
} else {
    $list_mode[5] = 'FUSER';
}

if ($_SESSION['OCS']["usecache"] == 1) {
    $list_mode[6] = 'NOCACHE';
} else {
    $list_mode[6] = 'CACHE';
}

$tab_typ_champ[0]['DEFAULT_VALUE'] = $list_mode;
$tab_typ_champ[0]['INPUT_NAME'] = "MODE";
$tab_typ_champ[0]['INPUT_TYPE'] = 2;
$tab_name[0] = $l->g(1014) . ":";
$tab_typ_champ[0]['RELOAD'] = "CHANGE";
$tab_typ_champ[0]['CONFIG']['JAVASCRIPT'] = "onChange='fuser_change(this.value)'";
$tab_field_name[0] = "form-group form-group-debug";

//VALUE FOR FUSER INPUT
$tab_typ_champ[1]['DEFAULT_VALUE'] = $protectedPost['FUSER'] ?? '';
$tab_typ_champ[1]['INPUT_NAME'] = "FUSER";
$tab_typ_champ[1]['INPUT_TYPE'] = 0;
$tab_name[1] = $l->g(926) . " ";
$tab_field_name[1] = "form-group form-group-hidden hidden";

modif_values($tab_name, $tab_typ_champ, '', array(
    'title' => $l->g(1015)
), $tab_field_name);


if (isset($protectedPost['Reset_modif'])) {
    reloadform_closeme('', true);
}

//passage en mode
if (isset($protectedPost['Valid_modif']) && $protectedPost["MODE"] != "") {
    AddLog("MODE", $list_mode[$protectedPost["MODE"]]);
    if ($protectedPost["MODE"] == 1) {
        unset($_SESSION['OCS']['DEBUG'], $_SESSION['OCS']['MODE_LANGUAGE'], $_SESSION['OCS']["usecache"]);
    } elseif ($protectedPost["MODE"] == 2) {
        unset($_SESSION['OCS']['MODE_LANGUAGE']);
        $_SESSION['OCS']['DEBUG'] = "ON";
    } elseif ($protectedPost["MODE"] == 3) {
        unset($_SESSION['OCS']['DEBUG']);
        $_SESSION['OCS']['MODE_LANGUAGE'] = "ON";
    } elseif ($protectedPost["MODE"] == 4) {
        $_SESSION['OCS']['MODE_LANGUAGE'] = "ON";
        $_SESSION['OCS']['DEBUG'] = "ON";
    } elseif ($protectedPost["MODE"] == 5) {
        if (!isset($_SESSION['OCS']['TRUE_USER'])) {
            $true_user = $_SESSION['OCS']['loggeduser'];
            $list_page_profil = $_SESSION['OCS']['profile']->getPages();
            $restriction = $_SESSION['OCS']['profile']->getRestriction('GUI');
            $loggeduser = $protectedPost["FUSER"];
            unset($_SESSION['OCS']);
            $_SESSION['OCS']['TRUE_USER'] = $true_user;
            $_SESSION['OCS']['TRUE_PAGES'] = $list_page_profil;
            $_SESSION['OCS']['TRUE_RESTRICTION'] = $restriction;
            $_SESSION['OCS']['loggeduser'] = $loggeduser;
        } else {
            $loggeduser = $_SESSION['OCS']['TRUE_USER'];
            $restriction = $_SESSION['OCS']['TRUE_RESTRICTION'];
            unset($_SESSION['OCS']);
            $_SESSION['OCS']['loggeduser'] = $loggeduser;
            $_SESSION['OCS']['profile']->setRestriction('GUI', $restriction);
        }
    } elseif ($protectedPost["MODE"] == 6) {
        if (isset($_SESSION['OCS']["usecache"])
                and $_SESSION['OCS']["usecache"] == 1) {
            $_SESSION['OCS']["usecache"] = 0;
        } else {
            $_SESSION['OCS']["usecache"] = 1;
        }
    }


    reloadform_closeme('log_out', true);
}
?>