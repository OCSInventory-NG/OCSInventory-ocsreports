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

/**
 * All Software
 */
require_once('require/softwares/AllSoftware.php');
$allSoft = new AllSoftware();

// If RESET
if (isset($protectedPost['RESET'])) {
    unset($protectedPost['NAME_RESTRICT']);
    unset($protectedPost['NBRE']);
    unset($protectedPost['CLASS']);
    unset($protectedPost['COMPAR']);
    unset($protectedPost['OS']);
    unset($protectedPost['GROUP']);
    unset($protectedPost['TAG']);
    unset($protectedPost['ASSET']);
    unset($protectedPost['SUBMIT_FORM_RESTRICT']);
    unset($_SESSION['OCS']['AllSoftware']['filter']['csv_data']);
    unset($_FILES['csv_file']);
}

// If SUBMIT
if (isset($protectedPost['SUBMIT_FORM'])) {
    $tab_options['CACHE'] = 'RESET';
}

// Initialize filter empty value 
$filters   = null;
$sqlFilter = null;

if(is_defined($protectedPost['NAME_RESTRICT']) && trim($protectedPost['NAME_RESTRICT']) != "") {
    $filters['NAME_RESTRICT'] = $protectedPost['NAME_RESTRICT'];
}  
if(is_defined($protectedPost['NBRE']) && is_defined($protectedPost['COMPAR'])) {
    $filters['NBRE'] = $protectedPost['NBRE'];
    $filters['COMPAR'] = $protectedPost['COMPAR'];
}
if(is_defined($protectedPost['OS']) && $protectedPost['OS'] != "0") {
    $filters['OS'] = $protectedPost['OS'];
}
if(is_defined($protectedPost['GROUP']) && $protectedPost['GROUP'] != "0") {
    $filters['GROUP'] = $protectedPost['GROUP'];
}
if(is_defined($protectedPost['TAG']) && $protectedPost['TAG'] != "0") {
    $filters['TAG'] = $protectedPost['TAG'];
}
if(is_defined($protectedPost['ASSET']) && $protectedPost['ASSET'] != "0") {
    $filters['ASSET'] = $protectedPost['ASSET'];
}
if(is_defined($_FILES['csv_file'])) {
    $allSoft->verifyCsv($_FILES['csv_file']);
}
if (is_defined($_SESSION['OCS']['AllSoftware']['filter']['csv_data'])) {
    $filters['CSV'] = $_SESSION['OCS']['AllSoftware']['filter']['csv_data']['result'];
}
if(is_defined($protectedPost['SUBMIT_FORM_RESTRICT']) && $protectedPost['SUBMIT_FORM_RESTRICT'] == "yes") {
    $filters['SUBMIT_FORM_RESTRICT'] = true;
}

$sqlFilter = $allSoft->generateQueryFilter($filters);

//form name
$form_name = 'all_soft';
//form open
echo open_form($form_name, '', 'enctype="multipart/form-data"', 'form-horizontal');

$list_cat = $softCat->onglet_cat();
$first_onglet = $list_cat['first_onglet'] ?? '';
$categorie_id = $list_cat['category_name'] ?? '';
$os = $list_cat['OS'] ?? '';

//definition of onglet
$def_onglets['ALL'] = $l->g(765); //Category list.

// Check if default category is configured
$champs = array('DEFAULT_CATEGORY' => 'DEFAULT_CATEGORY');
$values = look_config_default_values($champs);

// If defined default category add onglet
if(!is_null($values['ivalue']['DEFAULT_CATEGORY'])) {
    $def_onglets['WITHOUT'] = $l->g(1516); //Category list.
}

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

if (is_defined($protectedPost['NAME_RESTRICT']) || 
    is_defined($protectedPost['NBRE']) || 
    is_defined($protectedPost['OS']) || 
    is_defined($protectedPost['GROUP']) || 
    is_defined($protectedPost['TAG']) ||
    is_defined($protectedPost['ASSET']) ||
    is_defined($_SESSION['OCS']['AllSoftware']['filter']['csv_data'])) {
    if(is_defined($_SESSION['OCS']['AllSoftware']['filter']['csv_data'])) {
        $msg = $l->g(767)." ".$l->g(1520);
    } else {
        $msg = $l->g(767);
    }
    msg_warning($msg);
}
if (is_defined($_SESSION['OCS']['AllSoftware']['filter']['csv_data']['missing'])) {
    $txt = $l->g(1519);
    $txt .= "<ul>";
    foreach ($_SESSION['OCS']['AllSoftware']['filter']['csv_data']['missing'] as $key => $value) {
        $txt .= "<li>";
        $txt .= $value . "\n";
        $txt .= "<li>";
    }
    $txt .= "</ul>";
    msg_error($txt);
}

