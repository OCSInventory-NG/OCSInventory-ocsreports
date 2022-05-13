<script type="text/javascript">

    function convertToUpper(v_string) {
        v_string.value = v_string.value.toUpperCase();
    }

    function codeTouche(evenement) {
        for (prop in evenement) {
            if (prop == 'which')
                return(evenement.which);
        }
        return(evenement.keyCode);
    }

    function pressePapierNS6(evenement, touche)
    {
        var rePressePapierNS = /[cvxz]/i;

        for (prop in evenement)
            if (prop == 'ctrlKey')
                isModifiers = true;
        if (isModifiers)
            return evenement.ctrlKey && rePressePapierNS.test(touche);
        else
            return false;
    }

    function scanTouche(evenement, exReguliere) {
        var reCarSpeciaux = /[\x00\x08\x0D\x03\x16\x18\x1A]/;
        var reCarValides = exReguliere;
        var codeDecimal = codeTouche(evenement);
        var car = String.fromCharCode(codeDecimal);
        var autorisation = reCarValides.test(car) || reCarSpeciaux.test(car) || pressePapierNS6(evenement, car);
        var toto = autorisation;
        return autorisation;
    }

    $(document).ready(function() {
        $('.option-auto').select2();
    });
</script>


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

$numeric = "onKeyPress='return scanTouche(event,/[0-9]/)'
		  onkeydown='convertToUpper(this)'
		  onkeyup='convertToUpper(this)'
		  onblur='convertToUpper(this)'
		  onclick='convertToUpper(this)'";

$sup1 = "<br><font color=green size=1><i> (" . $l->g(759) . " 1)</i></font>";
$sup10 = "<br><font color=green size=1><i> (" . $l->g(759) . " 10)</i></font>";

/*
 *
 * function for add ligne in tab
 * $name= varchar : name of ligne
 * $lbl= varchar : wording of the ligne
 * $type= varchar : type of the ligne. in (radio,checkbox,input,text,select)
 * $data= array : data of type ex: 'BEGIN'=> text behing the field,
 * 								   'VALUE'=> value of the field
 * 								   'END'=> text after the field
 * 								   'SIZE'=> field size
 * 								   'MAXLENGTH'=> field MAXLENGTH
 * 								   'JAVASCRIPT'=> if you want a javascript on the field
 * 								   'CHECK' => only for checkbox for ckeck the good boxes
 * $data_hidden = data of hidden ex: 'HIDDEN'=> name of field when you clic on, a hidden field appear
 * 									 'HIDDEN_VALUE'=> value of the hidden field
 * 									 'END'=> what you see after the field
 * 									 'JAVASCRIPT'=> if you want a javascript on the hidden field
 */

function ligne($name, $lbl, $type, $data, $data_hidden = '', $readonly = '', $helpInput = '') {
    global $l;
    echo "<hr />";
    echo "<div class='row config-row'>";
    echo "<div class='col-md-6'>";
    echo "<label for='" . $name . "'>" . $name . "</label>";
    echo "<p class='help-block'>" . $lbl . "</p>";
    echo "</div>";

    echo "<div class='col-md-4'>";
    //si on est dans un type bouton ou boite à cocher
    echo "<div class='form-group'>";
    if ($type == 'radio' || $type == 'checkbox') {
        if ($data_hidden != '') {
            //javascript for hidden or show an html DIV
            echo "<script language='javascript'>
					function active(id, sens) {
						var mstyle = document.getElementById(id).style.display	= (sens!=0?\"block\" :\"none\");
					}
					</script>";
        }
        //si le champ hidden est celui qui doit être affiché en entrée, il faut afficher le champ
        if (isset($data_hidden['HIDDEN']) && $data_hidden['HIDDEN'] == $data['VALUE']) {
            $display = "block";
        } else {
            $display = "none";
        }
        //var for name of chekbox
        $i = 1;
        //pour toutes les valeurs
        foreach ($data as $key => $value) {
            //sauf la valeur à afficher
            if ($key !== 'VALUE' && $key !== 'CHECK' && $key !== 'JAVASCRIPT') {
                echo "<input type='" . $type . "' class='' value='" . $key . "' id='" . $name . "' ";
                if ($readonly != '') {
                    echo "disabled=\"disabled\"";
                }
                echo "name='" . $name;
                if ($type == 'checkbox') {
                    echo "_" . $i;
                    $i++;
                }
                echo "'";
                //si un champ hidden est demandé, on gère l'affichage par javascript
                if ($data_hidden != '' && $data_hidden['HIDDEN'] == $key) {
                    echo "OnClick=\"active('" . $name . "_div',1);\"";
                } elseif ($data_hidden != '' && $data_hidden['HIDDEN'] != $key) {
                    echo "OnClick=\"active('" . $name . "_div',0);\"";
                } elseif (isset($data['JAVASCRIPT'])) {
                    echo $data['JAVASCRIPT'];
                }
                if ((isset($data['VALUE']) && $data['VALUE'] == $key) || isset($data['CHECK'][$key])) {
                    echo "checked";
                }
                echo ">" . $value;
                if ($data_hidden != '' && $data_hidden['HIDDEN'] == $key) {
                    if (isset($data_hidden['MAXLENGTH'])) {
                        $maxlength = $data_hidden['MAXLENGTH'];
                    } elseif (isset($data_hidden['SIZE'])) {
                        $maxlength = $data_hidden['SIZE'];
                    } else {
                        $maxlength = "2";
                    }
                    echo "<div id='" . $name . "_div' style='display:" . $display . "'>";

                    echo "<div class='input-group'>";
                    if (isset($data_hidden['BEGIN']) && $data_hidden['BEGIN'] != '') {
                        echo "<span class='input-group-addon'>" . $data_hidden['BEGIN'] . "</span>";
                    }
                    echo "<input class='form-control input-sm' type='text' maxlength='" . $maxlength . "' id='" . $name . "_edit' name='" . $name . "_edit' value='" . $data_hidden['HIDDEN_VALUE'] . "' " . ($data_hidden['JAVASCRIPT'] ?? ''). ">";

                    if (isset($data_hidden['END']) && $data_hidden['END'] != '') {
                        echo "<span class='input-group-addon'>" . $data_hidden['END'] . "</span>";
                    }

                    echo "</div>";
                    echo "<p class='help-block'>" . $helpInput . "</p>";
                    echo "</div>";
                }
                echo "<br>";
                if (isset($data['JAVASCRIPT'])) {
                    echo "<input type='hidden' name='Valid' value='" . $l->g(103) . "'>";
                }
            }
        }
    } elseif ($type == 'input') {
        if ($readonly != '') {
            $ajout_readonly = " disabled=\"disabled\" style='color:black; background-color:#e1e1e2;'";
        }
        echo "<div class='input-group'>";
        if (isset($data['BEGIN']) || !empty($data['BEGIN'])) {
            echo "<span class='input-group-addon'>" . $data['BEGIN'] . "</span>";
        }
        echo "<input " .($ajout_readonly ?? ''). "  class='form-control input-sm' type='text' name='" . $name . "' id='" . $name . "' value='" . $data['VALUE'] . "' maxlength=" . $data['MAXLENGTH'] . " " . ($data['JAVASCRIPT'] ?? '') . ">";

        if (!empty($data['END']) || isset($data['END'])) {
            echo "<span class='input-group-addon'>" . $data['END'] . "</span>";
        }
        echo "</div>";
        echo "<p class='help-block'>" . $helpInput . "</p>";
    } elseif ($type == 'text') {
        echo $data[0];
    } elseif ($type == 'list') {
        echo "<table>";
        if (isset($data['END'])) {
            echo "<tr><td>" . $data['END'] . "</td></tr>";
        }
        if (is_array($data['VALUE'])) {
            foreach ($data['VALUE'] as $value) {
                echo "<tr><td>" . $value . "</td></tr>";
            }
        }
        echo "</table>";
    } elseif ($type == 'select') {
        echo "<select class='form-control' name='" . $name . "'";
        if (isset($data['RELOAD'])) {
            echo " onChange='document." . $data['RELOAD'] . ".submit();'";
        }
        echo ">";
        foreach ($data['SELECT_VALUE'] as $key => $value) {
            echo "<option value='" . $key . "'";
            if ($data['VALUE'] == $key) {
                echo " selected";
            }
            echo ">" . $value . "</option>";
        }
        echo "</select>";
    } elseif ($type == 'select2') {
        $data["VALUE"] = explode(",", $data["VALUE"]);
        $data["VALUE"] = array_flip($data["VALUE"]);

        echo "<select class='form-control option-auto' name='" . $name . "' multiple='multiple'>";
        foreach ($data['SELECT_VALUE'] as $key => $value) {
            echo "<option value='" . $key . "'";
            if (array_key_exists($key, $data['VALUE'])) {
                echo " selected";
            }
            echo ">" . $value . "</option>";
        }
        echo "</select>";
    }elseif ($type == 'long_text') {
        echo "<textarea name='" . $name . "' id='" . $name . "' cols='" . $data['COLS'] . "' rows='" . $data['ROWS'] . "'  class='down' " . ($data['JAVASCRIPT'] ?? '') . ">" . $data['VALUE'] . "</textarea>" . ($data['END'] ?? '');
    }elseif($type == 'password'){
        echo "<input class='form-control input-sm' type='password' name='" . $name . "' id='" . $name . "' value='" . $data['VALUE'] . "' maxlength=" . $data['MAXLENGTH'] . " " . ($data['JAVASCRIPT'] ?? ''). ">";
        echo "<p class='help-block'>" . $helpInput . "</p>";
    } else {
        echo $data['LINKS'];
    }
    echo "</div>";
    echo "</div>";
    echo "</div>";
    echo "</td></tr>";
}

function debut_tab() {
    echo "<table cellspacing='5' border= '0'
				 width='70%'
				ALIGN = 'Center' >";
}

function verif_champ() {
    global $protectedPost;
    $supp1 = array("DOWNLOAD_CYCLE_LATENCY", "DOWNLOAD_FRAG_LATENCY", "DOWNLOAD_PERIOD_LATENCY",
        "DOWNLOAD_PERIOD_LENGTH", "DOWNLOAD_TIMEOUT", "PROLOG_FREQ", "INTERFACE_LAST_CONTACT", "IPDISCOVER_MAX_ALIVE",
        "GROUPS_CACHE_REVALIDATE", "GROUPS_CACHE_OFFSET", "LOCK_REUSE_TIME", "INVENTORY_CACHE_REVALIDATE",
        "IPDISCOVER_BETTER_THRESHOLD", "GROUPS_CACHE_OFFSET", "GROUPS_CACHE_REVALIDATE", "INVENTORY_FILTER_FLOOD_IP_CACHE_TIME",
        "SESSION_VALIDITY_TIME", "IPDISCOVER_LATENCY", "SECURITY_AUTHENTICATION_NB_ATTEMPT", "SECURITY_AUTHENTICATION_TIME_BLOCK", 
        "SECURITY_PASSWORD_MIN_CHAR");
    $supp10 = array("IPDISCOVER_LATENCY");
    $file_exist = array('CONF_PROFILS_DIR' => array('FIELD_READ' => 'CONF_PROFILS_DIR_edit', 'END' => "/conf/", 'FILE' => "4all_config.txt", 'TYPE' => 'r'),
        'DOWNLOAD_PACK_DIR' => array('FIELD_READ' => 'DOWNLOAD_PACK_DIR_edit', 'END' => "/download/", 'FILE' => "", 'TYPE' => 'r'),
        'IPDISCOVER_IPD_DIR' => array('FIELD_READ' => 'IPDISCOVER_IPD_DIR_edit', 'END' => "/ipd/", 'FILE' => "", 'TYPE' => 'r'),
        'LOG_DIR' => array('FIELD_READ' => 'LOG_DIR_edit', 'END' => "/logs/", 'FILE' => "", 'TYPE' => 'r'),
        'LOG_SCRIPT' => array('FIELD_READ' => 'LOG_SCRIPT_edit', 'END' => "/scripts/", 'FILE' => "", 'TYPE' => 'r'),
        'OLD_CONF_DIR' => array('FIELD_READ' => 'OLD_CONF_DIR_edit', 'END' => "/old_conf/", 'FILE' => "", 'TYPE' => 'r'),
        'DOWNLOAD_REP_CREAT' => array('FIELD_READ' => 'DOWNLOAD_REP_CREAT_edit', 'END' => "", 'FILE' => "", 'TYPE' => 'r'),
        'CUSTOM_THEME' => array('FIELD_READ' => 'CUSTOME_THEME_edit', 'END' => "", 'FILE' => "", 'TYPE' =>'r'));

    foreach ($file_exist as $key => $value) {
        if ((isset($protectedPost[$key])) && (($protectedPost[$key] == 'CUSTOM') || preg_match('/^(\/+\w{0,}){0,}/', $protectedPost[$key]) == true)) {
            //Try to find a file
            if ($value['FILE'] != '' ) {
                if ($protectedPost[$value['FIELD_READ']] != '' and ! @fopen($protectedPost[$value['FIELD_READ']] . $value['END'] . $value['FILE'], $value['TYPE'])) {
                    $tab_error[$key] = array('FILE_NOT_EXIST' => $protectedPost[$value['FIELD_READ']] . $value['END'] . $value['FILE']);
                }
                //Try to find a directory
            } elseif (!is_dir($protectedPost[$value['FIELD_READ']] . $value['END'])) {
                if ($protectedPost[$value['FIELD_READ']] != '') {
                    $tab_error[$key] = array('FILE_NOT_EXIST' => $protectedPost[$value['FIELD_READ']] . $value['END']);
                }
            }
        }
    }

    $i = 0;
    if(is_array($supp1[$i])) {
        while ($supp1[$i]) {
            if (isset($protectedPost[$supp1[$i]]) && $protectedPost[$supp1[$i]] < 1) {
                $tab_error[$supp1[$i]] = '1';
            }
        $i++;
        }
    }

    $i = 0;
    if(is_array($supp1[$i])) {
        while ($supp10[$i]) {
            if (isset($protectedPost[$supp10[$i]]) && $protectedPost[$supp10[$i]] < 10) {
                $tab_error[$supp10[$i]] = '10';
            }
            $i++;
        }
    }
    return $tab_error ?? '';
}

function fin_tab($disable = '') {
    global $l;
    if ($disable != '') {
        $gris = "disabled=disabled";
    } else {
        $gris = "OnClick='garde_valeur(\"RELOAD\",\"RELOAD_CONF\");'";
    }
    echo "<br /><input type='submit' class='btn btn-success' name='Valid' value='" . $l->g(103) . "' $gris>";
}

/*
 * function for update, or delete or insert a value in config table
 * $name => value of field 'NAME' (name of config option)
 * $value => value of this config option
 * $default_value => last value of this field
 * $field => 'ivalue' or 'tvalue'
 */

function insert_update($name, $value, $default_value, $field) {
    global $l;

    if(is_array($value)){
        $value = implode(',', $value);
    }

    if ($default_value != $value) {
        $arg = array($field, $value, $name);

        if ($default_value != '') {
            $sql = "update config set %s = '%s' where NAME ='%s'";
        } else {
            $sql = "insert into config (%s, NAME) value ('%s','%s')";
        }
        mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg, $l->g(821));

        if($name == 'CUSTOM_THEME'){
            ?> <script> reload(); </script> <?php
        }
    }
}

