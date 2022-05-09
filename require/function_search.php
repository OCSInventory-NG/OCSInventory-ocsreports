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
/*
 * Fichier de fonctions pour la recherche multicritère.
 */

//définition du poids de chaque table pour optimiser la recherche d'abord sur les tables "peut couteuse"
$weight_table = array("HARDWARE" => 1,
    "DRIVES" => 5,
    "GROUPS_CACHE" => 2,
    "SOFTWARES" => 10,
    "ACCOUNTINFO" => 1,
    "BIOS" => 3,
    "MONITORS" => 1,
    "NETWORKS" => 3,
    "REGISTRY" => 5,
    "DOWNLOAD_HISTORY" => 6,
    "DEVICES" => 3,
    "VIDEOS" => 2,
    "PRINTERS" => 4,
    "CPUS" => 1);
asort($weight_table);

//utilisation des tables de cache pour:
if ($_SESSION['OCS']["usecache"] == true) {
    //liste des tables
    $table_cache = array('SOFTWARES' => 'SOFTWARES_NAME_CACHE');
    //liste des champs correspondants ou la recherche doit se faire
    $field_cache = array('SOFTWARES_NAME_CACHE' => 'NAME');
}

//liste des tables qui ne doivent pas faire des fusions de requête
//cas pour les tables multivaluées
$tab_no_fusion = array("DEVICES", "REGISTRY", "DRIVES", "SOFTWARES", "DOWNLOAD_HISTORY", "PRINTERS", "CPUS", "GROUPS_CACHE");

//define caption of fields
$lbl_fields_calcul['PRINTERS'] = array($l->g(79) . ": " . $l->g(49) => 'printers.name',
    $l->g(79) . ": " . $l->g(278) => 'printers.driver',
    $l->g(79) . ": " . $l->g(279) => 'printers.port',
    $l->g(79) . ": " . $l->g(53) => 'printers.description',
    $l->g(79) . ": " . $l->g(1323) => 'printers.servername',
    $l->g(79) . ": " . $l->g(1324) => 'printers.sharename',
    $l->g(79) . ": " . $l->g(1325) => 'printers.resolution',
    $l->g(79) . ": " . $l->g(51) => 'printers.comment',
    $l->g(79) . ": " . $l->g(1326) => 'printers.shared',
    $l->g(79) . ": " . $l->g(1327) => 'printers.network');

$lbl_fields_calcul['DRIVES'] = array($l->g(838) => 'drives.LETTER',
    $l->g(839) => 'drives.TYPE',
    $l->g(840) => 'drives.FILESYSTEM',
    $l->g(841) => 'drives.TOTAL',
    $l->g(842) => 'drives.FREE',
    $l->g(843) => 'drives.VOLUMN');
$lbl_fields_calcul['GROUPS_CACHE'] = array($l->g(844) => 'groups_cache.GROUP_ID',
    $l->g(845) => 'groups_cache.STATIC');

if (isset($_SESSION['OCS']['USE_NEW_SOFT_TABLES']) && $_SESSION['OCS']['USE_NEW_SOFT_TABLES'] == 1) {
    $lbl_fields_calcul['SOFTWARES'] = array($l->g(846) => 'softwares.PUBLISHER',
        $l->g(847) => 'softwares.NAME_ID',
        $l->g(848) => 'softwares.VERSION_ID',
        addslashes($l->g(849)) => 'softwares.FOLDER',
        $l->g(850) => 'softwares.COMMENTS');
} else {
    $lbl_fields_calcul['SOFTWARES'] = array($l->g(846) => 'softwares.PUBLISHER',
        $l->g(847) => 'softwares.NAME',
        $l->g(848) => 'softwares.VERSION',
        addslashes($l->g(849)) => 'softwares.FOLDER',
        $l->g(850) => 'softwares.COMMENTS');
}

