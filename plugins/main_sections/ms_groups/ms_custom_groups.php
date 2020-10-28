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
require_once('require/function_search.php');
require_once('require/function_groups.php');
$form_name = "groups_affect";
echo open_form($form_name, '', '', 'form-horizontal');
$list_id = multi_lot($form_name, $l->g(601));

/* * *******************************************TRAITEMENT DES DONNEES**************************************** */
if (isset($protectedPost['VALID_GROUP'])) {
    
    echo "<div class='col col-md-12'>";
    //gestion groupe de serveurs
    if ($protectedPost['onglet'] == mb_strtoupper($l->g(651))) {
        require_once('require/function_server.php');
        //ajout de machines
        if ($protectedPost['NEW_RAZ'] == "ADD") {
            $action = 'add_serv';
        }
        //nouveau groupe
        if ($protectedPost['NEW_RAZ'] == "NEW") {
            $name_or_id = $protectedPost['NAME_GROUP'];
            $lbl = $protectedPost['LBL_GROUP'];
            $action = 'new_serv';
        }
        //remplacement d'un groupe
        if ($protectedPost['NEW_RAZ'] == "RAZ") {
            $action = 'replace_serv';
        }
        //suppression de machines dans le groupe de serveur
        if ($protectedPost['NEW_RAZ'] == "DEL") {
            $action = 'del_serv';
        }

        if (!isset($name_or_id)) {
            $name_or_id = $protectedPost['group_list'];
        }

        if (!isset($lbl)) {
            $lbl = "''";
        }

        $msg_error = admin_serveur($action, $name_or_id, $lbl, $list_id);
    }//gestion groupe de machines
    else {
        if ($protectedPost['onglet'] == $l->g(809)) {
            $group_type = "STATIC";
        } else {
            $group_type = "DYNAMIC";
        }

        //ajout a un groupe
        if ($protectedPost['NEW_RAZ'] == "ADD") {
            $nb_mach = add_computers_cache($list_id, $protectedPost['group_list'], 1);
            $msg_success = $l->g(973);
        }

        //suppression des machines du groupe en masse
        if ($protectedPost['NEW_RAZ'] == "DEL") {
            $nb_mach = remove_of_group($protectedPost['group_list'], $list_id);
            $msg_success = $l->g(971) . "<br>" . $l->g(972);
        }
        //Création d'un nouveau groupe
        if ($protectedPost['NEW_RAZ'] == "NEW") {
            $result = creat_group($protectedPost['NAME_GROUP'], $protectedPost['LBL_GROUP'], $list_id, $_SESSION['OCS']['SEARCH_SQL_GROUP'], $group_type);
            if ($result['RESULT'] == "ERROR") {
                $nb_mach = "ERROR";
            } else {
                $nb_mach = $result['LBL'];
                $msg_success = $l->g(880);
            }
        }
        //ecrasement d'un groupe
        if ($protectedPost['NEW_RAZ'] == "RAZ") {
            $nb_mach = replace_group($protectedPost['group_list'], $list_id, $_SESSION['OCS']['SEARCH_SQL_GROUP'], $group_type);
            $msg_success = $l->g(879);
        }
        if ($nb_mach == "ERROR") {
            $msg_error = $result['LBL'];
        } elseif (isset($nb_mach) && $protectedPost['NEW_RAZ'] != "DEL") {
            $msg_success .= "<br>" . $nb_mach . " " . $l->g(974);
        }
    }

    if (is_defined($msg_error)) {
        msg_error($msg_error);
    }elseif (is_defined($msg_success)) {
        msg_success($msg_success);
    }
    echo "</div>";
}
/* * *******************************************CALCUL DES CHAMPS A AFFICHER************************************ */
if ($list_id) {
    //définition des onglets
    //for all
    $def_onglets[$l->g(809)] = $l->g(809); //GROUPES STATIQUES
}
if ($_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES") {
    $def_onglets[$l->g(810)] = $l->g(810); //GROUPES DYNAMIQUES
    $def_onglets[mb_strtoupper($l->g(651))] = mb_strtoupper($l->g(651)); //GROUPES DE SERVEURS
    //definition of option NEW every time
    $optionList['NEW'] = $l->g(586);
}


//if no select => first onget selected
if (!is_defined($protectedPost['onglet'])) {
    if (isset($def_onglets[$l->g(809)])) {
        $protectedPost['onglet'] = $l->g(809);
    } else {
        $protectedPost['onglet'] = $l->g(810);
    }
}

