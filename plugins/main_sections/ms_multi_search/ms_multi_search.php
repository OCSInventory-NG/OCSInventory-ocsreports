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
    $ajax = true;
} else {
    $ajax = false;
}
//limite du nombre de résultat
//sur les tables de cache
//ex: software_name_cache, osname_cache...
$limit_result_cache = 1000;
//intégration des fonctions liées à la recherche multicritère
require_once('require/function_search.php');
//fonction machines
require_once('require/function_computers.php');
//nom du formulaire de la page
$form_name = 'multisearch';
//nom du tableau d'affichage
$table_tabname = "TAB_MULTICRITERE";
//cas où l'on arrive d'une autre page
//ex: la page des stats
if (isset($protectedGet['fields']) && (!isset($protectedPost['GET']) || $protectedPost['GET'] == '')) {
    unset($protectedPost);
    foreach ($_SESSION['OCS'] as $key => $value) {
        $valeur = explode("-", $key);
        if ($valeur[0] == "InputValue" || $valeur[0] == "SelFieldValue" || $valeur[0] == "SelFieldValue3" || $valeur[0] == "SelAndOr" || $valeur[0] == "SelComp")
            unset($_SESSION['OCS'][$key]);
    }
    $tab_session = explode(',', $protectedGet['fields']);
    $sel_comp = explode(',', $protectedGet['comp']);
    $fields_values = explode(',', $protectedGet['values']);
    $fields_values2 = explode(',', $protectedGet['values2']);
    $type_field = explode(',', $protectedGet['type_field']);
    if (is_array($tab_session)) {
        unset($_SESSION['OCS']['multiSearch']);
        foreach ($tab_session as $key => $value) {
            $_SESSION['OCS']['multiSearch'][] = $value;
            $_SESSION['OCS']['SelComp-' . $value . "-" . $key] = $sel_comp[$key];
            if ($type_field[$key] == 'InputValue' || $type_field[$key] == '') {
                $_SESSION['OCS']['InputValue-' . $value . "-" . $key] = $fields_values[$key];
                $protectedPost['InputValue-' . $value . "-" . $key] = $fields_values[$key];
            }
            if ($type_field[$key] == 'SelFieldValue' || $type_field[$key] == '') {
                $_SESSION['OCS']['SelFieldValue-' . $value . "-" . $key] = $fields_values[$key];
                $protectedPost['SelFieldValue-' . $value . "-" . $key] = $fields_values[$key];
            }
            $_SESSION['OCS']['SelFieldValue2-' . $value . "-" . $key] = $fields_values2[$key];
            $protectedPost['SelComp-' . $value . "-" . $key] = $sel_comp[$key];


            $protectedPost['SelFieldValue2-' . $value . "-" . $key] = $fields_values2[$key];
        }
        $protectedPost['Valid-search'] = $l->g(30);
        $protectedPost['multiSearch'] = $l->g(32);
        $protectedPost['Valid'] = 1;
        $protectedPost['GET'] = 'GET';
    }
}
//need to delete this part... 
if (isset($protectedGet['prov']) && (!isset($protectedPost['GET']) || $protectedPost['GET'] == '')) {
    unset($protectedPost);
    foreach ($_SESSION['OCS'] as $key => $value) {
        $valeur = explode("-", $key);
        if ($valeur[0] == "InputValue" || $valeur[0] == "SelFieldValue" || $valeur[0] == "SelFieldValue3" || $valeur[0] == "SelAndOr" || $valeur[0] == "SelComp")
            unset($_SESSION['OCS'][$key]);
    }
    if ($protectedGet['prov'] == "stat") {
        $tab_session[] = "DEVICES-DOWNLOAD";
        $tab_stat = array('SelComp-DEVICES-DOWNLOAD-0' => "exact", 'SelFieldValue-DEVICES-DOWNLOAD-0' => $protectedGet['id_pack'], 'SelFieldValue2-DEVICES-DOWNLOAD-0' => $protectedGet['stat']); //unset($_SESSION['OCS']);
    }
    if ($protectedGet['prov'] == "allsoft") {

        if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES']) && $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1) {
            $sql = "select name from softwares_name_cache where id=%s";
            $arg = $protectedGet['value'];
            $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
            $item = mysqli_fetch_object($result);
            $protectedGet['value'] = $item->name;
        }
        $tab_session[] = "SOFTWARES-NAME";
        $tab_stat = array('SelComp-SOFTWARES-NAME-0' => "exact", 'InputValue-SOFTWARES-NAME-0' => $protectedGet['value']); //unset($_SESSION['OCS']);
    }

    if ($protectedGet['prov'] == "ipdiscover" || $protectedGet['prov'] == "ipdiscover1") {
        $tab_session[] = "NETWORKS-IPSUBNET";
        $tab_stat['SelComp-NETWORKS-IPSUBNET-0'] = "exact";
        $tab_stat['InputValue-NETWORKS-IPSUBNET-0'] = $protectedGet['value']; //unset($_SESSION['OCS']);
    }
    if ($protectedGet['prov'] == "ipdiscover1") {
        $tab_session[] = "DEVICES-IPDISCOVER";
        $tab_session[] = "DEVICES-IPDISCOVER";
        $tab_stat['SelComp-DEVICES-IPDISCOVER-1'] = "exact";
        $tab_stat['SelFieldValue-DEVICES-IPDISCOVER-1'] = "1";
        $tab_stat['SelAndOr-DEVICES-IPDISCOVER-2'] = "OR";
        $tab_stat['SelComp-DEVICES-IPDISCOVER-2'] = "exact";
        $tab_stat['SelFieldValue-DEVICES-IPDISCOVER-2'] = "2";
    }
    if (isset($tab_stat)) {
        unset($_SESSION['OCS']['multiSearch']);
        foreach ($tab_session as $key => $value)
            $_SESSION['OCS']['multiSearch'][] = $value;

        foreach ($tab_stat as $key => $value)
            $protectedPost[$key] = $value;
        $protectedPost['Valid-search'] = $l->g(30);
        $protectedPost['multiSearch'] = $l->g(32);
        $protectedPost['Valid'] = 1;
    }
    $protectedPost['GET'] = $protectedGet['prov'];
}
//end need to delete this part...
//initialisation du tableau
//$list_fields_calcul=array();
//ouverture du formulaire
echo open_form($form_name, '', '', 'form-horizontal');
if (isset($protectedPost['GET'])) {
    echo "<input type=hidden name='GET' value='" . $protectedPost['GET'] . "'>";
}
//recherche des différents champs de accountinfo
require_once('require/function_admininfo.php');
$field_of_accountinfo = witch_field_more('COMPUTERS');
$optSelectField_account = array();
$opt2Select_account = array();
$sort_accountinfo = array();
$list_fields_account_info = array();
foreach ($field_of_accountinfo['LIST_FIELDS'] as $id => $lbl) {

    if ($field_of_accountinfo['LIST_NAME'][$id] == "TAG") {
        $name_field_accountinfo = "TAG";
        $delfault_tag = $l->g(1210) . " " . $lbl;
    } else
        $name_field_accountinfo = "fields_" . $id;

    $sort_accountinfo['ACCOUNTINFO-' . $name_field_accountinfo] = $l->g(1210) . " " . $lbl;
    if (in_array($field_of_accountinfo['LIST_TYPE'][$id], array(0, 1, 3, 6))) {

        $optSelectField_account['ACCOUNTINFO-' . $name_field_accountinfo] = $sort_accountinfo['ACCOUNTINFO-' . $name_field_accountinfo]; //"Accinf: ".$lbl;
        if ($field_of_accountinfo['LIST_TYPE'][$id] == 6) {
            $optSelectField_account["ACCOUNTINFO-" . $name_field_accountinfo . "-LBL"] = "calendar";
            $optSelectField_account["ACCOUNTINFO-" . $name_field_accountinfo . "-SELECT"] = array("exact" => $l->g(410), "small" => $l->g(346), "tall" => $l->g(347));
        }
    } elseif (in_array($field_of_accountinfo['LIST_TYPE'][$id], array(2, 4, 7))) {
        $opt2Select_account['ACCOUNTINFO-' . $name_field_accountinfo] = $l->g(1210) . " " . $lbl;
        $opt2Select_account['ACCOUNTINFO-' . $name_field_accountinfo . "-SQL1"] = "select ivalue as ID,tvalue as NAME from config where name like 'ACCOUNT_VALUE_" . $field_of_accountinfo['LIST_NAME'][$id] . "%' order by 2";
        if ($field_of_accountinfo['LIST_TYPE'][$id] == 4) {
            $accountinfo_checkbox[] = $name_field_accountinfo;
            $opt2Select_account['ACCOUNTINFO-' . $name_field_accountinfo . "-SELECT"] = array('like' => $l->g(507), 'diff' => $l->g(508));
        } else {
            $opt2Select_account['ACCOUNTINFO-' . $name_field_accountinfo . "-SELECT"] = array('exact' => $l->g(507), 'diff' => $l->g(508));
        }
    }
//		$list_fields_account_info['Accinf: '.$lbl]="a." . $name_field_accountinfo;
    $Accinfo = $l->g(1210) . " " . $lbl;
    $list_fields_account_info[$Accinfo] = "a." . $name_field_accountinfo;
}
//si on ajoute un champ de recherche
//on efface les données précedemment en cache
if ($protectedPost['delfield'] != "" || $protectedPost['multiSearch'] != $l->g(32)) {
    unset($protectedPost['Valid-search']);
    unset($_SESSION['OCS']['ID_REQ']);
    unset($_SESSION['OCS']['DATA_CACHE'][$table_tabname]);
}
//cas d'une suppression de machine
if ($protectedPost['SUP_PROF'] != '') {
    deleteDid($protectedPost['SUP_PROF']);
    //on force la valeur cachée de la validation du formulaire 
    //pour rejouer la requete et ne pas utiliser le cache
    $protectedPost['Valid'] = "SUP";
}
//for save field and value
if ($protectedPost['Valid-search'] && $protectedPost['Valid'] != '') {
    foreach ($protectedPost as $key => $value) {
        $valeur = explode("-", $key);
        if ($valeur[0] == "InputValue" || $valeur[0] == "SelFieldValue" || $valeur[0] == "SelFieldValue3" || $valeur[0] == "SelAndOr" || $valeur[0] == "SelComp") {
            $_SESSION['OCS'][$key] = $value;
        }
    }
} else {
    foreach ($_SESSION['OCS'] as $key => $value) {
        $valeur = explode("-", $key);
        if ($valeur[0] == "InputValue" || $valeur[0] == "SelFieldValue" || $valeur[0] == "SelFieldValue3" || $valeur[0] == "SelAndOr" || $valeur[0] == "SelComp")
            $protectedPost[$key] = $value;
    }
}
if ($protectedPost['multiSearch'] != '' && $protectedPost['multiSearch'] != $l->g(32)) {
    $_SESSION['OCS']['multiSearch'][] = $protectedPost['multiSearch'];
    arsort($_SESSION['OCS']['multiSearch']);
}
//cas de la réinitialisation
if ($protectedPost['reset'] != "") {
    unset($_SESSION['OCS']['ID_REQ']);
    unset($_SESSION['OCS']['multiSearch']);
    unset($_SESSION['OCS']['DATA_CACHE'][$table_tabname]);
    unset($protectedPost);
}
if ($protectedPost['delfield'] != "") {
    unset($_SESSION['OCS']['multiSearch'][$protectedPost['delfield']]);
}
//	
//une recherche est demandée sur des critères
//pas d'utilisation de cache
//bouton de validation actionné
if ($protectedPost['Valid-search'] && $protectedPost['Valid'] != '') {
    unset($_SESSION['OCS']['SQL_DATA_FIXE']);
    unset($_SESSION['OCS']['ID_REQ']);
    $sqlRequest_Group = "";
    //on commence par décomposer tous les postes pour
    //définir les différentes tables, champs de recherche, valeur à rechercher
    $i = 0;
    //parcourt du tableau de POST
    foreach ($protectedPost as $key => $value) {
        //on récupère uniquement les POST qui nous intéressent
        if ($key != 'Valid-search' && $key != 'multiSearch') {
            //en fonction du nom de la variable, on arrive a savoir quel est la recherche demandée
            $valeur = explode("-", $key);
            if ($valeur[0] == "InputValue" || $valeur[0] == "SelFieldValue") {
                //en position 1 du tableau, on a toujours le nom de la table sur laquelle s'effectue la recherche
                $table[$i] = $valeur[1];
                //en position 2 du tableau, on a toujours le nom du champ sur lequel on effectue la recherche
                $field[$i] = $valeur[2];
                //en position 3 on a le numéro du champ.
                $fieldNumber[$i] = $valeur[3];
                //on récupère l'élément de comparaison
                $field_compar[$i] = $protectedPost["SelComp-" . $table[$i] . "-" . $field[$i] . "-" . $fieldNumber[$i]];

                //si le champ de saisi est à vide, on annule la recherche sur ce champ
                if ($value == '') {
                    unset($table[$i]);
                    unset($field[$i]);
                    unset($field_compar[$i]);
                    unset($fieldNumber[$i]);
                } else {
                    //sinon, on la prend en compte	
                    //en fonction de la valeur en position 0, on sait quel genre de recherche on doit effecuter
                    //si on a un SelComp, on récupère la valeur saisie
                    if ($valeur[0] == "InputValue" || $valeur[0] == "SelFieldValue") {
                        //case of checkbox
                        if (isset($accountinfo_checkbox)) {
                            if (in_array($field[$i], $accountinfo_checkbox)) {
                                $value = $value . "&&&";
                            }
                        }
                        $field_value[$i] = $value;

                        //on vérifie que le premier champ d'une recherche multicritère
                        //ou l'on a plusieur fois le même champ n'est pas vide
                        //ex:  3 * le champ IP ADD mais avec le premier champ vide.
                        //		on se retrouve donc avec un champ AND/OR sur le deuxième champ IP ADD
                        //		qu'il ne faut pas prendre en compte
                        if ($i != 0) {
                            $k = $i;
                            //on regarde si dans les champs précédent on a bien
                            //le même champ pour traiter le champ AND/OR
                            while ($k > 0) {
                                if ($table[$k] == $table[$i] && $field[$k] == $field[$i]) {
                                    $field_and_or[$i] = $protectedPost["SelAndOr-" . $table[$i] . "-" . $field[$i] . "-" . $fieldNumber[$i]];
                                }
                                $k--;
                            }
                        }
                        if (isset($protectedPost[$valeur[0] . "2-" . $table[$i] . "-" . $field[$i] . "-" . $fieldNumber[$i]]))
                            $field_value_complement[$i] = $protectedPost[$valeur[0] . "2-" . $table[$i] . "-" . $field[$i] . "-" . $fieldNumber[$i]];
                        elseif (isset($protectedPost["SelFieldValue3-" . $valeur[1] . "-" . $field[$i] . "-" . $fieldNumber[$i]])) {
                            $field_value_complement[$i] = $protectedPost["SelFieldValue3-" . $table[$i] . "-" . $field[$i] . "-" . $fieldNumber[$i]];
                        }
                    }
                    $i++;
                }
            }
        }
    }
    if ($_SESSION['OCS']['DEBUG'] == 'ON') {
        $debug = $l->g(5009) . "<br>";
        if (isset($table)) {
            foreach ($table as $key => $value)
                $debug .= $key . " => " . $value . "<br>";
        }
        $debug .= $l->g(5010) . "<br>";
        if (isset($field)) {
            foreach ($field as $key => $value)
                $debug .= $key . " => " . $value . "<br>";
        }
        $debug .= $l->g(5011) . "<br>";
        if (isset($field_compar)) {
            foreach ($field_compar as $key => $value)
                $debug .= $key . " => " . $value . "<br>";
        }
        $debug .= $l->g(5012) . "<br>";
        if (isset($field_value)) {
            foreach ($field_value as $key => $value)
                $debug .= $key . " => " . $value . "<br>";
        }
        $debug .= $l->g(5013) . "<br>";
        if (isset($field_value_complement)) {
            foreach ($field_value_complement as $key => $value)
                $debug .= $key . " => " . $value . "<br>";
        }
        $debug .= $l->g(5014) . "<br>";
        if (isset($field_and_or)) {
            foreach ($field_and_or as $key => $value) {
                if ($value != '')
                    $debug .= $key . " => " . $value . "<br>";
            }
        }
        if (isset($debug) && $debug != '')
            msg_info($debug);
    }
    $i = 0;
    //tableau des requêtes à executer
    //qui est contruit au fur et a mesure
    $sql_search = array();
    while ($table[$i]) {

        //initialisation de la variable des requêtes temporaires
        $sql_temp = "";
        if ($field_compar[$i] == "" && substr($field_value[$i], 0, 4) != "ALL_")
            $field_compar[$i] = "exact";
        //traitement du champ de comparaison
        switch ($field_compar[$i]) {
            case "exact":
                $field_compar[$i] = " = ";
                $field_value[$i] = "'" . $field_value[$i] . "'";
                break;
            case "ressemble":
                $field_compar[$i] = " like ";
                break;
            case "small":
                $field_compar[$i] = " <= ";
                break;
            case "tall":
                $field_compar[$i] = " >= ";
                break;
            case "diff":
                $field_compar[$i] = " like ";
                $field_compar_origine[$i] = "diff";
                //la gestion de diff est particulière
                //et nécessite plus de code (voir plus loin dans le code)
                break;
            case "diff_exact":
                $field_compar[$i] = " = ";
                $field_compar_origine[$i] = "diff";
                break;

            case "list":
                $field_compar[$i] = " IN ";
                $field_value[$i] = " (" . $field_value[$i] . ")";
                break;
            case "notlist":
                $field_compar[$i] = " NOT IN ";
                $field_value[$i] = " (" . $field_value[$i] . ")";
                break;
            default:
                $field_compar[$i] = " " . $field_compar[$i] . " ";
        }

        //Prise en compte des jockers sur le champ de saisie uniquement sur les champs de comparaison 'like'
        if ($field_compar[$i] == " like " && $table[$i] != "DEVICES" && $field[$i] != 'DOWNLOAD')
            $field_value[$i] = jockers_trait($field_value[$i]);
        //traitement d'un champ quand c'est une date
        $new_value = compair_with_date($field[$i], $field_value[$i]);
        $field[$i] = $new_value['field'];
        $field_value[$i] = $new_value['field_value'];
        //gestion de tous les linux et de tous les windows
        if (substr($field_value[$i], 0, 4) == "ALL_" && $field[$i] == "OSNAME") {
            if ($field_value[$i] == "ALL_LINUX") {
                $sql_temp = "select distinct osname from hardware where osname like '%Linux%'";
            } elseif ($field_value[$i] == "ALL_WIN")
                $sql_temp = "select distinct osname from hardware where osname like '%win%'";
            $result_temp = mysqli_query($_SESSION['OCS']["readServer"], $sql_temp);
            while ($val_temp = mysqli_fetch_array($result_temp)) {
                $list[] = addslashes($val_temp['osname']);
            }
            if (!isset($list)) {
                $ERROR = $l->g(955);
            } else {
                $field_compar[$i] = " IN ";
                $field_value[$i] = " ('" . implode("','", $list) . "')";
                $field_modif = "field_value";
            }
            unset($list);
        }
        //traitement du cas particulier des recherches sur la table DEVICES
        //le champs de de comparaison ne se fait pas sur $field_value[$i]
        //le champs $field_compar doit donc se reporter sur le champs complémentaire
        if ($table[$i] == "DEVICES") {
            $original_field = $field[$i];
            $original_field_value_complement = $field_value_complement[$i];
            $field_value_complement[$i] = $field_value[$i];
            $field[$i] = "NAME";
            $field_value[$i] = "'" . $original_field . "'";
            //traitement pour le télédéploiement		
            if ($field_value[$i] == "'DOWNLOAD'") {
                //on utilise pas le champ ivalue
                unset($ivalue);
                //requete pour trouver tous les ID 
                //dans ce cas, le champ de recherche doit etre à null
                if ($original_field_value_complement == $l->g(482))
                    $tvalue = " AND TVALUE IS NULL ";
                //gestion de TOUT SAUF SUCCESS
                elseif ($original_field_value_complement == "***" . $l->g(548) . "***")
                    $tvalue = " AND TVALUE not like 'SUC%' ";
                //gestion de Toutes les erreurs
                elseif ($original_field_value_complement == "***" . $l->g(956) . "***")
                    $tvalue = " AND (TVALUE like 'ERR%' or TVALUE like 'EXIT_CODE%')";
                //gestion de TOUS LES SUCCESS
                elseif ($original_field_value_complement == "***" . $l->g(957) . "***")
                    $tvalue = " AND TVALUE like 'SUC%' ";
                elseif ($original_field_value_complement == "***" . $l->g(509) . "***")
                    $tvalue = "";
                else
                    $tvalue = " AND TVALUE = '" . $original_field_value_complement . "'";
                //echo $field_value_complement[$i];
                //recherche des id activés de ce paquet
                $sql_temp = "select id from download_enable d_e left join download_available d_a on d_a.fileid=d_e.fileid where 1=1 ";
                IF ($_SESSION['OCS']['profile']->getRestriction('TELEDIFF_VISIBLE', 'YES') == "YES")
                    $sql_temp .= " and d_a.comment not like '%[VISIBLE=0]%'";
                if ($field_value_complement[$i] != "'NULL'" &&
                        $field_value_complement[$i] != "NULL")
                    $sql_temp .= " and d_e.fileid=" . $field_value_complement[$i];
                $result_temp = mysql2_query_secure($sql_temp, $_SESSION['OCS']["readServer"]);
                while ($val_temp = mysqli_fetch_array($result_temp)) {
                    $list[] = addslashes($val_temp['id']);
                }
                //echo $sql_temp;
                if (!isset($list)) {
                    $ERROR = $l->g(958);
                } else {
                    $field_value_complement[$i] = " IN ('" . implode("','", $list) . "')";
                    $field_modif = "field_value_complement";
                }

                unset($list);
            }//gestion de la configuration des fréquences
            elseif ($field_value[$i] == "'FREQUENCY'" || $field_value[$i] == "'IPDISCOVER'") {

                //on n'utilise pas le champs tvalue
                unset($tvalue);

                if (!strstr($field_value_complement[$i], 'DEFAULT')) {
                    if ($field_value_complement[$i] != "'PERSO'") //gestion des cas normaux
                        $field_value_complement[$i] = " = " . $field_value_complement[$i];
                    else //gestion des valeurs de fréquences personnalisées
                        $field_value_complement[$i] = " NOT IN ('0','-1')";
                }elseif (strstr($field_value_complement[$i], 'DEFAULT')) {
                    $type_default = explode('DEFAULT', $field_value_complement[$i]);
                    //si on demande la valeur DEFAULT de frequency,
                    //on se retrouve a rechercher les ID des machines
                    //dans la table hardware qui ne sont pas dans DEVICES avec
                    //comme name='FREQUENCY'
                    $sql_frequency = "select hardware_id from devices where name=" . $field_value[$i];
                    if (isset($type_default[1]) && $type_default[1] != "'")
                        $sql_frequency .= " and IVALUE = " . $type_default[1]{0};
                    $result_frequency = mysqli_query($_SESSION['OCS']["readServer"], $sql_frequency);
                    $list_frequency = "";
                    while ($val_frequency = mysqli_fetch_array($result_frequency)) {
                        $list_frequency .= $val_frequency['hardware_id'] . ',';
                    }
                    if ($list_frequency == "")
                        $list_frequency = "'' ";
                    //on vide le champ de comparaison 
                    //pour ne pas entrer dans la boucle de traitement
                    $field_compar[$i] = " NOT IN ";
                    //création de la fin de requête de recherche
                    $field_value[$i] = " (" . substr($list_frequency, 0, -1) . ")";
                    $field_modif = "field_value";
                    //la requete doit se faire sur la table hardware et sur le champ ID
                    $table[$i] = "HARDWARE";
                    $field[$i] = "ID";
                    $field_value_complement[$i] = "";
                }
            }
        }

        if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES']) && $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1 && $table[$i] == "SOFTWARES" && ($field[$i] == 'NAME' || $field[$i] == "VERSION")) {
            if ($field[$i] == 'NAME') {
                $table_explode = "type_softwares_name";
            } else {
                $table_explode = "type_softwares_version";
            }
            $sql_temp = "select name, id from %s where name %s '%s'";
            //A REVOIR POUR ENLEVER LES ' DEVANT LE CHAMP DE RECHERCHE
            $arg_temp = array($table_explode, $field_compar[$i], str_replace("'", "", $field_value[$i]));
            $result_temp = mysql2_query_secure($sql_temp, $_SESSION['OCS']["readServer"], $arg_temp);
            while ($val_temp = mysqli_fetch_array($result_temp)) {
                $list[] = $val_temp['id'];
                if ($limit_result_cache < count($list)) {
                    $ERROR = $l->g(959);
                    break;
                }
            }
            if (!isset($list)) {
                $ERROR = $l->g(960);
            } else {
                $field[$i] = $field[$i] . "_ID";
                $field_compar[$i] = " IN ";
                $field_value[$i] = " (" . implode(",", $list) . ")";
                $field_modif = "field_value";
                $sql_temp = generate_secure_sql($sql_temp, $arg_temp);
                unset($list);
            }
        } elseif (isset($table_cache)) {
            //si on est sur une table de cache
            if ($table_cache[$table[$i]]) {
                //on remet à zero le tableau de logiciels
                unset($list);
                //champ sur lequel s'effectue la recherche
                $field_temp = $field_cache[$table_cache[$table[$i]]];
                if ($field_temp == $field[$i]) {
                    $sql_temp = "select " . $field_temp . " as name ";
                    if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES'])
                            and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1)
                        $sql_temp .= ",id ";
                    $sql_temp .= " from " . strtolower($table_cache[$table[$i]]) . " where " . $field_temp . $field_compar[$i] . $field_value[$i];
                    $result_temp = mysqli_query($_SESSION['OCS']["readServer"], $sql_temp);
                    $count_result = 0;
                    while ($val_temp = mysqli_fetch_array($result_temp)) {
                        if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES'])
                                and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1) {
                            $list[] = $val_temp['id'];
                        } else
                            $list[] = addslashes($val_temp['name']);

                        if ($limit_result_cache > $count_result)
                            $count_result++;
                        else {
                            $ERROR = $l->g(959);
                            break;
                        }
                    }
                    if (!isset($list)) {
                        $ERROR = $l->g(960);
                    } else {
                        $field_compar[$i] = " IN ";
                        $field_value[$i] = " ('" . implode("','", $list) . "')";
                        $field_modif = "field_value";
                        unset($list);
                    }
                }
            }
        }
        //gestion du champ complémentaire en fonction de la table
        //si le champs complémentaire existe
        if (isset($field_value_complement[$i]) && $field_value_complement[$i] != "") {
            switch ($table[$i]) {
                case "HARDWARE":
                    //on est dans un cas de recherche entre 2 valeurs
                    $field[$i] = $field[$i];
                    $field_value_complement[$i] = "AND " . $field[$i] . " > '" . $field_value_complement[$i] . "'";
                    $field_compar[$i] = " < ";
                    break;
                case "DRIVES":
                    //cas des partitions linux
                    if (substr($field_value_complement[$i], 0, 1) == '/')
                        $field_value_complement[$i] = " AND VOLUMN = '" . $field_value_complement[$i] . "' ";
                    else
                        $field_value_complement[$i] = " AND LETTER = '" . $field_value_complement[$i] . "' ";
                    break;
                case "REGISTRY":
                    $field_value_complement[$i] = " AND NAME = '" . $field_value_complement[$i] . "' ";
                    break;
                case "DEVICES":
                    $field_value_complement[$i] = " AND IVALUE " . $field_value_complement[$i] . $tvalue;
                    break;
                default:
                    $ERROR = $l->g(5015) . $table[$i];
            }
        }


        if ($_SESSION['OCS']['DEBUG'] == 'ON') {
            msg_success($l->g(5016) . $table[$i] . "<br>" . $l->g(5017) . $field[$i] . "<br>" . $l->g(5018) . $field_compar[$i] . "<br>" . $l->g(5019) . $field_value[$i] . "<br>" . $l->g(5020) . $field_value_complement[$i] . "<br>" . $l->g(5021) . $field_and_or[$i]);
        }
        //si une erreur a été rencontrée
        //le traitement est arrêté (gain de temps)
        if (isset($ERROR)) {
            msg_error($ERROR);
            break;
        }
        //si on est dans le cas d'une recherche sur "différent",
        //on va créer les requêtes dans le tableau $sql_seach['DIFF']
        if ($field_compar_origine[$i] == "diff")
            $operation = "DIFF";
        else //autremant dans les autres cas, on va créer le tableau de requête dans $sql_seach['NORMAL']
            $operation = "NORMAL";
        //recherche du dernier index de la derniere requete sur la table
        if (isset($sql_seach[$operation][$table[$i]])) {
            foreach ($sql_seach[$operation][$table[$i]] as $index => $poub)
                $k = $index;
        } else
            $k = "";

        if ($field_and_or[$i] == "AND")
            $no_fusion = true;


        //gestion du champ AND OR dans les requetes
        if ($field_and_or[$i] == "" && $operation == "DIFF")
            $field_and_or[$i] = "OR";
        if ($field_and_or[$i] == "" && $operation == "NORMAL")
            $field_and_or[$i] = "AND";

        if ($field_and_or[$i] == "AND")
            $field_and_or[$i] = " ) AND ( ";
        else
            $field_and_or[$i] = " OR ";
        //gestion de la non fusion des requêtes pour les tables définies
        //si on n'est pas dans le cas de "AND/OR" (deux fois le même champ)
        if (in_array($table[$i], $tab_no_fusion) && ($field_and_or[$i] == "" || $no_fusion || !isset($sql_seach[$operation][$table[$i]]))) {
            unset($no_fusion);
            $traitement = generate_sql($table[$i]);
            $sql_seach[$operation][$table[$i]][$i] = $traitement['sql_temp'] . " ( " . $field[$i] . $field_compar[$i] . $field_value[$i] . $field_value_complement[$i];
            //si une requête intermédiaire a été jouée
            //il faut donc la prendre en compte pour la création des groupes
            $trait_cache = traitement_cache($sql_temp, $field_modif, $field_value[$i], $field_value_complement[$i]);
            $sql_cache[$operation][$table[$i]][$i] = $traitement['sql_cache'] . " ( " . $field[$i] . $field_compar[$i] . $trait_cache['field_value'] . $trait_cache['field_value_complement'];
        }//si on est dans le cas "AND/OR", on concat les requêtes
        elseif (in_array($table[$i], $tab_no_fusion) && $field_and_or[$i] != "" && isset($sql_seach[$operation][$table[$i]])) {
            $sql_seach[$operation][$table[$i]][$k] .= $field_and_or[$i] . $field[$i] . $field_compar[$i] . $field_value[$i] . $field_value_complement[$i];
            $trait_cache = traitement_cache($sql_temp, $field_modif, $field_value[$i], $field_value_complement[$i]);
            $sql_cache[$operation][$table[$i]][$k] .= $field_and_or[$i] . $field[$i] . $field_compar[$i] . $trait_cache['field_value'] . $trait_cache['field_value_complement'];
        }//si on est dans un cas normal, on fusionne toutes les requêtes
        else {
            //si la requête existe déjà
            if (isset($sql_seach[$operation][$table[$i]])) {
                //si le champ "AND/OR" est vide, on doit concat des champs différents de la même table
//				if ($field_and_or[$i] == "")
//					$field_and_or[$i]="AND";
                $sql_seach[$operation][$table[$i]][$k] .= $field_and_or[$i] . $field[$i] . $field_compar[$i] . $field_value[$i] . $field_value_complement[$i];
                $trait_cache = traitement_cache($sql_temp, $field_modif, $field_value[$i], $field_value_complement[$i]);
                $sql_cache[$operation][$table[$i]][$k] .= $field_and_or[$i] . $field[$i] . $field_compar[$i] . $trait_cache['field_value'] . $trait_cache['field_value_complement'];
            }//si la requête n'existe pas
            else {
                //on la crée	
                $traitement = generate_sql($table[$i]);
                $sql_seach[$operation][$table[$i]][$i] = $traitement['sql_temp'] . " ( " . $field[$i] . $field_compar[$i] . $field_value[$i] . $field_value_complement[$i];
                $trait_cache = traitement_cache($sql_temp, $field_modif, $field_value[$i], $field_value_complement[$i]);
                $sql_cache[$operation][$table[$i]][$i] = $traitement['sql_cache'] . " ( " . $field[$i] . $field_compar[$i] . $trait_cache['field_value'] . $trait_cache['field_value_complement'];
            }
        }
        //stockage de la table sur laquelle on requete
        //pour afficher les champs correspondant
        $list_tables_request[$table[$i]] = $table[$i];
        //si une erreur a été rencontrée
        //le traitement est arrêté (gain de temps)
