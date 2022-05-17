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

$values = look_config_default_values(array('EXPORT_SEP'));
if (is_defined($values['tvalue']['EXPORT_SEP'])) {
    $separator = $values['tvalue']['EXPORT_SEP'];
} else {
    $separator = ';';
}

$link = $_SESSION['OCS']["readServer"];
$toBeWritten = "";

// Export DB data
if (isset($protectedGet['tablename']) && isset($_SESSION['OCS']['csv']['SQL'][$protectedGet['tablename']])) {

    // Gestion des entetes
    foreach ($_SESSION['OCS']['visible_col'][$protectedGet['tablename']] as $name => $nothing) {
        if ($name != 'SUP' && $name != 'CHECK' && $name != 'NAME' && $name != 'ACTIONS') {
            if ($_SESSION['OCS']['visible_col'][$protectedGet['tablename']][$name][1] == ".") {
                $lbl = substr(strrchr($_SESSION['OCS']['visible_col'][$protectedGet['tablename']][$name], "."), 1);
            } else {
                $lbl = $_SESSION['OCS']['visible_col'][$protectedGet['tablename']][$name];
            }
            $col[$lbl] = $name;
            $toBeWritten .= $name . $separator;
        } elseif ($name == 'NAME' || $name == $l->g(23)) {
            $lbl = $_SESSION['OCS']['visible_col'][$protectedGet['tablename']][$name];
            $col[$lbl] = $name;
            $toBeWritten .= $l->g(23) . $separator;
        }
    }

    // Data fixe
    if (isset($_SESSION['OCS']['SQL_DATA_FIXE'][$protectedGet['tablename']])) {
        $i = 0;
        while ($_SESSION['OCS']['SQL_DATA_FIXE'][$protectedGet['tablename']][$i]) {
            $result = mysqli_query($link, $_SESSION['OCS']['SQL_DATA_FIXE'][$protectedGet['tablename']][$i]) or die(mysqli_error($link));
            while ($cont = mysqli_fetch_array($result)) {
                foreach ($col as $field => $lbl) {
                    if (array_key_exists($lbl, $cont)) {
                        $data_fixe[$cont['HARDWARE_ID']][$field] = $cont[$lbl];
                    }
                }
            }
            $i++;
        }
    }

    if (isset($_SESSION['OCS']['csv']['ARG'][$protectedGet['tablename']])) {
        $arg = $_SESSION['OCS']['csv']['ARG'][$protectedGet['tablename']];
    } else {
        $arg = '';
    }

    if (isset($protectedGet['nolimit'])) {
        $result = mysql2_query_secure($_SESSION['OCS']['csv']['SQLNOLIMIT'][$protectedGet['tablename']], $link, $arg);
    } else {
        $result = mysql2_query_secure($_SESSION['OCS']['csv']['SQL'][$protectedGet['tablename']], $link, $arg);
    }

    $i = 0;
    require_once('require/function_admininfo.php');
    $inter = interprete_accountinfo($col, array());
    while ($cont = mysqli_fetch_array($result)) {
        unset($cont['MODIF']);
        if ($protectedGet['tablename'] == "IPDISCOVER") {
            $query = "SELECT `NAME` FROM subnet WHERE NETID = '" . $cont['ID'] . "'";
            $ipDiscoverResult = mysql2_query_secure($query, $link);
            $nameArray = mysqli_fetch_array($ipDiscoverResult);
            if (!empty($nameArray)) {
                $data[$i]['LBL_RSX'] = $nameArray[0];
            } else {
                $data[$i]['LBL_RSX'] = "";
            }
        }
        foreach ($inter as $field => $lbl) {
            if ($lbl == "name_of_machine" && !isset($cont[$field])) {
                $field = 'name';
            }
            $found = false;
            // find value case-insensitive
            foreach ($col as $key => $val) {
                if (str_contains($key, ".")) {
                    $exploded_key = explode(".", $key);
                    $key = $exploded_key[1];
                }
                if (array_key_exists($key, $cont)) {
                    $is_admindata = substr($key, 0, 7) == 'fields_' || substr($key, -8, 7) == 'fields_' || substr($key, -9, 7) == 'fields_';
                    if (($field == 'TAG' || $is_admindata ) && isset($inter['TAB_OPTIONS']['REPLACE_VALUE'][$val])) {
                        // administrative data
                        if(strpos($cont[$key], "&&&")){
                          $value_field = explode("&&&", $cont[$key]);
                          $value_admin = implode(" ", $value_field);
                          $inter['TAB_OPTIONS']['REPLACE_VALUE'][$val][$cont[$key]] = $value_admin;
                        }
                        $data[$i][$key] = $inter['TAB_OPTIONS']['REPLACE_VALUE'][$val][$cont[$key]];
                    } else {
                        // normal data
                        $data[$i][$key] = $cont[$key];
                    }
                    $found = true;
                } elseif (isset($_SESSION['OCS']['VALUE_FIXED'][$protectedGet['tablename']][$key][$cont['ID']])) {
                    $data[$i][$key] = $_SESSION['OCS']['VALUE_FIXED'][$protectedGet['tablename']][$key][$cont['ID']];
                    $found = true;
                } elseif (array_key_exists(strtoupper($key),$cont)){ // in the case key is in lower case and array cont is in upper case
                    $data[$i][strtoupper($key)] = $cont[strtoupper($key)];
                } elseif (str_contains($key, ' AS ') || str_contains($key, ' as ')) {
                    $key_explode  = explode(" ", $key);
                    $data[$i][$key_explode[2]] = $cont[$key_explode[2]];
                }
                if (isset($_SESSION['OCS']['csv']['REPLACE_VALUE'][$protectedGet['tablename']][$key])) {
                    $data[$i][$key] = $_SESSION['OCS']['csv']['REPLACE_VALUE'][$protectedGet['tablename']][$key][$data[$i][$key]];
                }
                if (isset($_SESSION['OCS']['csv']['REPLACE_VALUE_ALL_TIME'][$protectedGet['tablename']][$key])) {
                    $data[$i][$key] = $_SESSION['OCS']['csv']['REPLACE_VALUE_ALL_TIME'][$protectedGet['tablename']][$data[$i][$_SESSION['OCS']['csv']['FIELD_REPLACE_VALUE_ALL_TIME'][$protectedGet['tablename']]]];
                }
            }
        }
        $i++;
    }

    $i = 0;
    while (isset($data[$i])) {
        $toBeWritten .= "\r\n";
        foreach ($data[$i] as $donnee) {
          if (substr($donnee, 0 , 1) != "\"") {
            $toBeWritten .= "\"";
          }
          // decode html entities (single and double quotes are preserved)
          $toBeWritten .= htmlspecialchars_decode($donnee, ENT_QUOTES);
          if (empty($donnee) || $donnee[strlen($donnee)-1] != "\"") {
            $toBeWritten .= "\"";
          }
          $toBeWritten .= $separator;
        }
        $i++;
    }
}