$lbl_fields_calcul['BIOS'] = array($l->g(851) => 'bios.SMANUFACTURER',
    $l->g(852) => 'bios.SMODEL',
    $l->g(853) => 'bios.SSN',
    $l->g(854) => 'bios.TYPE',
    $l->g(855) => 'bios.BMANUFACTURER',
    $l->g(856) => 'bios.BVERSION',
    $l->g(857) => 'bios.BDATE');
$lbl_fields_calcul['MONITORS'] = array($l->g(858) => 'monitors.MANUFACTURER',
    $l->g(859) => 'monitors.CAPTION',
    $l->g(860) => 'monitors.DESCRIPTION',
    $l->g(861) => 'monitors.TYPE',
    $l->g(862) => 'monitors.SERIAL');
$lbl_fields_calcul['NETWORKS'] = array($l->g(863) => 'networks.DESCRIPTION',
    $l->g(864) => 'networks.TYPE',
    $l->g(865) => 'networks.TYPEMIB',
    $l->g(866) => 'networks.SPEED',
    $l->g(867) => 'networks.MACADDR',
    $l->g(868) => 'networks.STATUS',
    $l->g(869) => 'networks.IPADDRESS',
    $l->g(870) => 'networks.IPMASK',
    $l->g(871) => 'networks.IPSUBNET',
    $l->g(872) => 'networks.IPGATEWAY',
    $l->g(873) => 'networks.IPDHCP');
$lbl_fields_calcul['REGISTRY'] = array($l->g(874) => 'registry.NAME',
    $l->g(875) => 'registry.REGVALUE');
$lbl_fields_calcul['CPUS'] = array($l->g(64) => 'cpus.MANUFACTURER',
    $l->g(66) => 'cpus.TYPE',
    $l->g(36) => 'cpus.SERIALNUMBER',
    $l->g(429) => 'cpus.SPEED',
    $l->g(1317) => 'cpus.CORES',
    $l->g(1318) => 'cpus.L2CACHESIZE',
    $l->g(1247) => 'cpus.CPUARCH',
    $l->g(1312) => 'cpus.DATA_WIDTH',
    $l->g(1313) => 'cpus.CURRENT_ADDRESS_WIDTH',
    $l->g(1314) => 'cpus.LOGICAL_CPUS',
    $l->g(1319) => 'cpus.VOLTAGE',
    $l->g(1315) => 'cpus.CURRENT_SPEED',
    $l->g(1316) => 'cpus.SOCKET');

//fonction qui exécute les requetes de la recherche
//et qui retourne les ID des machines qui match.
function execute_sql_returnID($list_id, $execute_sql, $no_cumul = '', $table_name) {
    global $l;
    $debug = '';
    //on parcourt le tableau de requetes
    foreach ($execute_sql as $weight => $id) {
        $i = 0;
        //on prends toutes les requetes qui ont le même poids
        while ($id[$i]) {
            //on cherche a savoir si on est sur la table hardware
            //dans ce cas, la concat des id doit se faire avec le champ ID
            if (substr_count($id[$i], "from hardware")) {
                $name_field_id = " ID ";
                $fin_sql = " and deviceid<>'_SYSTEMGROUP_' AND deviceid <> '_DOWNLOADGROUP_' ";
            } else {
                $name_field_id = " HARDWARE_ID ";
                $fin_sql = "";
                if ($no_cumul == "") {
                    $_SESSION['OCS']['SQL_DATA_FIXE'][$table_name][] = $id[$i];
                } else {
                    $_SESSION['OCS']['SQL_DATA_FIXE'][$table_name][] = str_replace("like", "not like", $id[$i]);
                }
            }
            //si une liste d'id de machine existe,
            //on va concat la requête avec les ID des machines
            if ($list_id != "" and $no_cumul == '') {
                if (is_array($list_id)) {
                    $list = implode(',', $list_id);
                } else {
                    $list = $list_id;
                }
                $id[$i] .= " AND " . $name_field_id . " IN (" . $list . ")";
                unset($list_id);
            }
            $id[$i] .= $fin_sql;
            $result = mysqli_query($_SESSION['OCS']["readServer"], $id[$i]) or mysqli_error($_SESSION['OCS']["readServer"]);
            if ($result) {
                while ($item = mysqli_fetch_object($result)) {
                    $list_id[$item->HARDWARE_ID] = $item->HARDWARE_ID;
                    foreach ($item as $field => $value) {
                        if ($field != "HARDWARE_ID" and $field != "ID") {
                            $tab_options['VALUE'][$field][$item->HARDWARE_ID] = $value;
                        }
                    }
                }
            }
            if ($_SESSION['OCS']['DEBUG'] == 'ON') {
                $debug .= "<hr><br>" . $l->g(5001) . "<br>" . $id[$i] . "<br>" . $l->g(5002) . $weight;
            }
            //si aucun id trouvé => end
            if ($list_id == '') {
                return array('', $tab_options, 'DEBUG' => $debug);
            }
            $i++;
        }
    }
    return array($list_id, $tab_options, 'DEBUG' => $debug);
}

