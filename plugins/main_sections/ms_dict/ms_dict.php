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
 * New version of dico page
 */
require_once('require/function_dico.php');
//use or not cache
/*if ($_SESSION['OCS']['usecache']) {
    $table = "softwares_name_cache";
} else {
    $table = "software_name";
}*/
$table = "software_name";

//form name
$form_name = 'admin_param';
//form open
echo open_form($form_name, '', '', 'form-horizontal');
//definition of onglet
$def_onglets['CAT'] = $l->g(1027); //Categories
$def_onglets['NEW'] = $l->g(1028); //nouveau logiciels
$def_onglets['IGNORED'] = $l->g(1029); //ignor.
$def_onglets['UNCHANGED'] = $l->g(1030); //unchanged
//défault => first onglet
if ($protectedPost['onglet'] == "") {
    $protectedPost['onglet'] = "CAT";
}
//reset search
if ($protectedPost['RESET'] == "RESET") {
    unset($protectedPost['custom_search']);
}
//filtre
if ( isset($protectedPost['custom_search']) && $protectedPost['custom_search'] != "" ){
    $search_cache = " and cache.name like '%" . mysqli_real_escape_string( $_SESSION['OCS']["readServer"], $protectedPost['custom_search']) . "%' ";
    $search_count = " and extracted like '%" . mysqli_real_escape_string($_SESSION['OCS']["readServer"], $protectedPost['custom_search']) . "%' ";
} else {
    $search = "";
    $search_count = "";
}
//show first lign of onglet
show_tabs($def_onglets,$form_name,"onglet",true);
echo '<div class="col col-md-10" >';
//attention=> result with restriction
if ($search_count != "" || $search_cache != "") {
    msg_warning($l->g(767));
}
/* * ************************************ACTION ON DICO SOFT************************************* */

//transfert soft
if ($protectedPost['TRANS'] == "TRANS") {
    if ($protectedPost['all_item'] != '') {
        $list_check = search_all_item();
    } else {

        foreach ($protectedPost as $key => $value) {
            if (substr($key, 0, 5) == "check") {
                $list_check[] = substr($key, 5);
            }
        }
    }
    
    // If list check and protected post are OK for transfer
    if ($list_check != '' && ( isset($protectedPost['NEW_CAT']) || isset($protectedPost['EXIST_CAT'])  ) ) {
        if($protectedPost['EXIST_CAT'] != "NONE"){
            trans($protectedPost['onglet'], $list_check, $protectedPost['AFFECT_TYPE'], '', $protectedPost['EXIST_CAT']);
            unset($protectedPost['EXIST_CAT']); 
        }
        
        if($protectedPost['NEW_CAT'] != ""){
            trans($protectedPost['onglet'], $list_check, $protectedPost['AFFECT_TYPE'], $protectedPost['NEW_CAT'], '');
            unset($protectedPost['NEW_CAT']);
        }

        // unset vars
        $list_check = "";
        $protectedPost['AFFECT_TYPE'] = 'NONE';
 
    }
}
//delete a soft in list => return in 'NEW' liste
if ($protectedPost['SUP_PROF'] != "") {
    del_soft($protectedPost['onglet'], array($protectedPost['SUP_PROF']));
}
/* * **********************************END ACTION************************************* */