function delete($name) {
    $sql = "delete from config where name='%s'";
    $arg = array($name);
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
}

function update_config($name, $field, $value, $msg = true) {
    global $l;
    $sql = "update config set %s='%s' where name='%s'";
    $arg = array($field, $value, $name);
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
    if ($msg) {
        msg_success($l->g(1200));
    }
}

function update_default_value($POST) {
    global $l;
    //tableau des champs ou il faut juste mettre à jour le tvalue
    $array_simple_tvalue = array('DOWNLOAD_SERVER_URI', 'DOWNLOAD_SERVER_DOCROOT',
        'OCS_FILES_FORMAT', 'OCS_FILES_PATH',
        'CONEX_LDAP_SERVEUR', 'CONEX_LDAP_PORT', 'CONEX_DN_BASE_LDAP',
        'CONEX_LOGIN_FIELD', 'CONEX_LDAP_PROTOCOL_VERSION', 'CONEX_ROOT_DN',
        'CONEX_ROOT_PW', 
        'CONEX_LDAP_NB_FILTERS',
        'CONEX_LDAP_CHECK_DEFAULT_ROLE',
        'CONEX_LDAP_FILTER1',
        'CONEX_LDAP_FILTER1_ROLE',
        'CONEX_LDAP_FILTER2',
        'CONEX_LDAP_FILTER2_ROLE',
        'CAS_PORT' => 'CAS_PORT',
        'CAS_URI' => 'CAS_URI',
        'CAS_HOST' => 'CAS_HOST',
        'CAS_DEFAULT_ROLE' => 'CAS_DEFAULT_ROLE',
        'VULN_CVESEARCH_HOST', 'VULN_BAN_LIST',
        'IT_SET_NAME_TEST', 'IT_SET_NAME_LIMIT', 'IT_SET_TAG_NAME',
        'IT_SET_NIV_CREAT', 'IT_SET_NIV_TEST', 'IT_SET_NIV_REST', 'IT_SET_NIV_TOTAL', 'EXPORT_SEP', 'WOL_PORT',
        'CUSTOM_THEME', 'SNMP_MIB_DIRECTORY', 'DOWNLOAD_PROTOCOL');
    //tableau des champs ou il faut juste mettre à jour le ivalue
    $array_simple_ivalue = array('INVENTORY_DIFF', 'INVENTORY_TRANSACTION', 'INVENTORY_WRITE_DIFF',
        'INVENTORY_SESSION_ONLY', 'INVENTORY_CACHE_REVALIDATE', 'LOGLEVEL',
        'PROLOG_FREQ', 'INTERFACE_LAST_CONTACT', 'LOCK_REUSE_TIME', 'TRACE_DELETED', 'SESSION_VALIDITY_TIME',
        'IPDISCOVER_BETTER_THRESHOLD', 'IPDISCOVER_LATENCY', 'IPDISCOVER_MAX_ALIVE',
        'IPDISCOVER_NO_POSTPONE', 'IPDISCOVER_USE_GROUPS', 'ENABLE_GROUPS', 'GROUPS_CACHE_OFFSET', 'GROUPS_CACHE_REVALIDATE',
        'REGISTRY', 'GENERATE_OCS_FILES', 'OCS_FILES_OVERWRITE', 'PROLOG_FILTER_ON', 'INVENTORY_FILTER_ENABLED',
        'INVENTORY_FILTER_FLOOD_IP', 'INVENTORY_FILTER_FLOOD_IP_CACHE_TIME', 'INVENTORY_FILTER_ON',
        'LOG_GUI', 'DOWNLOAD', 'DOWNLOAD_CYCLE_LATENCY', 'DOWNLOAD_FRAG_LATENCY', 'DOWNLOAD_GROUPS_TRACE_EVENTS',
        'DOWNLOAD_PERIOD_LATENCY', 'DOWNLOAD_TIMEOUT', 'DOWNLOAD_PERIOD_LENGTH', 'DOWNLOAD_ACTIVATE_FRAG', 'DOWNLOAD_RATIO_FRAG', 'DOWNLOAD_AUTO_ACTIVATE', 'DEPLOY', 'AUTO_DUPLICATE_LVL',
        'IT_SET_PERIM', 'IT_SET_MAIL', 'IT_SET_MAIL_ADMIN', 'SNMP', 'SNMP_INVENTORY_DIFF', 'TAB_CACHE',
        'INVENTORY_CACHE_ENABLED', 'USE_NEW_SOFT_TABLES', 'WARN_UPDATE', 'INVENTORY_ON_STARTUP', 'DEFAULT_CATEGORY', 'ADVANCE_CONFIGURATION',
        'INVENTORY_SAAS_ENABLED', 'ACTIVE_NEWS', 'VULN_CVESEARCH_ENABLE','VULN_CVESEARCH_VERBOSE', 'VULN_CVESEARCH_ALL', 'VULN_CVE_EXPIRE_TIME', 'VULN_CVE_DELAY_TIME',
        'IPDISCOVER_LINK_TAG_NETWORK','IPDISCOVER_PURGE_OLD','IPDISCOVER_PURGE_VALIDITY_TIME', 'SECURITY_AUTHENTICATION_BLOCK_IP', 
        'SECURITY_AUTHENTICATION_NB_ATTEMPT', 'SECURITY_AUTHENTICATION_TIME_BLOCK', 'SECURITY_PASSWORD_ENABLED', 'SECURITY_PASSWORD_MIN_CHAR',
        'SECURITY_PASSWORD_FORCE_NB', 'SECURITY_PASSWORD_FORCE_UPPER', 'SECURITY_PASSWORD_FORCE_SPE_CHAR','EXCLUDE_ARCHIVE_COMPUTER');

    //tableau des champs ou il faut interpréter la valeur retourner et mettre à jour tvalue
    $array_interprete_tvalue = array('DOWNLOAD_REP_CREAT' => 'DOWNLOAD_REP_CREAT_edit', 'DOWNLOAD_PACK_DIR' => 'DOWNLOAD_PACK_DIR_edit',
        'IPDISCOVER_IPD_DIR' => 'IPDISCOVER_IPD_DIR_edit', 'LOG_DIR' => 'LOG_DIR_edit', 'TMP_DIR' => 'TMP_DIR_edit', 'DOWNLOAD_URI_FRAG' => 'DOWNLOAD_URI_FRAG_edit',
        'DOWNLOAD_URI_INFO' => 'DOWNLOAD_URI_INFO_edit',
        'LOG_SCRIPT' => 'LOG_SCRIPT_edit', 'CONF_PROFILS_DIR' => 'CONF_PROFILS_DIR_edit',
        'OLD_CONF_DIR' => 'OLD_CONF_DIR_edit', 'LOCAL_URI_SERVER' => 'LOCAL_URI_SERVER_edit', 'WOL_BIOS_PASSWD' => 'WOL_BIOS_PASSWD_edit');
    //tableau des champs ou il faut interpréter la valeur retourner et mettre à jour ivalue
    $array_interprete_ivalue = array('FREQUENCY' => 'FREQUENCY_edit', 'IPDISCOVER' => 'IPDISCOVER_edit');

    //recherche des valeurs par défaut
    $sql_exist = " select NAME,ivalue,tvalue from config ";
    $result_exist = mysql2_query_secure($sql_exist, $_SESSION['OCS']["readServer"]);
    while ($value_exist = mysqli_fetch_array($result_exist)) {
        if ($value_exist["ivalue"] != null) {
            $optexist[$value_exist["NAME"]] = $value_exist["ivalue"];
        } elseif ($value_exist["tvalue"] != null) {
            $optexist[$value_exist["NAME"]] = $value_exist["tvalue"];
        } elseif ($value_exist["tvalue"] == null && $value_exist["ivalue"] == null) {
            $optexist[$value_exist["NAME"]] = 'null';
        }
    }
    //pour obliger à prendre en compte
    //le AUTO_DUPLICATE_LVL quand il est vide
    //on doit l'initialiser tout le temps
    if ($POST['onglet'] == $l->g(499)) {
        insert_update('AUTO_DUPLICATE_LVL', 0, $optexist['AUTO_DUPLICATE_LVL'], 'ivalue');
        $optexist['AUTO_DUPLICATE_LVL'] = '0';
    }

    if ($POST['onglet'] == 'CNX') {
        $ldap_filters = nb_ldap_filters($default = true);
        
        // CONEX LDAP FILTERS need to be added dynamically to array_simple_tvalue 
        foreach ($ldap_filters as $filter) {
            array_push($array_simple_tvalue, $filter[0]['NAME'], $filter[1]['NAME']);
        }
        
    }


    //check all post
    foreach ($POST as $key => $value) {
        if (!isset($optexist[$key])) {
            $optexist[$key] = '';
        }

        if ($key == "INVENTORY_CACHE_ENABLED"
                and $value == '1'
                and $optexist['INVENTORY_CACHE_ENABLED'] != $value) {
            $sql = "update engine_persistent set ivalue=1 where name='INVENTORY_CACHE_CLEAN_DATE'";
            $arg = '';
            mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg, $l->g(821));
        }
        $name_field_modif = '';
        $value_field_modif = '';
        //check AUTO_DUPLICATE_LVL. Particular field
        if (strstr($key, 'AUTO_DUPLICATE_LVL_')) {
            $AUTO_DUPLICATE['AUTO_DUPLICATE_LVL_1'] = $POST['AUTO_DUPLICATE_LVL_1'] ?? '';
            $AUTO_DUPLICATE['AUTO_DUPLICATE_LVL_2'] = $POST['AUTO_DUPLICATE_LVL_2'] ?? '';
            $AUTO_DUPLICATE['AUTO_DUPLICATE_LVL_3'] = $POST['AUTO_DUPLICATE_LVL_3'] ?? '';
            $AUTO_DUPLICATE['AUTO_DUPLICATE_LVL_4'] = $POST['AUTO_DUPLICATE_LVL_4'] ?? '';
            $AUTO_DUPLICATE['AUTO_DUPLICATE_LVL_5'] = $POST['AUTO_DUPLICATE_LVL_5'] ?? '';
            $AUTO_DUPLICATE['AUTO_DUPLICATE_LVL_6'] = $POST['AUTO_DUPLICATE_LVL_6'] ?? '';
            $value = auto_duplicate_lvl_poids($AUTO_DUPLICATE, 2);
            $key = 'AUTO_DUPLICATE_LVL';
        }
        if (in_array($key, $array_simple_tvalue)) {
            //update tvalue simple
            insert_update($key, $value, $optexist[$key], 'tvalue');
        } elseif (in_array($key, $array_simple_ivalue)) {
            //update ivalue simple
            insert_update($key, $value, $optexist[$key], 'ivalue');
        } elseif (isset($array_interprete_tvalue[$key])) {
            $name_field_modif = "tvalue";
            $value_field_modif = $array_interprete_tvalue[$key];
        } elseif (isset($key, $array_interprete_ivalue[$key])) {
            $name_field_modif = "ivalue";
            $value_field_modif = $array_interprete_ivalue[$key];
        }
        if ($name_field_modif != '') {
            if ($value == "DEFAULT") {
                delete($key);
            } elseif ($value == "CUSTOM" || $value == "ON") {
                insert_update($key, $POST[$value_field_modif], $optexist[$key], $name_field_modif);
            } elseif ($value == "ALWAYS" || $value == 'OFF') {
                insert_update($key, '0', $optexist[$key], $name_field_modif);
            } elseif ($value == "NEVER") {
                insert_update($key, '-1', $optexist[$key], $name_field_modif);
            }
        }
    }
}

