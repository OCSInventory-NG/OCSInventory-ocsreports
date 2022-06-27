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

if (!AJAX) {
    if (isset($_SESSION['OCS']['DEBUG']) && $_SESSION['OCS']['DEBUG'] == 'ON') {
        if (isset($_SESSION['OCS']['SQL_DEBUG'])) {
            msg_info("<b>" . $l->g(5001) . "</b><br><br>" . implode('<br><hr>', $_SESSION['OCS']['SQL_DEBUG']));
        }
        echo "<hr/>";
        echo "<div align=center>VAR POST</div>";
        if (isset($protectedPost)) {
            print_r_V2($protectedPost);
        }
        echo "<hr/>";
        echo "<div align=center>VAR SESSION</div>";
        foreach ($_SESSION['OCS'] as $key => $value) {

            if ($key != "fichLang" && $key != "LANGUAGE_FILE" && $key != "mac" && $key != "writeServer" && $key != "readServer") {
                $tab_session[$key] = $value;
            }
        }
        if (isset($tab_session)) {
            print_r_V2($tab_session);
        }
    }

    $fin = microtime(true);
    if (isset($_SESSION['OCS']['DEBUG']) && $_SESSION['OCS']["DEBUG"] == "ON") {
        echo "<b>CACHE:&nbsp;<font color='" . ($_SESSION['OCS']["usecache"] ? "green'><b>ON</b>" : "red'><b>OFF</b>") . "</font>&nbsp;&nbsp;&nbsp;<font color='black'><b>" . round($fin - $debut, 3) . " secondes</b></font>&nbsp;&nbsp;&nbsp;";
        echo "<script language='javascript'>document.getElementById(\"tps\").innerHTML=\"<b>" . round($fin - $debut, 3) . " secondes</b>\"</script>";
    }
    echo open_form('ACTION_CLIC');
    echo "<input type='hidden' name='RESET' id='RESET' value=''>";
    echo "<input type='hidden' id='LANG' name='LANG' value=''>";
    echo close_form();

    echo '</body></html>';
}
?>