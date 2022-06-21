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


/**
 * Class Layout
 * Manage the layout of the table's columns in ocsreports
 * class will be used to save the current columns being displayed in the table
 * and restore a saved layout when needed
 */
class Layout {
    private $form_name;

    public function __construct($form) {
        $this->form_name = $form;
    }


    public function insertLayout($layout_name, $layout_descr, $user, $visib) {
        global $l;
        // check that we have everything before attempting to insert
        if (!empty($visib) && !empty($layout_name) && !empty($user)) {
            // check for a layout w/ same name or columns already existing for this user
            $dupli_check = $this->checkLayout($layout_name, $user, $visib);
            if (empty($dupli_check)) {
                $query = "INSERT INTO layouts (LAYOUT_NAME, USER, TABLE_NAME, DESCRIPTION, VISIBLE_COL) 
                          VALUES ('$layout_name', '$user', '".$this->form_name."', '$layout_descr', '$visib')";
                
                $result = mysql2_query_secure($query, $_SESSION['OCS']["writeServer"]);

                // check that layout was inserted
                if (isset($result) && !empty($result)) {
                    msg_success($l->g(9901));
                } else {
                    msg_error($l->g(9903));
                }

            } else {
                msg_error($dupli_check);
                return $dupli_check;
            }

        } else {
            msg_error($l->g(9904));
            return "wrong input";
        }
    }


    public function getLayout($user, $layout_name) {
        $query = "SELECT VISIBLE_COL FROM layouts WHERE USER = '".$user."' AND TABLE_NAME = '".$this->form_name."' AND LAYOUT_NAME = '".$layout_name."'";
        $result = mysql2_query_secure($query, $_SESSION['OCS']["readServer"]);
        if (isset($result) && !empty($result)) {
            $layout = mysqli_fetch_array($result);
            return $layout;
        } else {
            return false;
        }
    }


    public function deleteLayout($id) {
        global $l;
        $query = "DELETE FROM layouts WHERE ID IN ($id)";
        $result = mysql2_query_secure($query, $_SESSION['OCS']["writeServer"]);
        if (isset($result) && !empty($result)) {
            msg_success($l->g(9902));
        } else {
            msg_error($l->g(9905));
        }
    }

    // will get layout to be displayed if any selected + show the layouts select
    public function displayLayoutButtons($user, $current_tab, $table) {
        global $l;
        // if user selected a layout, get the correct columns
        if (isset($current_tab) && $current_tab != 'Add new') {
            $colus = $this->getLayout($_SESSION['OCS']['loggeduser'], $current_tab);
        }

        // display as many buttons as there are layouts for this user + an extra button to add a new layout
        $query = "SELECT * FROM layouts WHERE USER = '".$user."' AND TABLE_NAME = '".$this->form_name."'";
        $result = mysql2_query_secure($query, $_SESSION['OCS']["readServer"]);

        if (isset($result) && !empty($result)) {
            $nb_layouts = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $layout_tabs = array();

            foreach ($nb_layouts as $key => $value) {
                $layout_tabs[$value['LAYOUT_NAME']] = $value['LAYOUT_NAME'];
            }
        }

        // loop through layout_tabs and display select
        if (isset($layout_tabs) && sizeof($layout_tabs) > 0) {
            array_unshift($layout_tabs, '----');
            
            echo "<label class='control-label col-sm-4' for='layout'>".$l->g(9910)."</label>";
            echo "<div class='col-sm-8'>";
            echo "<select autocomplete='off' name='layout' id='layout' onchange=\"delete_cookie('".$this->form_name."_col');this.form.submit();\" class='form-control'>";
            
            foreach ($layout_tabs as $key => $value) {
                echo "<option value='$value'";

                if (isset($current_tab) && $current_tab == $value) {
                    echo " selected";
                    error_log('selected');
                }elseif(!isset($current_tab) && $value == '----'){
                    error_log('not selected');
                    echo " selected";
                }
                echo ">$value</option>";
            }

            echo "</select><br>";
            echo "</div>";

        }

        // redirect to ms_layouts page
        $urls = $_SESSION['OCS']['url_service'];
        $layout_url = $urls->getUrl('ms_layouts');
        $url = "index.php?function=$layout_url&value=$table&tab=add";
        echo "<a href='$url'>".$l->g(9909)."</a>";

        return $colus;
    }


    // checking for duplicate layout (same user and same name and/or columns for same table)
    private function checkLayout($layout_name, $user, $visib) {
        global $l;
        $query = "SELECT * FROM layouts WHERE USER = '$user' AND TABLE_NAME = '$this->form_name' AND (VISIBLE_COL = '$visib' OR LAYOUT_NAME = '$layout_name')";
        $result = mysql2_query_secure($query, $_SESSION['OCS']["readServer"]);
        if (isset($result) && mysqli_num_rows($result) > 0) {
            $dupli = $l->g(9906);
        } else {
            $dupli = false;
        }
        return $dupli;
    }
}
?>