/****************************************** ALL SOFTWARE ******************************************/
if($protectedPost['onglet'] == "ALL"){
    if(!is_defined($sqlFilter['SELECT'])) {
        $sql['SQL'] = ' SELECT n.NAME, p.PUBLISHER, v.VERSION, sl.IDENTIFIER as id, sc.CATEGORY_NAME, sl.COUNT as nb ';
                
        if (isset($protectedPost['SUBMIT_FORM_RESTRICT']) && $protectedPost['SUBMIT_FORM_RESTRICT'] == "yes") {
            $sql['SQL'] .= ', COUNT(DISTINCT s.HARDWARE_ID) as nb2 ';
        }

        $sql['SQL'] .= 'FROM software_link sl 
                LEFT JOIN software_name n ON sl.NAME_ID = n.ID 
                LEFT JOIN software_publisher p ON sl.PUBLISHER_ID = p.ID 
                LEFT JOIN software_version v ON sl.VERSION_ID = v.ID
                LEFT JOIN software_categories sc ON sl.CATEGORY_ID = sc.ID ';

        //If restriction
        if (is_defined($_SESSION['OCS']["mesmachines"])) {
            $sql['SQL'] .= "LEFT JOIN software s ON s.NAME_ID = sl.NAME_ID AND s.VERSION_ID = sl.VERSION_ID AND s.PUBLISHER_ID = sl.PUBLISHER_ID 
                    LEFT JOIN accountinfo AS a ON a.HARDWARE_ID = s.HARDWARE_ID 
                    WHERE ".$_SESSION['OCS']["mesmachines"]." ";
                
            if (isset($protectedPost['SUBMIT_FORM_RESTRICT']) && $protectedPost['SUBMIT_FORM_RESTRICT'] == "yes" && !is_defined($sqlFilter['GROUPBY'])) {
                $sql['SQL'] .= "GROUP BY id ";
            }
        }

        if(is_defined($sqlFilter['HAVING'])) {
            $sql['SQL'] .= $sqlFilter['HAVING'];
        }
    } else {
        $sql['SQL'] = $sqlFilter['SELECT'].$sqlFilter['FROM'].$sqlFilter['WHERE'].$sqlFilter['GROUPBY'];

        if(is_defined($sqlFilter['HAVING'])) {
            $sql['SQL'] .= $sqlFilter['HAVING'];
        }
    }

    if (isset($sql)) {
        $list_fields = array(
            $l->g(69) => 'p.PUBLISHER',
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

            if (isset($protectedPost['SUBMIT_FORM_RESTRICT']) && $protectedPost['SUBMIT_FORM_RESTRICT'] == "yes") {
                $list_fields['nbre'] = 'nb2';
                $tab_options['LIEN_LBL']['nbre'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_multi_search'] . '&prov=allsoft&value=';
                $tab_options['LIEN_CHAMP']['nbre'] = 'id';
            }
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
    if(!is_defined($sqlFilter['SELECT'])) {
        $sql['SQL'] = ' SELECT n.NAME, p.PUBLISHER, v.VERSION, sl.IDENTIFIER as id, sc.CATEGORY_NAME, sl.COUNT as nb ';

        if (isset($protectedPost['SUBMIT_FORM_RESTRICT']) && $protectedPost['SUBMIT_FORM_RESTRICT'] == "yes") {
            $sql['SQL'] .= ', COUNT(DISTINCT s.HARDWARE_ID) as nb2 ';
        }
                
        $sql['SQL'] .= 'FROM software_link sl
                LEFT JOIN software_name n ON sl.NAME_ID = n.ID
                LEFT JOIN software_publisher p ON sl.PUBLISHER_ID = p.ID
                LEFT JOIN software_version v ON sl.VERSION_ID = v.ID
                LEFT JOIN software_categories_link scl ON scl.NAME_ID = sl.NAME_ID AND scl.VERSION_ID = sl.VERSION_ID AND scl.PUBLISHER_ID = sl.PUBLISHER_ID
                LEFT JOIN software_categories sc ON scl.CATEGORY_ID = sc.ID ';

        //If restriction
        if(is_defined($_SESSION['OCS']["mesmachines"])) {
            $sql['SQL'] .= "LEFT JOIN software s ON s.NAME_ID = sl.NAME_ID AND s.VERSION_ID = sl.VERSION_ID AND s.PUBLISHER_ID = sl.PUBLISHER_ID
                    LEFT JOIN accountinfo AS a ON a.HARDWARE_ID = s.HARDWARE_ID 
                    WHERE ".$_SESSION['OCS']["mesmachines"]." AND scl.CATEGORY_ID != %s ";

            if (isset($protectedPost['SUBMIT_FORM_RESTRICT']) && $protectedPost['SUBMIT_FORM_RESTRICT'] == "yes" && !is_defined($sqlFilter['GROUPBY'])) {
                $sql['SQL'] .= "GROUP BY id ";
            }
        } else {
            $sql['SQL'] .= ' WHERE scl.CATEGORY_ID != %s ';
        }

        if(is_defined($sqlFilter['HAVING'])) {
            $sql['SQL'] .= $sqlFilter['HAVING'];
        }
    } else {
        $sql['SQL'] = $sqlFilter['SELECT'].$sqlFilter['FROM'].$sqlFilter['WHERE']."AND cl.CATEGORY_ID != %s ".$sqlFilter['GROUPBY'];

        if(is_defined($sqlFilter['HAVING'])) {
            $sql['SQL'] .= $sqlFilter['HAVING'];
        }
    }
    
    $sql['ARG'] = array($values['ivalue']['DEFAULT_CATEGORY']);

    if (isset($sql)) {
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

            if (isset($protectedPost['SUBMIT_FORM_RESTRICT']) && $protectedPost['SUBMIT_FORM_RESTRICT'] == "yes") {
                $list_fields['nbre'] = 'nb2';
                $tab_options['LIEN_LBL']['nbre'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_multi_search'] . '&prov=allsoft&value=';
                $tab_options['LIEN_CHAMP']['nbre'] = 'id';
            }
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
    if(!is_defined($sqlFilter['SELECT'])) {
        $sql['SQL'] = ' SELECT n.NAME, p.PUBLISHER, v.VERSION, sl.IDENTIFIER as id, sc.CATEGORY_NAME, sl.COUNT as nb ';

        if (isset($protectedPost['SUBMIT_FORM_RESTRICT']) && $protectedPost['SUBMIT_FORM_RESTRICT'] == "yes") {
            $sql['SQL'] .= ', COUNT(DISTINCT s.HARDWARE_ID) as nb2 ';
        }

        $sql['SQL'] .= 'FROM software_link sl 
                LEFT JOIN software_name n ON sl.NAME_ID = n.ID 
                LEFT JOIN software_publisher p ON sl.PUBLISHER_ID = p.ID 
                LEFT JOIN software_version v ON sl.VERSION_ID = v.ID
                LEFT JOIN software_categories_link scl ON scl.NAME_ID = sl.NAME_ID AND scl.VERSION_ID = sl.VERSION_ID AND scl.PUBLISHER_ID = sl.PUBLISHER_ID
                LEFT JOIN software_categories sc ON scl.CATEGORY_ID = sc.ID ';

        //If restriction
        if (is_defined($_SESSION['OCS']["mesmachines"])) {
            $sql['SQL'] .= "LEFT JOIN software s ON s.NAME_ID = sl.NAME_ID AND s.VERSION_ID = sl.VERSION_ID AND s.PUBLISHER_ID = sl.PUBLISHER_ID
                        LEFT JOIN accountinfo AS a ON a.HARDWARE_ID = s.HARDWARE_ID 
                        WHERE ".$_SESSION['OCS']["mesmachines"]." AND scl.CATEGORY_ID = %s ";
            
            if (isset($protectedPost['SUBMIT_FORM_RESTRICT']) && $protectedPost['SUBMIT_FORM_RESTRICT'] == "yes" && !is_defined($sqlFilter['GROUPBY'])) {
                $sql['SQL'] .= "GROUP BY id ";
            }  
        } else {
            $sql['SQL'] .= 'WHERE scl.CATEGORY_ID = %s ';
        }

        if(is_defined($sqlFilter['HAVING'])) {
            $sql['SQL'] .= $sqlFilter['HAVING'];
        }
    } else {
        $sql['SQL'] = $sqlFilter['SELECT'].$sqlFilter['FROM'].$sqlFilter['WHERE']."AND cl.CATEGORY_ID = %s ".$sqlFilter['GROUPBY'];

        if(is_defined($sqlFilter['HAVING'])) {
            $sql['SQL'] .= $sqlFilter['HAVING'];
        }
    }
    
    $sql['ARG'] = array($protectedPost['onglet']);

    if (isset($sql)) {;
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

            if (isset($protectedPost['SUBMIT_FORM_RESTRICT']) && $protectedPost['SUBMIT_FORM_RESTRICT'] == "yes") {
                $list_fields['nbre'] = 'nb2';
                $tab_options['LIEN_LBL']['nbre'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_multi_search'] . '&prov=allsoft&value=';
                $tab_options['LIEN_CHAMP']['nbre'] = 'id';
            }
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

$os     = $allSoft->getOperatingSystemList();
$group  = $allSoft->getGroupList();
$tag    = $allSoft->getTagList();
$asset  = $allSoft->getAssetCategoryList();

echo "<button type='button' data-toggle='collapse' data-target='#filter' class='btn'>" . $l->g(735) . "</button>";

echo "<div id='filter' class='collapse'>";
echo "<br/>";
formGroup('text', 'NAME_RESTRICT', $l->g(382), 20, 100, $protectedPost['NAME_RESTRICT']);

echo '<div class="form-group">
        <label class="control-label col-sm-2" for="COMPAR">'.$l->g(381).'</label>
        <div class="col-sm-1">
        <select name="COMPAR" id="COMPAR" class="form-control">
            <option value="">-----</option>';
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
        <input name="NBRE" type="number" class="form-control" maxlength="100" value="'.$protectedPost['NBRE'].'">
    </div>
</div>';

// FILTER OS/GROUP/TAG/ASSET

// OS
echo '<div class="form-group">';
echo "<label class='control-label col-sm-2' for='OS'>".$l->g(25)."</label>";
echo "<div class='col-sm-3'>";
echo "<select name='OS' id='OS' class='form-control'>";
foreach($os as $key => $name) {
    if(isset($protectedPost['OS']) && $protectedPost['OS'] == $key) {
        echo "<option value='".$key."' selected>".$name."</option>";
    } else {
        echo "<option value='".$key."'>".$name."</option>";
    }
}
echo "</select>";
echo "</div>";


// GROUP
echo "<label class='control-label col-sm-2' for='GROUP'>".$l->g(583)."</label>";
echo "<div class='col-sm-3'>";
echo "<select name='GROUP' id='GROUP' class='form-control'>";
foreach($group as $key => $name) {
    if(isset($protectedPost['GROUP']) && $protectedPost['GROUP'] == $key) {
        echo "<option value='".$key."' selected>".$name."</option>";
    } else {
        echo "<option value='".$key."'>".$name."</option>";
    }
}
echo "</select>";
echo "</div>";
echo "</div>";

// TAG
echo '<div class="form-group">';
echo "<label class='control-label col-sm-2' for='TAG'>".$l->g(1425)."</label>";
echo "<div class='col-sm-3'>";
echo "<select name='TAG' id='TAG' class='form-control'>";
foreach($tag as $key => $name) {
    if(isset($protectedPost['TAG']) && $protectedPost['TAG'] == $key) {
        echo "<option value='".$key."' selected>".$name."</option>";
    } else {
        echo "<option value='".$key."'>".$name."</option>";
    }
}
echo "</select>";
echo "</div>";

// ASSET CATEGORY
echo '<div class="form-group">';
echo "<label class='control-label col-sm-2' for='ASSET'>".$l->g(2132)."</label>";
echo "<div class='col-sm-3'>";
echo "<select name='ASSET' id='ASSET' class='form-control'>";
foreach($asset as $key => $name) {
    if(isset($protectedPost['ASSET']) && $protectedPost['ASSET'] == $key) {
        echo "<option value='".$key."' selected>".$name."</option>";
    } else {
        echo "<option value='".$key."'>".$name."</option>";
    }
}
echo "</select>";
echo "</div>";
echo "</div>";
// END FILTER OS/GROUP/TAG/ASSET

// FILTER BY CSV
echo "<div class='form_group'>";
echo "<div class='col-sm-12'>";
formGroup('file', 'csv_file', $l->g(1478).' :', '', '', $protectedPost['csv_file'] ?? '', '', '', '', "accept='.csv'");
echo "</div>"; 
echo "</div>"; 
// END FILTER CSV

if(is_defined($_SESSION['OCS']["mesmachines"])) {
    // DISPLAY COUNT FOR RESTRICTED TAG
    $selectOptions = [
        "no" => $l->g(454),
        "yes" => $l->g(455)
    ];

    echo '<div class="form-group">';
    echo "<label class='control-label col-sm-2' for='SUBMIT_FORM_RESTRICT'>".$l->g(1521)."</label>";
    echo "<div class='col-sm-3'>";
    echo "<select name='SUBMIT_FORM_RESTRICT' id='SUBMIT_FORM_RESTRICT' class='form-control'>";
    foreach($selectOptions as $key => $name) {
        if(isset($protectedPost['SUBMIT_FORM_RESTRICT']) && $protectedPost['SUBMIT_FORM_RESTRICT'] == $key) {
            echo "<option value='".$key."' selected>".$name."</option>";
        } else {
            echo "<option value='".$key."'>".$name."</option>";
        }
    }
    echo "</select>";
    echo "</div>";
    echo "</div>";
    // END DISPLAY COUNT FOR RESTRICTED TAG
}

echo '<input type="submit" class="btn btn-success" value="'.$l->g(393).'" name="SUBMIT_FORM">';
echo '<input type="submit" class="btn btn-danger" value="'.$l->g(396).'" name="RESET">';

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
