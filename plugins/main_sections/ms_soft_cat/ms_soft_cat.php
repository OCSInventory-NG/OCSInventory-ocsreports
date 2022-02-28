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
//use or not cache
/*if ($_SESSION['OCS']['usecache']) {
    $table = "softwares_name_cache";
} else {
    $table = "software";
}*/
$table = "software";

//form name
$form_name = 'soft_cat';
//form open
echo open_form($form_name, '', '', 'form-horizontal');
//definition of onglet
$def_onglets['CAT_LIST'] = $l->g(1502); //Category list.
$def_onglets['NEW_CAT'] = $l->g(1501); //New category
$def_onglets['ADD_SOFT'] = $l->g(1503); //add soft to category

//default => first onglet
if ($protectedPost['onglet'] == "") {
    $protectedPost['onglet'] = "CAT_LIST";
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

$os_version = [
	"ALL" => $l->g(1515),
	"WINDOWS" => "Windows",
	"unix" => "Unix",
	"Android" => "Android"
];

$softCat = new SoftwareCategory();

/*******************************************LIST OF CATEGORIES*****************************************************/
if($protectedPost['onglet'] == 'CAT_LIST'){

    $list_cat = $softCat->onglet_cat();
    $i = $list_cat['i'];
    $first_onglet = $list_cat['first_onglet'];
    $categorie_id = $list_cat['category_name'];
    $os = $list_cat['OS'];
    unset($list_cat['i']);
    unset($list_cat['first_onglet']);
    unset($list_cat['category_name']);
    unset($list_cat['OS']);

    //delete categorie
    if (is_defined($protectedPost['SUP_CAT'])) {
        // First delete regex
        $reqDcatall = "DELETE FROM software_category_exp WHERE CATEGORY_ID = (SELECT ID FROM software_categories WHERE CATEGORY_NAME = '" . $list_cat[$protectedPost['SUP_CAT']] . "')";
        mysqli_query($_SESSION['OCS']["writeServer"], $reqDcatall) or die(mysqli_error($_SESSION['OCS']["writeServer"]));
        // Second delete software_categories_link
        $reqDcatSoft = "DELETE FROM software_categories_link WHERE CATEGORY_ID = (SELECT ID FROM software_categories WHERE CATEGORY_NAME = '" . $list_cat[$protectedPost['SUP_CAT']] . "')";
        mysqli_query($_SESSION['OCS']["writeServer"], $reqDcatSoft) or die(mysqli_error($_SESSION['OCS']["writeServer"]));
		// End delete category info
        $reqDcat = "DELETE FROM software_categories WHERE CATEGORY_NAME ='" . $list_cat[$protectedPost['SUP_CAT']] . "'";
        mysqli_query($_SESSION['OCS']["writeServer"], $reqDcat) or die(mysqli_error($_SESSION['OCS']["writeServer"]));
        unset($list_cat[$protectedPost['SUP_CAT']]);
        unset($protectedPost['SUP_CAT']);

        $list_cat = $softCat->onglet_cat();
        $i = $list_cat['i'];
        $first_onglet = $list_cat['first_onglet'];
        $categorie_id = $list_cat['category_name'];
        $os = $list_cat['OS'];
        unset($list_cat['i']);
        unset($list_cat['first_onglet']);
        unset($list_cat['category_name']);
        unset($list_cat['OS']);
    }

	if ($protectedPost['onglet_soft'] == "" || !isset($list_cat[$protectedPost['onglet_soft']])) {
		$protectedPost['onglet_soft'] = $first_onglet;
	}

	if ($i <= 10) {
		echo "<p>";
		onglet($list_cat, $form_name, "onglet_soft", 5);
		echo "</p>";
	} else {
		echo "<p>" . $l->g(398) . ": " . show_modif($list_cat, 'onglet_soft', 2, $form_name) . "</p>";
	}

    //delete regex
    if (is_defined($protectedPost['SUP_PROF'])) {
        $reqDreg = "DELETE FROM software_category_exp WHERE CATEGORY_ID ='" . $categorie_id[$list_cat[$protectedPost['onglet_soft']]] . "' AND ID = ".$protectedPost['SUP_PROF'];
        mysqli_query($_SESSION['OCS']["writeServer"], $reqDreg) or die(mysqli_error($_SESSION['OCS']["writeServer"]));
        unset($list_cat[$protectedPost['SUP_PROF']]);
    }

    $reg = $softCat->display_reg($categorie_id[$list_cat[$protectedPost['onglet_soft']]]);

    //You can delete or not?
    if ($i != 1 && isset($list_cat[$protectedPost['onglet_soft']])) {
        echo "<a href=# OnClick='return confirme(\"\",\"" . $protectedPost['onglet_soft'] . "\",\"" . $form_name . "\",\"SUP_CAT\",\"" . $l->g(640) . "\");'>" . $l->g(921) . "</a></br>";
    }

    if($os_version[$os[$list_cat[$protectedPost['onglet_soft']]]] != null){
      	echo "<br><br><h4>".$l->g(274)." : ".$os_version[$os[$list_cat[$protectedPost['onglet_soft']]]]."</h4><br>";
    }else{
      	echo "<br><br><h4>".$l->g(274)." : ".$l->g(1515)."</h4><br>";
    }

    if(!empty($list_cat)){
        echo "<h4>".$l->g(1504)."</h4><br>";
        echo "<div class='tableContainer'>
                <div id='affich_regex_wrapper' class='dataTables_wrapper form-inline no-footer'>
                  <div>
                    <div class='dataTables_scroll'>
                      <div class='dataTables_scrollHead' style='overflow: hidden; position: relative; border: 0px; width: 100%;'>
                        <div class='dataTables_scrollHeadInner' style='box-sizing: content-box; width: 998px; padding-left: 0px;'>
                          <table width='100%' class='table table-striped table-condensed table-hover cell-border dataTable no-footer' role='grid' style='margin-left: 0px; width: 998px;'>
                            <thead>
                              <tr role='row'>
                                <th class='SOFTWARE_EXP' tabindex='0' aria-controls='affich_regex' rowspan='1' colspan='1' style='width: 22%;' aria-label='Regular expression or Software name: activate to sort column ascending'><font>".$l->g(1504)."</font></th>
                                <th class='SOFTWARE_EXP' tabindex='0' aria-controls='affich_version' rowspan='1' colspan='1' style='width: 22%;' aria-label='Version'><font>".$l->g(1511)."</font></th>
                                <th class='SOFTWARE_EXP' tabindex='0' aria-controls='affich_version' rowspan='1' colspan='1' style='width: 22%;' aria-label='Version'><font>".$l->g(277)."</font></th>
                                <th class='SOFTWARE_EXP' tabindex='0' aria-controls='affich_publisher' rowspan='1' colspan='1' style='width: 22%;' aria-label='Publisher'><font>".$l->g(69)."</font></th>
                                <th class='SOFTWARE_EXP' tabindex='0' aria-controls='affich_version' rowspan='1' colspan='1' style='width: 12%;' aria-label='Version'><font>".$l->g(1381)."</font></th>
                              </tr>
                            </thead>
                          </table>
                        </div>
                      </div>
                      <div class='dataTables_scrollBody' style='overflow: auto; width: 100%;'>
                        <table id='affich_regex' class='table table-striped table-condensed table-hover cell-border dataTable no-footer' role='grid' aria-describedby='affich_regex_info' style='width: 100%;'>
                        <tbody>";

        if($reg != null){
            for($i=0; $reg[$i]!=null; $i++){
                echo "<tr class='odd'><td valign='top' colspan='1' style='width: 22%;' class='affich_regex'>".$reg[$i]['NAME']."</td>
                      <td valign='top' colspan='1'  style='width: 22%;' class='affich_publisher'>".$reg[$i]['SIGN']."</td>
                      <td valign='top' colspan='1'  style='width: 22%;'c lass='affich_version'>".$reg[$i]['VERSION']."</td>
                      <td valign='top' colspan='1'  style='width: 22%;' class='affich_publisher'>".$reg[$i]['PUBLISHER']."</td>
                      <td class='ACTIONS' valign='top' colspan='1' style='width: 12%;'><a href=# OnClick='confirme(\"\",\"".$reg[$i]['ID']."\",\"".$form_name."\",\"SUP_PROF\",\"".$l->g(640)."\");'><span class='glyphicon glyphicon-remove'></span></a></td></tr>";
            }
        }else{
            echo "<tr class='odd'><td valign='top' colspan='1' class='affich_regex'>".$l->g(1334)."</td></tr>";
        }
        echo "</tbody></table></div></div></div></div></div><br><br>";

        $table_name = $form_name;
        $tab_options = $protectedPost;
        $tab_options['form_name'] = $form_name;
        $tab_options['table_name'] = $table_name;

        $list_fields = array(
			$l->g(69) => 'PUBLISHER',
			$l->g(382) => 'NAME',
			$l->g(277) => 'VERSION',
        );

        $queryDetails = "SELECT n.NAME, p.PUBLISHER, v.VERSION FROM software_categories_link scl
                        LEFT JOIN software_name n ON n.ID = scl.NAME_ID
                        LEFT JOIN software_publisher p ON p.ID = scl.PUBLISHER_ID 
                        LEFT JOIN software_version v ON v.ID = scl.VERSION_ID 
                        WHERE scl.CATEGORY_ID =".$categorie_id[$list_cat[$protectedPost['onglet_soft']]];
        $default_fields = $list_fields;
        $list_col_cant_del = $list_fields;
        $list_fields[$l->g(51)] = 'COMMENTS';
        $list_fields[$l->g(1248)] = 'FOLDER';
        $list_fields[$l->g(446)] = 'FILENAME';
        $list_fields[ucfirst(strtolower($l->g(953)))] = 'FILESIZE';

        $list_fields['GUID'] = 'GUID';
        $list_fields[ucfirst(strtolower($l->g(1012)))] = 'LANGUAGE';
        $list_fields[$l->g(1238)] = 'INSTALLDATE';
        $list_fields[$l->g(1247)] = 'BITSWIDTH';

        ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
	}else{
		msg_warning($l->g(1506));
	}
}

/**********************************************NEW CATEGORY********************************************************/
if($protectedPost['onglet'] == 'NEW_CAT'){

    if(isset($protectedPost['valid'])){
        $result = $softCat->add_category($protectedPost['cat_name'], $protectedPost['os_version']);
        if($result == true){
          	msg_success($l->g(572));
        }else{
          	msg_error($l->g(573));
        }
    }

    echo "<div class='row margin-top30'>
            <div class='col-sm-10'>";
    formGroup('text', 'cat_name', $l->g(49).' :', '', '', '', '', '', '', "required");
    formGroup('select', 'os_version', $l->g(274).' :', '', '', '', '', $os_version, $os_version);
    echo "<input type='submit' name='valid' id='valid' class='btn btn-success' value='".$l->g(13)."'>";
    echo "</div></div>";
}

/****************************************ADD SOFTWARE TO CATEGORY*************************************************/
if($protectedPost['onglet'] == 'ADD_SOFT'){

  $operatorsArray = [
      "0" => " ",
      "EQUAL" => $l->g(1430),
      "MORE" => $l->g(1513),
      "LESS" => $l->g(1512),
  ];

    if(isset($protectedPost['valid_reg'])){
        if($protectedPost['cat_select'] != 0){
            $result_reg = $softCat->insert_exp($protectedPost['cat_select'], $protectedPost['regular_exp'],$protectedPost['version_sign'], $protectedPost['version_soft'],$protectedPost['vendor_soft']);
            if($result_reg == true){
              msg_success($l->g(572));
              unset($protectedPost['regular_exp']);
            }else{
              msg_error($l->g(573));
            }
        }else{
            msg_error($l->g(1517));
        }
    }

    //autocompletion for Software Name
    $xml_file = "index.php?" . PAG_INDEX . "=" . $pages_refs['ms_options_soft_cat'] . "&no_header=1";
    echo "\n" . '<script type="text/javascript">
    	window.onload = function(){initAutoComplete(document.getElementById(\'' . $form_name . '\'), document.getElementById(\'regular_exp\'), document.getElementById(\'valid_reg\'),\'' . $xml_file . '\')}
    	</script>';

    echo "<div class='row margin-top30'>
            <div class='col-sm-10'>";

    $select_cat = $softCat->search_all_cat();

    if(isset($protectedPost['advanced'])){
        $check = 'checked';
        $resend = 'onfocusOut="this.form.submit()" required';
    }else{
        $check = '';
        $resend = 'required';
        unset($protectedPost['version_sign']);
        unset($protectedPost['version_soft']);
        unset($protectedPost['vendor_soft']);
    }

    formGroup('select', 'cat_select', $l->g(388).' :', '', '',$protectedPost['cat_select'] , '', $select_cat, $select_cat, 'required');
    formGroup('text', 'regular_exp', $l->g(382).' :', '', '', $protectedPost['regular_exp'], '', '', '', $resend);
    echo "<p>".$l->g(358)."</p>";

    echo '<div><input style="display:initial;width:20px;height:14px;" type="checkbox" name="advanced" value="0" id="advanced" class="form-control" '.$check.' onClick="this.form.submit();">'.$l->g(1509).'</div><br/>';

    if(isset($protectedPost['advanced'])){
      $version = $softCat->search_version($protectedPost['regular_exp']);
      $vendor = $softCat->search_vendor($protectedPost['regular_exp']);

      formGroup('select', 'version_sign', $l->g(1510).' :', '', '',$protectedPost['version_sign'] , '', $operatorsArray, $operatorsArray);
      formGroup('select', 'version_soft', $l->g(1507).' :', '', '', $protectedPost['version_soft'], '', $version, $version);
      formGroup('select', 'vendor_soft', $l->g(1508).' :', '', '', $protectedPost['vendor_soft'], '', $vendor, $vendor);
    }

    echo "<input type='submit' name='valid_reg' id='valid_reg' class='btn btn-success' value='".$l->g(13)."'>";
    echo "</div></div>";
}

echo "</div>";
echo "<input type='hidden' name='RESET' id='RESET' value=''>";
echo "<input type='hidden' name='SUP_CAT' id='SUP_CAT' value=''>";
echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
}