//		if (isset($ERROR)){
//			echo "ATTENTION: ERREUR ".$ERROR;
//			break;
//		}
        $i++;
    }
    $list_id = "";
//traitement sur les requetes
//echo "<br><br>";
//		print_r_V2($sql_seach);
//		echo "<br><br>";
    //si un tableau de requête existe
    if (isset($sql_seach)) {
        //on commence par traiter le cas normal
        if (isset($sql_seach['NORMAL'])) {
            $execute_sql['NORMAL'] = class_weight($sql_seach['NORMAL']);
            $cache_sql['NORMAL'] = class_weight($sql_cache['NORMAL']);
        }
        if (isset($sql_seach['DIFF'])) {
            $execute_sql['DIFF'] = class_weight($sql_seach['DIFF']);
            $cache_sql['DIFF'] = class_weight($sql_cache['DIFF']);
        }
    }

    //execution des requêtes
    //si l'utilisateur a des droits limités
    //restriction des id 
    if ($_SESSION['OCS']['mesmachines'] != "" && isset($_SESSION['OCS']['mesmachines'])) {
        $list_id_restraint = substr(substr(computer_list_by_tag(), 1), 0, -1);
    }

    if ($_SESSION['OCS']['DEBUG'] == 'ON')
        $debug = '';
    if (isset($execute_sql['NORMAL'])) {
        $result = execute_sql_returnID($list_id_restraint, $execute_sql['NORMAL'], '', $table_tabname);
        if ($_SESSION['OCS']['DEBUG'] == 'ON') {
            $debug .= $l->g(5022) . "<br>" . $result['DEBUG'];
        }

        $list_id_norm = $result[0];
        if ($list_id_norm == "")
            $no_result = "YES";
        $tab_options = $result[1];
    }
    if (isset($execute_sql['DIFF']) && $no_result != "YES") {

        $result = execute_sql_returnID('', $execute_sql['DIFF'], 'NO_CUMUL', $table_tabname);

        if ($_SESSION['OCS']['DEBUG'] == 'ON') {
            $debug .= $l->g(5023) . "<br>" . $result['DEBUG'];
        }
        if ($result[0] != '')
            $list_id_diff = $result[0];
        else
            $list_id_diff[] = "'NO_DATA'";
    }

    if ($debug != '')
        msg_warning($debug);

    //pour le traitement des champs
    if ($list_id_diff != "") {
        $sql = "select distinct ID from hardware where ID NOT IN (" . implode(',', $list_id_diff) . ")";
        if ($list_id_norm != "") {
            $sql .= " AND ID IN (" . implode(',', $list_id_norm) . ")";
        } elseif ($list_id_restraint != "") {
            $sql .= " AND ID IN (" . $list_id_restraint . ")";
        }
        $result = mysqli_query($_SESSION['OCS']["readServer"], $sql) or mysqli_error($_SESSION['OCS']["readServer"]);
        while ($item = mysqli_fetch_object($result))
            $list_id[] = $item->ID;
    } else
        $list_id = $list_id_norm;

    $_SESSION['OCS']['ID_REQ'] = id_without_idgroups($list_id);
    $_SESSION['OCS']['list_tables_request'][$table_tabname] = $list_tables_request;
    //passage en SESSION des requêtes pour les groupes dynamiques
    sql_group_cache($cache_sql);
    //
    /* if (isset($where))
      $_SESSION['OCS']['WHERE_REQ']=$where;
      else
      unset($_SESSION['OCS']['WHERE_REQ']); */
}

