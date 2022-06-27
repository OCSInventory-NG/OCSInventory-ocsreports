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

 require 'require/saas/Saas.php';
 $saas = new Saas();

 printEnTete($l->g(8100));
 echo "<br/>";
 //ADD new static group
 if (isset($protectedPost['Valid_modif'])) {
     $result = $saas->add_saas($protectedPost['SAAS_NAME'], $protectedPost['DNS_SAAS']);
     if (!$result) {
         msg_error($l->g(8107));
     } elseif ($result) {
         msg_success($l->g(572));
         unset($protectedPost['Valid_modif']);
     }
     $tab_options['CACHE'] = 'RESET';
 }
 //reset add saas
 if (isset($protectedPost['Reset_modif']) ||  (isset($protectedPost['onglet']) && isset($protectedPost['old_onglet']) && ($protectedPost['onglet'] != $protectedPost['old_onglet']))) {
     unset($protectedPost['SAAS_NAME']);
     unset($protectedPost['DNS_SAAS']);
 }
 $tab_options = $protectedPost;

 //if delete saas
 if (!empty($protectedPost['SUP_PROF'])) {
     $sqlQuery = "DELETE FROM `saas_exp` WHERE ID = %s";
     $sqlArg = [$protectedPost['SUP_PROF']];
     mysql2_query_secure($sqlQuery, $_SESSION['OCS']["writeServer"], $sqlArg);
     $sqlQuery = "DELETE FROM `saas` WHERE SAAS_EXP_ID = %s";
     $sqlArg = [$protectedPost['SUP_PROF']];
     mysql2_query_secure($sqlQuery, $_SESSION['OCS']["writeServer"], $sqlArg);
     $tab_options['CACHE'] = 'RESET';
 }

 $form_name = 'saas';
 $tab_options['form_name'] = $form_name;

 echo open_form($form_name, '', '', 'form-horizontal');

 	$def_onglets['SAAS_LIST']=$l->g(8101); //Dynamic group
 	$def_onglets['SAAS_ADD']=$l->g(8102); //Static group centraux

 	if (empty($protectedPost['onglet'])){
   	$protectedPost['onglet']="SAAS_LIST";
 }

 //show onglet
 show_tabs($def_onglets,$form_name,"onglet",true);
 echo '<div class="col col-md-10">';


 if ($protectedPost['onglet'] == "SAAS_LIST") {
     $list_fields = array(
         $l->g(49) => 'e.NAME',
         $l->g(8104) => 'ENTRY',
         $l->g(8105) => 'DATA',
         'nb' => 'nb',);

     $table_name = "LIST_SAAS";

     $queryDetails = "SELECT e.NAME, count(DISTINCT s.HARDWARE_ID) as nb, s.ENTRY, GROUP_CONCAT(DISTINCT CASE WHEN s.DATA != 0x20 THEN s.DATA ELSE NULL END SEPARATOR 0x2D) as DATA, s.ENTRY id FROM saas s LEFT JOIN saas_exp e ON e.ID = s.SAAS_EXP_ID group by s.ENTRY";

     $tab_options['LIEN_LBL']['nb'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_multi_search'] . '&prov=saas&value=';
     $tab_options['LIEN_CHAMP']['nb'] = 'id';
     $tab_options['LBL']['nb'] = $l->g(1120);

 }elseif($protectedPost['onglet'] == "SAAS_ADD"){
    $protectedPost['SAAS_NAME'] = isset($protectedPost['SAAS_NAME']) ? $protectedPost['SAAS_NAME'] : '';
    $protectedPost['DNS_SAAS'] = isset($protectedPost['DNS_SAAS']) ? $protectedPost['DNS_SAAS'] : '';
     ?>
     <div class="row">
         <div class="col col-md-4 col-xs-offset-0 col-md-offset-4">
             <div class="form-group">
               <div class="col-sm-10">
             <?php
             formGroup('text', 'SAAS_NAME', $l->g(49)." :", '20', '', $protectedPost['SAAS_NAME']);
             formGroup('text', 'DNS_SAAS', $l->g(8103)." :", '', '', $protectedPost['DNS_SAAS']);
             ?>
             <p><?php echo $l->g(358) ?></p>
             </div>
           </div>
       </div>
   </div>
     <div class="row">
         <div class="col-md-12">
             <input type="submit" name="Valid_modif" value="<?php echo $l->g(1363) ?>" class="btn btn-success">
             <input type="submit" name="Reset_modif" value="<?php echo $l->g(1364) ?>" class="btn btn-danger">
         </div>
     </div>
     <hr>
     <?php

     $list_fields = array($l->g(49) => 'NAME',
         $l->g(8103) => 'DNS_EXP',);

     $list_fields['SUP'] = 'ID';
     $tab_options['LBL_POPUP']['SUP'] = 'NAME';
     $tab_options['LBL']['SUP'] = $l->g(122);

     $table_name = "SAAS";

     $queryDetails = "select * from saas_exp";
   }

 $tab_options['table_name'] = $table_name;

 $default_fields = $list_fields;
 $list_col_cant_del = $list_fields;

 //affichage du tableau
 $result_exist = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);

 echo "</div>";
 echo close_form();

 if (AJAX) {
     ob_end_clean();
     tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
 }