function auto_duplicate_lvl_poids($value, $entree_sortie) {
    //définition du poids des auto_duplicate_lvl
    $poids['HOSTNAME'] = 1;
    $poids['SERIAL'] = 2;
    $poids['MACADDRESS'] = 4;
    $poids['MODEL'] = 8;
    $poids['UUID'] = 16;
    $poids['ASSETTAG'] = 32;
    //si on veut les cases cochées par rapport à un chiffre
    if ($entree_sortie == 1) {
        //gestion des poids pour connaitre les cases cochées.
        //ex: si AUTO_DUPLICATE_LVL == 7 on a les cases HOSTNAME (de poids 1), SERIAL (de poids 2) et MACADDRESS (de poids 4)
        //cochées (1+2+4=7)
        foreach ($poids as $k => $v) {
            if ($value & $v) {
                $check[$k] = $k;
            }
        }
    }//si on veut le chiffre par rapport a la case cochée
    else {
        $check = 0;
        foreach ($poids as $k => $v) {
            if (in_array($k, $value)) {
                $check += $v;
            }
        }
    }

    return $check;
}

/*
 * Handle number of LDAP filters
 * $nb is number entered by user
 * $nb_old is calculated from current LDAP filters in config table
 * if $default is set to true, ldap filters will be left untouched but returned
 */