//Utilisation du cache pour éviter de rejouer la recherche
if (($protectedPost['Valid-search'] && $protectedPost['Valid'] == '')) {
    //	print_r($_SESSION['OCS']['list_tables_request']);
    //recupération de la liste des ID
    $list_id = $_SESSION['OCS']['ID_REQ'];
    //récupération des tables touchées par les requetes
    $list_tables_request = $_SESSION['OCS']['list_tables_request'][$table_tabname];
}
//echo $list_id;
/* * ******************************************AFFICHAGE DES RESULTATS******************************************* */
if ($list_id != "") {
    $list_fields = array($l->g(652) . ': id' => 'h.ID',
        $l->g(652) . ': ' . $l->g(46) => 'h.LASTDATE',
        $l->g(652) . ": " . $l->g(820) => 'h.LASTCOME',
        'NAME' => 'h.name',
        //$l->g(23)=>'h.name',
        $l->g(652) . ": " . $l->g(24) => 'h.USERID',
        $l->g(652) . ": " . $l->g(25) => 'h.OSNAME',
        $l->g(652) . ": " . $l->g(357) => 'h.USERAGENT',
        $l->g(82) . ": " . $l->g(33) => 'h.WORKGROUP',
        $l->g(652) . ": " . $l->g(26) => 'h.MEMORY',
        $l->g(652) . ": " . $l->g(569) => 'h.PROCESSORS',
        $l->g(652) . ": " . $l->g(350) => "h.processort",
        $l->g(652) . ": " . $l->g(351) => "h.processorn",
        $l->g(652) . ": " . $l->g(353) => "h.quality",
        $l->g(652) . ": " . $l->g(34) => 'h.IPADDR',
        $l->g(652) . ": " . $l->g(53) => 'h.DESCRIPTION',
        $l->g(652) . ": " . $l->g(354) => 'h.FIDELITY',
        $l->g(652) . ": " . $l->g(351) => 'h.PROCESSORN',
        $l->g(652) . ": " . $l->g(350) => 'h.PROCESSORT',
        $l->g(652) . ": " . $l->g(50) => 'h.SWAP',
        $l->g(652) . ": " . $l->g(111) => 'h.WINPRODKEY',
        $l->g(652) . ": " . $l->g(553) => 'h.WINPRODID',
        $l->g(652) . ": " . $l->g(355) => "h.wincompany",
        $l->g(652) . ": " . $l->g(356) => "h.winowner",
        $l->g(652) . ": " . $l->g(557) => "h.userdomain",
        $l->g(25) . ": " . $l->g(286) => "h.OSCOMMENTS",
        $l->g(25) . ": " . $l->g(277) => "h.OSVERSION",
        $l->g(652) . ": " . $l->g(34) => "h.ipaddr",
        $l->g(652) . ": " . $l->g(1247) => 'h.ARCH');
    $list_fields = array_merge($list_fields_account_info, $list_fields);
    if (is_array($tab_options))
        $tab_options = array_merge($tab_options, $protectedPost);
    else
        $tab_options = $protectedPost;
    //BEGIN SHOW ACCOUNTINFO
    require_once('require/function_admininfo.php');
    $option_comment['comment_be'] = $l->g(1210) . " ";
    $tab_options['REPLACE_VALUE'] = replace_tag_value('', $option_comment);

    if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES'])
            and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1) {
        $tab_options['REPLACE_VALUE'][$l->g(847)] = found_soft_type("type_softwares_name");
        $tab_options['REPLACE_VALUE'][$l->g(848)] = found_soft_type("type_softwares_version");
    }

    //END SHOW ACCOUNTINFO
    $queryDetails = 'SELECT h.id ';
    $querycount = 'SELECT count(h.id) ';
    //changement de nom lors de la requete
    $tab_options['AS']['h.NAME'] = "name_of_machine";
    $query_add_table = "";
    foreach ($list_tables_request as $table_name_4_field) {
        if ($lbl_fields_calcul[$table_name_4_field]) {
            $list_fields = array_merge($list_fields, $lbl_fields_calcul[$table_name_4_field]);
            /* 				if ($table_name_4_field == "SOFTWARES" and isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES']) 
              and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1){

              $query_add_table.=" left join ".strtolower($table_name_4_field)." on h.id=".strtolower($table_name_4_field).".hardware_id ";

              }else */
            $query_add_table .= " left join " . strtolower($table_name_4_field) . " on h.id=" . strtolower($table_name_4_field) . ".hardware_id ";
        }
    }
