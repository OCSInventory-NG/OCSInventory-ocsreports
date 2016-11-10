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
    //ouverture du formulaire
    echo open_form($form_name, '', '', 'form-horizontal');
    if ((!isset($protectedPost['FILE_SERV']) && $protectedPost['choix_activ'] == 'MAN') || (!isset($protectedPost['FILE_SERV_REDISTRIB']) && $protectedPost['choix_activ'] == 'AUTO') || !isset($protectedPost['HTTPS_SERV'])) {
        $default = $_SERVER["SERVER_ADDR"] . "/download";
        $values = look_config_default_values(array('DOWNLOAD_URI_INFO', 'DOWNLOAD_URI_FRAG'));
        $protectedPost['FILE_SERV'] = $values['tvalue']['DOWNLOAD_URI_FRAG'];
        $protectedPost['HTTPS_SERV'] = $values['tvalue']['DOWNLOAD_URI_INFO'];
        if ($protectedPost['FILE_SERV'] == "") {
            $protectedPost['FILE_SERV'] = $default;
        }
        if ($protectedPost['HTTPS_SERV'] == "") {
            $protectedPost['HTTPS_SERV'] = $default;
        }
    }
    //use redistribution servers?
    if ($_SESSION['OCS']["use_redistribution"] == 1) {
        $reqGroupsServers = "SELECT DISTINCT name,id FROM hardware WHERE deviceid='_DOWNLOADGROUP_'";
        $resGroupsServers = mysql2_query_secure($reqGroupsServers, $_SESSION['OCS']["readServer"]);
        while ($valGroupsServers = mysqli_fetch_array($resGroupsServers)) {
            $groupListServers[$valGroupsServers["id"]] = $valGroupsServers["name"];
        }
    }

    if (is_defined($protectedPost['Valid_modif'])) {
        $error = "";

        $opensslOk = function_exists("openssl_open");

        if ($opensslOk) {
            $httpsOk = @fopen("https://" . $protectedPost["HTTPS_SERV"] . "/" . $protectedGet["active"] . "/info", "r");
        } else {
            $error = "WARNING: OpenSSL for PHP is not properly installed. Your https server validity was not checked !<br>";
        }

        if (!$httpsOk) {
            $error .= $l->g(466) . " https://" . $protectedPost["HTTPS_SERV"] . "/" . $protectedGet["active"] . "/<br>";
        } else {
            fclose($httpsOk);
        }

        if ($protectedPost['choix_activ'] == "MAN") {
            $reqFrags = "SELECT fragments FROM download_available WHERE fileid='" . $protectedGet["active"] . "'";
            $resFrags = mysqli_query($_SESSION['OCS']["readServer"], $reqFrags);
            $valFrags = mysqli_fetch_array($resFrags);
            $fragAvail = ($valFrags["fragments"] > 0);
            if ($fragAvail) {
                $fragOk = @fopen("http://" . $protectedPost["FILE_SERV"] . "/" . $protectedGet["active"] . "/" . $protectedGet["active"] . "-1", "r");
            } else {
                $fragOk = true;
            }
        } else {
            $fragOk = true;
        }

        if (!$fragOk) {
            $error .= $l->g(467) . " http://" . $protectedPost['FILE_SERV'] . "/" . $protectedGet["active"] . "/<br>";
        } elseif ($fragAvail) {
            fclose($fragOk);
        }

        if (!$fragOk || !$httpsOk) {
            $error .= "<br>" . $l->g(468) . "<br><br>";
            $error .= "<input type='submit' name='YES' value='" . $l->g(455) . "'>&nbsp&nbsp&nbsp<input type='submit' name='NO' value='" . $l->g(454) . "'>";
        }
        if ($error != '') {
            msg_warning($error);
        }
    }

    if ($error == "" && isset($protectedPost['Valid_modif']) || isset($protectedPost['YES'])) {
        if ($protectedPost['choix_activ'] == "MAN") {
            activ_pack($protectedGet["active"], $protectedPost["HTTPS_SERV"], $protectedPost['FILE_SERV']);
        }

        if ($protectedPost['choix_activ'] == "AUTO") {
            activ_pack_server($protectedGet["active"], $protectedPost["HTTPS_SERV"], $protectedPost['FILE_SERV_REDISTRIB']);
        }
        echo "<script> alert('" . $l->g(469) . "');window.opener.document.packlist.submit(); self.close();</script>";
    }

    if ($_SESSION['OCS']["use_redistribution"] == 1) {
        $list_choise['MAN'] = $l->g(650);
        $list_choise['AUTO'] = $l->g(649);
        ?>
        <div class="row">

            <div class="col col-md-4 col-xs-offset-0 col-md-offset-4">

                <div class="form-group">
                    <label class="control-label col-sm-4" for="choix_activ>"><?php echo $l->g(514); ?> :</label>
                    <div class="col-sm-8">
                        <?php echo show_modif($list_choise, 'choix_activ', 2, $form_name); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {

        $protectedPost['choix_activ'] = "MAN";
        echo "<input type='hidden' name='choix_activ' value='MAN'>";
    }
    echo "<br>";
    if (is_defined($protectedPost['choix_activ'])) {
        if ($protectedPost['choix_activ'] == "MAN") {
            $tab_name = array($l->g(471), $l->g(470));
            $name_field = array("FILE_SERV", "HTTPS_SERV");
            $type_field = array(0, 0);
            $value_field = array($protectedPost['FILE_SERV'], $protectedPost['HTTPS_SERV']);
        } else {
            if (count($groupListServers) == 0) {
                msg_error($l->g(660));
            } else {
                $tab_name = array($l->g(651), $l->g(470));
                $name_field = array("FILE_SERV_REDISTRIB", "HTTPS_SERV");
                $type_field = array(2, 0);
                $value_field = array($groupListServers, $protectedPost['HTTPS_SERV']);
            }
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
            modif_values($tab_name, $tab_typ_champ, $tab_hidden, array(
                'title' => $l->g(465) . ' => ' . $info_id['NAME'] . " (" . $protectedGet["active"] . ")"
            ));
        }
    }

    //var_dump($tab_typ_champ);
    //fermeture du formulaire.
    echo close_form();
} else {
    msg_error($info_id['ERROR']);
}
?>