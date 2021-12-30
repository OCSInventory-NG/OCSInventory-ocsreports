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

require_once('require/function_config_generale.php');
require('require/stats/Stats.php');
require('require/console/Console.php');
require('require/charts/StatsChartsRenderer.php');
require('require/softwares/SoftwareCategory.php');
require_once('require/function_console.php');
require_once('require/function_groups.php');
require_once('require/function_computers.php');
require('require/news/News.php');

$stats = new Stats();
$console = new Console();
$soft = new SoftwareCategory();
$news = new News();

PrintEnTete($l->g(1600));
echo "<div class='col-md-10 col-xs-offset-0 col-md-offset-1'>";

/*********************************************** NEWS ************************************************/
$champs = array(
    'ACTIVE_NEWS' => 'ACTIVE_NEWS',
);
$values = look_config_default_values($champs);

if(isset($values) && $values['ivalue']['ACTIVE_NEWS'] == 1 ?? 0){
    $result_test = $news->test_connect();
    if($result_test == 'true'){
        $obj = $news->get_json_news();
        if(isset($obj->RECENT)){
          msg_info((string)$obj->RECENT);
        }
        echo '<div id="mySidepanel" class="sidepanel">
                <a href="javascript:void(0)" class="closebtn" onclick="closeNav()"><span class="glyphicon glyphicon-remove" title="'.$l->g(8024).'"></span></a>
                <a href="javascript:void(0)" class="returnbtn" onclick="openNav()" id="return" style="display:none;"><span class="glyphicon glyphicon-chevron-left" title="'.$l->g(8025).'"></span></a>';

                foreach ($obj->NEWS as $key => $value){
                  echo '<div id=news'.$key.' class="aside">';
                  echo '<a href="javascript:void(0)" onclick="openfullNav(\'news'.$key.'\')" id='.$key.'>'.$value->DATE.' - '.$value->TITLE.'</a>';
                  echo '<div id="imagenews'.$key.'" style="display:none;" align="center" class="aside">';
                  if($value->IMAGE != false){
                      echo '<br/><img src="'.$value->IMAGE.'" class="center"><br/>';
                  }
                  echo '</div>';
                  if($value->CONTENTMODIF != null){
                    echo '<p id="contentmodifnews'.$key.'">'.$value->CONTENTMODIF.'</p>';
                    echo '<p id="contentnews'.$key.'" style="display:none;">'.$value->CONTENT.'</p>';
                  }else{
                    echo '<p>'.$value->CONTENT.'</p>';
                  }
                  echo '<div id="linknews'.$key.'" style="display:none;" class="aside">';
                  if($value->LINK != false){
                      echo '<a href="'.$value->LINK.'" class="center" style="text-align:center;">'.$value->LINK.'</a>';
                  }
                  echo '</div>';
                  echo '</div>';
                }

        echo '</div>';

        echo '<button class="btn btn-info" onclick="openNav()">'.$l->g(8022).'</button>';
    } else {
        msg_error($result_test);
    }

}

/************************************** MACHINES *****************************************************/

$form_name = "all_machines";
echo open_form($form_name, '', '', 'form-horizontal');

$table = $console->html_table_machine("ALL COMPUTER");

echo $table;
echo "<hr>";
echo close_form();

/************************************** MACHINE CONTACTED TODAY **************************************/

$form_name = "console";
echo open_form($form_name, '', '', 'form-horizontal');

$table = $console->html_table_machine("CONTACTED");

echo "<br><h4>".$l->g(795)."</h4><br>";
echo $table;

echo "<hr>";
echo close_form();

/********************************************* STATISTIC *********************************************/

$form_name = "stat";
echo open_form($form_name, '', '', 'form-horizontal');

echo "<br><h4>".$l->g(1251)."</h4><br>";
echo "<div class='row'>";
$form = [
  'NB_AGENT' => 'Agent',
  'NB_OS' => 'OS',
  'SEEN' => 'Last seen',
  'MANUFAC' => 'Manufacturer',
  'TYPE' => 'Type'
];

$result = $stats->showForm($form);

if($result == false){
  msg_info($l->g(2135));
}

echo "</div>";
echo "<hr>";
echo close_form();

/********************************************* GROUPS ************************************************/

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

$form_name = 'groups';
$tab_options['form_name'] = $form_name;

echo open_form($form_name, '', '', 'form-horizontal');

echo "<br><h4>".$l->g(1601)."</h4><br>";

$list_fields = array('GROUP_NAME' => 'h.NAME',
    'GROUP_ID' => 'h.ID',
    'DESCRIPTION' => 'h.DESCRIPTION',
    'CREATE' => 'h.LASTDATE',
    'NBRE' => 'NBRE');

$tab_options['LBL']['GROUP_NAME'] = $l->g(49);

$table_name = "LIST_GROUPS";
$tab_options['table_name'] = $table_name;

$default_fields = array('GROUP_NAME' => 'GROUP_NAME', 'DESCRIPTION' => 'DESCRIPTION', 'CREATE' => 'CREATE', 'NBRE' => 'NBRE');
$list_col_cant_del = array('GROUP_NAME' => 'GROUP_NAME');
$query = prepare_sql_tab($list_fields, array('NBRE'));
$tab_options['ARG_SQL'] = $query['ARG'];
$querygroup = $query['SQL'];


$querygroup .= " from hardware h, `groups` g ";
$querygroup .= "where g.hardware_id=h.id and h.deviceid = '_SYSTEMGROUP_' ";

$querygroup .= " and ((g.request is not null and trim(g.request) != '')
			or (g.xmldef is not null and trim(g.xmldef) != ''))";

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

//on ajoute un javascript lorsque l'on clic sur la visibilité du groupe pour tous
$tab_options['JAVA']['CHECK']['NAME'] = "NAME";
$tab_options['JAVA']['CHECK']['QUESTION'] = $l->g(811);
$tab_options['FILTRE'] = array('NAME' => $l->g(679), 'DESCRIPTION' => $l->g(53));
//affichage du tableau
$result_exist = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);

echo "<hr>";
//fermeture du formulaire
echo close_form();


/********************************************* CATEGORIES ********************************************/

$form_name = "category";
echo open_form($form_name, '', '', 'form-horizontal');
echo "<br><h4>".$l->g(1027)."</h4><br>";
echo "<div class='row'>";

// Software category
echo '<div class="col-md-6">
      <div class="panel">
      <div class="panel-heading panel-ocs">'.$l->g(1500).'</div>
      <div class="panel-body">';
      $cat = $console->html_software_cat($soft->search_all_cat());
      echo $cat;
echo '</div>
      </div></div>';

// Assets category
echo '<div class="col-md-6">
      <div class="panel">
      <div class="panel-heading panel-ocs">'.$l->g(2132).'</div>
      <div class="panel-body">';

      $assets_cat = $console->get_assets();
      echo $assets_cat;
echo '</div>
      </div></div>';

echo "</div>";
echo close_form();

echo "</div>";

if (AJAX) {
  ob_end_clean();
  tab_req($list_fields, $default_fields, $list_col_cant_del, $querygroup, $tab_options);
}
?>