// Get directory of log file
if (isset($protectedGet['log']) && !preg_match("/([^A-Za-z0-9.])/", $protectedGet['log'])) {
    $Directory = $_SESSION['OCS']['LOG_DIR'] . "/";
}

// Generate output page
if ($toBeWritten != "" || (isset($Directory) && file_exists($Directory . $protectedGet['log'])) ) {

    // Work around iexplorer problem
    if (ini_get("zlib.output-compression")) {
        ini_set("zlib.output-compression", "Off");
    }

    // Static headers
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-control: private", false);
    header("Content-type: application/force-download");
    header("Content-Transfer-Encoding: binary");

    if ($toBeWritten != "") {
	// Generate output page for DB data export
        header("Content-Disposition: attachment; filename=\"export.csv\"");
        header("Content-Length: " . strlen($toBeWritten));
        echo $toBeWritten;
    } else {
	// Generate output page for log export
	$filename = $Directory . $protectedGet['log'];
        header("Content-Disposition: attachment; filename=\"" . $protectedGet['log'] . "\"");
        header("Content-Length: " . filesize($filename));
        readfile($filename);
    }

} else {

    // Nothing to export
    require_once (HEADER_HTML);
    msg_error($l->g(920));
    require_once(FOOTER_HTML);

}

die();

?>
