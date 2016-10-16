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
    $ajax = true;
} else {
    $ajax = false;
}

function array_merge_values($arr, $arr2) {
    foreach ($arr2 as $key => $values) {
        array_push($arr, $values);
    }
    return $arr;
}

if ($protectedPost['RESET']) {
    unset($protectedPost['NAME_RESTRICT']);
    unset($protectedPost['NBRE']);
    unset($protectedPost['CLASS']);
}
echo "<div class='col-md-12'>";
if ($protectedPost['SUBMIT_FORM'])
    $tab_options['CACHE'] = 'RESET';

$sql_fin['SQL'] = "";
$sql_fin['ARG'] = array();
$sql_list_alpha['ARG'] = array();
if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES'])
        and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1) {
    $info_name_soft = array("table" => "type_softwares_name", "field" => "name", "field_name_soft" => 'name_id');
} elseif ($_SESSION['OCS']["usecache"] == 1) {
    $info_name_soft = array("table" => "softwares_name_cache", "field" => "name", "field_name_soft" => 'name');
} else {
    $info_name_soft = array("table" => "softwares", "field" => "name", "field_name_soft" => 'name');
}

$field_name_soft = $info_name_soft['table'] . "." . $info_name_soft['field'];

$sql_list_alpha['SQL'] = "select substr(trim(" . $field_name_soft . "),1,1) alpha, " . $field_name_soft . " ";
if (isset($protectedPost['NBRE']) && $protectedPost['NBRE'] != "" && isset($protectedPost['COMPAR']) && $protectedPost['COMPAR'] != "") {
    $sql_list_alpha['SQL'] .= ",count(*) nb ";
    $sql_fin['SQL'] = " having nb %s %s ";
    $sql_fin['ARG'] = array($protectedPost['COMPAR'], $protectedPost['NBRE']);
}
$sql_list_alpha['SQL'] .= " from ";
$and_where = "";
//if ($_SESSION['OCS']["usecache"] == 1  && $protectedPost['NBRE'] == ""){
$sql_list_alpha['SQL'] .= $info_name_soft['table'] . " left join dico_soft on dico_soft.extracted=" . $field_name_soft;
//	$and_where=" where ";
//}else{
//	$sql_list_alpha['SQL'] .=" softwares s left join dico_soft on dico_soft.extracted=s.name ";
/* if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES']) 
  and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1){
  $sql_list_alpha['SQL'] .=" left join type_softwares_name type_soft_name on type_soft_name.id=s.name ";
  } */

if ($_SESSION['OCS']["mesmachines"] != "") {
    if ($info_name_soft['table'] != 'softwares') {
        $join = " left join softwares on softwares." . $info_name_soft['field_name_soft'] . "=" . $field_name_soft . " ";
    } else {
        $join = "";
    }
    // left join softwares s on s.".$info_name_soft['field_name_soft']."=".$field_name_soft."
    $sql_list_alpha['SQL'] .= $join . ",accountinfo a where " . $_SESSION['OCS']["mesmachines"] . " and a.hardware_id=softwares.HARDWARE_ID ";
    $and_where = " and ";
} else
    $and_where = " where ";
//}
$sql_list_alpha['SQL'] .= $and_where . " substr(trim(" . $field_name_soft . "),1,1) is not null ";
if (isset($protectedPost['NAME_RESTRICT']) && $protectedPost['NAME_RESTRICT'] != "") {
    $sql_list_alpha['SQL'] .= " and " . $field_name_soft . " like '%s' ";
    $sql_list_alpha['ARG'] = array('%' . $protectedPost['NAME_RESTRICT'] . '%');
}
/* if (isset($protectedPost['CLASS']) && $protectedPost['CLASS'] != ""){
  $sql_list_alpha['SQL'].=" && (dico_soft.formatted in ";
  $sql_list_alpha=mysql2_prepare($sql_list_alpha['SQL'],$sql_list_alpha['ARG'],$list_soft_by_statut[$protectedPost['CLASS']]);
  $sql_list_alpha['SQL'] .=" ) ";
  } */
