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
 * Page des groupes
 */
require_once('require/function_groups.php');
require_once('require/function_computers.php');
//ADD new static group
if ($protectedPost['Valid_modif']) {
    $result = creat_group($protectedPost['NAME'], $protectedPost['DESCR'], '', '', 'STATIC');
    if ($result['RESULT'] == "ERROR") {
        msg_error($result['LBL']);
    } elseif ($result['RESULT'] == "OK") {
        msg_success($result['LBL']);
        unset($protectedPost['add_static_group']);
    }
    $tab_options['CACHE'] = 'RESET';
}
//reset add static group
if ($protectedPost['Reset_modif'] || ($protectedPost['onglet'] != $protectedPost['old_onglet'])) {
    unset($protectedPost['add_static_group']);
}
$tab_options = $protectedPost;
//view only your computers
if ($_SESSION['OCS']['profile']->getRestriction('GUI') == 'YES') {
    $mycomputers = computer_list_by_tag();
    if ($mycomputers == "ERROR") {
        msg_error($l->g(893));
        require_once(FOOTER_HTML);
        die();
    }
}
//View for all profils?
if (!AJAX) {
    if (is_defined($protectedPost['CONFIRM_CHECK'])) {
        $result = group_4_all($protectedPost['CONFIRM_CHECK']);
    }
}
//if delete group
if ($protectedPost['SUP_PROF'] != "") {
    $result = delete_group($protectedPost['SUP_PROF']);
    if ($result['RESULT'] == "ERROR") {
        msg_error($result['LBL']);
    }
    $tab_options['CACHE'] = 'RESET';
}

$form_name = 'groups';
$tab_options['form_name'] = $form_name;

echo open_form($form_name, '', '', 'form-horizontal');
//view all groups
if ($_SESSION['OCS']['profile']->getConfigValue('GROUPS')=="YES"){
	$def_onglets['DYNA']=$l->g(810); //Dynamic group
	$def_onglets['STAT']=$l->g(809); //Static group centraux
	if ($protectedPost['onglet'] == "")
	$protectedPost['onglet']="STAT";	
	//show onglet
	show_tabs($def_onglets,$form_name,"onglet",true);
	echo '<div class="col col-md-10">';


}else{	
	$protectedPost['onglet']="STAT";
}

$list_fields = array('GROUP_NAME' => 'h.NAME',
    'GROUP_ID' => 'h.ID',
    'DESCRIPTION' => 'h.DESCRIPTION',
    'CREATE' => 'h.LASTDATE',
    'NBRE' => 'NBRE');
//only for admins
if ($_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES") {
    if ($protectedPost['onglet'] == "STAT") {
        $list_fields['CHECK'] = 'ID';
    }
    $list_fields['SUP'] = 'ID';
    $tab_options['LBL_POPUP']['SUP'] = 'NAME';
    $tab_options['LBL']['SUP'] = $l->g(122);
}
//changement de nom à l'affichage des champs
$tab_options['LBL']['CHECK'] = $l->g(52);
$tab_options['LBL']['GROUP_NAME'] = $l->g(49);

$table_name = "LIST_GROUPS";
$tab_options['table_name'] = $table_name;

$default_fields = array('GROUP_NAME' => 'GROUP_NAME', 'DESCRIPTION' => 'DESCRIPTION', 'CREATE' => 'CREATE', 'NBRE' => 'NBRE', 'SUP' => 'SUP', 'CHECK' => 'CHECK');
$list_col_cant_del = array('GROUP_NAME' => 'GROUP_NAME', 'SUP' => 'SUP', 'CHECK' => 'CHECK');
$query = prepare_sql_tab($list_fields, array('SUP', 'CHECK', 'NBRE'));
$tab_options['ARG_SQL'] = $query['ARG'];
$querygroup = $query['SQL'];