//	$queryDetails=substr($queryDetails,0,-2);
    $queryDetails .= " from hardware h left join accountinfo a on h.id=a.hardware_id ";
    $queryDetails .= $query_add_table;
    $queryDetails .= " where ";
    $queryDetails .= "  h.deviceid <>'_SYSTEMGROUP_' AND h.deviceid <> '_DOWNLOADGROUP_' ";
    $queryDetails .= " and h.id in (" . implode(', ', $list_id) . ")  group by h.ID ";
    //if (isset($protectedPost['tri_TAB_MULTICRITERE']) && $protectedPost['tri_TAB_MULTICRITERE'] != '')
    // $queryDetails .= " order by ".$protectedPost['tri_'.$table_tabname]." ".$protectedPost['sens_'.$table_tabname];
    // $limit=nb_page();
    //$queryDetails .= " limit ".$protectedPost['page']*$protectedPost['pcparpage'].", 5";
    //echo "page=>".$protectedPost['page']."pcparpage=>".$protectedPost['pcparpage'];
    //echo $queryDetails;
    $querycount .= " from hardware h left join accountinfo a on h.id=a.hardware_id ";
    $querycount .= $query_add_table;
    $querycount .= " where ";
    $querycount .= "  h.deviceid <>'_SYSTEMGROUP_' AND h.deviceid <> '_DOWNLOADGROUP_' ";
    $querycount .= " and h.id in (" . implode(', ', $list_id) . ") group by h.ID ";

    /* $resultlistid = mysql2_query_secure($queryDetails, $_SESSION['OCS']["readServer"]);
      while($item = mysqli_fetch_object($resultlistid)){
      $list_id_test[]=$item->id;
      } */

    $queryDetails = "SELECT ";
    foreach ($list_fields as $key => $value) {
        $queryDetails .= $value;
        if ($tab_options['AS'][$value])
            $queryDetails .= " as " . $tab_options['AS'][$value];
        $queryDetails .= ", ";
    }
    $queryDetails = substr($queryDetails, 0, -2);
    $queryDetails .= " from hardware h left join accountinfo a on h.id=a.hardware_id ";
    $queryDetails .= $query_add_table;
    $queryDetails .= " where h.id in ";
    $queryDetails = mysql2_prepare($queryDetails, array(), $list_id, true);
    $queryDetails['SQL'] .= " group by h.ID ";
    $tab_options['ARG_SQL'] = $queryDetails['ARG'];
    $tab_options['SQL_COUNT'] = $querycount;
    if ($_SESSION['OCS']['profile']->getConfigValue('DELETE_COMPUTERS') == "YES")
        $list_fields['SUP'] = 'h.ID';

    $list_fields['CHECK'] = 'h.ID';
    $list_col_cant_del = array('SUP' => 'SUP', 'NAME' => 'NAME', 'CHECK' => 'CHECK');
    $default_fields = array($delfault_tag => $delfault_tag,
        $l->g(652) . ': ' . $l->g(46) => $l->g(652) . ': ' . $l->g(46),
        $l->g(652) . ": " . $l->g(820) => $l->g(652) . ": " . $l->g(820),
        $l->g(23) => $l->g(23),
        $l->g(652) . ": " . $l->g(24) => $l->g(652) . ": " . $l->g(24),
        $l->g(652) . ": " . $l->g(25) => $l->g(652) . ": " . $l->g(25),
        $l->g(652) . ": " . $l->g(357) => $l->g(652) . ": " . $l->g(357),
        'SUP' => 'SUP', 'CHECK' => 'CHECK');
    //print_r($list_fields);
    //on modifie le type de champs en numéric de certain champs
    //pour que le tri se fasse correctement
    //$tab_options['TRI']['SIGNED']['a.TAG']="a.TAG";
    //choix des fonctionnalitées pour les utilisateurs 
    $list_fonct["image/groups_search.png"] = $l->g(583);
    if ($_SESSION['OCS']['profile']->getConfigValue('DELETE_COMPUTERS') == "YES") {
        $list_fonct["image/delete.png"] = $l->g(122);
        $list_pag["image/delete.png"] = $pages_refs["ms_custom_sup"];
        $tab_options['LBL_POPUP']['SUP'] = 'name';
    }
    $list_fonct["image/cadena_ferme.png"] = $l->g(1019);
    $list_fonct["image/mass_affect.png"] = $l->g(430);
    if ($_SESSION['OCS']['profile']->getConfigValue('CONFIG') == "YES") {
        $list_fonct["image/config_search.png"] = $l->g(107);
        $list_pag["image/config_search.png"] = $pages_refs['ms_custom_param'];
    }
    if ($_SESSION['OCS']['profile']->getConfigValue('TELEDIFF') == "YES") {
        $list_fonct["image/tele_search.png"] = $l->g(428);
        $list_pag["image/tele_search.png"] = $pages_refs["ms_custom_pack"];
    }
    $list_pag["image/groups_search.png"] = $pages_refs["ms_custom_groups"];

    $list_pag["image/cadena_ferme.png"] = $pages_refs["ms_custom_lock"];
    $list_pag["image/mass_affect.png"] = $pages_refs["ms_custom_tag"];
    //activation des LOGS	
    $tab_options['LOGS'] = 'SEARCH_RESULT';
    $tab_options['form_name'] = $form_name;
    $tab_options['table_name'] = $table_tabname;
    ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
    add_trait_select($list_fonct, $list_id, $form_name, $list_pag);
    echo "<input type='hidden' value='" . $protectedPost['Valid-search'] . "' name='Valid-search'>";
} elseif ($protectedPost['Valid-search'] != '')
    $no_result = "NO RESULT";
