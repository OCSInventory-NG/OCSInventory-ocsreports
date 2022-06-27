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
    $tab_options = $protectedPost;
}

/*
 * Software category
 */
require_once('require/softwares/SoftwareCategory.php');
$softCat = new SoftwareCategory();

//If RESET
if (isset($protectedPost['RESET'])) {
    unset($protectedPost['NAME_RESTRICT']);
    unset($protectedPost['NBRE']);
    unset($protectedPost['CLASS']);
    unset($protectedPost['COMPAR']);
}

//If SUBMIT
if (isset($protectedPost['SUBMIT_FORM'])) {
    $tab_options['CACHE'] = 'RESET';
}

$sql_fin['SQL'] = "";
$sql_fin['ARG'] = array();

if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES']) && $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1) {
    $info_name_soft = array("table" => "type_softwares_name", "field" => "name", "field_name_soft" => 'name_id');
} else {
    $info_name_soft = array("table" => "n", "field" => "name", "field_name_soft" => 'name');
}

$field_name_soft = $info_name_soft['table'] . "." . $info_name_soft['field'];

//Filter software
if (is_defined($protectedPost['NBRE']) && is_defined($protectedPost['COMPAR'])) {
    $sql_fin['SQL'] = " HAVING nb %s %s ";

    switch ($protectedPost['COMPAR']) {
        case "lt":
            $compar = "<";
            break;
        case "gt":
            $compar = ">";
            break;
        case "eq":
            $compar = "=";
            break;
        default:
            break;
    }
    $sql_fin['ARG'] = array($compar, $protectedPost['NBRE']);
}

if (is_defined($protectedPost['NAME_RESTRICT'])) {
  if (is_defined($protectedPost['NBRE']) && is_defined($protectedPost['COMPAR'])) {
    $sql_fin['SQL'] .= " AND " . $field_name_soft . " like '%s' ";
    $sql_fin['ARG'] = $softCat->array_merge_values($sql_fin['ARG'], array('%' . $protectedPost['NAME_RESTRICT'] . '%'));
  }else{
    $sql_fin['SQL'] .= " HAVING " . $field_name_soft . " like '%s' ";
    $sql_fin['ARG'] = array('%' . $protectedPost['NAME_RESTRICT'] . '%');
  }
}

//form name
$form_name = 'all_soft';
//form open
echo open_form($form_name, '', '', 'form-horizontal');

$list_cat = $softCat->onglet_cat();
$first_onglet = $list_cat['first_onglet'] ?? '';
$categorie_id = $list_cat['category_name'] ?? '';
$os = $list_cat['OS'] ?? '';

//definition of onglet
$def_onglets['ALL'] = $l->g(765); //Category list.
$def_onglets['WITHOUT'] = $l->g(1516); //Category list.
for($i=1; isset($list_cat[$i]); $i++){
  $def_onglets[$list_cat['category_name'][$list_cat[$i]]] = $list_cat[$i];
}

//default => first onglet
if (isset($protectedGet['onglet']) && !isset($protectedPost['old_onglet'])){
    $protectedPost['onglet'] = $protectedGet['onglet'];
}
if (empty($protectedPost['onglet'])) {
    $protectedPost['onglet'] = "ALL";
}

//show first lign of onglet
if($i < 11){
  show_tabs($def_onglets,$form_name,"onglet",true, $i);
}

if ($i >= 11) {
    echo "<div class='col col-md-2'>";
    echo show_modif($def_onglets, 'onglet', 2, $form_name) . "</div>";
}
echo '<div class="col col-md-10" >';

if (is_defined($protectedPost['NAME_RESTRICT']) || is_defined($protectedPost['NBRE'])) {
    msg_warning($l->g(767));
}

