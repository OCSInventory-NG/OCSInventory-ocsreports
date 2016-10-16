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
/* SERVERNAME = Nom du serveur de partage de l'imprimante
  SHARENAME = Nom de partage de l'imprimante sur le serveur
  RESOLUTION = Resolution au format horizontal x vertical
  COMMENT = commentaire
  SHARED = 1 si partagée, 0 sinon
  NETWORK = 1 si impirmante sur le réseau, 0 si imprimante connectée localement
  1323 Serveur de partage imprimante
  1324 Partage imprimante sur serveur
  1325 Résolution format horizontal/vertical
 */

if (AJAX) {
    parse_str($protectedPost['ocs']['0'], $params);
    $protectedPost += $params;
    ob_start();
    $ajax = true;
} else {
    $ajax = false;
}
if (!isset($protectedPost['SHOW']))
    $protectedPost['SHOW'] = 'NOSHOW';
print_item_header($l->g(79));
$form_name = "affich_printers";
$table_name = $form_name;
echo open_form($form_name, '', '', 'form-horizontal');
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;
$list_fields = array($l->g(49) => 'NAME',
    $l->g(278) => 'DRIVER',
    $l->g(279) => 'PORT',
    $l->g(53) => 'DESCRIPTION',
    $l->g(1323) => 'SERVERNAME',
    $l->g(1324) => 'SHARENAME',
    $l->g(1325) => 'RESOLUTION',
    $l->g(51) => 'COMMENT',
    $l->g(1326) => 'SHARED',
    $l->g(1327) => 'NETWORK');
$list_col_cant_del = $list_fields;
$default_fields = $list_fields;
$tab_options['FILTRE'] = array_flip($list_fields);
$queryDetails = "SELECT * FROM printers WHERE (hardware_id=$systemid)";
ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
echo close_form();
if ($ajax) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
    ob_start();
}
?>