if ($no_result == "NO RESULT" and ! isset($ERROR)) {
    //choix des fonctionnalitées pour les utilisateurs 
    $list_fonct["image/groups_search.png"] = $l->g(583);
    $list_pag["image/groups_search.png"] = $pages_refs["ms_custom_groups"];
    add_trait_select($list_fonct, $list_id, $form_name, $list_pag);
    msg_warning($l->g(42));
}
if ($_SESSION['OCS']["mesmachines"] != '')
    $list_id_computer = computer_list_by_tag();

//pour tous les tableaux:
//TABLE-NOMCHAMP =>lbl du champ
//option: TABLE-NOMCHAMP-LBL => commentaire à ajouter après le champ de saisi
//composotion du tableau
// option: TABLE-NOMCHAMP-SELECT =>array des valeurs du champ select ou requete sql (affichage du select)
// si option absente le select affiche array('exact'=> 'EXACTEMENT','ressemble'=>'RESSEMBLE','diff'=>'DIFFERENT')
//a l'affichage on se retrouve avec le lbl du champ,un select et un champ de saisi
$sort_list = array("NETWORKS-IPADDRESS" => $l->g(82) . ": " . $l->g(34),
    "NETWORKS-MACADDR" => $l->g(82) . ": " . $l->g(95),
    "SOFTWARES-NAME" => $l->g(20) . ": " . $l->g(49),
    "SOFTWARES-VERSION" => $l->g(20) . ": " . $l->g(277),
    "SOFTWARES-BITSWIDTH" => $l->g(20) . ": " . $l->g(1247),
    "SOFTWARES-PUBLISHER" => $l->g(20) . ": " . $l->g(69),
    "SOFTWARES-COMMENTS" => $l->g(20) . ": " . $l->g(51),
    "HARDWARE-DESCRIPTION" => $l->g(25) . ": " . $l->g(53),
    "HARDWARE-USERDOMAIN" => $l->g(82) . ": " . $l->g(557),
    "BIOS-BVERSION" => $l->g(273) . ": " . $l->g(209),
    "HARDWARE-USERID" => $l->g(243) . ": " . $l->g(49),
    "HARDWARE-OSCOMMENTS" => $l->g(25) . ": " . $l->g(51),
    "HARDWARE-OSVERSION" => $l->g(25) . ": " . $l->g(277),
    "NETWORKS-IPGATEWAY" => $l->g(82) . ": " . $l->g(207),
    "NETWORKS-IPSUBNET" => $l->g(82) . ": " . $l->g(331),
    "NETWORKS-IPDHCP" => $l->g(82) . ": " . $l->g(281),
    "BIOS-SSN" => $l->g(273) . ": " . $l->g(36),
    "BIOS-SMODEL" => $l->g(273) . ": " . $l->g(65),
    "HARDWARE-NAME" => $l->g(729) . ": " . $l->g(49),
    "HARDWARE-PROCESSORT" => $l->g(54) . " (old): " . $l->g(66),
    "BIOS-SMANUFACTURER" => $l->g(729) . ": " . $l->g(64),
    "MONITORS-SERIAL" => $l->g(554),
    "MONITORS-DESCRIPTION" => $l->g(556),
    "MONITORS-MANUFACTURER" => $l->g(555),
    "DRIVES-VOLUMN" => $l->g(92) . ": " . $l->g(964),
    "BIOS-BMANUFACTURER" => $l->g(273) . ": " . $l->g(284),
    "BIOS-BVERSION" => $l->g(273) . ": " . $l->g(277),
    "BIOS-ASSETTAG" => $l->g(273) . ": " . $l->g(216),
    "HARDWARE-LASTDATE" => "OCS: " . $l->g(46),
    "HARDWARE-LASTCOME" => "OCS: " . $l->g(820),
    "HARDWARE-WORKGROUP" => $l->g(82) . ": " . $l->g(33),
    "STORAGES-NAME" => $l->g(63) . ": " . $l->g(49),
    "STORAGES-SERIALNUMBER" => $l->g(63) . ": " . $l->g(36),
    "STORAGES-DISKSIZE" => $l->g(63) . ": " . $l->g(67),
    "PRINTERS-NAME" => $l->g(79) . ": " . $l->g(49),
    "PRINTERS-DRIVER" => $l->g(79) . ": " . $l->g(278),
    "PRINTERS-PORT" => $l->g(79) . ": " . $l->g(279),
    "PRINTERS-DESCRIPTION" => $l->g(79) . ": " . $l->g(53),
    "PRINTERS-SERVERNAME" => $l->g(79) . ": " . $l->g(1323),
    "PRINTERS-SHARENAME" => $l->g(79) . ": " . $l->g(1324),
    "PRINTERS-RESOLUTION" => $l->g(79) . ": " . $l->g(1325),
    "PRINTERS-COMMENT" => $l->g(79) . ": " . $l->g(51),
    "HARDWARE-ARCH" => $l->g(25) . ": " . $l->g(1247),
    "CPUS-MANUFACTURER" => $l->g(54) . ": " . $l->g(64),
    "CPUS-TYPE" => $l->g(54) . ": " . $l->g(66),
    "CPUS-SERIALNUMBER" => $l->g(54) . ": " . $l->g(36),
    "CPUS-SPEED" => $l->g(54) . ": " . $l->g(429),
    "CPUS-CORES" => $l->g(54) . ": " . $l->g(1317),
    "CPUS-L2CACHESIZE" => $l->g(54) . ": " . $l->g(1318),
    "CPUS-LOGICAL_CPUS" => $l->g(54) . ": " . $l->g(1314),
    "CPUS-VOLTAGE" => $l->g(54) . ": " . $l->g(1319),
    "CPUS-CURRENT_SPEED" => $l->g(54) . ": " . $l->g(1315),
    "CPUS-SOCKET" => $l->g(54) . ": " . $l->g(1316)
);