/****************************************** ALL SOFTWARE ******************************************/
if($protectedPost['onglet'] == "ALL"){
    $sql['SQL'] = ' SELECT n.NAME, p.PUBLISHER, v.VERSION, sl.IDENTIFIER as id, sc.CATEGORY_NAME, sl.COUNT as nb 
                    FROM software_link sl 
                    LEFT JOIN software_name n ON sl.NAME_ID = n.ID 
                    LEFT JOIN software_publisher p ON sl.PUBLISHER_ID = p.ID 
                    LEFT JOIN software_version v ON sl.VERSION_ID = v.ID
                    LEFT JOIN software_categories sc ON sl.CATEGORY_ID = sc.ID ';

    //If restriction
    if (is_defined($_SESSION['OCS']["mesmachines"])) {
        $sql['SQL'] .= "LEFT JOIN software s ON s.NAME_ID = sl.NAME_ID AND s.VERSION_ID = sl.VERSION_ID AND s.PUBLISHER_ID = sl.PUBLISHER_ID 
                        LEFT JOIN accountinfo AS a ON a.HARDWARE_ID = s.HARDWARE_ID 
                        WHERE ".$_SESSION['OCS']["mesmachines"];
    }

    if (isset($sql)) {
        if ($sql_fin['SQL'] != '') {
            $sql['SQL'] .= $sql_fin['SQL'];
            $sql['ARG'] =  $sql_fin['ARG'];
        }

        $list_fields = array($l->g(69) => 'p.PUBLISHER',
            'name' => 'n.NAME',
            $l->g(7003) => 'v.VERSION',
            $l->g(388) => 'sc.CATEGORY_NAME',
        );

        if(!is_defined($_SESSION['OCS']["mesmachines"])) {
            $list_fields['nbre'] = 'nb';
            $tab_options['LIEN_LBL']['nbre'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_multi_search'] . '&prov=allsoft&value=';
            $tab_options['LIEN_CHAMP']['nbre'] = 'id';
        } else {
            $tab_options['LIEN_LBL']['name'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_multi_search'] . '&prov=allsoft&value=';
            $tab_options['LIEN_CHAMP']['name'] = 'id';
        }

        $default_fields = $list_fields;
        $list_col_cant_del = $default_fields;
        $tab_options['LBL']['name'] = $l->g(847);
        $tab_options['LBL']['nbre'] = $l->g(1120);
        $tab_options['ARG_SQL'] = $sql['ARG'] ?? '';
        $tab_options['form_name'] = $form_name;
        $tab_options['table_name'] = $form_name;
        $result_exist = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
    }
}

/****************************************** ALL SOFTWARE WITH CATEGORY (EXCEPT DEFAULT)******************************************/
elseif($protectedPost['onglet'] == "WITHOUT") {
    $champs = array('DEFAULT_CATEGORY' => 'DEFAULT_CATEGORY');
    $values = look_config_default_values($champs);

    $sql['SQL'] = ' SELECT n.NAME, p.PUBLISHER, v.VERSION, sl.IDENTIFIER as id, sc.CATEGORY_NAME, sl.COUNT as nb
                    FROM software_link sl
                    LEFT JOIN software_name n ON sl.NAME_ID = n.ID
                    LEFT JOIN software_publisher p ON sl.PUBLISHER_ID = p.ID
                    LEFT JOIN software_version v ON sl.VERSION_ID = v.ID
                    LEFT JOIN software_categories_link scl ON scl.NAME_ID = sl.NAME_ID AND scl.VERSION_ID = sl.VERSION_ID AND scl.PUBLISHER_ID = sl.PUBLISHER_ID
                    LEFT JOIN software_categories sc ON scl.CATEGORY_ID = sc.ID ';

    //If restriction
    if(is_defined($_SESSION['OCS']["mesmachines"])) {
        $sql['SQL'] .= "LEFT JOIN software s ON s.NAME_ID = sl.NAME_ID AND s.VERSION_ID = sl.VERSION_ID AND s.PUBLISHER_ID = sl.PUBLISHER_ID
                        LEFT JOIN accountinfo AS a ON a.HARDWARE_ID = s.HARDWARE_ID 
                        WHERE ".$_SESSION['OCS']["mesmachines"]." AND scl.CATEGORY_ID != %s";
    } else {
        $sql['SQL'] .= ' WHERE scl.CATEGORY_ID != %s';
    }

    $sql['ARG'] = array($values['ivalue']['DEFAULT_CATEGORY']);
    if (isset($sql)) {
        if ($sql_fin['SQL'] != '') {
            $sql['SQL'] .= $sql_fin['SQL'];
            $sql['ARG'] = $softCat->array_merge_values($sql['ARG'], $sql_fin['ARG']);
        }
        $list_fields = array($l->g(69) => 'p.PUBLISHER',
            'name' => 'n.NAME',
            $l->g(7003) => 'v.VERSION',
            $l->g(388) => 'sc.CATEGORY_NAME',
        );

        if(!is_defined($_SESSION['OCS']["mesmachines"])) {
            $list_fields['nbre'] = 'nb';
            $tab_options['LIEN_LBL']['nbre'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_multi_search'] . '&prov=allsoft&value=';
            $tab_options['LIEN_CHAMP']['nbre'] = 'id';
        } else {
            $tab_options['LIEN_LBL']['name'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_multi_search'] . '&prov=allsoft&value=';
            $tab_options['LIEN_CHAMP']['name'] = 'id';
        }

        $default_fields = $list_fields;
        $list_col_cant_del = $default_fields;
        $tab_options['LBL']['name'] = $l->g(847);
        $tab_options['LBL']['nbre'] = $l->g(1120);
        $tab_options['ARG_SQL'] = $sql['ARG'];
        $tab_options['form_name'] = $form_name;
        $tab_options['table_name'] = $form_name;
        $result = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
   }
}

/****************************************** SOFTWARE PER CATEGORY ******************************************/
else {
    $sql['SQL'] = ' SELECT n.NAME, p.PUBLISHER, v.VERSION, sl.IDENTIFIER as id, sc.CATEGORY_NAME, sl.COUNT as nb 
                    FROM software_link sl 
                    LEFT JOIN software_name n ON sl.NAME_ID = n.ID 
                    LEFT JOIN software_publisher p ON sl.PUBLISHER_ID = p.ID 
                    LEFT JOIN software_version v ON sl.VERSION_ID = v.ID
                    LEFT JOIN software_categories_link scl ON scl.NAME_ID = sl.NAME_ID AND scl.VERSION_ID = sl.VERSION_ID AND scl.PUBLISHER_ID = sl.PUBLISHER_ID
                    LEFT JOIN software_categories sc ON scl.CATEGORY_ID = sc.ID ';

    //If restriction
    if (is_defined($_SESSION['OCS']["mesmachines"])) {
        $sql['SQL'] .= "LEFT JOIN software s ON s.NAME_ID = sl.NAME_ID AND s.VERSION_ID = sl.VERSION_ID AND s.PUBLISHER_ID = sl.PUBLISHER_ID
                        LEFT JOIN accountinfo AS a ON a.HARDWARE_ID = s.HARDWARE_ID 
                        WHERE ".$_SESSION['OCS']["mesmachines"]." AND scl.CATEGORY_ID = %s";
    } else {
        $sql['SQL'] .= ' WHERE scl.CATEGORY_ID = %s';
    }

    $sql['ARG'] = array($protectedPost['onglet']);
    if (isset($sql)) {;
        if ($sql_fin['SQL'] != '') {
            $sql['SQL'] .= $sql_fin['SQL'];
            $sql['ARG'] = $softCat->array_merge_values($sql['ARG'], $sql_fin['ARG']);
        }
        $list_fields = array($l->g(69) => 'p.PUBLISHER',
            'name' => 'NAME',
            $l->g(7003) => 'v.VERSION',
            $l->g(388) => 'sc.CATEGORY_NAME',
        );

        if(!is_defined($_SESSION['OCS']["mesmachines"])) {
            $list_fields['nbre'] = 'nb';
            $tab_options['LIEN_LBL']['nbre'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_multi_search'] . '&prov=allsoft&value=';
            $tab_options['LIEN_CHAMP']['nbre'] = 'id';
        } else {
            $tab_options['LIEN_LBL']['name'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_multi_search'] . '&prov=allsoft&value=';
            $tab_options['LIEN_CHAMP']['name'] = 'id';
        }

        $default_fields = $list_fields;
        $list_col_cant_del = $default_fields;
        $tab_options['LBL']['name'] = $l->g(847);
        $tab_options['LBL']['nbre'] = $l->g(1120);
        $tab_options['ARG_SQL'] = $sql['ARG'];
        $tab_options['form_name'] = $form_name;
        $tab_options['table_name'] = $form_name;
        $result = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
    }
}

/****************************************** FILTER ******************************************/
$options_compar = [
    "lt" => "&lt;",
    "gt" => "&gt;",
    "eq" => "=",
];

echo "<button type='button' data-toggle='collapse' data-target='#filter' class='btn'>" . $l->g(735) . "</button>";

echo "<div id='filter' class='collapse'>";
echo "<br/>";
formGroup('text', 'NAME_RESTRICT', $l->g(382), 20, 100, $protectedPost['NAME_RESTRICT']);

echo '<div class="form-group">
        <label class="control-label col-sm-2" for="COMPAR">'.$l->g(381).'</label>
        <div class="col-sm-1">
        <select name="COMPAR" id="COMPAR" class="form-control">
            <option value=""></option>';
            foreach ($options_compar as $key => $value){
                if(isset($protectedPost['COMPAR']) && $key == $protectedPost['COMPAR']){
                    echo '<option value="'.$key.'" selected>'.$value.'</option>';
                }else{
                    echo '<option value="'.$key.'">'.$value.'</option>';
                }
            }
echo '</select>
    </div>
    <div class="col-sm-2">
        <input name="NBRE" type="text" class="form-control" maxlength="100" value="'.$protectedPost['NBRE'].'">
    </div>
</div>

<input type="submit" class="btn btn-success" value="'.$l->g(393).'" name="SUBMIT_FORM">
<input type="submit" class="btn btn-danger" value="'.$l->g(396).'" name="RESET">';

echo "</div>";

echo "</div>";
echo close_form();

// Prevents searching in some columns (Allows using full-text search by default)
$tab_options['NO_SEARCH']['nb'] = 'nb';
$tab_options['NO_SEARCH']['sc.CATEGORY_NAME'] = 'sc.CATEGORY_NAME';
$tab_options['NO_SEARCH']['s.VERSION'] = 'v.VERSION';
$tab_options['NO_SEARCH']['id'] = 'id';

// Find out which visible columns are full-text indexed in the DB, and add this information to $tab_options
$ft_idx = dbGetFTIndex('software', 's');
if(isset($tab_options['visible_col']) && is_array($tab_options['visible_col'])) {
    foreach($tab_options['visible_col'] as $column) {
        $cname = $tab_options['columns'][$column]['name'];
        if (!empty($ft_idx[$cname])) {
            $tab_options['columns'][$column]['ft_index'] = 'true';
        } else {
            $tab_options['columns'][$column]['ft_index'] = 'false';
        }
    }
}

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $sql['SQL'], $tab_options);
}
?>
