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

require_once('require/ipdiscover/Ipdiscover.php');
require_once('require/function_files.php');

$ipdiscover = new Ipdiscover();

$form_name = 'admin_ipdiscover';
$table_name = 'admin_ipdiscover';
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;

echo open_form($form_name, '', '', 'form-horizontal');

if (isset($protectedGet['value']) && $protectedGet['value'] != ''){
	if (!in_array($protectedGet['value'],$_SESSION['OCS']["subnet_ipdiscover"])){
		msg_error($l->g(837));
		require_once(FOOTER_HTML);
		die();
	}
	$protectedPost['onglet'] = 'ADMIN_RSX';
	$protectedPost['MODIF']=$protectedGet['value'];
	$left_menu_displayed=false;
    echo '<div class="col col-md-12">';
} else {
    $data_on['ADMIN_RSX']=$l->g(1140);
    $data_on['ADMIN_TYPE']=$l->g(836);
    
    if ($_SESSION['OCS']['profile']->getConfigValue('MANAGE_SMTP_COMMUNITIES') == 'YES') {
        $data_on['ADMIN_SMTP']=$l->g(1205);
    }
    
    if ($protectedPost['onglet'] != $protectedPost['old_onglet']) {
        unset($protectedPost['MODIF']);
    }
    
    show_tabs($data_on,$form_name,"onglet",true);
    $left_menu_displayed=true;
    
    echo '<div class="col col-md-10">';
}

/************************************* SUBNET *************************************/
if ($protectedPost['onglet'] == 'ADMIN_RSX') {
    $method = $ipdiscover->verif_base_methode('OCS');
    $url_show_ipdiscover = 'index.php?function=show_ipdiscover';

    if (!$method) {
        if (is_defined($protectedPost['SUP_PROF'])) {
            $ipdiscover->delete_subnet($protectedPost['SUP_PROF']);
            $tab_options['CACHE'] = 'RESET';
        }
        if (isset($protectedPost['Valid_modif'])) {
            $result = $ipdiscover->add_subnet($protectedPost['ADD_IP'], $protectedPost['RSX_NAME'], $protectedPost['ID_NAME'], $protectedPost['ADD_SX_RSX'], $protectedPost['ADD_TAG']);
            if ($result) {
                msg_error($result);
            } else {
                if (isset($protectedPost['MODIF'])) {
                    msg_success($l->g(1121));
                } else {
                    msg_success($l->g(1141));
                }
                //erase ipdiscover cache
                unset($_SESSION['OCS']['DATA_CACHE'][$table_name], $_SESSION['OCS']["ipdiscover"], $protectedPost['ADD_SUB'], $protectedPost['MODIF']);
                require_once(BACKEND . 'ipdiscover/ipdiscover.php');
                if (is_defined($protectedGet['value']))
                    change_window($url_show_ipdiscover);
            }
            $tab_options['CACHE'] = 'RESET';
        }

        if (isset($protectedPost['Reset_modif'])) {
            unset($protectedPost['ADD_SUB'], $protectedPost['MODIF']);
            if (is_defined($protectedGet['value']))
                change_window($url_show_ipdiscover);
        }

        if (isset($protectedPost['ADD_SUB'])) {
            echo "<input type='hidden' name='ADD_SUB' id='ADD_SUB' value='" . $protectedPost['ADD_SUB'] . "'";
        }
        if ($protectedPost['MODIF'] != '') {
            echo "<input type='hidden' name='MODIF' id='MODIF' value='" . $protectedPost['MODIF'] . "'";
        }

        if (isset($protectedPost['ADD_SUB']) || $protectedPost['MODIF']) {
            if ($protectedPost['MODIF']) {
                $title = $l->g(931);

                $result = $ipdiscover->find_info_subnet($protectedPost['MODIF']);
                if (!isset($protectedPost['RSX_NAME'])) {
                    $protectedPost['RSX_NAME'] = $result->NAME;
                }
                if (!isset($protectedPost['ID_NAME'])) {
                    $protectedPost['ID_NAME'] = $result->ID;
                }
                if (!isset($protectedPost['ADD_TAG'])) {
                    $protectedPost['ADD_TAG'] = $result->TAG;
                }
                if (!isset($protectedPost['ADD_IP'])) {
                    $protectedPost['ADD_IP'] = $result->NETID;
                }
                if (!isset($protectedPost['ADD_SX_RSX'])) {
                    $protectedPost['ADD_SX_RSX'] = $result->MASK;
                }
                if (is_defined($protectedGet['value'])) {
                    $explode = explode(";", $protectedGet['value']);
                    $protectedPost['ADD_IP'] = $explode[0];
                    $protectedPost['ADD_TAG'] = $explode[1];
                }
            } else {
                $title = $l->g(303);
            }
            $list_id_subnet = look_config_default_values('ID_IPDISCOVER_%', 'LIKE');

            if (isset($list_id_subnet)) {
                foreach ($list_id_subnet['tvalue'] as $key => $value) {
                    $list_subnet[$value] = $value;
                }
            } else {
                $list_subnet = array();
            }

            $list_subnet = array(0 => "") + $list_subnet;

            $list_tag = $ipdiscover->get_tag();

            $default_values = array(
                'RSX_NAME' => $protectedPost['RSX_NAME'],
                'ID_NAME' => $list_subnet,
                'ADD_TAG' => $list_tag,
                'ADD_IP' => $protectedPost['ADD_IP'],
                'ADD_SX_RSX' => $protectedPost['ADD_SX_RSX']
            );

            $ipdiscover->form_add_subnet($title, $default_values, $form_name);
        } else {
            $sql = "SELECT NETID, NAME, ID, MASK, TAG, CONCAT(NETID,IFNULL(TAG, '')) as supsub FROM subnet";

            $list_fields = array(
                'TAG' => 'TAG',
                'NETID' => 'NETID',
                $l->g(49) => 'NAME',
                'GROUP' => 'ID',
                'MASK' => 'MASK',
                'MODIF' => 'supsub',
                'SUP' => 'supsub'
            );

            $default_fields = $list_fields;
            $list_col_cant_del = $list_fields;

            $result_exist = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);

            echo "<input type='submit' value='".$l->g(116)."' class='btn' name='ADD_SUB'>";
        }
    } else {
        msg_warning($method);
    }
}