$sql_list_alpha['SQL'] .= " group by " . $field_name_soft . " " . $sql_fin['SQL'];
//if ($sql_list_alpha['ARG'] != array() && $sql_fin['ARG'] != array())
$sql_list_alpha['ARG'] = array_merge_values($sql_list_alpha['ARG'], $sql_fin['ARG']);
//elseif ($sql_fin['ARG'] != array() && $sql_list_alpha['ARG'] == array())
//	$sql_list_alpha['ARG']=$sql_fin['ARG'];

unset($_SESSION['OCS']['REQ_ONGLET_SOFT']);
/* p($sql_fin['ARG']);
  p($sql_list_alpha['ARG']); */
//execute the query only if necessary 
if ($_SESSION['OCS']['REQ_ONGLET_SOFT'] != $sql_list_alpha || !isset($protectedPost['onglet'])) {
    $result_list_alpha = mysql2_query_secure($sql_list_alpha['SQL'], $_SESSION['OCS']["readServer"], $sql_list_alpha['ARG']);
    while ($item_list_alpha = mysqli_fetch_object($result_list_alpha)) {

        if (mb_strtoupper($item_list_alpha->alpha) == '"')
            $car = "'";
        else
            $car = mb_strtoupper($item_list_alpha->alpha);

        if ($car != "") {
            if (!isset($protectedPost['onglet']))
                $protectedPost['onglet'] = $car;
            $list_alpha[$car] = $car;
            if (!isset($first)) {
                $first = $car;
            }
        }
    }

    if (!isset($list_alpha[$protectedPost['onglet']])) {
        $protectedPost['onglet'] = $first;
    }
    $_SESSION['OCS']['REQ_ONGLET_SOFT'] = $sql_list_alpha;
    $_SESSION['OCS']['ONGLET_SOFT'] = $list_alpha;
}
$form_name = "all_soft";
$table_name = "all_soft";
echo open_form($form_name, '', '', 'form-horizontal');

onglet($_SESSION['OCS']['ONGLET_SOFT'], $form_name, "onglet", 20);
if ((isset($protectedPost['NAME_RESTRICT']) && $protectedPost['NAME_RESTRICT'] != "") or ( (isset($protectedPost['NBRE']) and $protectedPost['NBRE'] != "")))
    msg_warning($l->g(767));


//use cache
if ($_SESSION['OCS']["usecache"] == 1 and ! (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES'])
        and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1)) {
    $search_soft['SQL'] = "select name,id from softwares_name_cache";
    //$forcedRequest['SQL']=$search_soft['SQL'];
    if (isset($protectedPost['onglet'])) {
        $search_soft['SQL'] .= " where name like '%s'";
        $search_soft['ARG'] = array($protectedPost['onglet'] . "%");

        $and_where = " where ";
        if (isset($protectedPost['NAME_RESTRICT']) && $protectedPost['NAME_RESTRICT'] != "") {
            //$forcedRequest['SQL'].= $and_where." name like '%s' ";
            //$forcedRequest['ARG']=array("%".$protectedPost['NAME_RESTRICT']."%");
            $search_soft['SQL'] .= " and name like '%s' ";
            array_push($search_soft['ARG'], "%" . $protectedPost['NAME_RESTRICT'] . "%");
            $and_where = " and ";
        }
    } else {
        if (isset($protectedPost['NAME_RESTRICT']) && $protectedPost['NAME_RESTRICT'] != "") {
            //$forcedRequest['SQL'].= $and_where." name like '%s' ";
            //$forcedRequest['ARG']=array("%".$protectedPost['NAME_RESTRICT']."%");
            $search_soft['SQL'] .= " WHERE name like '%s' ";
            $search_soft['ARG'][] = "%" . $protectedPost['NAME_RESTRICT'] . "%";
        }
    }
    /* if (isset($protectedPost['CLASS']) && $protectedPost['CLASS'] != ""){
      //	$fin_sql=" and dico_soft.extracted is not null ";
      $forcedRequest.= $and_where." (dico_soft.formatted in ('".implode("','",$list_soft_by_statut[$protectedPost['CLASS']])."') ) and ";
      $search_soft.=" and (dico_soft.formatted in ('".implode("','",$list_soft_by_statut[$protectedPost['CLASS']])."') ) ";
      } */
    $result_search_soft = mysql2_query_secure($search_soft['SQL'], $_SESSION['OCS']["readServer"], $search_soft['ARG']);
    $list_soft = "";
    while ($item_search_soft = mysqli_fetch_object($result_search_soft)) {
        if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES'])
                and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1) {
            $list_soft[] = $item_search_soft->id;
        } else {
            $list_soft[] = $item_search_soft->name;
        }
    }
}
if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES'])
        and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1) {
    $field_name_soft = "s.name_id";
} elseif ($_SESSION['OCS']["usecache"] == 1) {
    $field_name_soft = "s.name";
} else {
    $field_name_soft = "s.name";
    $info_name_soft['table'] = "s";
}


