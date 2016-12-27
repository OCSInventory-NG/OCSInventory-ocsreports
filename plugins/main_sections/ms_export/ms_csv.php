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

//log directory
if (isset($protectedGet['log']) && !preg_match("/([^A-Za-z0-9.])/", $protectedGet['log'])) {
    $Directory = $_SESSION['OCS']['LOG_DIR'] . "/";
}

if (isset($Directory) && file_exists($Directory . $protectedGet['log'])) {
    $tab = file($Directory . $protectedGet['log']);
    while (list($cle, $val) = each($tab)) {
        $toBeWritten .= $val . "\r\n";
    }
    $filename = $protectedGet['log'];
} elseif (isset($_SESSION['OCS']['csv']['SQL'][$protectedGet['tablename']])) {
    $toBeWritten = "";
    //gestion des entetes
    foreach ($_SESSION['OCS']['visible_col'][$protectedGet['tablename']] as $name => $nothing) {
        if ($name != 'SUP' && $name != 'CHECK' && $name != 'NAME' && $name != 'ACTIONS') {
            if ($_SESSION['OCS']['visible_col'][$protectedGet['tablename']][$name]{1} == ".") {
                $lbl = substr(strrchr($_SESSION['OCS']['visible_col'][$protectedGet['tablename']][$name], "."), 1);
            } else {
                $lbl = $_SESSION['OCS']['visible_col'][$protectedGet['tablename']][$name];
            }
            $col[$lbl] = $name;
            $toBeWritten .= $name . $separator;
        } elseif ($name == 'NAME' || $name == $l->g(23)) {
            $col['name_of_machine'] = "name_of_machine";
            $toBeWritten .= $l->g(23) . $separator;
        }
    }
    //data fixe
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

    if ($_SESSION['OCS']['csv']['ARG'][$protectedGet['tablename']]) {
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
        foreach ($col as $field => $lbl) {
            if ($lbl == "name_of_machine" && !isset($cont[$field])) {
                $field = 'name';
            }

            $found = false;
            // find value case-insensitive
            foreach ($cont as $key => $val) {
                if (strtolower($key) == strtolower($field)) {
                    if (($field == 'TAG' || substr($field, 0, 7) == 'fields_') && isset($inter['TAB_OPTIONS']['REPLACE_VALUE'][$lbl])) {
                        // administrative data
                        $data[$i][$lbl] = $inter['TAB_OPTIONS']['REPLACE_VALUE'][$lbl][$val];
                    } else {
                        // normal data
                        $data[$i][$lbl] = $val;
                    }

                    $found = true;
                    break;
                } elseif (isset($_SESSION['OCS']['VALUE_FIXED'][$protectedGet['tablename']][$lbl][$cont['ID']]) && isset($cont['ID'])) {
                    $data[$i][$lbl] = $_SESSION['OCS']['VALUE_FIXED'][$protectedGet['tablename']][$lbl][$cont['ID']];
                    $found = true;
                    break;
                }
            }
            if (isset($_SESSION['OCS']['csv']['REPLACE_VALUE'][$protectedGet['tablename']][$key])) {
                $data[$i][$key] = $_SESSION['OCS']['csv']['REPLACE_VALUE'][$protectedGet['tablename']][$key][$data[$i][$key]];
            }
            if (isset($_SESSION['OCS']['csv']['REPLACE_VALUE_ALL_TIME'][$protectedGet['tablename']][$key])) {
                $data[$i][$key] = $_SESSION['OCS']['csv']['REPLACE_VALUE_ALL_TIME'][$protectedGet['tablename']][$data[$i][$_SESSION['OCS']['csv']['FIELD_REPLACE_VALUE_ALL_TIME'][$protectedGet['tablename']]]];
            }
            if (!$found) {
                // find values case-insensitive
                if (!is_null($data_fixe[$cont['ID']])) {
                    foreach ($data_fixe[$cont['ID']] as $key => $val) {
                        if (strtolower($key) == strtolower($field) && isset($data_fixe[$cont['ID']][$key])) {
                            $data[$i][$lbl] = $data_fixe[$cont['ID']][$key];

                            $found = true;
                            break;
                        }
                    }
                }

                if (!$found) {
                    $data[$i][$lbl] = "";
                }
            }
        }
        $i++;
    }
    $i = 0;
    while ($data[$i]) {
        $toBeWritten .= "\r\n";
        foreach ($data[$i] as $field_name => $donnee) {
            $toBeWritten .= $donnee . $separator;
        }
        $i++;
    }

    $filename = "export.csv";
}
if ($toBeWritten != "") {
    // iexplorer problem
    if (ini_get("zlib.output-compression")) {
        ini_set("zlib.output-compression", "Off");
    }

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-control: private", false);
    header("Content-type: application/force-download");
    header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . strlen($toBeWritten));
    echo $toBeWritten,
    die();
} else {
    require_once (HEADER_HTML);
    msg_error($l->g(920));
    require_once(FOOTER_HTML);
    die();
}
?>