$optSelectField = array("NETWORKS-IPADDRESS" => $sort_list["NETWORKS-IPADDRESS"],
    "NETWORKS-MACADDR" => $sort_list["NETWORKS-MACADDR"], //$l->g(82).": ".$l->g(95),
    "SOFTWARES-NAME" => $sort_list["SOFTWARES-NAME"], //$l->g(20).": ".$l->g(49),
    "SOFTWARES-VERSION" => $sort_list["SOFTWARES-VERSION"], //$l->g(20).": ".$l->g(277),
    "SOFTWARES-BITSWIDTH" => $sort_list["SOFTWARES-BITSWIDTH"],
    "SOFTWARES-PUBLISHER" => $sort_list["SOFTWARES-PUBLISHER"],
    "SOFTWARES-COMMENTS" => $sort_list["SOFTWARES-COMMENTS"],
    "HARDWARE-DESCRIPTION" => $sort_list["HARDWARE-DESCRIPTION"], //$l->g(25).": ".$l->g(53),
    "HARDWARE-USERDOMAIN" => $sort_list["HARDWARE-USERDOMAIN"], //$l->g(82).": ".$l->g(557),
    "BIOS-BVERSION" => $sort_list["BIOS-BVERSION"], //$l->g(273).": ".$l->g(209),
    "HARDWARE-USERID" => $sort_list["HARDWARE-USERID"], //$l->g(243).": ".$l->g(49),
    "HARDWARE-OSCOMMENTS" => $sort_list["HARDWARE-OSCOMMENTS"], //$l->g(25).": ".$l->g(51),
    "HARDWARE-OSVERSION" => $sort_list["HARDWARE-OSVERSION"],
    "NETWORKS-IPGATEWAY" => $sort_list["NETWORKS-IPGATEWAY"], //$l->g(82).": ".$l->g(207),
    "NETWORKS-IPSUBNET" => $sort_list["NETWORKS-IPSUBNET"], //$l->g(82).": ".$l->g(331),
    "NETWORKS-IPDHCP" => $sort_list["NETWORKS-IPDHCP"], //$l->g(82).": ".$l->g(281),
    "BIOS-SSN" => $sort_list["BIOS-SSN"], //$l->g(273).": ".$l->g(36),
    "BIOS-SMODEL" => $sort_list["BIOS-SMODEL"], //$l->g(273).": ".$l->g(65),
    "HARDWARE-NAME" => $sort_list["HARDWARE-NAME"], //$l->g(729).": ".$l->g(49),
    "HARDWARE-PROCESSORT" => $sort_list["HARDWARE-PROCESSORT"], //$l->g(54).": ".$l->g(66),
    "BIOS-SMANUFACTURER" => $sort_list["BIOS-SMANUFACTURER"], //$l->g(729).": ".$l->g(64),
    "MONITORS-SERIAL" => $sort_list["MONITORS-SERIAL"], //$l->g(554),
    "MONITORS-DESCRIPTION" => $sort_list["MONITORS-DESCRIPTION"], //$l->g(556),
    "MONITORS-MANUFACTURER" => $sort_list["MONITORS-MANUFACTURER"], //$l->g(555),
    "DRIVES-VOLUMN" => $sort_list["DRIVES-VOLUMN"], //$l->g(92).": ".$l->g(964),
    "BIOS-BMANUFACTURER" => $sort_list["BIOS-BMANUFACTURER"], //$l->g(273).": ".$l->g(284),
    "BIOS-BVERSION" => $sort_list["BIOS-BVERSION"], //$l->g(273).": ".$l->g(277),
    "BIOS-ASSETTAG" => $sort_list["BIOS-ASSETTAG"], //$l->g(273).": ".$l->g(277),
    "PRINTERS-NAME" => $sort_list["PRINTERS-NAME"],
    "PRINTERS-DRIVER" => $sort_list["PRINTERS-DRIVER"],
    "PRINTERS-PORT" => $sort_list["PRINTERS-PORT"],
    "PRINTERS-DESCRIPTION" => $sort_list['PRINTERS-DESCRIPTION'],
    "PRINTERS-SERVERNAME" => $sort_list["PRINTERS-SERVERNAME"],
    "PRINTERS-SHARENAME" => $sort_list["PRINTERS-SHARENAME"],
    "PRINTERS-RESOLUTION" => $sort_list["PRINTERS-RESOLUTION"],
    "PRINTERS-COMMENT" => $sort_list["PRINTERS-COMMENT"],
    "HARDWARE-ARCH" => $l->g(25) . ": " . $l->g(1247),
    "HARDWARE-LASTDATE" => $sort_list["HARDWARE-LASTDATE"], //"OCS: ".$l->g(46),
    "HARDWARE-LASTDATE-LBL" => "calendar",
    "HARDWARE-LASTDATE-SELECT" => array("small" => $l->g(346), "tall" => $l->g(347)),
    "HARDWARE-LASTCOME" => $sort_list["HARDWARE-LASTCOME"], //"OCS: ".$l->g(820),
    "HARDWARE-LASTCOME-LBL" => "calendar",
    "HARDWARE-LASTCOME-SELECT" => array("small" => $l->g(346), "tall" => $l->g(347)),
    "HARDWARE-WORKGROUP" => $sort_list["HARDWARE-WORKGROUP"],
    "STORAGES-NAME" => $sort_list["STORAGES-NAME"],
    "STORAGES-SERIALNUMBER" => $sort_list["STORAGES-SERIALNUMBER"],
    "STORAGES-DISKSIZE" => $sort_list["STORAGES-DISKSIZE"],
    "STORAGES-DISKSIZE-SELECT" => array("exact" => $l->g(410), "small" => $l->g(201), "tall" => $l->g(202)),
    "STORAGES-DISKSIZE-LBL" => "MB",
    "HARDWARE-ARCH" => $sort_list["HARDWARE-ARCH"],
    "CPUS-MANUFACTURER" => $sort_list["CPUS-MANUFACTURER"],
    "CPUS-SERIALNUMBER" => $sort_list["CPUS-SERIALNUMBER"],
    "CPUS-SOCKET" => $sort_list["CPUS-SOCKET"],
    "CPUS-TYPE" => $sort_list["CPUS-TYPE"],
    "CPUS-SPEED" => $sort_list["CPUS-SPEED"],
    "CPUS-SPEED-SELECT" => array("exact" => $l->g(410), "small" => $l->g(201), "tall" => $l->g(202)),
    "CPUS-SPEED-LBL" => "MHz",
    "CPUS-CORES" => $sort_list["CPUS-CORES"],
    "CPUS-CORES-SELECT" => array("exact" => $l->g(410), "small" => $l->g(201), "tall" => $l->g(202)),
    "CPUS-L2CACHESIZE" => $sort_list["CPUS-L2CACHESIZE"],
    "CPUS-L2CACHESIZE-SELECT" => array("exact" => $l->g(410), "small" => $l->g(201), "tall" => $l->g(202)),
    "CPUS-LOGICAL_CPUS" => $sort_list["CPUS-LOGICAL_CPUS"],
    "CPUS-LOGICAL_CPUS-SELECT" => array("exact" => $l->g(410), "small" => $l->g(201), "tall" => $l->g(202)),
    "CPUS-CURRENT_SPEED" => $sort_list["CPUS-CURRENT_SPEED"],
    "CPUS-CURRENT_SPEED-SELECT" => array("exact" => $l->g(410), "small" => $l->g(201), "tall" => $l->g(202)),
    "CPUS-CURRENT_SPEED-LBL" => "MHz",
    "CPUS-VOLTAGE" => $sort_list["CPUS-VOLTAGE"],
    "CPUS-VOLTAGE-SELECT" => array("exact" => $l->g(410), "small" => $l->g(201), "tall" => $l->g(202)),
);
//ajout des champs de accountinfo
$optSelectField = array_merge($optSelectField_account, $optSelectField);
//composotion du tableau
// TABLE-NOMCHAMP-SQL1 => requete avec les champs ID (option) et NAME. Peut également être un tableau de données
//à l'affichage on se retrouve avec le lbl du champ et un select
$sort_list_Select = array("HARDWARE-OSNAME" => $l->g(729) . ": " . $l->g(25),
    "VIDEOS-RESOLUTION" => $l->g(62));
