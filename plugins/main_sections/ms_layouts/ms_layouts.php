<?php
/*
 * Copyright 2005-2022 OCSInventory-NG/OCSInventory-ocsreports contributors.
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

printEnTete($l->g(9907));

$tab_options = $protectedPost;
$form_name = "layouts";
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $form_name;

$layout = new Layout($protectedGet['value'] ?? '');
//ADD new layout
if (isset($protectedPost['CREATE_VALID']) && (!empty($_SESSION['OCS']['layout_visib']))){
    if (isset($protectedPost['LAYOUT_SCOPE']) && $protectedPost['LAYOUT_SCOPE'] == 'GROUP') {
        $grp = $_SESSION['OCS']['user_group'];
    } else {
        $grp = '';
    }
    $dupli = $layout->insertLayout($protectedPost['LAYOUT_NAME'], $protectedPost['LAYOUT_DESCR'], $_SESSION['OCS']['loggeduser'], $_SESSION['OCS']['layout_visib'], $protectedPost['LAYOUT_SCOPE'] ?? 'USER', $grp);
    // if dupli, user needs to be redirected to the form and not to the list
    if (!empty($dupli)) {
        unset($protectedPost['CREATE_VALID']);
    }

// update layout
} elseif (isset($protectedPost['MODIF_VALID'])) {
    $update = array('LAYOUT_NAME' => $protectedPost['LAYOUT_NAME'],
                    'LAYOUT_DESCR' => $protectedPost['LAYOUT_DESCR'],
                    'LAYOUT_SCOPE' => $protectedPost['LAYOUT_SCOPE'] ?? 'USER',
                );

    $update['GROUP_ID'] = $update['LAYOUT_SCOPE'] == 'GROUP' ? $_SESSION['OCS']['user_group'] : '';

    $modif = $layout->updateLayout($protectedPost['ID_MODIF'], $update ?? '');
    unset($protectedPost['MODIF']);
}

//delete layout
if (isset($protectedPost['SUP_PROF']) && $protectedPost['SUP_PROF'] != '') {
    $layout->deleteLayout($protectedPost['SUP_PROF']);
    $tab_options['CACHE'] = 'RESET';
}

if (is_defined($protectedPost['del_check'])) {
    $layout->deleteLayout($protectedPost['del_check']);
    $tab_options['CACHE'] = 'RESET';
}

// if updating layout OR no layout has been added yet and user did not delete layout : show the form 
if (!empty($protectedPost['MODIF']) || (isset($protectedGet['tab']) && $protectedGet['tab'] == 'add') && (!isset($protectedPost['CREATE_VALID'])) && (!isset($protectedPost['SUP_PROF']) 
    && !isset($protectedPost['del_check'])) && !isset($protectedPost['show_list']) && !isset($protectedPost['MODIF_VALID'])) {
    echo open_form($form_name, '', '', 'form-horizontal');

    $visib = array(
        "USER" => $l->g(2146),
        "ALL" => $l->g(2148),
    );

    // added CAS and LDAP exclusion, not considered as groups
    if($_SESSION['OCS']['user_group'] != null && $_SESSION['OCS']['user_group'] != "" && ($_SESSION['OCS']['user_group'] != "LDAP" && $_SESSION['OCS']['user_group'] != "CAS")) {
        $visib['GROUP'] = $l->g(2147);
    }

    if (!empty($protectedPost['MODIF'])) {
        $modif = $layout->updateLayout($protectedPost['MODIF'], $update ?? '');
        $protectedPost['LAYOUT_NAME'] = $modif['LAYOUT_NAME'];
        $protectedPost['LAYOUT_DESCR'] = $modif['DESCRIPTION'];
        $protectedPost['LAYOUT_SCOPE'] = $modif['VISIBILITY_SCOPE'];
        $protectedPost['ID_MODIF'] = $protectedPost['MODIF'];

    }

    ?>
        <div class="col-md-12">
            <?php
            formGroup('text', 'LAYOUT_NAME', $l->g(9911).' :', '', '', $protectedPost['LAYOUT_NAME'] ?? '');
            formGroup('text', 'LAYOUT_DESCR', $l->g(9912).' :', '', '', $protectedPost['LAYOUT_DESCR'] ?? '');
            if ($_SESSION['OCS']['profile']->getConfigValue('MANAGE_LAYOUTS') == 'YES') {
                formGroup('select', 'LAYOUT_SCOPE', $l->g(9915).' :', '', '', $protectedPost['LAYOUT_SCOPE'] ?? '', '', $visib, $visib);
            }
            ?>
        </div>
    <div class="row">
        <div class="col-md-12">
            <?php
            // if updating existing layout, display update button
            if (isset($protectedPost['MODIF'])) {
                $name = 'MODIF_VALID';
                $value = $l->g(103);
                echo "<input type='hidden' name='ID_MODIF' value=".$protectedPost['MODIF'].">";
            } else {
                $name = 'CREATE_VALID';
                $value = $l->g(1363);
            }
            echo "<input type='submit' name='$name' value='$value' class='btn btn-success'>";
            ?>
            <input type="submit" name="show_list" value="<?php echo $l->g(9908) ?>" class="btn btn-info">
        </div>
    </div>
    <?php
    echo close_form();

// show the table
} else {
    echo open_form($form_name, '', '', 'form-horizontal');
    
    $list_fields = array(
        $l->g(9911) => 'LAYOUT_NAME',
        $l->g(9915) => 'VISIBILITY_SCOPE', 
        $l->g(844)  => 'GROUP_ID',
        $l->g(9914) => 'CREATOR',
        $l->g(9913) => 'TABLE_NAME',
        $l->g(53) => 'DESCRIPTION',
        'SUP' => 'SUP',
        'CHECK' => 'CHECK'
    );
    
    $list_fields['SUP'] = 'ID';
    $list_fields['CHECK'] = 'ID';
    $tab_options['LBL_POPUP']['SUP'] = 'LAYOUT_NAME';
    $list_fields['MODIF'] = 'ID';
    $list_col_cant_del = $list_fields;
    $default_fields = $list_col_cant_del;

    // if user has manage layouts perm : can see everything but other users' layouts
    if ($_SESSION['OCS']['profile']->getConfigValue('MANAGE_LAYOUTS') == 'YES') {
        $queryDetails = "SELECT ID, LAYOUT_NAME, CREATOR, TABLE_NAME, DESCRIPTION, VISIBILITY_SCOPE, GROUP_ID FROM `layouts` WHERE CREATOR = '".$_SESSION['OCS']['loggeduser']."' OR (VISIBILITY_SCOPE = 'ALL' OR VISIBILITY_SCOPE = 'GROUP') ";
    // user not allowed to manage + belongs to a grp : see their own layouts + group layouts + common layouts (ALL)
    } elseif ($_SESSION['OCS']['user_group'] != null && $_SESSION['OCS']['user_group'] != "") {
        $queryDetails = "SELECT ID, LAYOUT_NAME, CREATOR, TABLE_NAME, DESCRIPTION, VISIBILITY_SCOPE, GROUP_ID FROM `layouts` WHERE CREATOR = '".$_SESSION['OCS']['loggeduser']."' OR (VISIBILITY_SCOPE = 'GROUP' AND GROUP_ID = ".$_SESSION['OCS']['user_group'].") OR VISIBILITY_SCOPE = 'ALL'";
    // user not allowed + no group : see their own layouts + common layouts (ALL)
    } else {
        $queryDetails = "SELECT ID, LAYOUT_NAME, CREATOR, TABLE_NAME, DESCRIPTION, VISIBILITY_SCOPE, GROUP_ID FROM `layouts` WHERE CREATOR = '".$_SESSION['OCS']['loggeduser']."' OR VISIBILITY_SCOPE = 'ALL'";
    }
    
    ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
    del_selection($form_name);
    echo close_form();

}


if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
}