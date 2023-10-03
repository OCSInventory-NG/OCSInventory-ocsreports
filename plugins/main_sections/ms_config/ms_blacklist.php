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
 * this page makes it possible to seize the MAC addresses for blacklist
 */
require_once('require/function_blacklist.php');
$form_name = "blacklist";
//printEnTete($l->g(703));
if (!is_defined($protectedPost['onglet']))
    $protectedPost['onglet'] = 1;
$tab_options = $protectedPost;
//définition des onglets
$data_on[1] = $l->g(95);
$data_on[2] = $l->g(36);
$data_on[3] = $l->g(2005);
$data_on[4] = $l->g(116);
if (isset($protectedPost['enre'])) {
    if ($protectedPost['BLACK_CHOICE'] == 1) {
        $ok = add_mac_add($protectedPost);
    }
    if ($protectedPost['BLACK_CHOICE'] == 3) {
        $ok = add_subnet_add($protectedPost);
    }
    if ($protectedPost['BLACK_CHOICE'] == 2) {
        $ok = add_serial_add($protectedPost);
    }
    if ($ok) {
        msg_error($ok);
    } else {
        unset($_SESSION['OCS']['DATA_CACHE'], $_SESSION['OCS']['NUM_ROW']);
    }
}
echo open_form($form_name, '', '', 'form-horizontal');
show_tabs($data_on,$form_name,"onglet",true);
echo '<div class="col col-md-10">';
switch ($protectedPost['onglet']) {
    case 1:
        $table_name = "blacklist_macaddresses";
        $tab_options['form_name'] = $form_name;
        $tab_options['table_name'] = $table_name;
        $list_fields = array('ID' => 'ID',
            'MACADDRESS' => 'MACADDRESS',
            'SUP' => 'ID',
            'CHECK' => 'ID');
        $list_col_cant_del = $list_fields;
        $default_fields = $list_fields;
        $tab_options['FILTRE'] = array('MACADDRESS' => 'MACADDRESS');
        $tab_options['LBL_POPUP']['SUP'] = 'MACADDRESS';
        $tab_options['LBL']['MACADDRESS'] = $l->g(95);
        break;

    case 2:
        $table_name = "blacklist_serials";
        $tab_options['form_name'] = $form_name;
        $tab_options['table_name'] = $table_name;
        $list_fields = array('ID' => 'ID',
            'SERIAL' => 'SERIAL',
            'SUP' => 'ID',
            'CHECK' => 'ID');
        $list_col_cant_del = $list_fields;
        $default_fields = $list_fields;
        $tab_options['FILTRE'] = array('SERIAL' => 'SERIAL');
        $tab_options['LBL_POPUP']['SUP'] = 'SERIAL';
        $tab_options['LBL']['SERIAL'] = $l->g(36);
        break;

    case 3:
        $table_name = "blacklist_subnet";
        $tab_options['form_name'] = $form_name;
        $tab_options['table_name'] = $table_name;
        $list_fields = array('ID' => 'ID',
            'SUBNET' => 'SUBNET',
            'MASK' => 'MASK',
            'SUP' => 'ID',
            'CHECK' => 'ID');
        $list_col_cant_del = $list_fields;
        $default_fields = $list_fields;
        $tab_options['FILTRE'] = array('SUBNET' => 'SUBNET', 'MASK' => 'MASK');
        $tab_options['LBL_POPUP']['SUP'] = 'SUBNET';
        $tab_options['LBL']['SUBNET'] = $l->g(2005);
        break;

    case 4:
        $list_action[0] = ' ';
        $list_action[1] = $l->g(95);
        $list_action[2] = $l->g(36);
        $list_action[3] = $l->g(2005);
        formGroup('select', 'BLACK_CHOICE', $l->g(700), '', '', $protectedPost['BLACK_CHOICE'] ?? 0, '', $list_action, $list_action, 'onchange="document.blacklist.submit();"');
        if (is_defined($protectedPost['BLACK_CHOICE'])) {

            echo "<div class='row'>";
            echo "<div class='col-md-6 col-md-offset-3'>";
            /**
             * 1 = MAC ADDRESS
             * 2 = SERIAL NUMBER
             * 3 = SUBNET
             *
             */

            if ($protectedPost['BLACK_CHOICE'] == 1) {
                // 6 cases  POST    BASE_NAME   VALUE PER FIELD     SIZE    SEPARATOR : JS

                ?>
                <div class="input-group">
                <label style="margin-right: 10px" "><?php echo $l->g(654);?></label>
                <?php
                $i = 1;
                while ($i <= $MACnb_field) {
                    if ($i != 1) {
                        echo $MACseparat;
                    }
                    ?>
                    <input type="text" name="<?php echo $MACfield_name.$i; ?>" maxlength="<?php echo $MACnb_value_by_field ?>" size="3" <?php echo $javascript_mac; ?>>
                    <?php
                    $i++;
                }

                ?>
                </div>
<?php


            } elseif ($protectedPost['BLACK_CHOICE'] == 3) {
                ?>
                <div class="input-group">
                    <div class="col-sm-4">
                        <label style="margin-right: 10px" "><?php echo $l->g(1142);?></label>
                    </div>
                    <div class="col-sm-8">
                    <?php
                    $i = 1;
                    while ($i <= $SUBnb_field) {
                        if ($i != 1) {
                            echo $SUBseparat;
                        }
                        ?>
                        <input type="text" name="<?php echo $SUBfield_name.$i; ?>" maxlength="<?php echo $SUBnb_value_by_field ?>" size="3" <?php echo $chiffres; ?>>
                        <?php
                        $i++;
                    }

                    ?>
                    </div>
                </div>
                <div class="input-group">
                    <div class="col-sm-4">
                        <label style="margin-right: 10px" "><?php echo $l->g(1143);?></label>
                    </div>
                    <div class="col-sm-8">
                    <?php
                    $i = 1;
                    while ($i <= $MASKnb_field) {
                        if ($i != 1) {
                            echo $MASKseparat;
                        }
                        ?>
                        <input type="text" name="<?php echo $MASKfield_name.$i; ?>" maxlength="<?php echo $MASKnb_value_by_field ?>" size="3" <?php echo $chiffres; ?>>
                        <?php
                        $i++;
                    }

                    ?>

                    </div>
                </div>
                <?php
            } elseif ($protectedPost['BLACK_CHOICE'] == 2) {
                formGroup('text', $SERIALfield_name.$SERIALnb_field, $l->g(702), '', '', $protectedPost[$SERIALfield_name] ?? '');
            }
            echo "</div>";
            echo "</div>";
            echo "<div class='row margin-top30'>";
            echo "<div class='col col-md-12'>";
            echo "<input class='btn btn-success' name='enre' type='submit' value=" . $l->g(114) . ">";
            echo "</div>";
            echo "</div>";
        }
        break;

    default:
        break;
}
if (isset($list_fields)) {
    //cas of delete mac address or serial
    if (isset($protectedPost["SUP_PROF"]) && is_numeric($protectedPost["SUP_PROF"])) {
        $sql = "delete from %s where id=%s";
        $arg = array($table_name, $protectedPost["SUP_PROF"]);
        mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
    }
    if (is_defined($protectedPost['del_check'])) {
        // check that every substring in del_check is a number
        $del_check = explode(',', $protectedPost['del_check']);
        $del_check = array_filter($del_check, 'is_numeric');
        // rebuild the string from cleaned input
        $del_check = implode(',', $del_check);
        $sql = "delete from %s where id in (%s)";
        $arg = array($table_name, $del_check);
        mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
        $tab_options['CACHE'] = 'RESET';
    }
    $sql = prepare_sql_tab($list_fields, array('SUP', 'CHECK', 'MODIF'));
    $sql['SQL'] .= " from " . $table_name;
    $tab_options['ARG_SQL'] = $sql['ARG'];
    ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
    del_selection($form_name);
}
echo "</div>";
echo close_form();
if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $sql['SQL'], $tab_options);
}