function nb_ldap_filters($nb, $default = false) {

    if ($default == false) {
        // old values = from config table
        $sql = "SELECT * FROM config WHERE NAME REGEXP '^CONEX_LDAP_FILTER[0-9]*$'";
        $old_filters = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
        $nb_old = $old_filters->num_rows;
        $old_filters = end(mysqli_fetch_all($old_filters, MYSQLI_ASSOC));
        $last_filter = (int) preg_replace('/[^0-9]/', '', $old_filters['NAME']);

        if ($nb > $nb_old) { // new filters added
            $i = $last_filter;

            while ($i <= $nb - 1) {
                $i++;
                $filter_name = "CONEX_LDAP_FILTER$i";
                $filter_role = "CONEX_LDAP_FILTER".$i."_ROLE";
                $sql = "INSERT INTO config VALUES ('$filter_name', '', '', NULL), ('$filter_role', '', '', NULL)";
                $ok = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"]);
                
            }
        } elseif ($nb < $nb_old) { // filters to be removed
            $i = $nb_old;

            while ($i >= $nb + 1) {
                $filter_name = "CONEX_LDAP_FILTER$i";
                $sql = "DELETE FROM config WHERE NAME = 'CONEX_LDAP_FILTER$i' OR NAME = 'CONEX_LDAP_FILTER".$i."_ROLE'";
                $ok = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"]);
                $i--;
            }
        }
    }


    $sql = "SELECT * FROM config WHERE NAME REGEXP '^CONEX_LDAP_FILTER[0-9]*'";
    $filters = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
    $filters = mysqli_fetch_all($filters, MYSQLI_ASSOC);
    // sort ldap filters
    foreach ($filters as $filter) {
        if (preg_match('/^CONEX_LDAP_FILTER[0-9]*$/', $filter['NAME'])) {
            $ldap_filters[$filter['NAME']][0] = $filter;
            
        } elseif (preg_match('/^(CONEX_LDAP_FILTER[0-9]*)_ROLE$/', $filter['NAME'], $matches)) {
            $ldap_filters[$matches[1]][1] = $filter;
        }
    }

    return $ldap_filters;
}

function trait_post($name) {
    global $protectedPost, $values;
    if (isset($values['tvalue'][$name])) {
        $select = 'CUSTOM';
    } else {
        $select = 'DEFAULT';
    }

    if (is_defined($protectedPost[$name . "_edit"]) && $protectedPost[$name] == 'CUSTOM') {
        $values['tvalue'][$name] = $protectedPost[$name . "_edit"];
        $select = 'CUSTOM';
    }

    return $select;
}

function pageGUI($advance) {
    global $l, $values, $numeric, $sup1;
    //what ligne we need?
    if($advance){
      $champs = array('LOCAL_URI_SERVER' => 'LOCAL_URI_SERVER',
          'DOWNLOAD_PACK_DIR' => 'DOWNLOAD_PACK_DIR',
          'IPDISCOVER_IPD_DIR' => 'IPDISCOVER_IPD_DIR',
          'LOG_GUI' => 'LOG_GUI',
          'LOG_DIR' => 'LOG_DIR',
          'TMP_DIR' => 'TMP_DIR',
          'EXPORT_SEP' => 'EXPORT_SEP',
          'TAB_CACHE' => 'TAB_CACHE',
          'LOG_SCRIPT' => 'LOG_SCRIPT',
          'CONF_PROFILS_DIR' => 'CONF_PROFILS_DIR',
          'OLD_CONF_DIR' => 'OLD_CONF_DIR',
          'WARN_UPDATE' => 'WARN_UPDATE',
          'INTERFACE_LAST_CONTACT' => 'INTERFACE_LAST_CONTACT',
          'CUSTOM_THEME' => 'CUSTOM_THEME',
          'ACTIVE_NEWS' => 'ACTIVE_NEWS',
          'EXCLUDE_ARCHIVE_COMPUTER' => 'EXCLUDE_ARCHIVE_COMPUTER',
      );
    }else{
      $champs = array('LOG_GUI' => 'LOG_GUI',
          'INTERFACE_LAST_CONTACT' => 'INTERFACE_LAST_CONTACT',
          'CUSTOM_THEME' => 'CUSTOM_THEME',
          'ACTIVE_NEWS' => 'ACTIVE_NEWS',
          'EXCLUDE_ARCHIVE_COMPUTER' => 'EXCLUDE_ARCHIVE_COMPUTER',
      );
    }

    $values = look_config_default_values($champs);
    $select_local_uri = trait_post('LOCAL_URI_SERVER');
    $select_pack = trait_post('DOWNLOAD_PACK_DIR');
    $select_ipd = trait_post('IPDISCOVER_IPD_DIR');
    $select_log = trait_post('LOG_DIR');
    $select_tmp = trait_post('TMP_DIR');
    $select_scripts = trait_post('LOG_SCRIPT');
    $select_profils = trait_post('CONF_PROFILS_DIR');
    $select_old_profils = trait_post('OLD_CONF_DIR');
    $select_custom_theme = trait_post('CUSTOM_THEME');

    $themes = get_available_themes();
    if($advance){
      ligne('EXCLUDE_ARCHIVE_COMPUTER', $l->g(9800), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['EXCLUDE_ARCHIVE_COMPUTER']));

      ligne('ACTIVE_NEWS', $l->g(8026), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['ACTIVE_NEWS']));

      ligne('CUSTOM_THEME', $l->g(1420), 'select', array('VALUE' => $values['tvalue']['CUSTOM_THEME'], 'SELECT_VALUE' => $themes));

      ligne('INTERFACE_LAST_CONTACT', $l->g(484), 'input', array('END' => $l->g(496), 'VALUE' => $values['ivalue']['INTERFACE_LAST_CONTACT'], 'SIZE' => 1, 'MAXLENGTH' => 3, 'JAVASCRIPT' => $numeric, '', '', $sup1));

      ligne('LOCAL_URI_SERVER', $l->g(565), 'radio', array('DEFAULT' => $l->g(823) . " (http://localhost:80/ocsinventory)", 'CUSTOM' => $l->g(822), 'VALUE' => $select_local_uri), array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $values['tvalue']['LOCAL_URI_SERVER'] ?? '', 'SIZE' => "30%", 'MAXLENGTH' => 254));
      $def = VARLIB_DIR . '/download';
      ligne('DOWNLOAD_PACK_DIR', $l->g(775), 'radio', array('DEFAULT' => $l->g(823) . " ($def)", 'CUSTOM' => $l->g(822), 'VALUE' => $select_pack), array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $values['tvalue']['DOWNLOAD_PACK_DIR'], 'SIZE' => "30%", 'MAXLENGTH' => 254, 'END' => "/download"));

      $def = VARLIB_DIR . '/ipd';
      ligne('IPDISCOVER_IPD_DIR', $l->g(776), 'radio', array('DEFAULT' => $l->g(823) . " (" . $def . ")", 'CUSTOM' => $l->g(822), 'VALUE' => $select_ipd), array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $values['tvalue']['IPDISCOVER_IPD_DIR'], 'SIZE' => "30%", 'MAXLENGTH' => 254, 'END' => "/ipd"));

      ligne('LOG_GUI', $l->g(824), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['LOG_GUI']));

      $def = VARLOG_DIR . '/logs';
      ligne('LOG_DIR', $l->g(825), 'radio', array('DEFAULT' => $l->g(823) . " (" . $def . ")", 'CUSTOM' => $l->g(822), 'VALUE' => $select_log), array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $values['tvalue']['LOG_DIR'], 'SIZE' => "30%", 'MAXLENGTH' => 254, 'END' => "/logs"));
    
      $def = VARLIB_DIR . '/tmp_dir';
      ligne('TMP_DIR', $l->g(9611), 'radio', array('DEFAULT' => $l->g(823) . " (" . $def . ")", 'CUSTOM' => $l->g(822), 'VALUE' => $select_tmp), array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $values['tvalue']['TMP_DIR'] ?? '', 'SIZE' => "30%", 'MAXLENGTH' => 254, 'END' => "/tmp_dir"));

      $def = VARLOG_DIR . '/scripts';
      ligne('LOG_SCRIPT', $l->g(1254), 'radio', array('DEFAULT' => $l->g(823) . " (" . $def . ")", 'CUSTOM' => $l->g(822), 'VALUE' => $select_scripts), array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $values['tvalue']['LOG_SCRIPT'], 'SIZE' => "30%", 'MAXLENGTH' => 254, 'END' => "/scripts"));

      $def = ETC_DIR . '/' . MAIN_SECTIONS_DIR . 'conf/';
      ligne('CONF_PROFILS_DIR', $l->g(1252), 'radio', array('DEFAULT' => $l->g(823) . " (" . $def . ")", 'CUSTOM' => $l->g(822), 'VALUE' => $select_profils), array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $values['tvalue']['CONF_PROFILS_DIR'] ?? '', 'SIZE' => "30%", 'MAXLENGTH' => 254, 'END' => "/conf"));

      $def = ETC_DIR . '/' . MAIN_SECTIONS_DIR . 'old_conf/';
      ligne('OLD_CONF_DIR', $l->g(1253), 'radio', array('DEFAULT' => $l->g(823) . " (" . $def . ")", 'CUSTOM' => $l->g(822), 'VALUE' => $select_old_profils), array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $values['tvalue']['OLD_CONF_DIR'] ?? '', 'SIZE' => "30%", 'MAXLENGTH' => 254, 'END' => "/old_conf"));
      ligne('EXPORT_SEP', $l->g(1213), 'input', array('VALUE' => $values['tvalue']['EXPORT_SEP'] ?? '', 'SIZE' => "30%", 'MAXLENGTH' => 4));
      ligne('TAB_CACHE', $l->g(1249), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['TAB_CACHE'] ?? 0));
      ligne('WARN_UPDATE', $l->g(2117), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['WARN_UPDATE']));
    }else{
      ligne('EXCLUDE_ARCHIVE_COMPUTER', $l->g(9800), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['EXCLUDE_ARCHIVE_COMPUTER']));
      ligne('ACTIVE_NEWS', $l->g(8026), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['ACTIVE_NEWS']));
      ligne('CUSTOM_THEME', $l->g(1420), 'select', array('VALUE' => $values['tvalue']['CUSTOM_THEME'], 'SELECT_VALUE' => $themes));
      ligne('INTERFACE_LAST_CONTACT', $l->g(484), 'input', array('END' => $l->g(496), 'VALUE' => $values['ivalue']['INTERFACE_LAST_CONTACT'], 'SIZE' => 1, 'MAXLENGTH' => 3, 'JAVASCRIPT' => $numeric), '', '', $sup1);
      ligne('LOG_GUI', $l->g(824), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['LOG_GUI']));
    }
}