/************************************* TYPES *************************************/
if ($protectedPost['onglet'] == 'ADMIN_TYPE') {
    if (isset($protectedPost['Reset_modif'])) {
        unset($protectedPost['MODIF']);
    }

    if (is_defined($protectedPost['SUP_PROF'])) {
        $ipdiscover->delete_type($protectedPost['SUP_PROF']);
        $tab_options['CACHE'] = 'RESET';
    }

    if (isset($protectedPost['Valid_modif'])) {
        $result = $ipdiscover->add_type($protectedPost['TYPE_NAME'], $protectedPost['MODIF']);
        if ($result) {
            msg_error($result);
            $protectedPost['ADD_TYPE'] = "VALID";
        } else {
            $protectedPost = '';
            $tab_options['CACHE'] = 'RESET';
            $msg_ok = $l->g(1121);
        }
    }

    if ($protectedPost['MODIF'] != '') {
        echo "<input type='hidden' name='MODIF' id='MODIF' value='" . $protectedPost['MODIF'] . "'";
    }
    if (isset($protectedPost['ADD_TYPE']) || $protectedPost['MODIF']) {
        if ($protectedPost['MODIF']) {
            $info = $ipdiscover->find_info_type('', $protectedPost['MODIF']);
            $protectedPost['TYPE_NAME'] = $info->NAME;
        }
        $tab_typ_champ[0]['DEFAULT_VALUE'] = $protectedPost['TYPE_NAME'];
        $tab_typ_champ[0]['INPUT_NAME'] = "TYPE_NAME";
        $tab_typ_champ[0]['CONFIG']['SIZE'] = 60;
        $tab_typ_champ[0]['CONFIG']['MAXLENGTH'] = 255;
        $tab_typ_champ[0]['INPUT_TYPE'] = 0;
        $tab_name[0] = $l->g(938) . ": ";
        $tab_hidden['pcparpage'] = $protectedPost["pcparpage"];
        modif_values($tab_name, $tab_typ_champ, $tab_hidden, array(
            'title' => $title,
            'show_frame' => false
        ));
    } else {
        if (isset($msg_ok)) {
            msg_success($msg_ok);
        }
        $sql = "select ID,NAME from devicetype";
        $list_fields = array('ID' => 'ID',
            $l->g(49) => 'NAME',
            'MODIF' => 'ID',
            'SUP' => 'ID');
        $tab_options['LBL_POPUP']['SUP'] = 'NAME';
        $tab_options['LBL']['SUP'] = $l->g(122);
        $default_fields = $list_fields;
        $list_col_cant_del = $list_fields;
        $result_exist = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);

        echo "<input type='submit' class='btn' value='" . $l->g(116) . "' name='ADD_TYPE'>";
    }
}