/*
  if ((!isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES'])
  or $_SESSION['OCS']['USE_NEW_SOFT_TABLES']!= 1) && $_SESSION['OCS']["usecache"] == 1){
  $field_name_soft="s.name";
  }else{
  $field_name_soft="s.name_id"; */


if ($list_soft != "") {

    $and_where = "";
    if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES'])
            and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1) {
        $sql_re['SQL'] = "select  " . $info_name_soft['table'] . ".name , 
							count(s." . $info_name_soft['field_name_soft'] . ") nb, 
							s." . $info_name_soft['field_name_soft'] . " id 
						from softwares s 
							left join " . $info_name_soft['table'] . " 
							on " . $info_name_soft['table'] . ".id=s." . $info_name_soft['field_name_soft'] . " ";
    } else {
        $sql_re['SQL'] = "select  s." . $info_name_soft['field_name_soft'] . " , 
						count(s." . $info_name_soft['field_name_soft'] . ") nb, 
						s." . $info_name_soft['field_name_soft'] . " id from softwares s ";
    }

    if (isset($_SESSION['OCS']["mesmachines"]) && $_SESSION['OCS']["mesmachines"] != '') {
        $sql_re['SQL'] .= ",accountinfo a where " . $_SESSION['OCS']["mesmachines"] . " and a.hardware_id=s.HARDWARE_ID";
        $and_where = " and ";
    } else
        $and_where = " where ";
    //$_SESSION['OCS']["forcedRequest"]=$sql['SQL'].$and_where." name in (".$forcedRequest.")";
    $sql_re['SQL'] .= $and_where . " s." . $info_name_soft['field_name_soft'] . " in ";
    $sql_re['ARG'] = array();
    $sql = mysql2_prepare($sql_re['SQL'], $sql_re['ARG'], $list_soft);
    //$sql['ARG']=('".implode("','",$list_soft)."')";
    //$sql.=$fin_sql;
}elseif (!isset($list_soft)) {
    $and_where = "";
    $sql['SQL'] = "select  " . $info_name_soft['table'] . "." . $info_name_soft['field'] . ", count(s." . $info_name_soft['field_name_soft'] . ") nb,
					 s." . $info_name_soft['field_name_soft'] . " id from softwares s";
    if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES'])
            and $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1) {
        $sql['SQL'] .= " left join " . $info_name_soft['table'] . " on " . $info_name_soft['table'] . ".id=s." . $info_name_soft['field_name_soft'] . " ";
    }
    $sql['ARG'] = array();
    if (isset($_SESSION['OCS']["mesmachines"]) && $_SESSION['OCS']["mesmachines"] != '') {
        $sql['SQL'] .= ",accountinfo a where " . $_SESSION['OCS']["mesmachines"] . " and a.hardware_id=s.HARDWARE_ID";
        $and_where = " and ";
    } else
        $and_where = " where ";
    //$_SESSION['OCS']["forcedRequest"]=$sql;
    $sql['SQL'] .= $and_where . " " . $info_name_soft['table'] . "." . $info_name_soft['field'] . " like '%s'";
    array_push($sql['ARG'], $protectedPost['onglet'] . "%");
    if (isset($protectedPost['NAME_RESTRICT']) && $protectedPost['NAME_RESTRICT'] != "") {
        $sql['SQL'] .= " and " . $info_name_soft['table'] . "." . $info_name_soft['field'] . " like '%s' ";
        array_push($sql['ARG'], "%" . $protectedPost['NAME_RESTRICT'] . "%");
        //$_SESSION['OCS']["forcedRequest"].=$and_where."name like '%".$protectedPost['NAME_RESTRICT']."%'" ;
    }
}

