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
 *  if your version of ocs < 2.0, your tag are in this table but not in accountinfo_config
 * so we have to add them.
 */

//show all columns in accountinfo table
$sql = "show columns from accountinfo";
$res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
while ($value = mysqli_fetch_object($res)) {
    if ($value->Field != 'HARDWARE_ID' && $value->Field != 'TAG') {
        $list_field[$value->Field] = $value->Field;
    }
    $type_field[$value->Field] = $value->Type;
}
$fields_table = array('ID', 'NAME_ACCOUNTINFO', 'TYPE,NAME', 'ID_TAB', 'COMMENT', 'SHOW_ORDER', 'ACCOUNT_TYPE');
$sql = prepare_sql_tab($fields_table);
$sql['SQL'] .= "from accountinfo_config where ACCOUNT_TYPE='COMPUTERS'";
$res = mysql2_query_secure($sql['SQL'], $_SESSION['OCS']["readServer"], $sql['ARG']);
while ($value = mysqli_fetch_object($res)) {
    if (!isset($max_order) || $value->SHOW_ORDER > $max_order) {
        $max_order = $value->SHOW_ORDER;
    }
    if ($value->NAME_ACCOUNTINFO != 'TAG') {
        //this column does'nt exist in accountinfo_config
        if (!$list_field['fields_' . $value->ID]) {
            //add this column in accountinfo_config
            $sql_column_account = "ALTER TABLE accountinfo ADD COLUMN %s VARCHAR(255) default NULL";
            $arg = "fields_" . $value->ID;
            if (is_defined($protectedPost['EXE'])) {
                mysql2_query_secure($sql_column_account, $_SESSION['OCS']["writeServer"], $arg);
                addLog('SCRIPT_ADD_COLUMN_ACCOUNTINFO', $arg);
            } else {
                $add_colum_accountinfo[] = $arg;
            }
        }
        $name_accountinfo["fields_" . $value->ID] = "fields_" . $value->ID;
    }
}
//for each column we are going to verify that this field exist in accountinfo_config
if (is_array($list_field)) {
    foreach ($list_field as $name) {
        //if this name does'nt exist in accuontinfo_config
        if (!isset($name_accountinfo[$name])) {
            //echo $name_accountinfo[$name]."=>".$name."<br>";
            unset($fields_table, $values);
            $fields_table = array('TYPE', 'NAME', 'ID_TAB', 'COMMENT', 'SHOW_ORDER', 'ACCOUNT_TYPE');
            $max_order++;

            if ($type_field[$name] == "varchar(10)" || $type_field[$name] == "date") {
                $type = 6;
                $type_field[$name] = "varchar(10)";
            } elseif ($type_field[$name] == "blob") {
                $type = 5;
            } elseif ($type_field[$name] == "varchar(255)") {
                $type = 0;
            } else {
                $type = 0;
            }
            $sql = "insert into accountinfo_config ";
            $sql = mysql2_prepare($sql, '', $fields_table, true);
            $values = array($type, $name, 1, $name . " (" . $l->g(2101) . ")", $max_order, 'COMPUTERS');
            $sql = mysql2_prepare($sql['SQL'] . " VALUES ", $sql['ARG'], $values);

            if (is_defined($protectedPost['EXE'])) {
                mysql2_query_secure($sql['SQL'], $_SESSION['OCS']["writeServer"], $sql['ARG']);
            } else {
                $add_lign_accountinfo_config[] = $sql['ARG'];
            }
            $sql_alter = "ALTER TABLE accountinfo CHANGE %s  %s %s";
            $arg = array($name, "fields_" . mysqli_insert_id($_SESSION['OCS']["writeServer"]), $type_field[$name]);

            if (is_defined($protectedPost['EXE'])) {
                mysql2_query_secure($sql_alter, $_SESSION['OCS']["writeServer"], $arg);
                addLog('SCRIPT_ADD_DATA_ACCOUNTINFO_CONFIG', $name);
            } else {
                $rename_col_accountinfo[] = $arg;
            }
        }
    }
}
$add_colum_accountinfo = array('1', '2');
if (isset($add_colum_accountinfo) || isset($add_lign_accountinfo_config) || isset($rename_col_accountinfo)) {
    $form_name = "console";
    echo open_form($form_name, '', '', 'form-horizontal');
    echo "<p><b>This script is going to help you to update your old admin info<br>";
    echo "to the new version 2.0 </b> </p>";
    echo "<input type=submit class='btn btn-success' id='EXE' name='EXE'>";
    echo close_form();
    echo '<div class="col col-md-12" >';
    echo "<p><font size=4><i>Summary of actions to be undertaken</i></font></p>";
    if (isset($add_colum_accountinfo)) {
        echo "<p><b><span class=red>add column in accountinfo table<br>
					(orphans found in accountinfo_config table (Inconsistency))</span></b></p>";
        foreach ($add_colum_accountinfo as $key => $values) {
            echo $values . "<br>";
        }
    }

    if (isset($add_lign_accountinfo_config)) {
        echo "<p><b><span class=blue>add lignes in accountinfo_config table<br>
					(orphans found in accountinfo table (=> 2.0))</span></b></p>";
        foreach ($add_lign_accountinfo_config as $key => $values) {
            $i = 0;
            echo "<p>";
            while (isset($values[$i])) {
                echo $values[$i];
                echo "&nbsp;";
                if ($i == 5) {
                    echo "<br />";
                }
                $i++;
            }
            echo "</p>";
        }
    }
    if (isset($rename_col_accountinfo)) {
        echo "<p><b><span class=blue>Renaming of old columns in accountinfo table
					<br>(=> 2.0)</span></b></p>";
        foreach ($rename_col_accountinfo as $key => $values) {
            echo $values[0] . "<br>";
        }
    }
    echo "</div>";
} else {
    echo "<font size=4 color=blue>" . $l->g(2105) . "</font>";
}
?>