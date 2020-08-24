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

/**
 * Get all files of a directory
 * @param string $directory path of the directory
 * @param string $filetype
 * @return boolean|string list of files
 */
function scanDirectory($directory, $filetype = '.') {
    // Is it a directory ?
    if (!is_dir($directory)) {
        return false;
    }
    $myDirectory = @opendir($directory);
    // Can it be opened ?
    if (!$myDirectory) {
        return false;
    }
    // For every content on the directory
    while ($entry = @readdir($myDirectory)) {
        // I'm searching a filetype & I found a file ?
        if (substr($entry, -strlen($filetype)) == $filetype && $filetype != ".") {
            // Get the file !
            $data['name'][] = $entry;
            $data['date_create'][] = date("d M Y H:i:s.", filectime($directory . $entry));
            $data['date_modif'][] = date("d M Y H:i:s.", filemtime($directory . $entry));
            $data['size'][] = filesize($directory . $entry);
        } elseif ($filetype == ".") {
            if (!strripos($entry, $filetype)) {
                $data[$entry] = $entry;
            }
            unset($data['.'], $data['..']);
        }
    }
    closedir($myDirectory);
    return $data;
}

/**
 *
 * @param string $ms_cfg_file name of file to read
 * @param type $search array of values to find like array('PAGE_PROFIL'=>'MULTI','RESTRICTION'=>'SINGLE','ADMIN_BLACKLIST'=>'SINGLE')
 * <br />SINGLE => You have only one value to read
 * <br />MULTI => You have few values to read
 * @param type $id_field
 * @return string
 */
function read_configuration($ms_cfg_file, $search, $id_field = '') {
    if (!is_readable($ms_cfg_file)) {
        return "NO_FILES";
    }
    $fd = fopen($ms_cfg_file, "r");
    $capture = '';
    while (!feof($fd)) {
        $line = trim(fgets($fd, 256));
        if (substr($line, 0, 2) == "</") {
            $capture = '';
        }
        if ($capture != '') {
            foreach ($search as $value_2_search => $option) {
                if ($capture == 'OK_' . $value_2_search) {
                    if (strstr($line, ':')) {
                        $tab_lbl = explode(":", $line);
                        $find[$value_2_search][$tab_lbl[0]] = $tab_lbl[1];
                    } elseif ($option == 'SINGLE') {
                        $find[$value_2_search] = $line;
                    } elseif ($option == 'MULTI') {
                        $find[$value_2_search][$line] = $line;
                    } elseif ($option == 'MULTI2') {
                        //Fix your id with a field file (the first field only)
                        if ($id_field != '' && $value_2_search == $id_field) {
                            $id = $line;
                        }
                        if (isset($id)) {
                            $find[$value_2_search][$id] = $line;
                        } else {
                            $find[$value_2_search][] = $line;
                        }
                    }
                }
            }
        }
        if ($line[0] == "<") {  //Getting tag type for the next launch of the loop
            $capture = 'OK_' . substr(substr($line, 1), 0, -1);
        }
    }
    fclose($fd);
    return $find;
}

function update_config_file($ms_cfg_file, $new_value, $sauv = 'YES') {
    if ($sauv == 'YES') {
        getcopy_config_file($ms_cfg_file);
    }
    $ms_cfg_file = $_SESSION['OCS']['CONF_PROFILS_DIR'] . $ms_cfg_file . "_config.txt";

    $new_ms_cfg_file = '';
    foreach ($new_value as $key_val => $val) {
        $new_ms_cfg_file .= "<" . $key_val . ">\n";
        foreach ($val as $key_value => $value) {
            if (is_defined($value)) {
                $new_ms_cfg_file .= $key_value . ':' . $value . "\n";
            } else {
                $new_ms_cfg_file .= $key_value . "\n";
            }
        }
        $new_ms_cfg_file .= "</" . $key_val . ">\n\n";
    }
    $file = fopen($ms_cfg_file, "w+");
    fwrite($file, $new_ms_cfg_file);
    fclose($file);
}

function getcopy_config_file($ms_cfg_file, $record = 'YES', $sauv = false) {
    if ($record != 'YES') {
        return false;
    }
    if (!$sauv) {
        $newfile = $_SESSION['OCS']['OLD_CONF_DIR'] . $ms_cfg_file . '_config_old_' . time();
    } else {
        $newfile = $_SESSION['OCS']['CONF_PROFILS_DIR'] . $sauv . '_config.txt';
    }

    $ms_cfg_file = $_SESSION['OCS']['CONF_PROFILS_DIR'] . $ms_cfg_file . "_config.txt";
    @copy($ms_cfg_file, $newfile);
    return true;
}

function parse_xml_file($file, $tag, $separe) {
    $tab_data = array();
    // open file
    if (!is_readable($file)) {
        return "NO_FILES";
    }
    $fp = fopen($file, "r");
    $i = 0;
    // read line
    while ($ln = fgets($fp, 1024)) {
        $ln = preg_replace('(\r\n|\n|\r|\t| )', '', $ln);
        foreach ($tag as $key) {
            if (substr($ln, 0, strlen($key) + 2) == '<' . $key . '>') {
                $search = array("<" . $key . ">", "</" . $key . ">");
                $replace = array('', '');
                $tab_data[$i][$key] = str_replace($search, $replace, $ln);
            }
        }
        if ($ln == "</" . $separe . ">") {
            $i++;
        }
    }
    fclose($fp);
    return ($tab_data);
}

function post_ocs_file_to_server($datastream, $url, $port) {
    $url = preg_replace("@^http://@i", "", $url);
    $host = substr($url, 0, strpos($url, "/"));
    $uri = strstr($url, "/");
    $reqbody = $datastream;

    $contentlength = strlen($reqbody);
    $reqheader = "POST $uri HTTP/1.1\r\n" .
            "Host: $host\r\n" . "User-Agent: OCS_local_" . GUI_VER . "\r\n" .
            "Content-type: application/x-compress\r\n" .
            "Content-Length: $contentlength\r\n\r\n" .
            "$reqbody\r\n";

    $socket = @fsockopen($host, $port, $errno, $errstr);

    if (!$socket) {
        $result["errno"] = $errno;
        $result["errstr"] = $errstr;
        return $result;
    }
    fputs($socket, $reqheader);

    while (!feof($socket)) {
        $result[] = fgets($socket, 4096);
    }

    fclose($socket);
    return $result;
}

?>
