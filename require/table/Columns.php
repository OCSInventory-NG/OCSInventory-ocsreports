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
/**
 * Handle Column Objects arrays for the table
 */
class Columns {
    private $allcolumns;
    private $columnsvisible;

    public function __construct() {
        $this->allcolumns = array();
        $this->columnsvisible = array();
    }

    /*
     * Return an array containing all implemented columns of the Table
     */

    public function getColumnsSimple() {
        return $this->allcolumns;
    }

    /*
     * Return an array containing all implemented columns of the Table
     * 	sorted by properties
     *
     */

    public function getColumns() {
        $columnsreturn = array();
        foreach ($this->getColumnsVisible() as $visible) {
            $columnsreturn['visible'][$visible] = $this->allcolumns [$visible];
        }
        foreach ($this->getColumnsCantDel() as $cantdel) {
            $columnsreturn['cantdel'][$cantdel] = $this->allcolumns [$cantdel];
        }
        foreach ($this->getColumnsSpecial() as $special) {
            $columnsreturn['special'][$special] = $this->allcolumns [$special];
        }
    }

    /*
     * Get the displayed column with the key corresponding to $key
     *
     */

    public function getColumn($key) {
        if (array_key_exists($key, $this->allcolumns)) {
            return $this->allcolumns[$key];
        } else {
            return false;
        }
    }

    /*
     * Add a column, returning it afterwards
     */

    public function addColumn($key, $label, $visible, $deletable, $sortable) {
        $column = $this->getColumn($key);
        if (!$column) {
            $this->allcolumns[$key] = new Column($key, $label, $visible, $deletable, $sortable);
            if ($visible) {
                $this->columnsvisible[] = $key;
            }
            return $this->allcolumns[$key];
        } else {
            return $column;
        }
    }

    /*
     * Set visibility false for the column
     */

    public function hideColumn($key) {
        if ($column) {
            if (in_array($key, $this->columnsvisible)) {
                unset($this->columnsvisible[$key]);
            }
            $column->setVisible(false);
        } else {
            return false;
        }
    }

    /*
     * Set visibility true for the column
     */

    public function showColumn($key) {
        $column = $this->getColumn($key);
        if ($column) {
            if (!in_array($key, $this->columnsvisible)) {
                $this->columnsvisible[] = $key;
            }
            $column->setVisible(true);
        } else {
            return false;
        }
    }

}
?>