if ($protectedPost['onglet'] != $protectedPost['old_onglet']) {
    unset($protectedPost['onglet_soft']);
}
/* * *****************************************************CAS OF CATEGORIES****************************************************** */
if ($protectedPost['onglet'] == 'CAT') {
    //search all categories
    $sql_list_cat = "select formatted  name
		  from dico_soft where extracted!=formatted " . $search_count . " group by formatted";
    $result_list_cat = mysqli_query($_SESSION['OCS']["readServer"], $sql_list_cat);
    $i = 1;
    while ($item_list_cat = mysqli_fetch_object($result_list_cat)) {
        if ($i == 1) {
            $first_onglet = $i;
        }
        $list_cat[$i] = $item_list_cat->name;
        $i++;
    }
    //delete categorie
    if (is_defined($protectedPost['SUP_CAT'])) {
        if ($protectedPost['SUP_CAT'] == 1) {
            $first_onglet = 2;
        }
        $reqDcat = "DELETE FROM dico_soft WHERE formatted='" . $list_cat[$protectedPost['SUP_CAT']] . "'";
        mysqli_query($_SESSION['OCS']["writeServer"], $reqDcat) or die(mysqli_error($_SESSION['OCS']["writeServer"]));
        unset($list_cat[$protectedPost['SUP_CAT']]);
    }
    //no selected? default=>first onglet

    if ($protectedPost['onglet_soft'] == "" || !isset($list_cat[$protectedPost['onglet_soft']])) {
        $protectedPost['onglet_soft'] = $first_onglet;
    }
    //show all categories
    if ($i <= 20) {
        echo "<p>";  
        onglet($list_cat, $form_name, "onglet_soft", 5);
        echo "</p>";
    } else {
        echo "<p>" . $l->g(398) . ": " . show_modif($list_cat, 'onglet_soft', 2, $form_name) . "</p>";
    }
    //You can delete or not?
    if ($i != 1 && isset($list_cat[$protectedPost['onglet_soft']])) {
        echo "<a href=# OnClick='return confirme(\"\",\"" . $protectedPost['onglet_soft'] . "\",\"" . $form_name . "\",\"SUP_CAT\",\"" . $l->g(640) . "\");'>" . $l->g(921) . "</a>";
    }
    $list_fields = array('SOFT_NAME' => 'EXTRACTED',
        'ID' => 'ID',
        'SUP' => 'ID',
        'CHECK' => 'ID'
    );
    $table_name = "CAT_EXIST";
    $default_fields = array('SOFT_NAME' => 'SOFT_NAME', 'SUP' => 'SUP', 'CHECK' => 'CHECK');
    $list_col_cant_del = array('SOFT_NAME' => 'SOFT_NAME', 'SUP' => 'SUP', 'CHECK' => 'CHECK');
    $querydico = 'SELECT ';
    foreach ($list_fields as $key => $value) {
        if ($key != 'SUP' && $key != 'CHECK') {
            $querydico .= $value . ',';
        }
    }
    error_log($list_cat[$protectedPost['onglet_soft']]);
    $querydico = substr($querydico, 0, -1);
    $querydico .= " from dico_soft left join " . $table . " cache on dico_soft.extracted=cache.name
				 where formatted='" . mysqli_real_escape_string($_SESSION['OCS']["readServer"], $list_cat[$protectedPost['onglet_soft']]) . "' " . $search_count . " group by EXTRACTED";
}
/* ******************************************************CAS OF NEW****************************************************** */
if ($protectedPost['onglet'] == 'NEW') {
    $sql_list_alpha = "select
    distinct left(trim(name),1) alpha
    from " . $table . " cache
    where name is not null
    and name not in (select extracted name from dico_soft)
    and name not in (select extracted name from dico_ignored) " . $search_cache;
    $first = '';
    //execute the query only if necessary
    $result_list_alpha = mysqli_query($_SESSION['OCS']["readServer"], $sql_list_alpha);
    $i = 1;
    while ($item_list_alpha = mysqli_fetch_object($result_list_alpha)) {
        if (mb_strtoupper($item_list_alpha->alpha) != "") {
            if ($first == '') {
                $first = $i;
            }
            $list_alpha[$i] = mb_strtoupper($item_list_alpha->alpha);
            $i++;
        }
    }
    //execute the query only if necessary
    $_SESSION['OCS']['REQ_ONGLET_SOFT'] = $sql_list_alpha;
    $_SESSION['OCS']['ONGLET_SOFT'] = $list_alpha;
    $_SESSION['OCS']['FIRST_DICO'] = $first;
    if (!isset($protectedPost['onglet_soft'])) {
        $protectedPost['onglet_soft'] = $_SESSION['OCS']['FIRST_DICO'];
    }
    echo "<p>";
    onglet($list_alpha, $form_name, "onglet_soft", 20);
    echo "</p>";
    
    //search all soft for the tab as selected
    $search_soft = "select distinct trim(name) name from " . $table . " cache
    where name like '" . $_SESSION['OCS']['ONGLET_SOFT'][$protectedPost['onglet_soft']] . "%'
    and name not in (select extracted name from dico_soft)
    and name not in (select extracted name from dico_ignored) " . $search_cache;
    $result_search_soft = mysqli_query($_SESSION['OCS']["readServer"], $search_soft);
    $list_soft = "'";
    while ($item_search_soft = mysqli_fetch_object($result_search_soft)) {
        $list_soft .= addslashes($item_search_soft->name) . "','";
    }
    $list_soft = substr($list_soft, 0, -2);
    if ($list_soft == "") {
        $list_soft = "''";
    }

    $list_fields = array('SOFT_NAME' => 'NAME',
        'QTE' => 'QTE',
        'ID' => 'ID',
        'CHECK' => 'ID');
    $table_name = "CAT_NEW";
    $default_fields = array('SOFT_NAME' => 'SOFT_NAME', 'QTE' => 'QTE', 'CHECK' => 'CHECK');
    $list_col_cant_del = array('SOFT_NAME' => 'SOFT_NAME', 'QTE' => 'QTE', 'CHECK' => 'CHECK');
    $querydico = 'SELECT ';
    foreach ($list_fields as $key => $value) {
        if ($key != 'CHECK' && $key != 'QTE') {
            $querydico .= $value . ',';
        } elseif ($key == 'QTE') {
            $querydico .= ' count(NAME) as ' . $value . ',';
        }
    }

    $querydico = substr($querydico, 0, -1);
    $querydico .= " from software_name
                    where name in (" . $list_soft . ") and name != ''
                    and name not in (select extracted name from dico_soft)
                    and name not in (select extracted name from dico_ignored)
                    group by name ";
}
/* * *****************************************************CAS OF IGNORED****************************************************** */
if ($protectedPost['onglet'] == 'IGNORED') {
    $list_fields = array('SOFT_NAME' => 'EXTRACTED',
        'ID' => 'ID',
        'SUP' => 'ID',
        'CHECK' => 'ID'
    );
    $table_name = "CAT_IGNORED";
    $default_fields = array('SOFT_NAME' => 'SOFT_NAME', 'SUP' => 'SUP', 'CHECK' => 'CHECK');
    $list_col_cant_del = array('SOFT_NAME' => 'SOFT_NAME', 'CHECK' => 'CHECK', 'SUP' => 'SUP');
    $querydico = 'SELECT ';
    foreach ($list_fields as $key => $value) {
        if ($key != 'SUP' && $key != 'CHECK') {
            $querydico .= $value . ',';
        }
    }
    if ($search_count != "") {
        $modif_search = " where " . substr($search_count, 5);
    }
    $querydico = substr($querydico, 0, -1);
    $querydico .= " from dico_ignored left join " . $table . " cache on cache.name=dico_ignored.extracted " . $modif_search . " group by EXTRACTED ";
}
/* * *****************************************************CAS OF UNCHANGED****************************************************** */
if ($protectedPost['onglet'] == 'UNCHANGED') {
    $list_fields = array('SOFT_NAME' => 'EXTRACTED',
        'ID' => 'ID',
        'SUP' => 'ID',
        'CHECK' => 'ID'
    );
    $table_name = "CAT_UNCHANGE";
    $default_fields = array('SOFT_NAME' => 'SOFT_NAME', 'SUP' => 'SUP', 'CHECK' => 'CHECK');
    $list_col_cant_del = array('SOFT_NAME' => 'SOFT_NAME', 'CHECK' => 'CHECK', 'SUP' => 'SUP');
    $querydico = 'SELECT ';
    foreach ($list_fields as $key => $value) {
        if ($key != 'SUP' && $key != 'CHECK') {
            $querydico .= $value . ',';
        }
    }
    $querydico = substr($querydico, 0, -1);
    $querydico .= " from dico_soft left join " . $table . " cache on cache.name=dico_soft.extracted
	 	where extracted=formatted " . $search_cache . " group by EXTRACTED ";
}
if (isset($querydico)) {
    $_SESSION['OCS']['query_dico'] = $querydico;
    $tab_options = $protectedPost;
    $tab_options['form_name'] = $form_name;
    $tab_options['table_name'] = $table_name;
    $tab_options['LIEN_LBL']['QTE'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_multi_search'] . '&prov=allsoft&value=';
    $tab_options['LIEN_CHAMP']['QTE'] = 'NAME';
    $tab_options['LBL']['SOFT_NAME'] = $l->g(382);
    $tab_options['LBL']['QTE'] = $l->g(55);
    $result_exist = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
}

//récupération de toutes les catégories
$list_categories['NONE'] = " ";
$list_categories['IGNORED'] = "IGNORED";
$list_categories['UNCHANGED'] = "UNCHANGED";
$sql_list_categories = "select distinct(formatted) name from dico_soft where formatted!=extracted order by formatted";
$result_list_categories = mysqli_query($_SESSION['OCS']["readServer"], $sql_list_categories);
while ($item_list_categories = mysqli_fetch_object($result_list_categories)) {
    $list_categories[$item_list_categories->name] = $item_list_categories->name;
}
//définition de toutes les options possibles
$choix_affect['NONE'] = " ";
$choix_affect['NEW_CAT'] = $l->g(385);
$choix_affect['EXIST_CAT'] = $l->g(387);

if($protectedPost['onglet'] == "NEW"){
?>
<div class="row">
    <div class="col-md-6 col-md-offset-4">
        <?php formGroup('text', 'custom_search', $l->g(1051), '', '', $protectedPost['custom_search']); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <input class='btn btn-success' type='submit' value='<?php echo $l->g(393); ?>'>
        <button class='btn btn-danger' value='<?php echo $l->g(396); ?>' onclick='return pag("RESET","RESET","<?php echo $form_name ?>");' ><?php echo $l->g(396); ?></button>
    </div>
</div>
<div class="row margin-top30">
    <div class="col-md-6 col-md-offset-3">
        <input name='all_item' id='all_item' type='checkbox' <?php echo (isset($protectedPost['all_item']) ? " checked " : "") . ">" . $l->g(384); ?>
        <br />
        <?php
        formGroup('select', 'AFFECT_TYPE', $l->g(1381), '', '', $protectedPost['AFFECT_TYPE'], '', $choix_affect, $choix_affect, 'onchange="document.admin_param.submit();"');
        
        if(isset($protectedPost['AFFECT_TYPE']) && $protectedPost['AFFECT_TYPE'] != "NONE"){
            if ($protectedPost['AFFECT_TYPE'] == "NEW_CAT") {
                formGroup('text', 'NEW_CAT', $l->g(391), '', 100, $protectedPost['NEW_CAT']);
            }elseif($protectedPost['AFFECT_TYPE'] == "EXIST_CAT"){
                formGroup('select', 'EXIST_CAT', $l->g(388), '', 100, $protectedPost['EXIST_CAT'], '', $list_categories, $list_categories);
            }
            echo "<input type='hidden' name='TRANS' id='TRANS' value='TRANS'>";
            echo "<input type='submit' class='btn btn-success' value=".$l->g(13).">";
        }

        ?>
    </div>
</div>
<?php
}

echo '</div>';
echo "<input type='hidden' name='RESET' id='RESET' value=''>";
echo "<input type='hidden' name='SUP_CAT' id='SUP_CAT' value=''>";
echo close_form();

if(isset($protectedPost['custom_search'])){
    unset($tab_options['custom_search']);
}

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $querydico, $tab_options);
}
?>