//fonction pour ordonner les requetes en fonction
//du poids de la table
function class_weight($list_sql) {
    global $weight_table;
    foreach ($list_sql as $table_name => $id) {
        $poids = $weight_table[$table_name];
        foreach ($id as $sql) {
            //ajout de la dernière parenthèse pour fermer la requête
            $execute_sql[$poids][] = $sql . '))';
        }
    }
    ksort($execute_sql);
    return $execute_sql;
}

//fonction qui permet de prendre en compte les requêtes intermédiaires pour
//la création des groupes dynamiques
function traitement_cache($sql_temp, $field_modif, $field_value, $field_value_complement) {
    if ($sql_temp != "") {
        if ($field_modif == "field_value") {
            $field_value = " (" . $sql_temp . ") ";
        } else {
            $value_complement_temp = explode('(', $field_value_complement);
            $value_complement_temp2 = explode(')', $value_complement_temp[1]);
            if (substr(trim($value_complement_temp[0]), -2) != 'IN') {
                $in = 'IN';
            } else {
                $in = '';
            }
            $field_value_complement = $value_complement_temp[0] . " " . $in . " (" . $sql_temp . ") " . $value_complement_temp2[1];
        }
    }
    return array('field_value' => $field_value, 'field_value_complement' => $field_value_complement);
}

//fonction qui permet de passer en SESSION
//les requetes pour la création des groupes dynamiques
function sql_group_cache($cache_sql) {
    unset($_SESSION['OCS']['SEARCH_SQL_GROUP']);
    //requête de recherche "normale" (ressemble, exactement)
    if ($cache_sql['NORMAL']) {

        foreach ($cache_sql['NORMAL'] as $list) {
            $i = 0;
            while ($list[$i]) {
                $fin_sql = "";
                if (substr_count($list[$i], "from hardware")) {
                    $fin_sql = " and deviceid<>'_SYSTEMGROUP_' AND deviceid <> '_DOWNLOADGROUP_' ";
                } else {
                    $fin_sql = "";
                }
                $_SESSION['OCS']['SEARCH_SQL_GROUP'][] = $list[$i] . $fin_sql;
                $i++;
            }
        }
    }
    //requête de recherche "différent", "n'appartient pas"
    if ($cache_sql['DIFF']) {
        foreach ($cache_sql['DIFF'] as $list) {
            $i = 0;
            while ($list[$i]) {
                $fin_sql = "";
                if (substr_count($list[$i], "from hardware")) {
                    $fin_sql = " and deviceid<>'_SYSTEMGROUP_' AND deviceid <> '_DOWNLOADGROUP_' ";
                } else {
                    $fin_sql = "";
                }
                $_SESSION['OCS']['SEARCH_SQL_GROUP'][] = "select distinct id as HARDWARE_ID from hardware where id not in (" . $list[$i] . ")" . $fin_sql;
                $i++;
            }
        }
    }
}