/************************************* COMMUNITIES *************************************/
if ($protectedPost['onglet'] == 'ADMIN_SMTP' && $_SESSION['OCS']['profile']->getConfigValue('MANAGE_SMTP_COMMUNITIES') == 'YES') {
    if (isset($protectedPost['Valid_modif'])) {
        $msg_result = $ipdiscover->add_community($protectedPost['MODIF'], $protectedPost['NAME'], $protectedPost['VERSION'], $protectedPost['USERNAME'], $protectedPost['AUTHKEY'], $protectedPost['AUTHPASSWD']);
        if (isset($msg_result['SUCCESS'])) {
            unset($protectedPost['MODIF'], $protectedPost['ADD_COMM']);
            $msg_ok = $msg_result['SUCCESS'];
            $tab_options['CACHE'] = 'RESET';
        } else {
            $msg_error = $msg_result['ERROR'];
        }
    }

    if (isset($protectedPost['Reset_modif'])) {
        unset($protectedPost['MODIF'], $protectedPost['ADD_COMM']);
    }

    if (isset($protectedPost['SUP_PROF']) && is_numeric($protectedPost['SUP_PROF'])) {
        $ipdiscover->del_community($protectedPost['SUP_PROF']);
        $msg_ok = $l->g(1212);
    }

    if (isset($msg_ok)) {
        msg_success($msg_ok);
    }

    if (isset($msg_error)) {
        msg_error($msg_error);
    }

    if ($protectedPost['ADD_COMM'] == $l->g(116) || is_numeric($protectedPost['MODIF'])) {
        $list_version = array('-1' => '2c', '1' => '1', '2' => '2', '3' => '3');
        $title = $l->g(1207);
        if (isset($protectedPost['MODIF']) && is_numeric($protectedPost['MODIF']) && !isset($protectedPost['NAME'])) {
            $info_com = $ipdiscover->find_community_info($protectedPost['MODIF']);
            $default_values = array('ID' => $protectedPost['MODIF'],
                'NAME' => $info_com->NAME,
                'VERSION' => $list_version,
                'USERNAME' => $info_com->USERNAME,
                'AUTHKEY' => $info_com->AUTHKEY,
                'AUTHPASSWD' => $info_com->AUTHPASSWD);
            if ($info_com->VERSION == "2c") {
                $protectedPost['VERSION'] = -1;
            } else {
                $protectedPost['VERSION'] = $info_com->VERSION;
            }
        } else {
            $default_values = array('ID' => $protectedPost['ID'],
                'NAME' => $protectedPost['NAME'],
                'VERSION' => $list_version,
                'USERNAME' => $protectedPost['USERNAME'],
                'AUTHKEY' => $protectedPost['AUTHKEY'],
                'AUTHPASSWD' => $protectedPost['AUTHPASSWD']);
        }
        $ipdiscover->form_add_community($title, $default_values, $form_name);
    } else {
        $sql = "select * from snmp_communities";
        $list_fields = array($l->g(277) => 'VERSION',
            $l->g(49) => 'NAME',
            $l->g(24) => 'USERNAME',
            $l->g(2028) => 'AUTHKEY',
            $l->g(217) => 'AUTHPASSWD',
            'MODIF' => 'ID',
            'SUP' => 'ID');
        $default_fields = $list_fields;
        $list_col_cant_del = $list_fields;
        $tab_options['LBL_POPUP']['SUP'] = 'NAME';
        $tab_options['LBL']['SUP'] = $l->g(122);
        $result_exist = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);

        echo "<input type='submit' class='btn' value='" . $l->g(116) . "' name='ADD_COMM'>";
        $protectedPost['ADD_COMM'] = $l->g(116);
    }
}

echo '</div>';
echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $sql, $tab_options);
}