if (isset($sql)) {
    $sql['SQL'] .= " group by " . $field_name_soft;
//	$_SESSION['OCS']["forcedRequest"].=" group by name";
    if ($sql_fin['SQL'] != '') {
        $sql['SQL'] .= $sql_fin['SQL'];
        $sql['ARG'] = array_merge_values($sql['ARG'], $sql_fin['ARG']);
//	$_SESSION['OCS']["forcedRequest"].=$sql_fin;
    }
    $list_fields = array('name' => 'name',
        'nbre' => 'nb'
    );
    $default_fields = $list_fields;
    $list_col_cant_del = $default_fields;
    $tab_options['LIEN_LBL']['nbre'] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_multi_search'] . '&prov=allsoft&value=';
    $tab_options['LIEN_CHAMP']['nbre'] = 'id';
    $tab_options['LBL']['name'] = $l->g(847);
    $tab_options['LBL']['nbre'] = $l->g(1120);
    $tab_options['ARG_SQL'] = $sql['ARG'];
    $tab_options['form_name'] = $form_name;
    $tab_options['table_name'] = $table_name;
    $result_exist = ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
}


echo "<h4>" . $l->g(735) . "</h4>";

formGroup('text', 'NAME_RESTRICT', $l->g(382), 20, 100, $protectedPost['NAME_RESTRICT']);
?>
<div class="form-group">
    <label class="control-label col-sm-2" for="COMPAR"><?php echo $l->g(381); ?></label>
    <div class="col-sm-1">
        <select name="COMPAR" id="COMPAR" class="form-control">
            <option value="<">&lt;</option>
            <option value=">">&gt;</option>
            <option value="=">=</option>
        </select>
    </div>
    <div class="col-sm-2">
        <input type="text" class="form-control" maxlength="100" value="<?php echo $protectedPost['NBRE']; ?>">
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <a href="index.php?<?php echo PAG_INDEX . "=" . $pages_refs['ms_soft_csv'] . "&no_header=1&soft=" . $protectedPost['NAME_RESTRICT'] . "&nb=" . $protectedPost['NBRE'] . "&comp=" . htmlentities($protectedPost['COMPAR'], ENT_COMPAT | ENT_HTML401, "UTF-8") ?>"><?php echo $l->g(765); ?></a>
    </div>
</div>
<?php
if ($protectedPost['COMPAR'] == '<' && $protectedPost['NBRE'] <= 15 && $protectedPost['NBRE'] != "") {
    echo "<a href='index.php?" . PAG_INDEX . "=" . $pages_refs['ms_soft_csv'] . "&no_header=1&soft=" . $protectedPost['NAME_RESTRICT'] . "&nb=" . $protectedPost['NBRE'] . "&comp=" . htmlentities($protectedPost['COMPAR'], ENT_COMPAT | ENT_HTML401, "UTF-8") . "&all_computers=yes'>" . $l->g(912) . "</a>";
}
?>
<input type="submit" class="btn btn-success" value="<?php echo $l->g(393); ?>" name="SUBMIT_FORM">
<input type="submit" class="btn btn-danger" value="<?php echo $l->g(396); ?>" name="RESET">
<?php
echo close_form();

echo "</div>";

if ($ajax) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $sql['SQL'], $tab_options);
}
?>
