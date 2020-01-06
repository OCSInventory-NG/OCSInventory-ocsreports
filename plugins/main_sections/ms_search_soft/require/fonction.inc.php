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

function remplirListe($input_name, $label = '') {
    global $protectedPost;
    //requete SQL avec filtre sur les logiciels des pc linux et Correctifs, mise a jour windows
    $sql = "SELECT DISTINCT softwares.NAME FROM softwares_name_cache softwares  WHERE  softwares.NAME NOT LIKE '%Correctif%' AND softwares.NAME NOT LIKE '%Mise a jour%' ORDER BY softwares.NAME";

    $query = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
    //remplit la liste deroulante
    $name[""] = "";
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $name[$row['NAME']] = $row['NAME'];
    }
    formGroup('select', $input_name, $label, '', '', $protectedPost[$input_name], '', $name, $name);
    //echo show_modif($name, $input_name, 2, '', '');
}

function creerTableau($var) {  //$var est le $_post de mon script.php
    echo "<br /><b><i>Vous avez choisi :<br />" . $var . "</i></b>";
    $sql_version = "SELECT hardware.NAME AS 'hnom',hardware.IPADDR AS 'ip',hardware.WORKGROUP AS 'domaine', softwares.NAME AS 'snom', softwares.VERSION AS 'sversion',softwares.FOLDER as 'sfold' FROM hardware INNER JOIN softwares ON softwares.HARDWARE_ID =hardware.ID WHERE softwares.NAME='$var' ORDER BY softwares.VERSION";
    $query_version = mysql2_query_secure($sql_version, $_SESSION['OCS']["readServer"]);
    $html_data .= "<table>\n";
    $html_data .= "<tr><th>Nom du PC   </th><th>Nom du logiciel   </th><th>Version du logiciel </th><th>Repertoire</th><th>Adresse IP</th><th>Domaine</th></tr> ";
    while ($row = $query_version->fetch(PDO::FETCH_ASSOC)) {
        if ($row['sfold'] == "") {
            $row['sfold'] = "&nbsp";
        }
        if ($row['sversion'] == "") {
            $row['sversion'] = "&nbsp";
        }
        $html_data .= "\n<tr><td style='color: blue'>" . $row['hnom'] . " </td><td style='color : green'>" . $row['snom'] . " </td><td style='color : red'> " . $row['sversion'] . "<td style='color : black'>" . $row['sfold'] . "</td><td style='color : blue'>" . $row['ip'] . "</td><td style='color: blue'>" . $row['domaine'] . "</td></tr>";
    }
    $html_data .= "</table>";
    echo $html_data;
}

function csv($var) {
    $sql_version = "SELECT hardware.NAME AS 'hnom',softwares.NAME AS 'snom',softwares.VERSION AS 'sversion', softwares.FOLDER as 'sfold', hardware.IPADDR AS 'ip',hardware.WORKGROUP AS 'domaine' FROM hardware INNER JOIN softwares ON softwares.HARDWARE_ID =hardware.ID WHERE softwares.NAME='$var' ORDER BY softwares.VERSION";
    $query_version = mysql2_query_secure($sql_version, $_SESSION['OCS']["readServer"]);
    print "nom du PC;" . "Nom du logiciel;" . "Version du logiciel;" . "Repertoire;" . "Adresse IP;" . "Domaine;" . "\n\n\n";
    while ($row = $query_version->fetch()) {
        print '"' . stripslashes(implode('";"', $row)) . "\"\n";
    }
    exit;
}

?>
