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

$lbl_log = $l->g(1128);
$list_fields = array();
if (!isset($protectedPost['SHOW'])) {
    $protectedPost['SHOW'] = 'NOSHOW';
}
$tab_options = $protectedPost;
print_item_header($l->g(1128));
$form_name = "affich_notes";
$table_name = $form_name;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;

echo open_form($form_name, '', '', 'form-horizontal');

//delete a list of notes
if (isset($protectedPost['del_check'])) {
    $arg_sql = array();
    $sql = "update itmgmt_comments set visible=0 where id in ";
    $sql = mysql2_prepare($sql, $arg_sql, $protectedPost['del_check']);

    mysql2_query_secure($sql['SQL'], $_SESSION['OCS']["writeServer"], $sql['ARG'], 'DEL_NOTES');
    //update table cache
    $tab_options['CACHE'] = 'RESET';
}

if (is_defined($protectedPost['SUP_PROF'])) {
    $sql = "update itmgmt_comments set visible=0 where id=%s";
    $arg = array($protectedPost['SUP_PROF']);
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg, 'DEL_NOTE');
    //update table cache
    $tab_options['CACHE'] = 'RESET';
}

if (is_defined($protectedPost['Valid_modif'])) {
    //ajout de note
    if (is_defined($protectedPost['NOTE'])) {
        $sql = "insert into itmgmt_comments (HARDWARE_ID,DATE_INSERT,USER_INSERT,COMMENTS,ACTION)
					value (%s,%s,'%s','%s','%s')";
        $arg = array($systemid, "sysdate()", $_SESSION['OCS']["loggeduser"], $protectedPost['NOTE'], "ADD_NOTE_BY_USER");
        mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg, 'ADD_NOTE_BY_USER');
        unset($protectedPost['NOTE']);
        //regénération du cache
        $tab_options['CACHE'] = 'RESET';
    } elseif (is_defined($protectedPost['NOTE_MODIF'])) {
        $sql = "update itmgmt_comments set COMMENTS='%s'";
        $arg = array($protectedPost['NOTE_MODIF']);
        if (!strstr($protectedPost['USER_INSERT'], $_SESSION['OCS']["loggeduser"])) {
            $sql .= " , USER_INSERT = '%s/%s'";
            array_push($arg, $protectedPost['USER_INSERT'], $_SESSION['OCS']["loggeduser"]);
        }
        $sql .= " where id=%s";
        array_push($arg, $protectedPost['ID_MODIF']);
        $lbl_log .= "  Old Comments=" . $protectedPost['OLD_COMMENTS'];
        mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg, 'UPDATE_NOTE');
        //regénération du cache
        $tab_options['CACHE'] = 'RESET';
    }
}
if (isset($protectedPost['ADD_NOTE'])) {
    unset($tab_name, $tab_typ_champ);
    $tab_name[1] = $l->g(1126) . ": ";
    $tab_name[2] = $l->g(1127) . ": ";
    $tab_name[3] = $l->g(1128) . ": ";
    $tab_typ_champ[1]['DEFAULT_VALUE'] = date($l->g(1242));
    $tab_typ_champ[2]['DEFAULT_VALUE'] = $_SESSION['OCS']["loggeduser"];
    $tab_typ_champ[1]['INPUT_TYPE'] = 0;
    $tab_typ_champ[2]['INPUT_TYPE'] = 13;
    $tab_typ_champ[3]['INPUT_NAME'] = 'NOTE';
    $tab_typ_champ[3]['INPUT_TYPE'] = 1;
    modif_values($tab_name, $tab_typ_champ, $tab_hidden);
}

$queryDetails = "SELECT ID,DATE_INSERT,USER_INSERT,COMMENTS,ACTION FROM itmgmt_comments WHERE (visible is null or visible =1) and hardware_id=$systemid";
$list_fields = array( $l->g(1126) => 'DATE_INSERT',
    $l->g(899) => 'USER_INSERT',
    $l->g(51) => 'COMMENTS',
    $l->g(443) => 'ACTION');

if (!isset($show_all_column)) {
    // modif management
    $list_fields['MODIF'] = 'ID';
    
    // SUP Management
    $list_fields['SUP'] = 'ID';
    $tab_options['LBL_POPUP']['SUP'] = 'COMMENTS';
    
    $list_fields['CHECK'] = 'ID';
}
$list_col_cant_del = $list_fields;
$default_fields = $list_fields;

ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
if (!isset($show_all_column)) {
    echo "<p><input type='submit' name='ADD_NOTE' id='ADD_NOTE' value='" . $l->g(898) . "' class='btn'></p>";
    del_selection($form_name);
}

if (is_defined($protectedPost['MODIF'])) {
    unset($tab_name, $tab_typ_champ);
    $queryDetails = "SELECT ID,DATE_INSERT,USER_INSERT,COMMENTS,ACTION FROM itmgmt_comments WHERE id=%s";
    $argDetail = array($protectedPost['MODIF']);
    $resultDetails = mysql2_query_secure($queryDetails, $_SESSION['OCS']["readServer"], $argDetail);
    $item = mysqli_fetch_array($resultDetails, MYSQLI_ASSOC);
    $tab_name[1] = $l->g(1126) . ": ";
    $tab_name[2] = $l->g(1127) . ": ";
    $tab_name[3] = $l->g(1128) . ": ";
    $tab_typ_champ[1]['DEFAULT_VALUE'] = $item['DATE_INSERT'];
    $tab_typ_champ[2]['DEFAULT_VALUE'] = $item['USER_INSERT'];
    $tab_typ_champ[3]['DEFAULT_VALUE'] = $item['COMMENTS'];
    $tab_typ_champ[1]['INPUT_TYPE'] = 0;
    $tab_typ_champ[2]['INPUT_TYPE'] = 13;
    $tab_typ_champ[3]['INPUT_NAME'] = 'NOTE_MODIF';
    $tab_typ_champ[3]['INPUT_TYPE'] = 1;
    $tab_hidden['USER_INSERT'] = $item['USER_INSERT'];
    $tab_hidden['ID_MODIF'] = $protectedPost['MODIF'];
    $tab_hidden['OLD_COMMENTS'] = $item['COMMENTS'];
    modif_values($tab_name, $tab_typ_champ, $tab_hidden);
}
echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
    ob_start();
}
?>