//fonction pour prendre en compte les jockers dans la saisie (* et ?)
function jockers_trait($field_value) {
    $field_value_modif = $field_value;
    //prise en compte du caractère * pour les champs
    $count_ast = substr_count($field_value, "*");
    //si au moins un * a été trouvé
    if ($count_ast > 0) {
        $field_value_modif = str_replace("*", "%", $field_value);
    }

    //prise en compte du caractère ? pour les champs
    $count_intero = substr_count($field_value_modif, "?");
    //si au moins un ? 	a été trouvé
    if ($count_intero > 0) {
        $field_value_modif = str_replace("?", "_", $field_value_modif);
    }
    //on retourne la valeur traitée
    //echo "<br>".$field_value_modif."<br>".$field_value."<br>";
    if ($field_value_modif == $field_value) {
        return "'%" . $field_value . "%'";
    } else {
        return "'" . $field_value_modif . "'";
    }
}

//function for search on date
function compair_with_date($field, $field_value) {
    global $l;
    //convert string to date for some fields
    if ($field == "LASTDATE" || $field == "LASTCOME" || $field == "REGVALUE") {
        $d = str_replace('/', '-', $field_value);
        $tab_date = explode('-', $d);
        // expect 3 fields
        if (count($tab_date) == 3) {
            $field_value = "str_to_date('" . $field_value . "', '" . $l->g(269) . "')";
        }
    }
    return array('field' => $field, 'field_value' => $field_value);
}

//fonction qui permet de créer le début des requêtes à exécuter
function generate_sql($table_name) {
    global $lbl_fields_calcul;
    if ($table_name == "HARDWARE") {
        $VALUE_id = "ID";
        $entre = " as HARDWARE_ID";
    } else {
        $VALUE_id = "HARDWARE_ID";
        $complement_id = ",";
        if (isset($lbl_fields_calcul[$table_name])) {
            foreach ($lbl_fields_calcul[$table_name] as $key => $value) {
                $complement_id .= $value . " as '" . $key . "',";
            }
        }
        $complement_id = substr($complement_id, 0, -1);
    }
    $sql_temp = "select distinct " . $VALUE_id . $entre . $complement_id . " from " . strtolower($table_name) . " where (";
    $sql_cache = "select distinct " . $VALUE_id . $entre . " from " . strtolower($table_name) . " where (";
    return array('sql_temp' => $sql_temp, 'sql_cache' => $sql_cache);
}

