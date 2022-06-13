<?php
/*
 * Copyright 2022 OCSInventory-NG/OCSInventory-ocsreports contributors.
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

// create a class Layout to manage the layout of the table's columns in ocsreports
// class will be used to save the current columns being displayed in the table
// and restore a saved layout when needed
// not allowing the user to update a layout is a arbitrary choice bc too much trouble
class Layout {
    private $form_name;

    public function __construct($form) {
        $this->form_name = $form;
    }


    public function insertLayout($layout_name, $user, $cols) {
        // check that we have everything before attempting to insert
        if (!empty($cols) && !empty($layout_name) && !empty($user)) {
            // check for a layout w/ same name or columns already existing for this user
            $dupli_check = $this->checkLayout($layout_name, $user, $cols);
            if (empty($dupli_check)) {
                $query = "INSERT INTO layouts (LAYOUT_NAME, USER, TABLE_NAME, COLUMNS) VALUES ('$layout_name', '$user', '".$this->form_name."', $cols)";
                $result = mysql2_query_secure($query, $_SESSION['OCS']["writeServer"]);

                // check that layout was inserted
                if ($result) {
                    msg_success("Layout '$layout_name' created");
                } else {
                    msg_error("Error creating layout '$layout_name'");
                }

            } else {
                msg_error($dupli_check);
            }

        } else {
            msg_error("Please provide a layout name");
        }
    }


    public function getLayout($user, $layout_name) {
        $query = "SELECT COLUMNS FROM layouts WHERE USER = '".$user."' AND TABLE_NAME = '".$this->form_name."' AND LAYOUT_NAME = '".$layout_name."'";
        // error_log($query);
        $result = mysql2_query_secure($query, $_SESSION['OCS']["readServer"]);
        if ($result) {
            $layout = mysqli_fetch_array($result);
            return $layout;
        } else {
            return false;
        }
    }


    public function deleteLayout() {

    }

    // will get layout to be displayed if any selected + show the layouts buttons
    public function displayLayoutButtons($user, $current_tab) {
        // error_log(print_r($current_tab, true));
        // if user selected a layout, get the correct columns
        if (isset($current_tab) && $current_tab != 'Add new') {
            $colus = $this->getLayout($_SESSION['OCS']['loggeduser'], $current_tab);
        } elseif (isset($current_tab) && $current_tab == 'Add new') {
            // redirect to ms_layouts page
            $urls = $_SESSION['OCS']['url_service'];
            $layout_url = $urls->getUrl('ms_layouts');
            //error_log($layout_url);
            $url = "index.php?function=$layout_url&value=$this->form_name&tab=add";
            change_window($url);
        }


        // display as many buttons as there are layouts for this user + an extra button to add a new layout
        $query = "SELECT * FROM layouts WHERE USER = '".$user."' AND TABLE_NAME = '".$this->form_name."'";
        $result = mysql2_query_secure($query, $_SESSION['OCS']["readServer"]);
        $nb_layouts = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $layout_tabs = array();

        foreach ($nb_layouts as $key => $value) {
            $layout_tabs[$value['LAYOUT_NAME']] = $value['LAYOUT_NAME'];
        }
        array_push($layout_tabs, "Add new");
        $this->layout_tabs = $layout_tabs;


        // loop through layout_tabs and display a link for each one
        foreach ($layout_tabs as $key => $value) {
            // display buttons ('add new' takes user to layouts page / others display correct layout of cols)
            if ($value == 'Add new') {
                echo "<small><input type='submit' name='layout' value='".$value."'></small>";
            } else {
                echo "<small><input type='submit' name='layout' value='".$value."' onclick='delete_cookie(\"" . $this->form_name . "_col\");'></small>";
            }

        }

        return $colus;
    }


    // checking for duplicate layout (same user and same name and/or columns)
    private function checkLayout($layout_name, $user, $cols) {
        $query = "SELECT * FROM layouts WHERE USER = '$user' AND COLUMNS = $cols OR LAYOUT_NAME = '$layout_name'";
        $result = mysql2_query_secure($query, $_SESSION['OCS']["writeServer"]);
        if (mysqli_num_rows($result) > 0) {
            $dupli = ("A layout with the same name or columns already exists for your user, please choose another name or update the existing one");
        } else {
            $dupli = false;
        }
        return $dupli;
    }

}
?>