function pageteledeploy($advance) {
    global $l, $numeric, $sup1;
    //open array;
    //what ligne we need?
    if($advance){
        $champs = array('DOWNLOAD' => 'DOWNLOAD',
            'DOWNLOAD_CYCLE_LATENCY' => 'DOWNLOAD_CYCLE_LATENCY',
            'DOWNLOAD_FRAG_LATENCY' => 'DOWNLOAD_FRAG_LATENCY',
            'DOWNLOAD_GROUPS_TRACE_EVENTS' => 'DOWNLOAD_GROUPS_TRACE_EVENTS',
            'DOWNLOAD_PERIOD_LATENCY' => 'DOWNLOAD_PERIOD_LATENCY',
            'DOWNLOAD_TIMEOUT' => 'DOWNLOAD_TIMEOUT',
            'DOWNLOAD_PERIOD_LENGTH' => 'DOWNLOAD_PERIOD_LENGTH',
            'DEPLOY' => 'DEPLOY',
            'DOWNLOAD_URI_INFO' => 'DOWNLOAD_URI_INFO',
            'DOWNLOAD_URI_FRAG' => 'DOWNLOAD_URI_FRAG',
            'DOWNLOAD_ACTIVATE_FRAG' => 'DOWNLOAD_ACTIVATE_FRAG',
            'DOWNLOAD_RATIO_FRAG' => 'DOWNLOAD_RATIO_FRAG',
            'DOWNLOAD_AUTO_ACTIVATE' => 'DOWNLOAD_AUTO_ACTIVATE',
            'DOWNLOAD_PROTOCOL' => 'DOWNLOAD_PROTOCOL'
        );
    }else{
        $champs = array('DOWNLOAD' => 'DOWNLOAD',
            'DOWNLOAD_CYCLE_LATENCY' => 'DOWNLOAD_CYCLE_LATENCY',
            'DOWNLOAD_FRAG_LATENCY' => 'DOWNLOAD_FRAG_LATENCY',
            'DOWNLOAD_PERIOD_LATENCY' => 'DOWNLOAD_PERIOD_LATENCY',
            'DOWNLOAD_TIMEOUT' => 'DOWNLOAD_TIMEOUT',
            'DEPLOY' => 'DEPLOY',
            'DOWNLOAD_URI_INFO' => 'DOWNLOAD_URI_INFO',
            'DOWNLOAD_URI_FRAG' => 'DOWNLOAD_URI_FRAG',
            'DOWNLOAD_ACTIVATE_FRAG' => 'DOWNLOAD_ACTIVATE_FRAG',
            'DOWNLOAD_RATIO_FRAG' => 'DOWNLOAD_RATIO_FRAG',
            'DOWNLOAD_PROTOCOL' => 'DOWNLOAD_PROTOCOL'
        );
    }


    $values = look_config_default_values($champs);
    if (isset($values['tvalue']['DOWNLOAD_URI_INFO'])) {
        $select_info = 'CUSTOM';
    } else {
        $select_info = 'DEFAULT';
    }
    if (isset($values['tvalue']['DOWNLOAD_URI_FRAG'])) {
        $select_frag = 'CUSTOM';
    } else {
        $select_frag = 'DEFAULT';
    }
    
    $protocol = array(
        'HTTP' => 'HTTP',
        'HTTPS' => 'HTTPS'
    );

    //create diff lign for general config
    ligne('DOWNLOAD', $l->g(417), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['DOWNLOAD']));
    ligne('DOWNLOAD_CYCLE_LATENCY', $l->g(720), 'input', array('VALUE' => $values['ivalue']['DOWNLOAD_CYCLE_LATENCY'], 'END' => $l->g(511), 'SIZE' => 2, 'MAXLENGTH' => 4, 'JAVASCRIPT' => $numeric), '', '', $sup1);
    ligne('DOWNLOAD_FRAG_LATENCY', $l->g(721), 'input', array('VALUE' => $values['ivalue']['DOWNLOAD_FRAG_LATENCY'], 'END' => $l->g(511), 'SIZE' => 2, 'MAXLENGTH' => 4, 'JAVASCRIPT' => $numeric), '', '', $sup1);
    if($advance){
      ligne('DOWNLOAD_GROUPS_TRACE_EVENTS', $l->g(758), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['DOWNLOAD_GROUPS_TRACE_EVENTS']));
    }
    ligne('DOWNLOAD_PERIOD_LATENCY', $l->g(722), 'input', array('VALUE' => $values['ivalue']['DOWNLOAD_PERIOD_LATENCY'], 'END' => $l->g(511), 'SIZE' => 2, 'MAXLENGTH' => 4, 'JAVASCRIPT' => $numeric), '', '', $sup1);
    ligne('DOWNLOAD_TIMEOUT', $l->g(424), 'input', array('VALUE' => $values['ivalue']['DOWNLOAD_TIMEOUT'], 'END' => $l->g(496), 'SIZE' => 1, 'MAXLENGTH' => 3, 'JAVASCRIPT' => $numeric), '', '', $sup1);
    if($advance){
      ligne('DOWNLOAD_PERIOD_LENGTH', $l->g(723), 'input', array('VALUE' => $values['ivalue']['DOWNLOAD_PERIOD_LENGTH'], 'SIZE' => 1, 'MAXLENGTH' => 3, 'JAVASCRIPT' => $numeric));
    }
    ligne('DEPLOY', $l->g(414), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['DEPLOY']));
    ligne('DOWNLOAD_URI_FRAG', $l->g(826), 'radio', array('DEFAULT' => $l->g(823) . " (HTTP://localhost/download)", 'CUSTOM' => $l->g(822), 'VALUE' => $select_frag), array('BEGIN' => "http://", 'HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $values['tvalue']['DOWNLOAD_URI_FRAG'] ?? '', 'SIZE' => 70, 'MAXLENGTH' => 254));
    ligne('DOWNLOAD_URI_INFO', $l->g(827), 'radio', array('DEFAULT' => $l->g(823) . " (HTTPS://localhost/download)", 'CUSTOM' => $l->g(822), 'VALUE' => $select_info), array('BEGIN' => "https://", 'HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $values['tvalue']['DOWNLOAD_URI_INFO'] ?? '', 'SIZE' => 70, 'MAXLENGTH' => 254));
    ligne('DOWNLOAD_ACTIVATE_FRAG', $l->g(9203), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['DOWNLOAD_ACTIVATE_FRAG'] ?? 0));
    ligne('DOWNLOAD_RATIO_FRAG', $l->g(9204), 'input', array('VALUE' => $values['ivalue']['DOWNLOAD_RATIO_FRAG'] ?? '', 'END' => 'MB', 'SIZE' => 2, 'MAXLENGTH' => 4, 'JAVASCRIPT' => $numeric), '', '', $sup1);
    ligne('DOWNLOAD_AUTO_ACTIVATE', $l->g(9205), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['DOWNLOAD_AUTO_ACTIVATE'] ?? 0));
    ligne('DOWNLOAD_PROTOCOL', $l->g(9106), 'select', array('VALUE' => $values['tvalue']['DOWNLOAD_PROTOCOL'] ?? '', 'SELECT_VALUE' => $protocol));
}

function pagegroups() {
    global $l, $numeric, $sup1;
    //open array;
    //what ligne we need?
    $champs = array('ENABLE_GROUPS' => 'ENABLE_GROUPS',
        'GROUPS_CACHE_OFFSET' => 'GROUPS_CACHE_OFFSET',
        'GROUPS_CACHE_REVALIDATE' => 'GROUPS_CACHE_REVALIDATE');

    $values = look_config_default_values($champs);
    //create diff lign for general config
    //create diff lign for general config
    ligne('ENABLE_GROUPS', $l->g(736), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['ENABLE_GROUPS']));
    ligne('GROUPS_CACHE_OFFSET', $l->g(737), 'input', array('END' => $l->g(511), 'VALUE' => $values['ivalue']['GROUPS_CACHE_OFFSET'], 'SIZE' => 5, 'MAXLENGTH' => 6, 'JAVASCRIPT' => $numeric), '', '', $sup1);
    ligne('GROUPS_CACHE_REVALIDATE', $l->g(738), 'input', array('END' => $l->g(511), 'VALUE' => $values['ivalue']['GROUPS_CACHE_REVALIDATE'], 'SIZE' => 5, 'MAXLENGTH' => 6, 'JAVASCRIPT' => $numeric), '', '', $sup1);
}