if ($_SESSION['OCS']['usecache'] == '1')
    $table_hardware = 'hardware_osname_cache';
else
    $table_hardware = 'hardware';

$optSelect = array("HARDWARE-OSNAME" => $sort_list_Select["HARDWARE-OSNAME"], //$l->g(729).": ".$l->g(25),
    "HARDWARE-OSNAME-SQL1" => "select 'ALL_LINUX' as ID, '" . $l->g(1202) . "' as NAME union select 'ALL_WIN', '" . $l->g(1203) . "' union select OSNAME,OSNAME from " . $table_hardware . " where osname != '' ",
    "VIDEOS-RESOLUTION" => $sort_list_Select["VIDEOS-RESOLUTION"], //$l->g(965).": ".$l->g(62),
    "VIDEOS-RESOLUTION-SQL1" => "select DISTINCT RESOLUTION as 'ID', RESOLUTION as 'NAME' from videos " . (isset($list_id_computer) ? " where hardware_id in " . $list_id_computer : '') . " order by 1");
//composotion du tableau
//option : TABLE-NOMCHAMP-SELECT =>array des valeurs du champ select ou requete sql (1er select)
// TABLE-NOMCHAMP-SQL1 => requete avec les champs ID (option) et NAME. Peut également être un tableau de données (2eme select)
//à l'affichage on se retrouve avec  le lbl du champ, 2 select et un champ de saisi
$sort_list_2SelectField = array("REGISTRY-REGVALUE" => $l->g(211) . ": " . $l->g(212),
    "DRIVES-FREE" => $l->g(92) . ": " . $l->g(45));
$opt2SelectField = array("REGISTRY-REGVALUE" => $sort_list_2SelectField["REGISTRY-REGVALUE"], //$l->g(211).": ".$l->g(212),
    "REGISTRY-REGVALUE-SQL1" => "select NAME from registry_name_cache order by 1",
    "REGISTRY-REGVALUE-LBL" => "calendar",
    "REGISTRY-REGVALUE-SELECT" => array('exact' => $l->g(410), 'ressemble' => $l->g(129),
        'diff' => $l->g(130),
        "small" => $l->g(346), "tall" => $l->g(347)),
    "DRIVES-FREE" => $sort_list_2SelectField["DRIVES-FREE"], //$l->g(92).": ".$l->g(45),
    "DRIVES-FREE-SQL1" => "select distinct LETTER from drives where letter != '' " . (isset($list_id_computer) ? " and hardware_id in " . $list_id_computer : '') . "
									 union select distinct volumn from drives where letter = '' and volumn != '' " . (isset($list_id_computer) ? " and hardware_id in " . $list_id_computer : '') . " order by 1",
    "DRIVES-FREE-LBL" => "MB",
    "DRIVES-FREE-SELECT" => array('exact' => $l->g(410), "small" => $l->g(201), "tall" => $l->g(202)));
//composotion du tableau
//option : TABLE-NOMCHAMP-SELECT =>array des valeurs du champ select ou requete sql (1er select)
// TABLE-NOMCHAMP-SQL1 => requete avec les champs ID (option) et NAME. Peut également être un tableau de données (2eme select)
//à l'affichage on se retrouve avec le lbl du champ et 2 select
$sort_list_2Select = array("HARDWARE-USERAGENT" => "OCS: " . $l->g(966),
    "DEVICES-IPDISCOVER" => $l->g(107) . ": " . $l->g(312),
    "DEVICES-FREQUENCY" => $l->g(107) . ": " . $l->g(429),
    "GROUPS_CACHE-GROUP_ID" => $l->g(583) . ": " . $l->g(49),
    "DOWNLOAD_HISTORY-PKG_ID" => $l->g(512) . ": " . $l->g(969),
    "STORAGES-TYPE" => $l->g(63) . ": " . $l->g(66),
    "STORAGES-DESCRIPTION" => $l->g(63) . ": " . $l->g(53),
    "STORAGES-MODEL" => $l->g(63) . ": " . $l->g(65),
    "BIOS-TYPE" => $l->g(273) . ": " . $l->g(66),
    "CPUS-CPUARCH" => $l->g(54) . ": " . $l->g(1247),
    "CPUS-DATA_WIDTH" => $l->g(54) . ": " . $l->g(1312),
    "CPUS-CURRENT_ADDRESS_WIDTH" => $l->g(54) . ": " . $l->g(1313),
);
$sql_history_download = "select FILEID as ID,NAME from download_available d_a";
if ($_SESSION['OCS']['profile']->getRestriction('TELEDIFF_VISIBLE', 'YES') == "YES")
    $sql_history_download .= " where d_a.comment not like '%[VISIBLE=0]%'";
