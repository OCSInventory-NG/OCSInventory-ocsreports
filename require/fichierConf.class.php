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
$tab_dont_see = array(527, 528, 529, 530, 531, 532, 533, 534, 535, 536, 537, 538, 539, 540, 541, 542, 543, 544, 545);

class language {
    protected $tableauMots;    // tableau contenant tous les mots du fichier
    protected $plug_language;

    function __construct($language, $plugin = '') { // constructeur
        if ($plugin != '') {
            require_once('require/function_files.php');
            $rep_list = scanDirectory(MAIN_SECTIONS_DIR);
            foreach ($rep_list as $key) {
                if (file_exists(MAIN_SECTIONS_DIR . $key . '/language/' . $language . ".txt")) {
                    $file = fopen(MAIN_SECTIONS_DIR . $key . '/language/' . $language . ".txt", "r");
                    while (!feof($file)) {
                        $val = fgets($file, 1024);
                        $tok1 = rtrim(strtok($val, " "));
                        $tok2 = rtrim(strtok(""));
                        $this->plug_language[$tok1] = $tok2;
                    }
                    fclose($file);
                    echo MAIN_SECTIONS_DIR . $key . '/language/' . $language . ".txt<br>";
                }
            }
        }

        $language_file = PLUGINS_DIR . "language/" . $language . "/" . $language . ".txt";
        if (file_exists($language_file)) {
            $file = fopen($language_file, "r");
            if ($file) {
                while (!feof($file)) {
                    $val = fgets($file, 1024);
                    $tok1 = rtrim(strtok($val, " "));
                    $tok2 = rtrim(strtok(""));
                    $this->tableauMots[$tok1] = $tok2;
                }
                fclose($file);
            }
        }
    }

    function addExternalLangFile($path){
        if(file_exists($path)){
            $externalFile = fopen($path, "r");
            while (!feof($externalFile)) {
                $val = fgets($externalFile, 1024);
                $tok1 = rtrim(strtok($val, " "));
                $tok2 = rtrim(strtok(""));
                $this->tableauMots[$tok1] = $tok2;
            }
            fclose($externalFile);
        }
    }

    function g($i) {
        global $tab_dont_see;
        //If word doesn't exist for language, return default english word
        if ($this->tableauMots[$i] == null) {
            $defword = new language('en_GB');
            $word = $defword->tableauMots[$i];
        } else {
            $word = $this->tableauMots[$i];
        }
        //language mode
        if (isset($_SESSION['OCS']['MODE_LANGUAGE']) && $_SESSION['OCS']['MODE_LANGUAGE'] == "ON") {
            if (!in_array($i, $tab_dont_see)) {
                $_SESSION['OCS']['EDIT_LANGUAGE'][$i] = $word;
            }
            $word .= "{" . $i . "}";
        }
        return stripslashes($word);
    }

    function g_plug($i) {
        if ($this->plug_language[$i] == null) {
            $defword = new language('en_GB', 'plugin');
            $word = $defword->plug_language[$i];
        } else {
            $word = $this->plug_language[$i];
        }
        return stripslashes($word);
    }

}
?>
