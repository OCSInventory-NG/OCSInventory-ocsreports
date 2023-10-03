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

class Column {
    private $required;
    private $sortable;
    private $searchable;
    private $formatter;

    private $name;
    private $label;

    public function __construct($name, $label, $options = array()) {
        $options = array_merge(array(
            'required' => false,
            'sortable' => true,
            'searchable' => true,
            'formatter' => null
        ), $options);
        $this->name = $name;
        $this->label = $label;
        $this->required = $options['required'];
        $this->sortable = $options['sortable'];
        $this->searchable = $options['searchable'];
        $this->formatter = $options['formatter'];
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getLabel() {
        return $this->label;
    }

    public function setLabel($label) {
        $this->label = $label;
        return $this;
    }

    public function isRequired() {
        return $this->required;
    }

    public function setRequired($required) {
        $this->required = $required;
        return $this;
    }

    public function isSortable() {
        return $this->sortable;
    }

    public function setSortable($sortable) {
        $this->sortable = $sortable;
        return $this;
    }

    public function isSearchable() {
        return $this->searchable;
    }

    public function setSearchable($searchable) {
        $this->searchable = $searchable;
        return $this;
    }

    public function getFormatter() {
        return $this->formatter;
    }

    public function setFormatter($formatter) {
        $this->formatter = $formatter;
        return $this;
    }

    public function format($record) {
        if (is_callable($this->formatter)) {
            return call_user_func($this->formatter, $record, $this);
        } else {
            // If record is an object, try to call $record->getXxx(), then $record->xxx
            if (is_object($record)) {
                $func = $this->camelize('get_' . $this->name);
                if (is_callable(array($record, $func))) {
                    $value = call_user_func(array($record, $func));
                } else {
                    $value = $record->{$this->name};
                }
            } else {
                // Else record is an array, simply access the wanted property
                $value = $record[$this->name];
            }

            $value = htmlspecialchars($value);

            if ($this->formatter && is_string($this->formatter)) {
                return sprintf($this->formatter, $value);
            } else {
                return $value;
            }
        }
    }

    protected function camelize($str) {
        return preg_replace_callback('/(^|_)([a-z])/', function($match) {
            return strtoupper($match[2]);
        }, $str);
    }

}
?>