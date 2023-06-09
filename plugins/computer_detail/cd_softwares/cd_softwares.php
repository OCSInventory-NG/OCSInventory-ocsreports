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
 * Software category
 */
require_once('require/softwares/SoftwareCategory.php');

unset($list_fields);
print_item_header($l->g(20));
echo "<br/>";
$form_name = "affich_soft";
$table_name = $form_name;
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;
echo open_form($form_name, '', '', 'form-horizontal');

$softCat = new SoftwareCategory();

$all_soft = [0 => $l->g(765)];
$list_cat = $softCat->onglet_cat_cd($systemid);
$i = $list_cat['i'] ?? "";
$first_onglet = $list_cat['first_onglet'] ?? "";
$categorie_id = $list_cat['category_name'] ?? "";

unset($list_cat['i']);
unset($list_cat['first_onglet']);
unset($list_cat['category_name']);
unset($list_cat['OS']);

$list_cat_soft = $softCat->array_merge_values($all_soft, $list_cat);

if ($i <= 10) {
    echo "<p>";
    onglet($list_cat_soft, $form_name, "onglet_soft", 5);
    echo "</p>";
} else {
    echo "<p>" . $l->g(398) . ": " . show_modif($list_cat_soft, 'onglet_soft', 2, $form_name) . "</p>";
}

$list_fields[$l->g(69)] = 'PUBLISHER';

if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES'])
        and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1) {
    $queryDetails = "SELECT s.PUBLISHER,
                            s_name.NAME AS NAME,
                            s_version.NAME AS VERSION,
                            s.COMMENTS,s.FOLDER,s.FILENAME,s.FILESIZE,s.GUID,
                            s.LANGUAGE,s.INSTALLDATE,s.BITSWIDTH, s.ARCHITECTURE, c.CATEGORY_NAME AS CATEGORY
                    FROM software s
                    LEFT JOIN type_softwares_name s_name ON s_name.id= s.name_id
                    LEFT JOIN type_softwares_version s_version ON s_version.id=s.version_id
                    LEFT JOIN software_categories c ON c.id = s.category
                    WHERE (hardware_id=$systemid)";
    $list_fields[$l->g(49)] = 's_name.NAME';
} else {
    $queryDetails = "SELECT *, c.CATEGORY_NAME as CATEGORY, n.NAME, p.PUBLISHER, v.VERSION, v.PRETTYVERSION, v.MAJOR, v.MINOR, v.PATCH 
                    FROM software s LEFT JOIN software_name n ON s.NAME_ID = n.ID 
                    LEFT JOIN software_publisher p ON s.PUBLISHER_ID = p.ID 
                    LEFT JOIN software_version v ON s.VERSION_ID = v.ID 
                    LEFT JOIN software_categories_link scl ON scl.NAME_ID = n.ID AND scl.VERSION_ID = v.ID AND scl.PUBLISHER_ID = p.ID
                    LEFT JOIN software_categories c ON scl.CATEGORY_ID = c.ID
					WHERE (hardware_id=$systemid)";
    $list_fields[$l->g(49)] = 'NAME';
}

if($protectedPost['onglet_soft'] != 0){
  $queryDetails .= " AND c.id = ".$categorie_id[$list_cat[$protectedPost['onglet_soft']]];
}

$list_fields[$l->g(277)] = 'VERSION';
$list_fields[$l->g(51)] = 'COMMENTS';
if (isset($show_all_column)) {
    $list_col_cant_del = $list_fields;
} else {
    $list_col_cant_del = array($l->g(49) => $l->g(49));
}

$default_fields = $list_fields;
$list_fields[$l->g(1248)] = 'FOLDER';
$list_fields[$l->g(446)] = 'FILENAME';
$list_fields[ucfirst(strtolower($l->g(953)))] = 'FILESIZE';

$list_fields['GUID'] = 'GUID';
$list_fields[ucfirst(strtolower($l->g(1012)))] = 'LANGUAGE';
$list_fields[$l->g(1238)] = 'INSTALLDATE';
$list_fields[$l->g(1312)] = 'BITSWIDTH';
$list_fields[$l->g(1247)] = 'ARCHITECTURE';
$list_fields[$l->g(388)] = 'c.CATEGORY_NAME';
$list_fields[$l->g(1522)] = 'PRETTYVERSION';
$list_fields[$l->g(1523)] = 'MAJOR';
$list_fields[$l->g(1524)] = 'MINOR';
$list_fields[$l->g(1525)] = 'PATCH';

$tab_options['FILTRE'] = array_flip($list_fields);

ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
echo close_form();
if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
    ob_start();
}
?>
