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

class TableRenderer {
    private static $jsIncluded = false;

    public function show($table, $records, $options = array()) {
        global $l, $protectedPost;

        $options = array_merge_recursive(array(
            'paginate' => array(
                'offset' => 0,
                'limit' => -1
            ),
            'visible' => array()
                ), $options);

        $this->includeJS();
        $this->callJS($table, $options);
    }

    private function includeJS() {
        global $l;

        if (!self::$jsIncluded) {
            $lang = array(
                "sEmptyTable" => $l->g(1334),
                "sInfo" => $l->g(1335),
                "sInfoEmpty" => $l->g(1336),
                "sInfoFiltered" => $l->g(1337),
                "sInfoPostFix" => "",
                "sInfoThousands" => $l->g(1350),
                "decimal" => $l->g(1351),
                "sLengthMenu" => $l->g(1338),
                "sLoadingRecords" => $l->g(1339),
                "sProcessing" => $l->g(1340),
                "sSearch" => $l->g(1341),
                "sZeroRecords" => $l->g(1342),
                "oPaginate" => array(
                    "sFirst" => $l->g(1343),
                    "sLast" => $l->g(1344),
                    "sNext" => $l->g(1345),
                    "sPrevious" => $l->g(1346),
                ),
                "oAria" => array(
                    "sSortAscending" => ": " . $l->g(1347),
                    "sSortDescending" => ": " . $l->g(1348),
                )
            );

            echo '<script>';
            require 'require/tables/tables.js';
            echo 'tables.language = ' . json_encode($lang) . ';';
            echo '</script>';

            self::$jsIncluded = true;
        }
    }

    private function callJS($table, $options) {
        global $protectedPost;

        $tableName = json_encode(htmlspecialchars($table->getName()));
        $csrfNumber = json_encode(htmlspecialchars($_SESSION['OCS']['CSRFNUMBER']));

        $url = isset($_SERVER['QUERY_STRING']) ? "ajax.php?" . $_SERVER['QUERY_STRING'] : "";
        $url = json_encode($url . '&no_header=true&no_footer=true');

        $postData = json_encode($protectedPost);
        $columns = json_encode($this->showColumns($table, $options));

        require 'require/tables/table.html.php';
    }

    private function showColumns($table, $options) {
        $columns = array();

        foreach ($table->getColumns() as $name => $col) {
            $columns [] = $this->showColumn($name, $col, $options);
        }

        return $columns;
    }

    public function showColumn($name, $col, $options) {
        $visible = $col->isRequired() || in_array($name, $options['visible']);
        $sortable = $col->isSortable();
        $searchable = $col->isSearchable();

        return array(
            'data' => $name,
            'class' => 'column-' . $name,
            'name' => $name,
            'defaultContent' => ' ',
            'orderable' => $sortable,
            'searchable' => $searchable,
            'visible' => $visible
        );
    }

}
?>