if ($protectedPost['onglet'] == $l->g(810)) {
    $all_groups = all_groups('DYNAMIC');
}
if ($protectedPost['onglet'] == $l->g(809)) {
    $all_groups = all_groups('STATIC');
    $delGroups = "select distinct id, name,workgroup from hardware,groups_cache
			where groups_cache.HARDWARE_ID in (" . $list_id . ")
				and groups_cache.group_id=hardware.id
				and deviceid = '_SYSTEMGROUP_'
				and groups_cache.static = 1";
    if (!($_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES")) {
        $delGroups .= " and workgroup = 'GROUP_4_ALL'";
    }
}
if ($protectedPost['onglet'] == mb_strtoupper($l->g(651)) && $list_id != '') {
    $all_groups = all_groups('SERVER');
    $delGroups = "select distinct group_id as id, name
				from download_servers,hardware
				where hardware_id in(" . $list_id . ")
					and hardware.id=download_servers.group_id";
}
//search all groups for listid selection
if (isset($delGroups)) {
    $resDelGroups = mysqli_query($_SESSION['OCS']["readServer"], $delGroups);
    while ($valDelGroups = mysqli_fetch_array($resDelGroups)) {
        $groupDelList[$valDelGroups["id"]] = $valDelGroups["name"];
    }
}
if ($protectedPost['onglet'] != $l->g(810)) {
    $optionList['ADD'] = $l->g(975);
} else {
    $optionList['ADD'] = $l->g(589);
}
//if groups exist => add option for go out of the group
if (isset($groupDelList)) {
    $optionList['DEL'] = $l->g(818);
} else {
    if ($protectedPost['NEW_RAZ'] == "DEL") {
        unset($protectedPost['NEW_RAZ']);
    }
}
//if group list exist
if (isset($all_groups) && $_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES") {
    //show RAZ field
    $optionList['RAZ'] = $l->g(588);
}
$select = show_modif($optionList, 'NEW_RAZ', 2, $form_name);


/* * ****************************************show RESULT*********************************************** */
//show onglet
echo "<div class='col col-md-8 col-md-offset-1'>";
if(is_defined($protectedPost['CHOISE']) && $protectedPost['CHOISE'] != 'NONE'){
    echo "<p>";
    onglet($def_onglets, $form_name, 'onglet', 7);   
    echo "</p>";
}

//create a "valid" button
$valid = "<tr><td align=center colspan=10><input type=submit value='" . $l->g(13) . "' name='VALID_GROUP' class='btn'></td></tr>";
//open table
echo "<table cellspacing='5' width='80%' BORDER='0' ALIGN = 'Center' CELLPADDING='0' BGCOLOR='#C7D9F5' BORDERCOLOR='#9894B5'><tr><td>";
echo "<tr><td align =center colspan=10>";
if (is_defined($protectedPost['CHOISE']) && $protectedPost['CHOISE'] != 'NONE') {
    echo $select;
    echo "</td></tr>";
    //if user want give up or go out of the group
    if ($protectedPost['NEW_RAZ'] == "RAZ" || $protectedPost['NEW_RAZ'] == "ADD") {
        $List = $all_groups;
    }
    if ($protectedPost['NEW_RAZ'] == "DEL") {
        $List = $groupDelList;
    }
    if ($protectedPost['NEW_RAZ'] == "NEW") {
        $nom = show_modif($protectedPost['NAME_GROUP'], 'NAME_GROUP', 0, '');
        $lbl = show_modif($protectedPost['LBL_GROUP'], 'LBL_GROUP', 1, '');
        $addgroup = "<tr><td align=center>" . $l->g(49) . ":</td><td align=left>" . $nom . "</td></tr>";
        $addgroup .= "<tr><td align=center>" . $l->g(53) . ":</td><td align=left>" . $lbl . "</td></tr>";
        $addgroup .= $valid;
        echo $addgroup;
    }
    if ($protectedPost['NEW_RAZ'] == "RAZ" || $protectedPost['NEW_RAZ'] == "DEL" || $protectedPost['NEW_RAZ'] == "ADD") {
        $select = show_modif($List, 'group_list', 2, '');
        //list of choise
        $groupList = "<tr><td align =center>";
        $groupList .= $select;
        $groupList .= "</td></tr>";
        $groupList .= $valid;
        echo $groupList;
    }
}
echo "</td></tr></table>";


echo "</div>";
echo close_form();
?>