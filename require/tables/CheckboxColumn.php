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
require_once('require/tables/Column.php');

class CheckboxColumn extends Column {

    public function __construct($idProperty = 'id') {
        parent::__construct('_checkbox', '<input type="checkbox" class="check-all" name="check-all"/>', array(
            'required' => true,
            'sortable' => false,
            'searchable' => false,
            'formatter' => function($record, $col) use ($idProperty) {
                // If record is an object, try to call $record->getId(), then $record->id
                if (is_object($record)) {
                    $func = $this->camelize('get_' . $idProperty);
                    if (is_callable(array($record, $func))) {
                        $id = call_user_func(array($record, $func));
                    } else {
                        $id = $record->$idProperty;
                    }
                } else {
                    // Else record is an array, simply access the wanted property
                    $id = $record[$idProperty];
                }

                $id = htmlspecialchars($id);

                return '<input type="checkbox" class="check-row" name="check[' . $id . ']"/>';
            }
        ));
    }

}
?>