//fonction qui permet d'afficher la ligne de recherche en fonction
//du type du champ
function show_ligne($value, $id_field, $ajout, $form_name) {

    global $optSelectField, $opt2SelectField, $opt2Select, $optSelect2Field, $opt3Select, $optSelect, $optArray, $l, $protectedPost;

    echo "<div class='row'>";
    echo "<div class='col-md-12'>";

    $nameField = $value . "-" . $id_field;
    if ($ajout != '') {
        $and_or = show_modif(array('AND' => 'AND', 'OR' => 'OR'), "SelAndOr-" . $nameField, 2, '', array('DEFAULT' => 'NO'));
    }
    //si le champ comporte une valeur du champ select par défaut
    if (array_key_exists($value . '-SELECT', $optArray)) {
        //on prend les valeurs du champ
        $champ_select = $optArray[$value . '-SELECT'];
    } else { //si on garde les valeurs par défaut
        $champ_select = array('exact' => $l->g(410), 'ressemble' => $l->g(129), 'diff' => $l->g(130));
    }

	//on génére le premier champ select
	$select="<select name='SelComp-".$nameField."' id='SelComp-".$nameField."' class='form-control'>";
	$countHl=0;
	foreach ($champ_select as $k=>$v){
		//si un javascript a été passé en paramètre
		if ($k!='javascript'){
			//on remplace la chaine générique field_name du javascript par le vrai nom de champ
			$champ_select['javascript'][$k] =str_replace("field_name", $nameField, $champ_select['javascript'][$k]);
			$select .= "<option value='".$k."' ".($protectedPost['SelComp-'.$nameField] == $k ? " selected":"")." ".$champ_select['javascript'][$k]." ".($countHl%2==1?" class='hi'":" class='down'")." >".$v."</option>";
		}
		$countHl++;
	}
	$select .= "</select>";

	//on affiche le début de ligne
	if ($ajout != '') echo $and_or;
	echo "<div class='form-group'>";
	echo "<label for='InputValue-".$nameField."' class='col-sm-2 control-label'>".$optArray[$value]."</label>";
	echo "<div class='col-sm-10'>";

	//echo "<div class='input-group date form_datetime'>";

    //TITRE,CHAMP (EGAL,LIKE,NOTLIKE),valeur
    if( array_key_exists($value,$optSelectField)) {
        echo $select;
        if ($optSelectField[$value . "-LBL"] == "calendar") {
          echo "<div class='input-group date form_datetime'>";
        }
        echo "<input type='text' name='InputValue-" . $nameField . "' class='form-control' id='InputValue-" . $nameField . "' value='".$protectedPost['InputValue-' . $nameField ]."'>";
        if ($optSelectField[$value . "-LBL"] == "calendar") {
            echo "<span class='input-group-addon'>";
            echo calendars("InputValue-" . $nameField, $l->g(1270));
            echo "</span>";
            echo "</div>";
        }
    }
    //TITRE,CHAMPSELECT,(pour $optSelect)
    //et les champs suivants en plus pour $opt2SelectField: CHAMP (EGAL,LIKE,NOTLIKE) et valeur
    if( array_key_exists($value,$opt2SelectField) or array_key_exists($value,$optSelect)){
        if (array_key_exists($value,$opt2SelectField)){
            $data=$opt2SelectField;
            //nom en Value3 car le traitement doit se faire sur la valeur de ce champ (cas particulier)
            $name_select='SelFieldValue3';
        }
        else{
            $data=$optSelect;
            $name_select='SelFieldValue';
        }
        $select2="<select name='".$name_select."-".$nameField."' id='".$name_select."-".$nameField."' class='form-control'>";

        if (is_array($data[$value.'-SQL1'])){
            foreach ($data[$value.'-SQL1'] as $k=>$v){
                $select2 .= "<option value='".$k."' ".($protectedPost[$name_select."-".$nameField] == $k ? " selected":"").">".$v."</option>";
            }
        }else{
            $result = mysqli_query($_SESSION['OCS']["readServer"], $data[$value.'-SQL1']);
            while( $val = mysqli_fetch_array( $result ) ) {
                //$val= utf8_encode($val);
                foreach ($val as $name_of_field=>$value_of_request){
                    if (!is_numeric($name_of_field) and $name_of_field != 'ID'){
                        if (!isset($val['ID'])){
                            $val['ID']=$value_of_request;
                        }
                        //	echo $val['ID']."=>".$value_of_request."<br>";
                        $select2 .= "<option value='".$val['ID']."' ".($protectedPost[$name_select.'-'.$nameField] == $val['ID'] ? " selected":"").">".$value_of_request."</option>";
                    }
                }
            }
        }
        $select2 .= "</select>";
        echo $select2;
        if (array_key_exists($value,$opt2SelectField)){
            if ($optSelect2Field[$value . "-LBL"] == "calendar") {
              echo "<div class='input-group date form_datetime'>";
            }
            echo $select."<input name='InputValue-".$nameField."' class='form-control' id='InputValue-".$nameField."' value='".$protectedPost['InputValue-' . $nameField ]."'>";
            if ($optSelect2Field[$value."-LBL"] == "calendar") {
                echo "<span class='input-group-addon'>";
                echo calendars("InputValue-" . $nameField, $l->g(1270));
                echo "</span>";
                echo "</div>";
            }
        }
    }
    //TITRE,CHAMP (EGAL,LIKE,NOTLIKE),CHAMPSELECT
    if( array_key_exists($value,$opt2Select)){
        $selectValue="<select name='SelFieldValue-".$nameField."' id='SelFieldValue-".$nameField."' class='form-control'>";
        if (is_array($opt2Select[$value.'-SQL1'])){
            foreach ($opt2Select[$value.'-SQL1'] as $k=>$v){
                $selectValue .= "<option value='".$k."' ".($protectedPost['SelFieldValue-'.$nameField] == $k ? " selected":"").">".$v."</option>";
            }
        }else{
            $result = mysqli_query($_SESSION['OCS']["readServer"] ,$opt2Select[$value.'-SQL1']);
            while( $val = mysqli_fetch_array( $result ) ) {
                if (!isset($val['ID']))
                    $val['ID']=$val['NAME'];
                $selectValue .= "<option value='".$val['ID']."' ".($protectedPost['SelFieldValue-'.$nameField] == $val['ID'] ? " selected":"").">".$val['NAME']."</option>";
            }
        }
        $selectValue .= "</select>";
        echo $select.$selectValue;
    }
    //TITRE,CHAMPSELECT,valeur1,valeur2
    if( array_key_exists($value,$optSelect2Field)){
        //gestion de la vision du deuxieme champ de saisi
        //on fonction du POST
        if ($protectedPost['SelComp-'.$nameField] == "between")
            $display="inline";
        else
            $display="none";

        echo $select."<input name='InputValue-".$nameField."' id='InputValue-".$nameField."' value=\"".stripslashes($protectedPost["InputValue-".$nameField])."\">
				 <div style='display:".$display."' id='FieldInput2-".$nameField."'> --
				 <input name='InputValue2-".$nameField."' value=\"".stripslashes($protectedPost["InputValue2-".$nameField])."\">
				 </div>".$optSelect2Field[$value."-LBL"];
    }

    if( array_key_exists($value,$opt3Select)){
        $selectValue1="<select name='SelFieldValue-".$nameField."' id='SelFieldValue-".$nameField."' class='form-control'>";
        $result = mysqli_query($_SESSION['OCS']["readServer"] ,$opt3Select[$value.'-SQL1']);
        while( $val = mysqli_fetch_array( $result ) ) {
            if (!isset($val['ID']))
                $val['ID']=$val['NAME'];
            $selectValue1 .= "<option value='".$val['ID']."' ".($protectedPost['SelFieldValue-'.$nameField] == $val['ID'] ? " selected":"").">".$val['NAME']."</option>";
        }
        $selectValue1 .= "</select>";

        $selectValue2="<select name='SelFieldValue2-".$nameField."' id='SelFieldValue2-".$nameField."' class='form-control'>";
        $result = mysqli_query( $_SESSION['OCS']["readServer"],$opt3Select[$value.'-SQL2'] );
        while( $val = mysqli_fetch_array( $result ) ) {
            if (!isset($val['ID']))
                $val['ID']=$val['NAME'];
            $selectValue2 .= "<option value='".$val['ID']."' ".($protectedPost['SelFieldValue2-'.$nameField] == $val['ID'] ? " selected":"").">".$val['NAME']."</option>";
        }
        $selectValue2 .= "</select>";
        echo $select;
        echo "<input class='form-control' id='disabledInput' value='".$l->g(667)." :' type='text' disabled>";
        echo $selectValue1;
        echo "<input class='form-control' id='disabledInput' value='".$l->g(546)." :' type='text' disabled>";
        echo $selectValue2;
    }

    //echo "</div>"; // input group
    echo "<button class='btn btn-danger btn-block' alt='".$l->g(41)."' onclick='pag(\"".$id_field."\",\"delfield\",\"".$form_name."\");'><span class='glyphicon glyphicon-remove delete-span delete-span-xs' style='color:white'></span></button>";
    echo "</div>"; // col
    echo "</div>"; // form group
    echo "</div>"; // col

    echo "</div>"; // row

}

