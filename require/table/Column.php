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
 * Handle properties of every column of the table
 */
abstract class Column {
    private $label;
    private $key;
    private $visible;
    private $deletable;
    private $sortable;

    public function __construct($label, $visible, $deletable, $sortable) {
        $this->key = $key;
        $this->label = $label;
        $this->visible = $visible;
        $this->deletable = $deletable;
        $this->sortable = $sortable;
    }

    /*
     * Return the column display content
     */

    public function getLabel() {
        return $this->label;
    }

    /*
     * Set the column display content
     */

    public function setLabel($label) {
        $this->label = $label;
    }

    /*
     * 	@params ( false || true )
     *  Set the display options of the column :
     *  visibility : displayed or not
     * 	deletable : appears in Hide/Show list
     * 	sortable : up and down arrows on the right
     *
     */

    public function setVisible($visible) {
        $this->visible = $visible;
    }

    public function setDeletable($deletable) {
        $this->deletable = $deletable;
    }

    public function setSortable($sortable) {
        $this->sortable = $sortable;
    }

    /*
     * Return the display options of the column
     */

    public function isVisible() {
        return $this->visible;
    }

    public function isDeletable() {
        return $this->deletable;
    }

    public function isSortable() {
        return $this->sortable;
    }

}
?>