function pageserveur($advance) {
    global $l, $numeric, $sup1;

    //what ligne we need?
    if($advance){
      $champs = array('LOGLEVEL' => 'LOGLEVEL',
          'PROLOG_FREQ' => 'PROLOG_FREQ',
          'AUTO_DUPLICATE_LVL' => 'AUTO_DUPLICATE_LVL',
          'SECURITY_LEVEL' => 'SECURITY_LEVEL',
          'LOCK_REUSE_TIME' => 'LOCK_REUSE_TIME',
          'TRACE_DELETED' => 'TRACE_DELETED',
          'SESSION_VALIDITY_TIME' => 'SESSION_VALIDITY_TIME',
          'INVENTORY_ON_STARTUP' => 'INVENTORY_ON_STARTUP',
          'ADVANCE_CONFIGURATION' => 'ADVANCE_CONFIGURATION');
    }else{
      $champs = array('LOGLEVEL' => 'LOGLEVEL',
          'PROLOG_FREQ' => 'PROLOG_FREQ',
          'AUTO_DUPLICATE_LVL' => 'AUTO_DUPLICATE_LVL',
          'TRACE_DELETED' => 'TRACE_DELETED',
          'INVENTORY_ON_STARTUP' => 'INVENTORY_ON_STARTUP',
          'ADVANCE_CONFIGURATION' => 'ADVANCE_CONFIGURATION');
    }


    $values = look_config_default_values($champs);
    if (isset($champs['AUTO_DUPLICATE_LVL'])) {
        //on utilise la fonction pour connaître les cases cochées correspondantes au chiffre en base de AUTO_DUPLICATE_LVL
        $check = auto_duplicate_lvl_poids($values['ivalue']['AUTO_DUPLICATE_LVL'], 1);
    }
    ligne('LOGLEVEL', $l->g(416), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['LOGLEVEL']));
    ligne('PROLOG_FREQ', $l->g(564), 'input', array('END' => $l->g(730), 'VALUE' => $values['ivalue']['PROLOG_FREQ'], 'SIZE' => 1, 'MAXLENGTH' => 3, 'JAVASCRIPT' => $numeric), $sup1);
    ligne('AUTO_DUPLICATE_LVL', $l->g(427), 'checkbox', array(
        'HOSTNAME' => 'hostname',
        'SERIAL' => 'Serial',
        'MACADDRESS' => 'macaddress',
        'MODEL' => 'model',
        'UUID' => 'uuid',
        'ASSETTAG' => 'AssetTag',
        'CHECK' => $check,
    ));
    if($advance){
      ligne('SECURITY_LEVEL', $l->g(739), 'input', array('VALUE' => $values['ivalue']['SECURITY_LEVEL'] ?? '', 'SIZE' => 1, 'MAXLENGTH' => 3, 'JAVASCRIPT' => $numeric), '', "readonly");
      ligne('LOCK_REUSE_TIME', $l->g(740), 'input', array('END' => $l->g(511), 'VALUE' => $values['ivalue']['LOCK_REUSE_TIME'], 'SIZE' => 1, 'MAXLENGTH' => 3, 'JAVASCRIPT' => $numeric), '', '', $sup1);
      ligne('SESSION_VALIDITY_TIME', $l->g(777), 'input', array('END' => $l->g(511), 'VALUE' => $values['ivalue']['SESSION_VALIDITY_TIME'], 'SIZE' => 1, 'MAXLENGTH' => 4, 'JAVASCRIPT' => $numeric), '', '', $sup1);
    }
    ligne('TRACE_DELETED', $l->g(415), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['TRACE_DELETED']));
    ligne('INVENTORY_ON_STARTUP', $l->g(2121), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['INVENTORY_ON_STARTUP']));
    ligne('ADVANCE_CONFIGURATION', $l->g(1700), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['ADVANCE_CONFIGURATION'] ?? 0));
}

function pageinventory($advance) {
    global $l, $numeric, $sup1;
    //what ligne we need?
    if($advance){
      $champs = array('FREQUENCY' => 'FREQUENCY',
          'INVENTORY_DIFF' => 'INVENTORY_DIFF',
          'INVENTORY_TRANSACTION' => 'INVENTORY_TRANSACTION',
          'INVENTORY_WRITE_DIFF' => 'INVENTORY_WRITE_DIFF',
          'INVENTORY_SESSION_ONLY' => 'INVENTORY_SESSION_ONLY',
          'INVENTORY_CACHE_REVALIDATE' => 'INVENTORY_CACHE_REVALIDATE',
          'INVENTORY_CACHE_ENABLED' => 'INVENTORY_CACHE_ENABLED',
          'DEFAULT_CATEGORY' => 'DEFAULT_CATEGORY',
          'INVENTORY_SAAS_ENABLED' => 'INVENTORY_SAAS_ENABLED');
    }else{
      $champs = array('FREQUENCY' => 'FREQUENCY',
          'INVENTORY_CACHE_REVALIDATE' => 'INVENTORY_CACHE_REVALIDATE',
          'INVENTORY_CACHE_ENABLED' => 'INVENTORY_CACHE_ENABLED',
          'DEFAULT_CATEGORY' => 'DEFAULT_CATEGORY',
          'INVENTORY_SAAS_ENABLED' => 'INVENTORY_SAAS_ENABLED');
    }

    $values = look_config_default_values($champs);

    if ($values['ivalue']['FREQUENCY'] == 0 && isset($values['ivalue']['FREQUENCY'])) {
        $optvalueselected = 'ALWAYS';
    } elseif ($values['ivalue']['FREQUENCY'] == -1) {
        $optvalueselected = 'NEVER';
    } else {
        $optvalueselected = 'CUSTOM';
    }

    if($advance){
      ligne('FREQUENCY', $l->g(494), 'radio', array('ALWAYS' => $l->g(485), 'NEVER' => $l->g(486), 'CUSTOM' => $l->g(487), 'VALUE' => $optvalueselected), array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $values['ivalue']['FREQUENCY'], 'END' => $l->g(496), 'JAVASCRIPT' => $numeric));
      ligne('INVENTORY_DIFF', $l->g(741), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['INVENTORY_DIFF']));
      ligne('INVENTORY_TRANSACTION', $l->g(742), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['INVENTORY_TRANSACTION']));
      ligne('INVENTORY_WRITE_DIFF', $l->g(743), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['INVENTORY_WRITE_DIFF']));
      ligne('INVENTORY_SESSION_ONLY', $l->g(744), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['INVENTORY_SESSION_ONLY'] ?? 0));
      ligne('INVENTORY_CACHE_REVALIDATE', $l->g(745), 'input', array('END' => $l->g(496), 'VALUE' => $values['ivalue']['INVENTORY_CACHE_REVALIDATE'], 'SIZE' => 1, 'MAXLENGTH' => 3, 'JAVASCRIPT' => $numeric), '', '', $sup1);
      ligne('INVENTORY_CACHE_ENABLED', $l->g(1265), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['INVENTORY_CACHE_ENABLED']));
    }else{
      ligne('FREQUENCY', $l->g(494), 'radio', array('ALWAYS' => $l->g(485), 'NEVER' => $l->g(486), 'CUSTOM' => $l->g(487), 'VALUE' => $optvalueselected), array('HIDDEN' => 'CUSTOM', 'HIDDEN_VALUE' => $values['ivalue']['FREQUENCY'], 'END' => $l->g(496), 'JAVASCRIPT' => $numeric));
      ligne('INVENTORY_CACHE_REVALIDATE', $l->g(745), 'input', array('END' => $l->g(496), 'VALUE' => $values['ivalue']['INVENTORY_CACHE_REVALIDATE'], 'SIZE' => 1, 'MAXLENGTH' => 3, 'JAVASCRIPT' => $numeric), '', '', $sup1);
      ligne('INVENTORY_CACHE_ENABLED', $l->g(1265), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['INVENTORY_CACHE_ENABLED']));
    }

    // Get all software categories
    require 'require/softwares/SoftwareCategory.php';
    $category = new SoftwareCategory();
    $list_cat = $category->search_all_cat();

    ligne('INVENTORY_SAAS_ENABLED', $l->g(8108), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['INVENTORY_SAAS_ENABLED'] ?? 0));
    ligne('DEFAULT_CATEGORY', $l->g(1505), 'select', array('VALUE' => $values['ivalue']['DEFAULT_CATEGORY'], 'SELECT_VALUE' => $list_cat));

}

function pageregistry() {
    global $l;
    //which line we need?
    $champs = array('REGISTRY' => 'REGISTRY');
    $values = look_config_default_values($champs);
    ligne('REGISTRY', $l->g(412), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['REGISTRY']));
}

