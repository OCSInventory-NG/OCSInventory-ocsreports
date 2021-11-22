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
class LinkColumn extends Column {
    private $idProperty;

    public function __construct($name, $label, private $url, $options = array()) {
        $options['formatter'] = array($this, 'formatLink');
        $this->idProperty = $options['idProperty'] ?: 'id';

        parent::__construct($name, $label, $options);
    }

    public function formatLink($record) {
        // If record is an object, try to call $record->getId(), then $record->id
        if (is_object($record)) {
            $func = $this->camelize('get_' . $this->idProperty);
            if (is_callable(array($record, $func))) {
                $id = call_user_func(array($record, $func));
            } else {
                $id = $record->{$this->idProperty};
            }
        } else {
            // Else record is an array, simply access the wanted property
            $id = $record[$this->idProperty];
        }

        // If record is an object, try to call $record->getXxx(), then $record->xxx
        if (is_object($record)) {
            $func = $this->camelize('get_' . $this->getName());
            if (is_callable(array($record, $func))) {
                $value = call_user_func(array($record, $func));
            } else {
                $value = $record->{$col->getName()};
            }
        } else {
            // Else record is an array, simply access the wanted property
            $value = $record[$col->getName()];
        }

        $id = htmlspecialchars($id);
        $value = htmlspecialchars($value);

        return '<a href="' . $this->url . $id . '">' . $value . '</a>';
    }

}