function add_trait_select($img,$list_id,$form_name,$list_pag,$comp = false)
{
	global 	$l;
	$_SESSION['OCS']['ID_REQ']=id_without_idgroups($list_id);
	echo "<script language=javascript>
		function garde_check(image,id,computer)
		 {
			var idchecked = '';
			var cptr = 0;
			for(i=0; i<document." . $form_name . ".elements.length; i++)
			{
				if(document." . $form_name . ".elements[i].name.substring(0,5) == 'check'){
			        if (document." . $form_name . ".elements[i].checked){
						idchecked = idchecked + document." . $form_name . ".elements[i].name.substring(5) + ',';
						cptr ++;
					}
				}
			}

			if(computer){
				if(cptr == 0){
					alert('" . addslashes($l->g(7015)) . "');
					return;
				}
			}

			idchecked = idchecked.substr(0,(idchecked.length -1));
			if(!computer){
				window.open(\"index.php?" . PAG_INDEX . "=\"+image+\"&head=1&idchecked=\"+idchecked,\"rollo\");
			}else{
				window.open(\"index.php?" . PAG_INDEX . "=\"+image+\"&head=1&idchecked=\"+idchecked+\"&comp=\"+computer,\"rollo\");
			}
		}
	</script>";
        ?>
        <div class="btn-group">
            <?php
            foreach ($img as $key => $value) {
                $list_id = is_array($list_id) ? implode(',', $list_id) : $list_id;
                echo '<button type="button" onclick=garde_check("' . $list_pag[$key] . '","' . $list_id . '","' . $comp . '") class="btn">' . $value . '</button>';
            }
            ?>
        </div>

        <?php
    }

    function multi_lot($form_name, $lbl_choise) {
        global $protectedPost, $protectedGet, $l;

        echo "<div class='row'>";
        echo "<div class='col col-md-10 col-md-offset-1'>";

        $list_id = "";
        if (!isset($protectedGet['origine'])) {
            if (is_defined($protectedGet['idchecked'])) {
                $choise_req_selection['NONE'] = " ";
                if (!isset($protectedGet['comp'])) {
                    $choise_req_selection['REQ'] = $l->g(584);
                    $choise_req_selection['SEL'] = $l->g(585);
                } else {
                    $choise_req_selection['SEL'] = $l->g(585);
                }
                formGroup('select', 'CHOISE', $lbl_choise, '', '', $protectedPost['CHOISE'], '', $choise_req_selection, $choise_req_selection, "onchange='$(\"#".$form_name."\").submit();'");
            }
            if ((isset($protectedPost['CHOISE']) && $protectedPost['CHOISE'] == 'REQ') || $protectedGet['idchecked'] == '') {
                msg_info($l->g(901));
                if ($protectedGet['idchecked'] == '') {
                    echo "<input type='hidden' name='CHOISE' value='" . ($protectedPost['CHOISE'] ?? '') . "'>";
                    $protectedPost['CHOISE'] = 'REQ';
                }
                $list_id = $_SESSION['OCS']['ID_REQ'];
            }
            if ($protectedPost['CHOISE'] == 'SEL') {
                msg_info($l->g(902));
                $list_id = $protectedGet['idchecked'];
            }
            //gestion tableau
            if (is_array($list_id)) {
                $list_id = implode(",", $list_id);
            }
        } else {
            $list_id = $protectedGet['idchecked'];
        }

        echo "</div>";
        echo "</div>";

        if ($list_id != "") {
            return $list_id;
        } else {
            return false;
        }
    }

    function found_soft_type($type, $id = "", $name = "") {
        $sql = "select id, name from %s ";
        $arg = array($type);
        if ($id != "") {
            $sql .= " where id=%s";
            array_push($id, $arg);
        } elseif ($name != "") {
            $sql .= " where name='%s'";
            array_push($name, $arg);
        }
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
        if($result) while ($item = mysqli_fetch_object($result)) {
            $res[$item->id] = $item->name;
        }
        return $res;
    }

    function id_without_idgroups($list_id) {
        $sql = "select id from hardware where deviceid <> '_SYSTEMGROUP_'
										AND deviceid <> '_DOWNLOADGROUP_'
										AND id in ";
        $arg = array();
        $sql = mysql2_prepare($sql, $arg, $list_id);
        $result = mysql2_query_secure($sql['SQL'], $_SESSION['OCS']["readServer"], $sql['ARG']);
        if($result) while ($item = mysqli_fetch_object($result)) {
            $res[$item->id] = $item->id;
        }

        return $res ?? null;
    }
    ?>