function pageipdiscover($advance) {
    global $l, $numeric, $sup1, $sup10;
    //what ligne we need?
    if($advance) {
        $champs = array(
            'IPDISCOVER' => 'IPDISCOVER',
            'IPDISCOVER_BETTER_THRESHOLD' => 'IPDISCOVER_BETTER_THRESHOLD',
            'IPDISCOVER_LATENCY' => 'IPDISCOVER_LATENCY',
            'IPDISCOVER_MAX_ALIVE' => 'IPDISCOVER_MAX_ALIVE',
            'IPDISCOVER_NO_POSTPONE' => 'IPDISCOVER_NO_POSTPONE',
            'IPDISCOVER_USE_GROUPS' => 'IPDISCOVER_USE_GROUPS',
            'IPDISCOVER_LINK_TAG_NETWORK' => 'IPDISCOVER_LINK_TAG_NETWORK',
            'IPDISCOVER_PURGE_OLD' => 'IPDISCOVER_PURGE_OLD',
            'IPDISCOVER_PURGE_VALIDITY_TIME' => 'IPDISCOVER_PURGE_VALIDITY_TIME',   
        );
    } else {
        $champs = array('IPDISCOVER' => 'IPDISCOVER');
    }

    $values = look_config_default_values($champs);
    if (isset($champs['IPDISCOVER'])) {
        $ipdiscover = $values['ivalue']['IPDISCOVER'];
        //gestion des différentes valeurs de l'ipdiscover
        if ($values['ivalue']['IPDISCOVER'] != 0) {
            $values['ivalue']['IPDISCOVER'] = 'ON';
        } else {
            $values['ivalue']['IPDISCOVER'] = 'OFF';
        }
    }

    if($advance) {
        ligne('IPDISCOVER', $l->g(425), 'radio', array('ON' => 'ON', 'OFF' => 'OFF', 'VALUE' => $values['ivalue']['IPDISCOVER']), array('HIDDEN' => 'ON', 'HIDDEN_VALUE' => $ipdiscover, 'END' => $l->g(729), 'JAVASCRIPT' => $numeric));
        ligne('IPDISCOVER_BETTER_THRESHOLD', $l->g(746), 'input', array('VALUE' => $values['ivalue']['IPDISCOVER_BETTER_THRESHOLD'], 'END' => $l->g(496), 'SIZE' => 1, 'MAXLENGTH' => 3, 'JAVASCRIPT' => $numeric), '', '', $sup1);
        ligne('IPDISCOVER_LATENCY', $l->g(567), 'input', array('VALUE' => $values['ivalue']['IPDISCOVER_LATENCY'], 'END' => $l->g(732), 'SIZE' => 2, 'MAXLENGTH' => 4, 'JAVASCRIPT' => $numeric), '', '', $sup10);
        ligne('IPDISCOVER_MAX_ALIVE', $l->g(419), 'input', array('VALUE' => $values['ivalue']['IPDISCOVER_MAX_ALIVE'], 'END' => $l->g(496), 'SIZE' => 1, 'MAXLENGTH' => 3, 'JAVASCRIPT' => $numeric), '', '', $sup1);
        ligne('IPDISCOVER_NO_POSTPONE', $l->g(747), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['IPDISCOVER_NO_POSTPONE']));
        ligne('IPDISCOVER_USE_GROUPS', $l->g(748), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['IPDISCOVER_USE_GROUPS']));
        ligne('IPDISCOVER_LINK_TAG_NETWORK', $l->g(1457), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['IPDISCOVER_LINK_TAG_NETWORK'] ?? 0));
        ligne('IPDISCOVER_PURGE_OLD', $l->g(1560), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['IPDISCOVER_PURGE_OLD']));
        ligne('IPDISCOVER_PURGE_VALIDITY_TIME', $l->g(1561), 'input', array('VALUE' => $values['ivalue']['IPDISCOVER_PURGE_VALIDITY_TIME'], 'END' => $l->g(496), 'SIZE' => 1, 'MAXLENGTH' => 3, 'JAVASCRIPT' => $numeric), '', '', $sup1);
    } else {
        ligne('IPDISCOVER', $l->g(425), 'radio', array('ON' => 'ON', 'OFF' => 'OFF', 'VALUE' => $values['ivalue']['IPDISCOVER']), array('HIDDEN' => 'ON', 'HIDDEN_VALUE' => $ipdiscover, 'END' => $l->g(729), 'JAVASCRIPT' => $numeric));
    }
}


function pagefilesInventory() {
    global $l;
    //which line we need?
    $champs = array('GENERATE_OCS_FILES' => 'GENERATE_OCS_FILES',
        'OCS_FILES_FORMAT' => 'OCS_FILES_FORMAT',
        'OCS_FILES_OVERWRITE' => 'OCS_FILES_OVERWRITE',
        'OCS_FILES_PATH' => 'OCS_FILES_PATH');
    $values = look_config_default_values($champs);
    ligne('GENERATE_OCS_FILES', $l->g(749), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['GENERATE_OCS_FILES']));
    ligne('OCS_FILES_FORMAT', $l->g(750), 'select', array('VALUE' => $values['tvalue']['OCS_FILES_FORMAT'], 'SELECT_VALUE' => array('OCS' => 'OCS', 'XML' => 'XML')));
    ligne('OCS_FILES_OVERWRITE', $l->g(751), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['OCS_FILES_OVERWRITE']));
    ligne('OCS_FILES_PATH', $l->g(752), 'input', array('VALUE' => $values['tvalue']['OCS_FILES_PATH'], 'SIZE' => "30%", 'MAXLENGTH' => 254));
}

function pagefilter() {
    global $l, $numeric, $sup1;
    //what ligne we need?
    $champs = array('PROLOG_FILTER_ON' => 'PROLOG_FILTER_ON',
        'INVENTORY_FILTER_ENABLED' => 'INVENTORY_FILTER_ENABLED',
        'INVENTORY_FILTER_FLOOD_IP' => 'INVENTORY_FILTER_FLOOD_IP',
        'INVENTORY_FILTER_FLOOD_IP_CACHE_TIME' => 'INVENTORY_FILTER_FLOOD_IP_CACHE_TIME',
        'INVENTORY_FILTER_ON' => 'INVENTORY_FILTER_ON');
    $values = look_config_default_values($champs);
    ligne('PROLOG_FILTER_ON', $l->g(753), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['PROLOG_FILTER_ON']));
    ligne('INVENTORY_FILTER_ENABLED', $l->g(754), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['INVENTORY_FILTER_ENABLED']));
    ligne('INVENTORY_FILTER_FLOOD_IP', $l->g(755), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['INVENTORY_FILTER_FLOOD_IP']));
    ligne('INVENTORY_FILTER_FLOOD_IP_CACHE_TIME', $l->g(756), 'input', array('VALUE' => $values['ivalue']['INVENTORY_FILTER_FLOOD_IP_CACHE_TIME'], 'END' => $l->g(511), 'SIZE' => 1, 'MAXLENGTH' => 3, 'JAVASCRIPT' => $numeric), '', '', $sup1);
    ligne('INVENTORY_FILTER_ON', $l->g(757), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['INVENTORY_FILTER_ON']));
}

function pageVulnerability() {
    global $l, $numeric, $sup1;

    // Which lines do we need?
    $champs = array('VULN_CVESEARCH_ENABLE' => 'VULN_CVESEARCH_ENABLE',
        'VULN_CVESEARCH_HOST' => 'VULN_CVESEARCH_HOST',
        'VULN_BAN_LIST' => 'VULN_BAN_LIST',
        'VULN_CVESEARCH_VERBOSE' => 'VULN_CVESEARCH_VERBOSE',
        'VULN_CVESEARCH_ALL' => 'VULN_CVESEARCH_ALL',
        'VULN_CVE_EXPIRE_TIME' => 'VULN_CVE_EXPIRE_TIME',
        'VULN_CVE_DELAY_TIME' => 'VULN_CVE_DELAY_TIME');
    // Get configuration values from DB
    $values = look_config_default_values($champs);

    // Get all software categories
    require 'require/softwares/SoftwareCategory.php';
    $category = new SoftwareCategory();
    $list_cat = $category->search_all_cat();

    // Display configuration items
    ligne('VULN_CVESEARCH_ENABLE', $l->g(1461), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['VULN_CVESEARCH_ENABLE'] ?? 0));
    ligne('VULN_CVESEARCH_HOST', $l->g(1462), 'input', array('VALUE' => $values['tvalue']['VULN_CVESEARCH_HOST'] ?? 0, 'SIZE' => "30%", 'MAXLENGTH' => 200));
    ligne('VULN_BAN_LIST[]', $l->g(1469), 'select2', array('VALUE' => $values['tvalue']['VULN_BAN_LIST'] ?? 0, 'SELECT_VALUE' => $list_cat));
    ligne('VULN_CVESEARCH_VERBOSE', $l->g(1461), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['VULN_CVESEARCH_VERBOSE'] ?? 0));
    ligne('VULN_CVESEARCH_ALL', $l->g(1471), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['VULN_CVESEARCH_ALL'] ?? 0));
    ligne('VULN_CVE_EXPIRE_TIME', $l->g(1484), 'input', array('VALUE' => $values['ivalue']['VULN_CVE_EXPIRE_TIME'] ?? null, 'SIZE' => "30%", 'MAXLENGTH' => 3, 'END' => $l->g(730)));
    ligne('VULN_CVE_DELAY_TIME', $l->g(1459), 'input', array('VALUE' => $values['ivalue']['VULN_CVE_DELAY_TIME'], 'SIZE' => "30%", 'MAXLENGTH' => 3, 'END' => $l->g(511)));
}

function pageCas() {
    global $l;
    require_once('require/function_users.php');

    //which line we need?
    $champs = array(
        'CAS_PORT' => 'CAS_PORT',
        'CAS_URI' => 'CAS_URI',
        'CAS_HOST' => 'CAS_HOST',
        'CAS_DEFAULT_ROLE' => 'CAS_DEFAULT_ROLE',
    );
    $values = look_config_default_values($champs);
    $role1 = get_profile_labels();
    $default_role[''] = '';
    $default_role = array_merge($default_role, $role1);

    ligne('CAS_PORT', $l->g(9700). '<br>' . '', 'input', array('VALUE' => $values['tvalue']['CAS_PORT'] ?? '', 'SIZE' => "30%", 'MAXLENGTH' => 200));
    ligne('CAS_URI', $l->g(9701) . '<br>' . '', 'input', array('VALUE' => $values['tvalue']['CAS_URI'] ?? '', 'SIZE' => "30%", 'MAXLENGTH' => 200));
    ligne('CAS_HOST', $l->g(9702) . '<br>' . '', 'input', array('VALUE' => $values['tvalue']['CAS_HOST'] ?? '', 'SIZE' => "30%", 'MAXLENGTH' => 200));
    ligne('CAS_DEFAULT_ROLE', $l->g(9703), 'select', array('VALUE' => $values['tvalue']['CAS_DEFAULT_ROLE'] ?? '', 'SELECT_VALUE' => $default_role));
}


