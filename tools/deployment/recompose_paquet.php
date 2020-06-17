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
@session_start();
if ($_SESSION['OCS']["lvluser"] == SADMIN) {
    $valid = 'OK';
    $document_root = $_SERVER["DOCUMENT_ROOT"] . "/download/";
    $rep = $document_root = $_SERVER["DOCUMENT_ROOT"] . "/download/" . $protectedGet['id_pack'];
    $dir = opendir($rep);
    while ($f = readdir($dir)) {
        if ($protectedGet['id_pack'] == '') {
            if ($f != '.' && $f != '..') {
                echo "<a href='recompose_paquet.php?id_pack=" . $f . "'>" . $f . "</a><br>";
            }
        } else {
            if ($f == "info") {
                //récupération du fichier info
                $filename = $rep . '/' . $f;
                $handle = fopen($filename, "r");
                $info = fread($handle, filesize($filename));
                fclose($handle);
                //surpression des balises
                $info = substr($info, 1);
                $info = substr($info, 0, -1);
                //récupration par catégories du fichier
                $info_traite = explode(" ", $info);
                //récupération du nom du fichier
                $name = $info_traite[10];
                if (substr($name, 0, 4) != 'NAME') {
                    "<font color=red>PROBLEME AVEC LE NOM DU FICHIER</font><br>";
                    $valid = 'KO';
                }
                if (substr($info_traite[6], 0, 5) != 'FRAGS') {
                    "<font color=red>PROBLEME AVEC LE NOMBRE DE FRAGMENT</font><br>";
                    $valid = 'KO';
                }
                $name = substr($name, 6);
                $name = substr($name, 0, -1);
                $name = str_replace(".", "_", $name) . ".zip";
                //récupération du nombre de fragments
                $nb_frag = $info_traite[6];
                $nb_frag = substr($nb_frag, 7);
                $nb_frag = substr($nb_frag, 0, -1);
            }
        }
    }
    closedir($dir);

    if ($protectedGet['id_pack'] != '' && $valid == 'OK') {
        $temp = "";
        $i = 1;
        $filename = $rep . '/' . $protectedGet['id_pack'];
        $handfich_final = fopen($rep . '/' . $name, "a+b");
        while ($i <= $nb_frag) {
            echo "Lecture du fichier " . $filename . "-" . $i . " en cours...<br>";
            $handlefrag = fopen($filename . "-" . $i, "r+b");
            $temp = fread($handlefrag, filesize($filename . "-" . $i));
            fclose($handlefrag);
            fwrite($handfich_final, $temp);
            flush();
            $i++;
        }
        fclose($handfich_final);
        echo "<br><font color=green>FICHIER CREE</font>";
    }
} else {
    echo "PAGE INDISPONIBLE";
}
?>