//requete pour les groupes de serveurs
if ($protectedPost['onglet'] == "SERV") {
    $querygroup .= " from hardware h,download_servers ds where ds.group_id=h.id and h.deviceid = '_DOWNLOADGROUP_'";
    //calcul du nombre de machines par groupe de serveur
    $sql_nb_mach = "SELECT count(*) nb, group_id
					from download_servers group by group_id";
} else { //requete pour les groupes 'normaux'
    $querygroup .= " from hardware h,`groups` g ";
    $querygroup .= "where g.hardware_id=h.id and h.deviceid = '_SYSTEMGROUP_' ";
    if ($protectedPost['onglet'] == "DYNA") {
        $querygroup .= " and ((g.request is not null and trim(g.request) != '')
							or (g.xmldef is not null and trim(g.xmldef) != ''))";
    } elseif ($protectedPost['onglet'] == "STAT") {
        $querygroup .= " and (g.request is null or trim(g.request) = '')
					    and (g.xmldef  is null or trim(g.xmldef) = '') ";
    }
    if ($_SESSION['OCS']['profile']->getConfigValue('GROUPS') != "YES") {
        $querygroup .= " and h.workgroup='GROUP_4_ALL' ";
    }

    //calcul du nombre de machines par groupe
    $sql_nb_mach = "SELECT count(*) nb, group_id
					from groups_cache gc,hardware h where h.id=gc.hardware_id ";
    if ($_SESSION['OCS']['profile']->getRestriction('GUI') == "YES") {
        $sql_nb_mach .= " and gc.hardware_id in " . $mycomputers;
    }
    $sql_nb_mach .= " group by group_id";
}
$querygroup .= " group by h.ID";
$result = mysql2_query_secure($sql_nb_mach, $_SESSION['OCS']["readServer"]);
while ($item = mysqli_fetch_object($result)) {
    //on force les valeurs du champ "nombre" à l'affichage
    $tab_options['VALUE']['NBRE'][$item->group_id] = $item->nb;
    $_SESSION['OCS']['VALUE_FIXED'][$tab_options['table_name']]['NBRE'][$item->group_id] = $item->nb;
}

//Modif ajoutée pour la prise en compte
//du chiffre à rajouter dans la colonne de calcul
//quand on a un seul groupe et qu'aucune machine n'est dedant.
if (!isset($tab_options['VALUE']['NBRE'])) {
    $tab_options['VALUE']['NBRE'][] = 0;
}
//on recherche les groupes visible pour cocher la checkbox à l'affichage
if ($protectedPost['onglet'] == "STAT") {
    $sql = "select id from hardware where workgroup='GROUP_4_ALL'";
    $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
    while ($item = mysqli_fetch_object($result)) {
        $protectedPost['check' . $item->id] = "check";
    }
}
//on ajoute un javascript lorsque l'on clic sur la visibilité du groupe pour tous
$tab_options['JAVA']['CHECK']['QUESTION'] = $l->g(811);
$tab_options['FILTRE'] = array('NAME' => $l->g(679), 'DESCRIPTION' => $l->g(53));
//affichage du tableau
$result_exist = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);

//if your profil is an admin groups, you can create one
if ($_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES") {
    if ($protectedPost['onglet'] == "STAT") {
        ?>
        <div class="row">
            <div class='col-md-12'>
                <input type='submit' class='btn' name='add_static_group' value='<?php echo $l->g(587) ?>'>
            </div>
        </div>
        <?php
    }
}

//if user want add a new group
if (isset($protectedPost['add_static_group']) && $_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES") {
    ?>
    <div class="row rowMarginTop30">
        <div class="col-md-12">
            <?php
            formGroup('text', 'NAME', $l->g(577), '20', '', $protectedPost['NAME']);
            formGroup('text', 'DESCR', $l->g(53), '', '', $protectedPost['DESCR']);
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <input type="submit" name="Valid_modif" value="<?php echo $l->g(1363) ?>" class="btn btn-success">
            <input type="submit" name="Reset_modif" value="<?php echo $l->g(1364) ?>" class="btn btn-danger">
        </div>
    </div>
    <?php
}
echo '</div>';
//fermeture du formulaire
echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $querygroup, $tab_options);
}
?>