$sql_history_download .= " order by 2";
$opt2Select = array("HARDWARE-USERAGENT" => $sort_list_2Select["HARDWARE-USERAGENT"], //"OCS: ".$l->g(966),
    "HARDWARE-USERAGENT-SQL1" => "select distinct USERAGENT as 'NAME' from hardware where USERAGENT != '' " . (isset($list_id_computer) ? " and id in " . $list_id_computer : '') . " order by 1",
    "HARDWARE-USERAGENT-SELECT" => array('exact' => $l->g(410)
        , 'diff' => $l->g(130)
    ),
    "DEVICES-IPDISCOVER" => $sort_list_2Select["DEVICES-IPDISCOVER"], //$l->g(107).": ".$l->g(312),
    "DEVICES-IPDISCOVER-SQL1" => array("1" => $l->g(502), "2" => $l->g(503), "0" => $l->g(506), "DEFAULT1" => $l->g(504), "DEFAULT0" => $l->g(505)),
    "DEVICES-IPDISCOVER-SELECT" => array('exact' => $l->g(410)
        , 'diff' => $l->g(130)
    ),
    "DEVICES-FREQUENCY" => $sort_list_2Select["DEVICES-FREQUENCY"], //$l->g(107).": ".$l->g(429),
    "DEVICES-FREQUENCY-SQL1" => array("0" => $l->g(485), "DEFAULT" => $l->g(488), "-1" => $l->g(486), "PERSO" => $l->g(487)),
    "DEVICES-FREQUENCY-SELECT" => array('exact' => $l->g(410)
        , 'diff' => $l->g(130)
    ),
    "GROUPS_CACHE-GROUP_ID" => $sort_list_2Select["GROUPS_CACHE-GROUP_ID"], //$l->g(583).": ".$l->g(49),
    "GROUPS_CACHE-GROUP_ID-SQL1" => "select ID,NAME from hardware where deviceid = '_SYSTEMGROUP_' order by 2",
    "GROUPS_CACHE-GROUP_ID-SELECT" => array('exact' => $l->g(967)
        , 'diff_exact' => $l->g(968)
    ),
    "DOWNLOAD_HISTORY-PKG_ID" => $sort_list_2Select["DOWNLOAD_HISTORY-PKG_ID"], //$l->g(512).": ".$l->g(969),
    "DOWNLOAD_HISTORY-PKG_ID-SQL1" => $sql_history_download,
    "DOWNLOAD_HISTORY-PKG_ID-SELECT" => array('exact' => $l->g(507)
        , 'diff' => $l->g(508)
    ),
    "STORAGES-TYPE" => $sort_list_2Select["STORAGES-TYPE"], //$l->g(512).": ".$l->g(969),
    "STORAGES-TYPE-SQL1" => "select distinct type as ID,type as NAME from storages order by 2",
    "STORAGES-TYPE-SELECT" => array('exact' => $l->g(507)
        , 'diff' => $l->g(508)
    ),
    "STORAGES-DESCRIPTION" => $sort_list_2Select["STORAGES-DESCRIPTION"], //$l->g(512).": ".$l->g(969),
    "STORAGES-DESCRIPTION-SQL1" => "select distinct description as ID,description as NAME from storages order by 2",
    "STORAGES-DESCRIPTION-SELECT" => array('exact' => $l->g(507)
        , 'diff' => $l->g(508)
    ),
    "STORAGES-MODEL" => $sort_list_2Select["STORAGES-MODEL"], //$l->g(512).": ".$l->g(969),
    "STORAGES-MODEL-SQL1" => "select distinct MODEL as ID,MODEL as NAME from storages order by 2",
    "STORAGES-MODEL-SELECT" => array('exact' => $l->g(507)
        , 'diff' => $l->g(508)
    ),
    "BIOS-TYPE" => $sort_list_2Select["BIOS-TYPE"], //$l->g(273).": ".$l->g(66),
    "BIOS-TYPE-SQL1" => "select distinct TYPE as ID,TYPE as NAME from bios order by 2",
    "BIOS-TYPE-SELECT" => array('exact' => $l->g(507)
        , 'diff' => $l->g(508)
    ),
    "CPUS-CPUARCH" => $sort_list_2Select["CPUS-CPUARCH"], //$l->g(107).": ".$l->g(312),
    "CPUS-CPUARCH-SQL1" => array("32" => "32bits", "64" => "64bits", "128" => "128bits"),
    "CPUS-CPUARCH-SELECT" => array('exact' => $l->g(410)
        , 'diff' => $l->g(130)
    ),
    "CPUS-DATA_WIDTH" => $sort_list_2Select["CPUS-DATA_WIDTH"], //$l->g(107).": ".$l->g(312),
    "CPUS-DATA_WIDTH-SQL1" => array("32" => "32bits", "64" => "64bits", "128" => "128bits"),
    "CPUS-DATA_WIDTH-SELECT" => array('exact' => $l->g(410)
        , 'diff' => $l->g(130)
    ),
    "CPUS-CURRENT_ADDRESS_WIDTH" => $sort_list_2Select["CPUS-CURRENT_ADDRESS_WIDTH"], //$l->g(107).": ".$l->g(312),
    "CPUS-CURRENT_ADDRESS_WIDTH-SQL1" => array("32" => "32bits", "64" => "64bits"),
    "CPUS-CURRENT_ADDRESS_WIDTH-SELECT" => array('exact' => $l->g(410)
        , 'diff' => $l->g(130)
    ),
);
//ajout des champs de accountinfo
$opt2Select = array_merge($opt2Select_account, $opt2Select);
//à l'affichage on se retrouve avec  le lbl du champ, un select et deux champs de saisi
//option : TABLE-NOMCHAMP-SELECT =>array des valeurs du champ select ( select)
//ATTENTION: le deuxième champ de saisi est invisible. Pour le rendre visible, faire passer
//un javascript dans le lbl_default avec genre: onclick='document.getElementById(\"between-field_name\").style.display=\"block\";'
//la valeur "field_name" est ensuite transformé par le vrai nom de champ
$lbl_default = array('exact' => $l->g(410), 'ressemble' => $l->g(129)
    , 'diff' => $l->g(130)
    , 'small' => $l->g(201), 'tall' => $l->g(202), 'between' => $l->g(203),
    'javascript' => array('exact' => "onclick='document.getElementById(\"FieldInput2-field_name\").style.display=\"none\";'",
        'ressemble' => "onclick='document.getElementById(\"FieldInput2-field_name\").style.display=\"none\";'",
        'diff' => "onclick='document.getElementById(\"FieldInput2-field_name\").style.display=\"none\";'",
        'small' => "onclick='document.getElementById(\"FieldInput2-field_name\").style.display=\"none\";'",
        'tall' => "onclick='document.getElementById(\"FieldInput2-field_name\").style.display=\"none\";'",
        'between' => "onclick='document.getElementById(\"FieldInput2-field_name\").style.display=\"inline\";'"));
$sort_list_Select2Field = array("HARDWARE-MEMORY" => $l->g(25) . ": " . $l->g(26),
    "HARDWARE-PROCESSORS" => $l->g(54) . " (old): " . $l->g(377));
$optSelect2Field = array("HARDWARE-MEMORY" => $sort_list_Select2Field["HARDWARE-MEMORY"], //$l->g(25).": ".$l->g(26),
    "HARDWARE-MEMORY-LBL" => "MB",
    "HARDWARE-MEMORY-SELECT" => $lbl_default,
    "HARDWARE-PROCESSORS" => $sort_list_Select2Field["HARDWARE-PROCESSORS"], //$l->g(54).": ".$l->g(377),
    "HARDWARE-PROCESSORS-LBL" => "MHz",
    "HARDWARE-PROCESSORS-SELECT" => $lbl_default);
//composotion du tableau
//option : TABLE-NOMCHAMP-SELECT =>array des valeurs du champ select ou requete sql (1er select)
// TABLE-NOMCHAMP-SQL1 => requete avec les champs ID (option) et NAME. Peut également être un tableau de données (2eme select)
// TABLE-NOMCHAMP-SQL2 => requete avec les champs ID (option) et NAME. Peut également être un tableau de données (3eme select)
//à l'affichage on se retrouve avec  le lbl du champ et 3 select
$sort_list_3Select = array("DEVICES-DOWNLOAD" => $l->g(512) . ": " . $l->g(970));
$sql_download = "select 'NULL' as 'ID', '***" . $l->g(509) . "***' as NAME
				union select FILEID as ID,NAME from download_available d_a ";
IF ($_SESSION['OCS']['profile']->getRestriction('TELEDIFF_VISIBLE', 'YES') == "YES")
    $sql_download .= " where d_a.comment not like '%[VISIBLE=0]%' and DELETED = 0";
$sql_download .= " order by 2";
$opt3Select = array("DEVICES-DOWNLOAD" => $sort_list_3Select["DEVICES-DOWNLOAD"], //$l->g(512).": ".$l->g(970),
    "DEVICES-DOWNLOAD-SQL1" => $sql_download,
    "DEVICES-DOWNLOAD-SQL2" => "select '***" . $l->g(509) . "***' as 'NAME' union select '***" . $l->g(548) . "***' union select '***" . $l->g(956) . "***' union select '***" . $l->g(957) . "***' union select '" . $l->g(482) . "' union select distinct TVALUE from devices where name='DOWNLOAD' and tvalue!='' order by 1",
    "DEVICES-DOWNLOAD-SELECT" => array('exact' => $l->g(507), 'diff' => $l->g(508))
);
$optArray = array_merge($optSelectField, $opt2SelectField, $opt2Select, $optSelect, $optSelect2Field, $opt3Select);
$sort_list = array_merge($sort_accountinfo, $sort_list, $sort_list_3Select, $sort_list_Select2Field, $sort_list_2Select, $sort_list_2SelectField, $sort_list_Select);
$countHl++;
$optArray_trait[$l->g(32)] = "... " . $l->g(32) . " ...";
foreach ($sort_list as $key => $value) {
    $optArray_trait[$key] = ucfirst($value);
    $countHl++;
}
asort($optArray_trait);

$protectedPost['multiSearch'] = $l->g(32);
?>
<div class="row">
    <div class="col col-md-6 col-md-offset-3">
        <?php
        formGroup('select', 'multiSearch', $l->g(31), '', '', '', '', $optArray_trait, $optArray_trait, "onchange='document.multisearch.submit();'");
        ?>
    </div>
</div>
<?php
$aff_field_search .= "<img src='image/delete-small.png' onclick='pag(\"ok\",\"reset\",\"" . $form_name . "\");' alt='" . $l->g(41) . "' style='margin-left:20px'>";
echo "<div class='col col-md-12'>";
echo "<div class='field'>" . $aff_field_search . "</div>";
if (isset($_SESSION['OCS']['multiSearch']) && $_SESSION['OCS']['multiSearch'] != null) {

    $c = 0;
    foreach ($_SESSION['OCS']['multiSearch'] as $k => $v) {
        if (!isset($alreadyExist[$v])) {
            $alreadyExist[$v] = 'YES';
            $ajout = '';
        } else
            $ajout = $v;
        $color = $c % 2 == 0 ? "#F2F2F2" : "#FFFFFF";
        show_ligne($v, $color, $k, $ajout, $form_name);
        $c++;
    }
    echo "<div class='form-buttons'><input type='submit' name='Valid-search' value='" . $l->g(30) . "' onclick='garde_valeur(\"VALID\",\"Valid\");'></div>";
    echo "<input type=hidden name='Valid' id='Valid' value=''>";
}
echo "<input type=hidden name=delfield id=delfield value=''>";
echo "<input type=hidden name='reset' id='reset' value=''>";
echo "</div>";
echo close_form();
echo $l->g(358);
if ($ajax) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails['SQL'], $tab_options);
}