function pageConnexion() {
    global $l;
    require_once('require/function_users.php');

    //which line we need?
    $champs = array('CONEX_LDAP_SERVEUR' => 'CONEX_LDAP_SERVEUR',
        'CONEX_LDAP_PORT' => 'CONEX_LDAP_PORT',
        'CONEX_DN_BASE_LDAP' => 'CONEX_DN_BASE_LDAP',
        'CONEX_LOGIN_FIELD' => 'CONEX_LOGIN_FIELD',
        'CONEX_LDAP_PROTOCOL_VERSION' => 'CONEX_LDAP_PROTOCOL_VERSION',
        'CONEX_ROOT_DN' => 'CONEX_ROOT_DN',
        'CONEX_ROOT_PW' => 'CONEX_ROOT_PW',
        'CONEX_LDAP_NB_FILTERS' => 'CONEX_LDAP_NB_FILTERS',
        'CONEX_LDAP_CHECK_DEFAULT_ROLE' => 'CONEX_LDAP_CHECK_DEFAULT_ROLE');
    $values = look_config_default_values($champs);

    $role1 = get_profile_labels();
  
    $default_role[''] = '';
    $default_role = array_merge($default_role, $role1);

    // ldap nb filters
    $nb_filters = range(0, sizeof($role1));
    $nb_filters[0] = '';
    $ldap_filters = nb_ldap_filters($nb = $values['tvalue']['CONEX_LDAP_NB_FILTERS']);

    ligne('CONEX_LDAP_SERVEUR', $l->g(830), 'input', array('VALUE' => $values['tvalue']['CONEX_LDAP_SERVEUR'] ?? '', 'SIZE' => "30%", 'MAXLENGTH' => 200));
    ligne('CONEX_ROOT_DN', $l->g(1016) . '<br>' . $l->g(1018), 'input', array('VALUE' => $values['tvalue']['CONEX_ROOT_DN'] ?? '', 'SIZE' => "30%", 'MAXLENGTH' => 200));
    ligne('CONEX_ROOT_PW', $l->g(1017) . '<br>' . $l->g(1018), 'password', array('VALUE' => $values['tvalue']['CONEX_ROOT_PW'] ?? '', 'SIZE' => "30%", 'MAXLENGTH' => 200));
    ligne('CONEX_LDAP_PORT', $l->g(831), 'input', array('VALUE' => $values['tvalue']['CONEX_LDAP_PORT'] ?? '', 'SIZE' => "30%", 'MAXLENGTH' => 20));
    ligne('CONEX_DN_BASE_LDAP', $l->g(832), 'input', array('VALUE' => $values['tvalue']['CONEX_DN_BASE_LDAP'] ?? '', 'SIZE' => "30%", 'MAXLENGTH' => 200));
    ligne('CONEX_LOGIN_FIELD', $l->g(833), 'input', array('VALUE' => $values['tvalue']['CONEX_LOGIN_FIELD'] ?? '', 'SIZE' => "30%", 'MAXLENGTH' => 200));
    ligne('CONEX_LDAP_PROTOCOL_VERSION', $l->g(834), 'input', array('VALUE' => $values['tvalue']['CONEX_LDAP_PROTOCOL_VERSION'] ?? '', 'SIZE' => "30%", 'MAXLENGTH' => 5));
    ligne('CONEX_LDAP_CHECK_DEFAULT_ROLE', $l->g(1277), 'select', array('VALUE' => $values['tvalue']['CONEX_LDAP_CHECK_DEFAULT_ROLE'] ?? '', 'SELECT_VALUE' => $default_role));
    ligne('CONEX_LDAP_NB_FILTERS', $l->g(9650), 'select', array('VALUE' => $values['tvalue']['CONEX_LDAP_NB_FILTERS'] ?? '', 'SELECT_VALUE' => $nb_filters));
    foreach ($ldap_filters as $filter) {
        ligne($filter[0]['NAME'], $l->g(1111), 'input', array('VALUE' => $filter[0]['TVALUE'], 'SIZE' => "30%", 'MAXLENGTH' => 200));
        ligne($filter[1]['NAME'], $l->g(1116), 'select', array('VALUE' => $filter[1]['TVALUE'], 'SELECT_VALUE' => $role1));
    }
 
}

function pagesnmp() {
    global $l;
    //which line we need?
    $champs = array('SNMP' => 'SNMP', 'SNMP_MIB_DIRECTORY' => 'SNMP_MIB_DIRECTORY');
    $values = look_config_default_values($champs);
    if (isset($values['tvalue']['SNMP_DIR'])) {
        $select_rep_creat = 'CUSTOM';
    } else {
        $select_rep_creat = 'DEFAULT';
    }
    ligne('SNMP', $l->g(1137), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['SNMP'] ?? 0));
    ligne('SNMP_MIB_DIRECTORY', $l->g(9010), 'input', array('VALUE' => $values['tvalue']['SNMP_MIB_DIRECTORY'] ?? '', 'SIZE' => "30%", 'MAXLENGTH' => 200));
}

function pageswol() {
    global $l;
    $numeric_semicolon = "onKeyPress='return scanTouche(event,/[0-9 ,]/)'
		  onkeydown='convertToUpper(this)'
		  onkeyup='convertToUpper(this)'
		  onblur='convertToUpper(this)'
		  onclick='convertToUpper(this)'";
    $champs = array('WOL_PORT' => 'WOL_PORT', 'WOL_BIOS_PASSWD' => 'WOL_BIOS_PASSWD');
    $values = look_config_default_values($champs);

    if (isset($values['tvalue']['WOL_BIOS_PASSWD'])
            and $values['tvalue']['WOL_BIOS_PASSWD'] != ''
            and $values['tvalue']['WOL_BIOS_PASSWD'] != '0') {
        $wol_passwd = 'ON';
    } else {
        $wol_passwd = 'OFF';
    }

    ligne('WOL_PORT', $l->g(272) . " (" . $l->g(1320) . ")", 'input', array('VALUE' => $values['tvalue']['WOL_PORT'], 'SIZE' => "30%", 'MAXLENGTH' => "30%", 'JAVASCRIPT' => $numeric_semicolon));
    ligne('WOL_BIOS_PASSWD', 'Bios password', 'radio', array('ON' => 'ON', 'OFF' => 'OFF', 'VALUE' => $wol_passwd), array('HIDDEN' => 'ON', 'HIDDEN_VALUE' => $values['tvalue']['WOL_BIOS_PASSWD'] ?? '', 'SIZE' => 40, 'MAXLENGTH' => 254), "readonly");
}

function pagesSecurity(){
    global $l, $numeric, $sup1;

    $champs = array('SECURITY_AUTHENTICATION_BLOCK_IP' => 'SECURITY_AUTHENTICATION_BLOCK_IP', 
    'SECURITY_AUTHENTICATION_NB_ATTEMPT' => 'SECURITY_AUTHENTICATION_NB_ATTEMPT', 
    'SECURITY_AUTHENTICATION_TIME_BLOCK' => 'SECURITY_AUTHENTICATION_TIME_BLOCK',
    'SECURITY_PASSWORD_ENABLED' => 'SECURITY_PASSWORD_ENABLED', 
    'SECURITY_PASSWORD_MIN_CHAR' => 'SECURITY_PASSWORD_MIN_CHAR', 
    'SECURITY_PASSWORD_FORCE_NB' => 'SECURITY_PASSWORD_FORCE_NB',
    'SECURITY_PASSWORD_FORCE_UPPER' => 'SECURITY_PASSWORD_FORCE_UPPER', 
    'SECURITY_PASSWORD_FORCE_SPE_CHAR' => 'SECURITY_PASSWORD_FORCE_SPE_CHAR');

    $values = look_config_default_values($champs);

    ligne('SECURITY_AUTHENTICATION_BLOCK_IP', $l->g(1488), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['SECURITY_AUTHENTICATION_BLOCK_IP']));
    ligne('SECURITY_AUTHENTICATION_NB_ATTEMPT', $l->g(1489), 'input', array('VALUE' => $values['ivalue']['SECURITY_AUTHENTICATION_NB_ATTEMPT'], 'SIZE' => 1, 'MAXLENGTH' => 3, 'JAVASCRIPT' => $numeric), '', '', $sup1);
    ligne('SECURITY_AUTHENTICATION_TIME_BLOCK', $l->g(1490), 'input', array('END' => $l->g(511), 'VALUE' => $values['ivalue']['SECURITY_AUTHENTICATION_TIME_BLOCK'], 'SIZE' => 1, 'MAXLENGTH' => 10, 'JAVASCRIPT' => $numeric), '', '', $sup1);

    ligne('SECURITY_PASSWORD_ENABLED', $l->g(1491), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['SECURITY_PASSWORD_ENABLED']));
    ligne('SECURITY_PASSWORD_MIN_CHAR', $l->g(1492), 'input', array('VALUE' => $values['ivalue']['SECURITY_PASSWORD_MIN_CHAR'], 'SIZE' => 1, 'MAXLENGTH' => 3, 'JAVASCRIPT' => $numeric), '', '', $sup1);
    ligne('SECURITY_PASSWORD_FORCE_NB', $l->g(1493), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['SECURITY_PASSWORD_FORCE_NB']));
    ligne('SECURITY_PASSWORD_FORCE_UPPER', $l->g(1494), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['SECURITY_PASSWORD_FORCE_UPPER']));
    ligne('SECURITY_PASSWORD_FORCE_SPE_CHAR', $l->g(1495), 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['SECURITY_PASSWORD_FORCE_SPE_CHAR']));
}

function pagesdev() {
    $champs = array('USE_NEW_SOFT_TABLES' => 'USE_NEW_SOFT_TABLES');
    $values = look_config_default_values($champs);
    ligne('USE_NEW_SOFT_TABLES', 'Utilisation tables de soft OCS v2.1', 'radio', array(1 => 'ON', 0 => 'OFF', 'VALUE' => $values['ivalue']['USE_NEW_SOFT_TABLES']));
}

function get_available_themes() {
    $scan = scandir(THEMES_DIR);
    unset($scan[0]);
    unset($scan[1]);

    foreach ($scan as $theme) {
      if(file_exists(THEMES_DIR . $theme . '/style.css')){
        $result[$theme] = $theme;
      }
    }
    return $result;
}

?>
