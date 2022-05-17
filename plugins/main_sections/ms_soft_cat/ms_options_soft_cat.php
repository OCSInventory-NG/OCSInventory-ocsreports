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

//http://dcabasson.developpez.com/articles/javascript/ajax/ajax-autocompletion-pas-a-pas/
header('Content-Type: text/xml;charset=utf-8');
echo utf8_encode("<?xml version='1.0' encoding='UTF-8' ?><options>");

$sql = "SELECT DISTINCT NAME FROM software_name WHERE NAME NOT LIKE '%Correctif%' AND NAME NOT LIKE '%Mise a jour%' ORDER BY NAME";
$query = mysqli_query($_SESSION['OCS']["readServer"], $sql);
while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
    $liste[] = $row;
}
if (isset($_GET['debut'])) {
    $debut = utf8_decode($_GET['debut']);
} else {
    $debut = "";
}
$debut = strtolower($debut);  // met la premiere lettre en majuscule

$MAX_RETURN = 10;
$i = 0;

if (!empty($liste)) {
    foreach ($liste as $element) {
        if ($i < $MAX_RETURN && strtolower(substr($element['NAME'], 0, strlen($debut))) == $debut) {
            echo(utf8_encode("<option>" . $element['NAME'] . "</option>"));
            $i++;
        }
    }
}

echo "\n" . '</options>';
die();
?>
